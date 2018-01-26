#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
"""
merges database workDB into database matchDB.
does not take any data from originalData
Try to repair obvious errors
"""
import argparse, time, sys, os
import pickle
import numpy as np 
import shutil
import codecs, locale
locale.setlocale(locale.LC_ALL, 'en_US.UTF-8') #sorting??
sys.stdout = codecs.getwriter('UTF-8')(sys.stdout)
from errRelationUtils import sanity, repairChild, repairFam, repairRel
import common

parser = argparse.ArgumentParser()
parser.add_argument("workDB", help="Working database name" )
parser.add_argument("matchDB", help="Database to match against")
parser.add_argument("--noStrict", help="Accept relations errors", action='store_true' )
args = parser.parse_args()
workDB = args.workDB
matchDB = args.matchDB
noStrict = args.noStrict

dbName  = os.path.basename(workDB).split('.')[0]  #No '.' or '/' in databasenames
mDBname = os.path.basename(matchDB).split('.')[0]
config = common.init(dbName, matchDBName=mDBname, indexes=True)
common.config = config

ERRORS = False
WARNINGS = False
CHANGES = []
print 'Merging', dbName, 'into', mDBname
if noStrict:
    print 'Accept relation errors when merging'
    strict = False
else:
    print 'Do not accept relation errors when merging'
    strict = True
from mergeUtils import createMapSimple, mergeEvent
from mergeUtils import Imap, reverseImap, Fmap, reverseFmap, Fignore

t0 = time.time()

print 'Doing sanity checks'
if config['match_originalData'].find_one({'type': 'admin', 'mergedWith': dbName}):
    print mDBname, 'already merged with', dbName, '-- Exiting'
    sys.exit()
#if status in statManual exists => exit   ???????????
if config['matches'].find({'status': {'$in': list(common.statManuell)}}).count():
    print 'WARNING Person matches with manual status exists'
    WARNINGS = True
if config['fam_matches'].find({'status': {'$in': list(common.statManuell)}}).count():
    print 'WARNING Family matches with manual status exists'
    WARNINGS = True
#if contributionId in orgData => multiMerge => exit
print 'Time:',time.time() - t0

print 'Creating match map persons/families'
if createMapSimple(config): #Creates Imap, Fmap
    print 'WARNING multi-mappings present - might cause problems in merging'
    WARNINGS = True

print 'Time:',time.time() - t0

print 'Process flags'
relIgnore = []
for flag in config['flags'].find():
    if flag['typ'] == 'IgnoreRelation':
        relIgnore.append({'persId': flag['persId'], 'relTyp': flag['relTyp'], 'famId': flag['famId']})
    elif flag['typ'] == 'IgnoreFamilyMatch':
        Fmap[flag['workFam']].remove(flag['matchFam'])
        if not Fmap[flag['workFam']]: del(Fmap[flag['workFam']])
        #reverseFmap
        #reverseFmap[flag['matchFam']].remove(flag['workFam'])
        #if not reverseFmap[flag['matchFam']]: del(reverseFmap[flag['matchFam']])
    else:
        print 'Unknown flag:', flag

def inRelIgnore(relation):
    global relIgnore
    for r in relIgnore:
        if cmp(r, relation) == 0:
            return True
    return False

print 'Time:',time.time() - t0

def sanityChecks(config):
    global ERRORS
    global WARNINGS
    (childErr, famErr, relErr) = sanity(config['match_persons'], config['match_families'],
                                    config['match_relations'])
    for (pers, chFams) in childErr:
        print 'Relation ERROR Child', pers['name'], pers['_id'], chFams
        ERRORS = True
    for (famId, persList) in famErr:
        pStr = ''
        for p in persList:
            pStr += p['_id']+' '+p['name']+';'
        print 'Relation ERROR Family', famId, 'have multiple husb/wife', pStr
        ERRORS = True
    for p in relErr:
        print 'Relation WARNING Person without relations:', p['_id'], p['name']
        WARNINGS = True
    return (ERRORS, WARNINGS)

print 'Check if OK to merge'
if strict:
    for person in config['persons'].find():
        try:
            if len(Imap[person['_id']])>1:
                print 'ERROR multimap person:', person['_id'], '=', Imap[person['_id']]
                ERRORS = True
        except: pass
    for family in config['families'].find():
        try:
            if len(Fmap[family['_id']])>1:
                print 'ERROR multimap family:', family['_id'], '=', Fmap[family['_id']]
                ERRORS = True
        except: pass
    sanityChecks(config)  #Assigns global ERRORS, WARNINGS

    if ERRORS:
        print 'ERRORS present => NOT updating databases'
        print 'Merging NOT done'
        print 'Time:',time.time() - t0
        sys.exit()

