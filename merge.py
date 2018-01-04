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

#FIX save mDBname som mongodump in workdir

print 'Merging', dbName, 'into', mDBname
from mergeUtils import createMap, mergeOrgDataPers, mergeOrgDataFam
from mergeUtils import Imap, Fmap, Fignore

#FIX HANDLE Flags FIX

errCnt = 0
t0 = time.time()

print 'Doing sanity checks'
if config['match_originalData'].find_one({'type': 'admin', 'mergedWith': dbName}):
    print mDBname, 'already merged with', dbName, '-- Exiting'
    sys.exit()
"""
#sanity check - en person kan bara var barn i 1 familj
for p in config['persons'].find():
    if config['families'].find({'children': p['_id']}).count() > 1:
        print p['_id'],p['refId'],'child in more than one family'
"""
#if status in statManual exists => exit   ???????????
if config['matches'].find({'status': {'$in': list(common.statManuell)}}).count():
    print 'Person matches with manual status exists'
if config['fam_matches'].find({'status': {'$in': list(common.statManuell)}}).count():
    print 'Family matches with manual status exists'
#if contributionId in orgData => multiMerge => exit
print 'Time:',time.time() - t0

print 'Creating match map persons/families'
createMap(config)  #Creates/updates Imap, Fmap
print 'Time:',time.time() - t0

print 'Process flags'
relIgnore = []
"""
###TESTING
Vid sammanslagning uppstår 4 fall
Rel1 mot Rel2 (upptäcks i multiMatch, createMap)
1. dbI.A splitas så att dbI.C1 flyttas till dbII.B, resten slås ihop med dbII.C (split i DBI)
     flag on dbI.C1 move -> dbII.B, remove match dbI.A -> dbII.B Ignore
2. A, B, C slås samman (sammanslagning DBII)
     flag on dbII.B merge -> dbII.C: Detect same family after merge Auto+Warn
Rel2 mot Rel1 (upptäcks i multiMatch, ej i merge)
3. B, C, A slås samman (vanlig mappning DBI)
     -
4. dbII.A splitas så att dbI.C1 flyttas till dbI.B, resten slås ihop med dbI.C (split DBII)
     flag on dbII.C1 move -> dbI.B, remove match dbI.B -> dbII.A Ignore
###TESTING

#Fall 1 
#Modify Fmap (görs bättre i createMap?) Fmap['F192']=[F197, F199] A=C, B
Fmap['F_192'].remove('F_199') #Flag Ignore famMatch workFamId -> matchFamId
#Relationseditor för att fixa C1 in 2 families?
#eller Flag Ignore Relation C1 (P_419) child dbI.A (F_192)
relIgnore = [{'persId': 'P_419', 'relTyp': 'child', 'famId': 'F_192'}]

#Fall 2
#Detect same family after merge Auto+Warn - OK

#Fall 3
#OK

#Fall 4
##Fmap['F_235'].remove('F_228') #Flag Ignore famMatch workFamId -> matchFamId
Fmap['F_244'].remove('F_237') #Flag Ignore famMatch workFamId -> matchFamId
##relIgnore = [{'persId': 'P_499', 'relTyp': 'child', 'famId': 'F_228'}]
relIgnore = [{'persId': 'P_519', 'relTyp': 'child', 'famId': 'F_237'}]
"""
for flag in config['flags'].find():
    if flag['typ'] == 'IgnoreRelation':
        relIgnore.append({'persId': flag['persId'], 'relTyp': flag['relTyp'], 'famId': flag['famId']})
    elif flag['typ'] == 'IgnoreFamilyMatch':
        Fmap[flag['workFam']].remove(flag['matchFam'])
    else:
        print 'Unknown flag:', flag
for k in Fmap.keys(): #delete empty maps
    if not Fmap[k]: del(Fmap[k])
for rel in relIgnore:
    res = config['match_relations'].remove(rel)

print 'Time:',time.time() - t0

