#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import common

import argparse, time, sys, os
import pickle
import codecs, locale
locale.setlocale(locale.LC_ALL, 'en_US.UTF-8') #sorting??
sys.stdout = codecs.getwriter('UTF-8')(sys.stdout)

parser = argparse.ArgumentParser()
parser.add_argument("workDB", help="Working database name" )
parser.add_argument("matchDB", help="Database to match against")

args = parser.parse_args()
workDB = args.workDB
matchDB = args.matchDB

dbName  = os.path.basename(workDB).split('.')[0]  #No '.' or '/' in databasenames
mDBname = os.path.basename(matchDB).split('.')[0]
config = common.init(dbName, matchDBName=mDBname, indexes=True)
common.config = config

ERRORS = False
WARNINGS = False
CHANGES = []
print 'Merging', dbName, 'into', mDBname
from mergeUtils import createMap, mergeOrgDataPers, mergeOrgDataFam, mergeOrgDataRel
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
if createMap(config): #Creates/updates Imap, Fmap
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
        reverseFmap[flag['matchFam']].remove(flag['workFam'])
        if not reverseFmap[flag['matchFam']]: del(reverseFmap[flag['matchFam']])
    else:
        print 'Unknown flag:', flag
#for k in Fmap.keys(): #delete empty maps
#    if not Fmap[k]: del(Fmap[k])

print 'Time:',time.time() - t0

#LOCK matchDB
#Redo mapping??

#config['match_originalData'].insert_one({'type': 'admin', 'time': time.time(),
#                                     'mergedWith': dbName})
CHANGES.append([config['match_originalData'], 'insert_one', {'type': 'admin', 'time': time.time(),
                                                         'mergedWith': dbName}])
print 'Attempt merging ...'
#persons
"""
copy all work.originalData to match.originalData (covers persons, families, relations)
for all work.persons
    if matched OK:
        merge match_originalData records to match_person
    else:
        copy to match_person
"""
recs = config['originalData'].find()
config['match_originalData'].insert_many(recs)
#If errors undone below

inscnt=0
updcnt=0
for person in config['persons'].find():
    if person['_id'] in Imap:
        updcnt += 1
        matchid = Imap[person['_id']]
        #generate merged record
        if len(matchid)  == 1:
            #config['match_persons'].update({'_id': next(iter(matchid))},
            #                    mergeOrgDataPers(next(iter(matchid)), config['match_persons'],
            #                                     config['match_originalData']) )
            CHANGES.append([config['match_persons'], 'update', {'_id': next(iter(matchid))},
                        mergeOrgDataPers(next(iter(matchid)), config['match_originalData'])
                          ])
        else:
            print 'ERROR multimap person:', person['_id'], '=', matchid
            ERRORS = True
    else:
        #config['match_persons'].insert_one(person)
        CHANGES.append([config['match_persons'], 'insert_one', person])
        Imap[person['_id']].add(person['_id'])  #Identity map
        reverseImap[person['_id']].add(person['_id'])  #Identity map
        inscnt+=1
print 'Persons new=',inscnt,'updated=',updcnt
print 'Time:',time.time() - t0
#families
"""
for all families
    if matched OK:
        merge match_origialData records to match_family
    else:
        copy to match.families
"""
inscnt=0
updcnt=0
for family in config['families'].find():
    #New ignore KOLLA FIX
    #if family['_id'] in Fignore:
    #    continue
    #end
    if family['_id'] in Fmap:
        updcnt += 1
        matchid = Fmap[family['_id']]
        if len(matchid) == 1:
            #config['match_families'].update({'_id': next(iter(matchid))},
            #                    mergeOrgDataFam(next(iter(matchid)), config['match_families'],
            #                                     config['match_originalData']) )
            CHANGES.append([config['match_families'], 'update', {'_id': next(iter(matchid))},
                            mergeOrgDataFam(next(iter(matchid)), config['match_originalData'])
            ])
        else:
            print 'ERROR multimap family:', family['_id'], '=', matchid
            ERRORS = True
    else:
        #config['match_families'].insert_one(family)
        CHANGES.append([config['match_families'], 'insert_one', family])
        Fmap[family['_id']].add(family['_id'])  #Identity map
        reverseFmap[family['_id']].add(family['_id'])  #Identity map
        inscnt += 1
print 'Families new=',inscnt,'updated=',updcnt
print 'Time:',time.time() - t0

#Relations
inscnt=0
updcnt=0
for rel in config['relations'].find({}, {'_id': 0}):
    found = False
    for r in relIgnore:
        if cmp(r, rel) == 0:
            found = True
            break
    if found: continue
    if rel['persId'] in Imap and rel['famId'] in Fmap:
        updcnt += 1
        matchPid = Imap[rel['persId']]
        matchFid = Fmap[rel['famId']]
        if len(matchPid)>1 or len(matchFid)>1:
            print 'ERROR relation multimap in person', matchPid, 'or family', matchFid
            ERRORS = True
        else:
            for r in mergeOrgDataRel(next(iter(matchPid)), next(iter(matchFid)),
                                     config['match_originalData'], relIgnore):
                updcnt += 1
                #config['match_relations'].replace_one(r, r, upsert=True) #??
                CHANGES.append([config['match_relations'], 'replace_one', r, r])
    else:
        #config['match_relations'].insert_one(rel)
        CHANGES.append([config['match_relations'], 'insert_one', rel])
        inscnt += 1