#LOCK matchDB
#Redo mapping??

config['match_originalData'].insert_one({'type': 'admin', 'time': time.time(),
                                     'mergedWith': dbName})

print 'Attempt merging ...'
#persons
"""
copy all work.originalData to match.originalData (covers persons, families, relations)
for all work.persons
    if matched OK:
        merge records
    else:
        copy to match_person
"""
recs = config['originalData'].find()
config['match_originalData'].insert_many(recs)

inscnt=0
updcnt=0
for person in config['persons'].find():
    #if person['_id'] in Imap:
    if len(Imap[person['_id']])>0:
        updcnt += 1
        matchid = Imap[person['_id']]
        #merge birth/death
        for ev in ('birth', 'death'):
            for mid in matchid:
                Events = []
                event = config['persons'].find_one({'_id': person['_id']},
                                               {'_id': False, ev: True})
                if event: Events.append(event[ev])
                event = config['match_persons'].find_one({'_id': mid},
                                                {'_id': False, ev: True})
                if event: Events.append(event[ev])
                if Events:
                    config['match_persons'].update_one({'_id': mid},
                                        {'$set': {ev: mergeEvent(Events)}})
        for mid in matchid:
           config['match_originalData'].update_one({'recordId': mid, 'type': 'person'},
                                            {'$push': {'map': person['_id']}})
    else:
        config['match_persons'].insert_one(person)
        inscnt+=1
print 'Persons new=',inscnt,'updated=',updcnt
print 'Time:',time.time() - t0
#families
"""
for all families
    if matched OK:
        merge records
    else:
        copy to match.families
"""
inscnt=0
updcnt=0
for family in config['families'].find():
    #if family['_id'] in Fmap:
    if len(Fmap[family['_id']])>0:
        updcnt += 1
        matchid = Fmap[family['_id']]
        for mid in matchid:
            Events = []
            event = config['families'].find_one({'_id': family['_id']},
                                           {'_id': False, 'marriage': True})
            if event: Events.append(event['marriage'])
            #merge marriage
            event = config['match_families'].find_one({'_id': mid},
                                            {'_id': False, 'marriage': True})
            if event: Events.append(event['marriage'])
            if Events:
                config['match_families'].update_one({'_id': mid},
                                    {'$set': {'marriage': mergeEvent(Events)}})
                config['match_originalData'].update_one({'recordId': mid, 'type': 'family'},
                                            {'$push': {'map': family['_id']}})
    else:
        config['match_families'].insert_one(family)
        inscnt += 1
print 'Families new=',inscnt,'updated=',updcnt
print 'Time:',time.time() - t0

#Relations
for r in config['relations'].find({}, {'_id': 0}):
    if inRelIgnore(r): continue
    #if r['persId'] in Imap: pids = Imap[r['persId']]
    if len(Imap[r['persId']])>0: pids = Imap[r['persId']]
    else: pids = [r['persId']]
    #if r['famId'] in Fmap: fids = Fmap[r['famId']]
    if len(Fmap[r['famId']])>0: fids = Fmap[r['famId']]
    else: fids = [r['famId']]
    for pid in pids:
        for fid in fids:
            r['persId'] = pid
            r['famId'] = fid
            config['match_relations'].replace_one(r, r, upsert=True)

print 'Try to repair any new relation errors'
#FIX bästa ordningen?? Fam, child, rel ??
(childErr, famErr, relErr) = sanity(config['match_persons'], config['match_families'],
                                    config['match_relations'], do=['child'])
resChild = repairChild(childErr, config['match_persons'], config['match_families'],
                  config['match_relations'], config['match_originalData'])
(childErr, famErr, relErr) = sanity(config['match_persons'], config['match_families'],
                                    config['match_relations'], do=['family'])
resFam = repairFam(famErr, config['match_persons'], config['match_families'],
          config['match_relations'], config['match_originalData'])
#(childErr, famErr, relErr) = sanity(config['match_persons'], config['match_families'],
#                                    config['match_relations'], do=['match_child'])
#repairRel(relErr, config['match_persons'], config['match_families'],
#          config['match_relations'], config['match_originalData'])