#TEST and quit ON ERRORS???

#LOCK matchDB
#Redo mapping??

config['match_originalData'].insert({'type': 'admin', 'time': time.time(),
                                     'mergedWith': dbName})
print 'Merging ...'
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
inscnt=0
updcnt=0
for person in config['persons'].find():
    if person['_id'] in Imap:
        updcnt += 1
        matchid = Imap[person['_id']]
        #generate merged record
        if len(matchid)  == 1:
            config['match_persons'].update({'_id': next(iter(matchid))}, 
                                mergeOrgDataPers(next(iter(matchid)), config['match_persons'],
                                                 config['match_originalData']) )
        else: print 'NOT Updating Imap list longer than one:', matchid
    else:
        config['match_persons'].insert_one(person)
        Imap[person['_id']].add(person['_id'])  #Identity map
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
            config['match_families'].update({'_id': next(iter(matchid))},
                                mergeOrgDataFam(next(iter(matchid)), config['match_families'],
                                                 config['match_originalData']) )
        else:
            print 'NOT Updating Fmap list longer than one:', matchid
    else:
        config['match_families'].insert_one(family)
        Fmap[family['_id']].add(family['_id'])  #Identity map
        inscnt += 1
print 'Families new=',inscnt,'updated=',updcnt
print 'Time:',time.time() - t0
#Relations
"""
for all relations
   if famId in Fmap: use mapped famId
   if persId in Imap: use mapped persId
   if relation not in match_relations: add
"""
totRel = 0
updatedRel = 0
for rel in config['relations'].find():
    #if rel['famId'] in Fignore: continue  #Kolla FIX
    del(rel['_id'])
    if rel in relIgnore:
        continue
    fmaplist = Fmap[rel['famId']]
    if not fmaplist: fmaplist = [rel['famId']]
    imaplist = Imap[rel['persId']]
    if not imaplist: imaplist = [rel['persId']]
    for mappedFamId in fmaplist:
        for mappedPersId in imaplist:
            rel['famId'] = mappedFamId
            rel['persId'] = mappedPersId
            res = config['match_relations'].update(rel, rel, upsert=True)
            totRel += 1
            if res['nModified']: updatedRel += 1
    """
    if rel['famId'] in Fmap: rel['famId'] = Fmap[rel['famId']]
    if rel['persId'] in Imap: rel['persId'] = Imap[rel['persId']]
    del(rel['_id'])
    res = config['match_relations'].update(rel, rel, upsert=True)
    totRel += 1
    if res['nModified']: updatedRel += 1
    """
