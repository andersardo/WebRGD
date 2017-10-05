#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import common

import argparse, time, sys, os
import pickle

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
            config['match_persons'].update({'_id': matchid[0]}, 
                                mergeOrgDataPers(matchid[0], config['match_persons'],
                                                 config['match_originalData']) )
        else: print 'NOT Updating Imap list longer than one:', matchid
    else:
        config['match_persons'].insert_one(person)  #Kolla att _id behålls
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
    if family['_id'] in Fignore:
        continue
    #end
    if family['_id'] in Fmap:
        updcnt += 1
        matchid = Fmap[family['_id']]
        if len(matchid) == 1:
            config['match_families'].update({'_id': matchid[0]},
                                mergeOrgDataFam(matchid[0], config['match_families'],
                                                 config['match_originalData']) )
        else:
            print 'NOT Updating Fmap list longer than one:', matchid
    else:
        config['match_families'].insert_one(family)
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
    if rel['famId'] in Fignore: continue  #Kolla FIX
    del(rel['_id'])
    for mappedFamId in Fmap[rel['famId']]:
        for mappedPersId in Imap[rel['persId']]:
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

#SANITY CHECKS
#can only be child in 1 family
aggrPipe = [
    {'$match': {'relTyp': 'child'}},
    {'$project': {'persId': '$persId', 'count': {'$concat': ['1']}}},
    {'$group': {'_id': '$persId', 'count': {'$sum': 1}}},
    {'$match': {'count': {'$gt': 1}}}
]
for multiChild in config['match_relations'].aggregate(aggrPipe):
    print 'Relation ERROR Child', multiChild['_id'], 'in', multiChild['count'], 'families'
#1 husb/wife per family
for partner in ('husb', 'wife'):
    aggrPipe = [
        {'$match': {'relTyp': partner}},
        {'$project': {'famId': '$famId', 'count': {'$concat': ['1']}}},
        {'$group': {'_id': '$famId', 'count': {'$sum': 1}}},
        {'$match': {'count': {'$gt': 1}}}]
    for multiPartner in config['match_relations'].aggregate(aggrPipe):
        print 'Relation ERROR Family', multiPartner['_id'], 'have', multiPartner['count'], partner
#Persons without relations
for pers in config['match_persons'].find():
    rel = config['match_relations'].find_one({'persId': pers['_id']})
    if not rel:
        print 'Relation ERROR Person without relations:', pers['_id'], pers['name']

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
index(config['match_persons'],config['match_families'])
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
