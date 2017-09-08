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
from bson.objectid import ObjectId
from mergeUtils import createMap, mergeOrgDataPers, mergeOrgDataFam
from mergeUtils import Imap, Fmap, Fignore

#FIX HANDLE Flags FIX

errCnt = 0
t0 = time.time()

print 'Doing sanity checks'
if config['match_originalData'].find_one({'type': 'admin', 'mergedWith': dbName}):
    print mDBname, 'already merged with', dbName, '-- Exiting'
    sys.exit()

#sanity check - en person kan bara var barn i 1 familj
for p in config['persons'].find():
    if config['families'].find({'children': p['_id']}).count() > 1:
        print p['_id'],p['refId'],'child in more than one family'

#if status in statManual exists => exit
if config['matches'].find({'status': {'$in': list(common.statManuell)}}).count():
    print 'Person matches with manual status exists'
if config['fam_matches'].find({'status': {'$in': list(common.statManuell)}}).count():
    print 'Family matches with manual status exists'
#if contributionId in orgData => multiMerge => exit
print 'Time:',time.time() - t0

print 'Creating map'
createMap(config)
print 'Time:',time.time() - t0

#TEST and quit ON ERRORS???

#LOCK matchDB

#Redo mapping??
config['match_originalData'].insert({'type': 'admin', 'time': time.time(),
                                     'mergedWith': dbName})
inscnt=0
updcnt=0
print 'Merging ...'
#persons
"""
for all work.persons
    if matched OK:
        add to originalData
        merge originalData to person
    else:
        copy to originalData
        copy to person
"""
for person in config['persons'].find():
    workOrgData = config['originalData'].find_one({'recordId': person['_id'], 'type': 'person'})
#    mt = config['matches'].find_one({'pwork._id': person['_id'],
#                                     'status': {'$in': list(common.statOK)}})
#    if mt:
#        if Imap[person['_id']] != mt['pmatch']['_id']: print 'Imap error A'
    if person['_id'] in Imap:
        updcnt += 1
#        matchid = mt['pmatch']['_id']
        matchid = Imap[person['_id']]
        for rec in workOrgData['data']:
            config['match_originalData'].update({'recordId': matchid},
                                            {'$push': {'data': rec}})
            #generate merged record
            config['match_persons'].update({'_id': matchid}, 
                                    mergeOrgDataPers(matchid, config['match_persons'],
                                                     config['match_originalData']) )
    else:
#        if person['_id'] in Imap: print 'Imap error B'
        try:
            config['match_persons'].insert(person)  #Kolla att _id behålls
            config['match_originalData'].insert(workOrgData)  #Kolla att _id behålls
            inscnt+=1
        except:
            #pass
            print 'ERROR inserting new person', person['refId']
print 'Persons new=',inscnt,'updated=',updcnt        
print 'Time:',time.time() - t0

#families
"""
for all families
    if matched OK:
        add to match.originalData
    else:
        copy to match.families
        copy to match.originalData
    merge match.originalData to match.families (includes mapping of personids)
"""
inscnt=0
updcnt=0
for family in config['families'].find():
    #New ignore
    if family['_id'] in Fignore:
        continue
    #end
    workOrgData = config['originalData'].find_one({'recordId': family['_id'], 'type': 'family'})
    if family['_id'] in Fmap:
        matchid = Fmap[family['_id']]
        updcnt += 1
        for rec in workOrgData['data']:
            config['match_originalData'].update({'recordId': matchid},
                                            {'$push': {'data': rec}})
    else:
#        if family['_id'] in Fmap:  print 'Fmap error B'
        matchid = family['_id']
        config['match_families'].insert(family)
        config['match_originalData'].insert(workOrgData)
        inscnt += 1
    config['match_families'].update({'_id': matchid}, 
                                    mergeOrgDataFam(matchid, config['match_families'],
                                                    config['match_originalData']))
print 'Families new=',inscnt,'updated=',updcnt
print 'Time:',time.time() - t0

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