print 'Relations: total=', totRel, 'updated=', updatedRel
print 'Time:',time.time() - t0
"""
#NEW CODE with merging
Förutsätter att alla relations sparas i originalData
för varje rel (famId, typ, persId) med mappade IDs
  för typ in [husb, wife] # varje familj kan bara ha en husb,wife
    get famcluster famId (reverseFmap)
    get all relations från originalData med
            relTyp == typ
            famId in famCluster
    => list_av_relations
  för typ in [child] # varje person kan bara vara child i en familj
    get perscluster persId (reverseFmap)
    get all relations från originalData med
            relTyp == typ
            persId in persCluster
    => list_av_relations
  uniqRelations = alla unika i list_av_relations med mappadeID
    (jämföra dicts gör om till json med sorted keys)
  om mer än 1 välj bästa
==========
totRel = 0
updatedRel = 0
for relTyp in ('husb', 'wife', 'child'):
    mappedRels = set()  #Eler init to rel from match_relations?
    for rel in config['relations'].find({'typRel': relTyp}):
        if rel['famId'] in Fignore: continue  #Kolla FIX
        if rel['famId'] in Fmap:
            #find All from this cluster in originalData
            for famuid in reverseFmap[rel['famId']]:
               tmp  = config['match_originalData'].find({'relation.famId': famuid,
                                                         'relation.typRel': relTyp})
               mappedRels.add(Imap[tmp['persId']]) # alt keep track of howmany for auto merge?
            #add all new to relations
            for persId in mappedRels:
                relDict = {'relTyp': relTyp, 'famId': rel['famId'], 'persId': persId}
                res = config['match_relations'].update(relDict, relDict, upsert=True)
                totRel += 1
print 'Relations: total=', totRel, 'updated=', updatedRel
print 'Time:',time.time() - t0
"""

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
      marrEvents = []
      F = config['match_families'].find_one({'_id': fdubl[0]}, {'_id': 1})
      FId = F['_id']
      #USE for fd in fdubl[1:]:
      for fd in fdubl:
          if fd == fdubl[0]: continue
          #FIX check marriage dates - see pattern notes
          #Enl Rolf: Marr  datum kan vara olika eller blanka
          fam2beMerged = config['match_families'].find_one({'_id': fd})
          if 'marriage' in fam2beMerged: marrEvents.append(fam2beMerged['marriage'])
          print 'Merging family %s into %s' % (fam2beMerged['_id'], FId)

          config['match_families'].delete_one({'_id': fam2beMerged['_id']})
          #Fmap[fam2beMerged['_id']] = [F['_id']]
          Fmap[fam2beMerged['_id']] = F['_id'] #KOLLA
          config['match_relations'].delete_many({'$and': [{'famId': fam2beMerged['_id']},
                                               {'$or': [{'relTyp': 'husb'},
                                                        {'relTyp': 'wife'}]}
                                           ]})
          #only children in fam2beMerged left - move to new family
          config['match_relations'].update_many({'famId': fam2beMerged['_id']},
                                          {'$set': {'famId': F['_id']}})
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

      #merge all marriage events
      config['match_families'].update_one({'_id': F['_id']}, {'$set':
                                              {'marriage': mergeEvent(marrEvents)}})

#SANITY CHECKS
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
#1 husb/wife per family
for partner in ('husb', 'wife'):
    aggrPipe = [
        {'$match': {'relTyp': partner}},
        {'$project': {'famId': '$famId', 'count': {'$concat': ['1']}}},
        {'$group': {'_id': '$famId', 'count': {'$sum': 1}}},
        {'$match': {'count': {'$gt': 1}}}]
    for multiPartner in config['match_relations'].aggregate(aggrPipe):
        #print multiPartner
        #fam = config['match_families'].find_one({'_id': multiPartner['_id']})
        #print fam
        print 'Relation ERROR Family', multiPartner['_id'], 'have', multiPartner['count'], partner
#Persons without relations
for pers in config['match_persons'].find():
    rel = config['match_relations'].find_one({'persId': pers['_id']})
    if not rel:
        print 'Relation WARNING Person without relations:', pers['_id'], pers['name']

#Save Imap, Fmap in match_originalData to be used in next merge
if '_id' in Imap:
    config['match_originalData'].save({'_id': Imap['_id'], 'type': 'Imap', 'data': pickle.dumps(Imap)})
else:
    config['match_originalData'].save({'type': 'Imap', 'data': pickle.dumps(Imap)})
if '_id' in Fmap:
    config['match_originalData'].save({'_id': Fmap['_id'], 'type': 'Fmap', 'data': pickle.dumps(Fmap)})
else:
    config['match_originalData'].save({'type': 'Fmap', 'data': pickle.dumps(Fmap)})

#Gedcom xrefs
workOrgData = config['originalData'].find_one({'type': 'gedcomRecords'})
for rec in workOrgData['data']:
    config['match_originalData'].update({'type': 'gedcomRecords'},
                                        {'$push': {'data': rec}})

##FIX if errors restore mDBname med mongorestore

print 'Indexing'
#EVT only reindex affected persons, families
from luceneUtils import setupDir, index
setupDir(mDBname)
index(config['match_persons'],config['match_families'],config['match_relations'])
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