print 'Check and merge duplicate families'
#Check for duplicate families: Fall 2 relationserror
#Find and merge families where husb and wife are same
#  and marriages do not conflict
from collections import defaultdict
from mergeUtils import mergeEvent
d = defaultdict(set)
#USE db.collection.group???
for husb in config['match_relations'].find({'relTyp': 'husb'}):
    for wife in config['match_relations'].find({'$and':
                        [{'famId': husb['famId']}, {'relTyp': 'wife'}]}):
        d[husb['persId'], wife['persId']].add(husb['famId'])
for s in d.values():
    if len(s)>=2:
      fdubl = list(s)
      #merge all into fdubl[0]
      F = config['match_families'].find_one({'_id': fdubl[0]})
      print 'Fdubl=', fdubl
      if 'marriage'in F:
          marrEvents = [F['marriage']]
      else:
          marrEvents = []
      FId = F['_id']
      #USE for fd in fdubl[1:]:
      for fd in fdubl:
          if fd == fdubl[0]: continue
          #FIX check marriage dates - see pattern notes
          #Enl Rolf: Marr  datum kan vara olika eller blanka
          fam2beMerged = config['match_families'].find_one({'_id': fd})
          #print 'Dubl', fd, fam2beMerged
          if 'marriage' in fam2beMerged: marrEvents.append(fam2beMerged['marriage'])
          print 'Merging family %s into %s' % (fam2beMerged['_id'], FId)
          config['match_originalData'].update_one({'recordId': FId, 'type': 'family'},
                                            {'$push': {'map': fam2beMerged['_id']}})
          config['match_families'].delete_one({'_id': fam2beMerged['_id']})
          config['match_relations'].delete_many({'$and': [{'famId': fam2beMerged['_id']},
                                               {'$or': [{'relTyp': 'husb'},
                                                        {'relTyp': 'wife'}]}
                                           ]})
          #only children in fam2beMerged left - move to new family
          config['match_relations'].update_many({'famId': fam2beMerged['_id']},
                                          {'$set': {'famId': F['_id']}})
          #remove duplicates ???? FIX!!!
          for ids in config['match_relations'].aggregate([
              { "$group": { 
                  "_id": { "persId": "$persId", "relTyp": "$relTyp", 'famId': '$famId' }, 
                  "uniqueIds": { "$addToSet": "$_id" },
                  "count": { "$sum": 1 } 
                }}, 
              { "$match": { "count": { "$gt": 1 } } }
          ]):
              config['match_relations'].remove({'_id': ids['uniqueIds'][1]})
      #merge all marriage events
      if marrEvents:
          config['match_families'].update_one({'_id': F['_id']}, {'$set':
                                              {'marriage': mergeEvent(marrEvents)}})
print 'Time:',time.time() - t0

if WARNINGS:
    print 'Some warnings - check log above'

print 'Time:',time.time() - t0
#SANITY CHECKS
print 'Doing sanity checks after merge'
sanityChecks(config)

#Gedcom xrefs
workOrgData = config['originalData'].find_one({'type': 'gedcomRecords'})
for rec in workOrgData['data']:
    config['match_originalData'].update({'type': 'gedcomRecords'},
                                        {'$push': {'data': rec}})

print 'Time:',time.time() - t0
print 'Indexing'
#EVT only reindex affected persons, families; Delete old index?
(user,db) = mDBname.split('_', 1)
directory = "./files/"+user+'/'+db+'/LuceneIndex'
if os.path.isdir(directory): shutil.rmtree(directory)
from luceneDB import luceneDB
searchDB = luceneDB(mDBname)
searchDB.index(config['persons'],config['families'],config['relations'])
print 'Indexed', mDBname, 'in Lucene'
print 'Time:',time.time() - t0

#UNLOCK 

#Efter merge av dbName i mDBname så skall:
for mdb in common.admClient.database_names():
    for coll in common.admClient[mdb].collection_names():
        if mdb == mDBname and coll.startswith('match'):
#1: alla eventuella matchningsinfon hos mDBname tas bort
            print 'Dropping', mdb, coll
            common.admClient[mdb][coll].drop()
#        elif mdb == dbName and coll.endswith(mDBname):
#2: dbName matchningsinfo mot mDBname tas bort (dbName tillbaka till ursprung)
#            print 'Dropping', mdb, coll
#            common.admClient[mdb][coll].drop()
        elif coll.endswith(mDBname):  #3
#3: alla databasers matchningsinfo mot mDBname tas bort
            print 'Dropping', mdb, coll
            common.admClient[mdb][coll].drop()
print dbName, 'merged into', mDBname, 'succesfully'