print 'Relations: total=', updcnt+inscnt, 'updated=', updcnt
print 'Time:',time.time() - t0

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
          print 'Dubl', fd, fam2beMerged
          if 'marriage' in fam2beMerged: marrEvents.append(fam2beMerged['marriage'])
          print 'Merging family %s into %s' % (fam2beMerged['_id'], FId)

          config['match_families'].delete_one({'_id': fam2beMerged['_id']})
          #CHANGES
          #Fmap[fam2beMerged['_id']] = [F['_id']]
          Fmap[fam2beMerged['_id']] = F['_id'] #KOLLA
          config['match_relations'].delete_many({'$and': [{'famId': fam2beMerged['_id']},
                                               {'$or': [{'relTyp': 'husb'},
                                                        {'relTyp': 'wife'}]}
                                           ]})
          #CHANGES
          #only children in fam2beMerged left - move to new family
          config['match_relations'].update_many({'famId': fam2beMerged['_id']},
                                          {'$set': {'famId': F['_id']}})
          #CHANGES
          #remove duplicates
          for ids in config['match_relations'].aggregate([
              { "$group": { 
                  "_id": { "persId": "$persId", "relTyp": "$relTyp", 'famId': '$famId' }, 
                  "uniqueIds": { "$addToSet": "$_id" },
                  "count": { "$sum": 1 } 
                }}, 
              { "$match": { "count": { "$gt": 1 } } }
          ]):
              config['match_relations'].remove({'_id': ids['uniqueIds'][1]})
              #CHANGES
      #merge all marriage events
      if marrEvents:
          config['match_families'].update_one({'_id': F['_id']}, {'$set':
                                              {'marriage': mergeEvent(marrEvents)}})
          #CHANGES
print 'Time:',time.time() - t0

if WARNINGS:
    print 'Some warnings - check log above'

#ERROR handling
if ERRORS:
    print 'ERRORS present => NOT updating databases'
    print 'Merging NOT done'
    #Undo delete of relIgnore records from flag handling
    for rel in relIgnore:
        config['match_relations'].insert(rel)
    #Undo copying of orginalData
    for rec in config['originalData'].find():
        config['match_originalData'].delete_one(rec)
    print 'Time:',time.time() - t0
    sys.exit()
#All OK execute CHANGES
print 'Actually updating databases'
for rel in relIgnore:
    res = config['match_relations'].remove(rel)
for op in CHANGES:
    if op[1]=='replace_one' and len(op)==4:
        op[0].replace_one(op[2], op[3], upsert=True)
    elif op[1]=='update' and len(op)==4:
        op[0].update(op[2], op[3])
    elif op[1]=='insert_one' and len(op)==3:
        op[0].insert_one(op[2])
    else:
        print 'ERROR - unkown database operation'
print 'Time:',time.time() - t0

#SANITY CHECKS
print 'Doing sanity checks'
#can only be child in 1 family
aggrPipe = [
    {'$match': {'relTyp': 'child'}},
    {'$project': {'persId': '$persId', 'count': {'$concat': ['1']}}},
    {'$group': {'_id': '$persId', 'count': {'$sum': 1}}},
    {'$match': {'count': {'$gt': 1}}}
]
for multiChild in config['match_relations'].aggregate(aggrPipe):
    pers = config['match_persons'].find_one({'_id': multiChild['_id']})
    print 'Relation ERROR Child', pers['name'], multiChild['_id'], 'in', multiChild['count'], 'families'
    ERRORS = True
#1 husb/wife per family
for partner in ('husb', 'wife'):
    aggrPipe = [
        {'$match': {'relTyp': partner}},
        {'$project': {'famId': '$famId', 'count': {'$concat': ['1']}}},
        {'$group': {'_id': '$famId', 'count': {'$sum': 1}}},
        {'$match': {'count': {'$gt': 1}}}]
    for multiPartner in config['match_relations'].aggregate(aggrPipe):
        print 'Relation ERROR Family', multiPartner['_id'], 'have', multiPartner['count'], partner
        ERRORS = True
#Persons without relations
for pers in config['match_persons'].find():
    rel = config['match_relations'].find_one({'persId': pers['_id']})
    if not rel:
        print 'Relation WARNING Person without relations:', pers['_id'], pers['name']
        WARNINGS = True

#Save Imap, Fmap in match_originalData to be used in next merge
map = config['match_originalData'].find_one({'type': 'Imap'}, {'_id': 1})
#if '_id' in Imap:
#    config['match_originalData'].save({'_id': Imap['_id'], 'type': 'Imap', 'data': pickle.dumps(Imap)})
if map:
    config['match_originalData'].save({'_id': map['_id'], 'type': 'Imap', 'data': pickle.dumps(Imap)})
else:
    config['match_originalData'].save({'type': 'Imap', 'data': pickle.dumps(Imap)})
map = config['match_originalData'].find_one({'type': 'Fmap'}, {'_id': 1})
#if '_id' in Fmap:
#    config['match_originalData'].save({'_id': Fmap['_id'], 'type': 'Fmap', 'data': pickle.dumps(Fmap)})
if map:
    config['match_originalData'].save({'_id': map['_id'], 'type': 'Fmap', 'data': pickle.dumps(Fmap)})
else:
    config['match_originalData'].save({'type': 'Fmap', 'data': pickle.dumps(Fmap)})

#Gedcom xrefs
workOrgData = config['originalData'].find_one({'type': 'gedcomRecords'})
for rec in workOrgData['data']:
    config['match_originalData'].update({'type': 'gedcomRecords'},
                                        {'$push': {'data': rec}})

print 'Time:',time.time() - t0
print 'Indexing'
#EVT only reindex affected persons, families; Delete old index?
from luceneUtils import setupDir, index
setupDir(mDBname)
index(config['match_persons'], config['match_families'], config['match_relations'])
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
