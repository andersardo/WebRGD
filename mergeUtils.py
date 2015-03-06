# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import re
from collections import defaultdict
import common
from utils import matchFam
import pickle

Imap = {}
Fmap = {}

def maxdict(d):
    """ key for max value in dict
        What if there are more than one?
         a) create a list of the dict's keys and values; 
         b) return the key with the max value """ 
    v=list(d.values())
    if (len(v)==0): return None
    k=list(d.keys())
    i=0
    retval=''
    for vv in v:
        if vv==max(v):
            m=re.search('\d', k[i])
            if m: k[i] += '       '
            #print i,k[i],len(k[i]),retval,len(retval),'<br>'
            if (len(k[i])>len(retval)): retval = k[i]
        i += 1
    return retval.rstrip()

def merge(items):
    it={}  #use defaultdict?
    for item in items:
        if item in it: it[item] += 1
        else: it[item]=1
    return maxdict(it)

def mergeEvent(eventType, orgDataRec):
    evDict = {}
    for item in	("date", "source", "place" "normPlaceUid") :
        ll = []
        for rec in orgDataRec['data']:
            if eventType in rec['record']:
                if item in rec['record'][eventType]:
                    ll.append(rec['record'][eventType][item])
        if ll:
           evDict[item] = merge(ll)
    return evDict

def mergeOrgDataPers(recordid, persons, originalData):
    persDict = persons.find_one({'_id': recordid})
    orgDataRec = originalData.find_one({'recordId': recordid, 'type': 'person'})
#    print 'mergeOrgDataPers', recordid
    for field in ('name', 'sex', 'grpNameLast', 'grpNameGiven'):
        ll = []
        for rec in orgDataRec['data']:
            if field in rec['record']:
                ll.append(rec['record'][field])
        if ll:
           persDict[field] = merge(ll)
    #events
    for ev in ('birth', 'death'):
        persDict[ev] = mergeEvent(ev, orgDataRec)
#    print 'persDict', persDict
    return persDict

def mergeOrgDataFamImport(recordid, families, originalData):
    """ Merge orginalData for 'recordid' into a
        combined record used in RGD.
        husb, wife should be identical - if not raises mergeERR
        children is just added together (no duplicates)
        marriage uses maxdict to determine which value to keep"""
    #print 'mergeOrgDataFam', recordid
    famDict = families.find_one({'_id': recordid})
    #keep _id, refId
    orgDataRec = originalData.find_one({'recordId': recordid, 'type': 'family'})
    #children
    famDict['children'] =[]
    for rec in orgDataRec['data']:
        for child in rec['record']['children']:
             if child not in famDict['children']: famDict['children'].append(child)
    #marriage
    famDict['marriage'] = mergeEvent('marriage', orgDataRec)
    #husb, wife
    for partner in ('husb', 'wife'):
        for rec in orgDataRec['data']:
            if rec['record'][partner]:
                if famDict[partner]:
                     if famDict[partner] != rec['record'][partner]:  #use assert?
                        print 'ERR mergeOrgDataFam',recordid, partner,famDict[partner], rec['record'][partner]
                        import pprint
                        pp = pprint.PrettyPrinter(indent=4)
                        print 'famDict'
                        pp.pprint(famDict)
                        print 'orgDataRec'
                        pp.pprint(orgDataRec)
                        #raise mergeERR
                else:
                    famDict[partner] = rec['record'][partner]
#    pp.pprint(orgDataRec)
    return famDict

def checkPers(partner, orgDataRec):
    pers = []
    for rec in orgDataRec['data']:
        p1 = common.config['match_persons'].find_one({'_id':rec['record'][partner]})
        if not p1:
            p1 = common.config['persons'].find_one({'_id':rec['record'][partner]})
        pers.append(p1)
    for p in pers:
        print p['refId']
    print

def checkFam(wid,mid):
  fams = set()
  for role in ('husb', 'wife', 'children'):
    tFam = common.config['families'].find({role: wid}, {'_id': 1, 'marriage.date': 1} )
    rFam = common.config['match_families'].find({role: mid}, {'_id': 1, 'marriage.date': 1} )
    tfams = []
    tDone = []
#take all combinations of families
#if several posibilities for 1 work-family keep only the the pair where marriage-date matches
    for f in tFam:
        for ff in rFam:
            try:
                if len(f['marriage']['date'])>4 and f['marriage']['date'] == ff['marriage']['date']:
                    fams.add((f['_id'], ff['_id']))
                    tDone.append(f['_id'])
                else:
                    tfams.append([f['_id'], f['marriage']['date'], ff['_id'], ff['marriage']['date']])
            except:
                fams.add((f['_id'], ff['_id']))
    for l in tfams:
        if l[0] not in tDone:
            fams.add((l[0], l[2]))
#?#
    for (tFamId,rFamId) in fams:    #  for all involved families
        print 'checking',tFamId,rFamId
        famMatchData = matchFam(tFamId, rFamId, config)
        if common.config['fam_matches'].find({'workid': tFamId, 'matchid': rFamId}).count() == 0:
            if famMatchData['status'] in common.statOK.union(common.statManuell):
                #fam_matches.insert(famMatchData)
                print 'NY',famMatchData['workRefId'],famMatchData['workRefId'],famMatchData['status']

def mergeOrgDataFam(recordid, families, originalData):
    """ Merge orginalData for 'recordid' into a
        combined record used in RGD.
        husb, wife should be identical
        children is just added together (no duplicates)
        marriage uses maxdict to determine which value to keep"""
#    print 'mergeOrgDataFam', recordid
    famDict = families.find_one({'_id': recordid})
    #keep _id, refId
    orgDataRec = originalData.find_one({'recordId': recordid, 'type': 'family'})
    #children
    famDict['children'] = []
    chSet = set()
    for rec in orgDataRec['data']:
        for child in rec['record']['children']:
#if mapped
            #chSet.add(relationsMap(child))
            if child in Imap:
                chSet.add(Imap[child])
            else:
#                print "Check if identitymap", child
                #if exists personrecord in originalData with recordId=child
                if originalData.find_one({'recordId': child}):
                    chSet.add(child)
                else: print "No map for child", child
    for ch in chSet:
        famDict['children'].append(ch)
    #marriage
    famDict['marriage'] = mergeEvent('marriage', orgDataRec)
    #husb, wife
    for partner in ('husb', 'wife'):
#create set of mapped ids
#if len(set)==1 => OK
#else choose one
        ids = defaultdict(int)
        refIds = []
        for rec in orgDataRec['data']:
            if rec['record'][partner]:
#if mapped
                #ids[relationsMap(rec['record'][partner])] += 1
                if rec['record'][partner] in Imap:
                    ids[Imap[rec['record'][partner]]] += 1
                    refIds.append(rec['record']['refId'])
                else:
                   #print "Check if identitymap", rec['record']['refId'] 
                   #if exists personrecord in originalData with recordId=_id
                    if originalData.find_one({'recordId': rec['record'][partner]}):
                        ids[rec['record'][partner]] += 1
                        refIds.append(rec['record']['refId'])
                    else:
                        print "No map for partner", rec['record'][partner], rec['record']['refId']
        if len(ids) == 1: 
            famDict[partner] = ids.keys()[0]
        elif len(ids)>1: 
            famDict[partner] = max((v,k) for k,v in ids.items())[1]
            print 'WARN MultiMap in fam', recordid, partner
            print 'refId fams', ', '.join(refIds)
            #print orgDataRec
            #print 'Id alternatives', ids
            #for (id,n) in ids.iteritems():
            #    o = originalData.find_one({'recordId': id, 'type': 'person'})
            #    print o
            checkPers(partner, orgDataRec)
    return famDict

def createMap(config):
#?does not work?    global Fmap, Imap
    cnt=0
    #families
    #get map from earlier merge's
    map = config['match_originalData'].find_one({'type': 'Fmap'})
    if map:
        Fmap['_id'] = map['_id']
        for (k,v) in pickle.loads(map['data']).iteritems(): Fmap[k] = v
    for famMatch in config['fam_matches'].find({'status': {'$in': list(common.statOK)}}):
        cnt += 1
        if famMatch['workid'] in Fmap and Fmap[famMatch['workid']] != famMatch['matchid']:
            print famMatch['workRefId'], 'Family dubble map'
        Fmap[famMatch['workid']] = famMatch['matchid']
        workOrg = config['originalData'].find_one({'recordId': famMatch['workid'], 'type': 'family'})
        for rec in workOrg['data']:
            if rec['record']['_id'] in Fmap and Fmap[rec['record']['_id']] != famMatch['matchid']:
                print famMatch['workRefId'], 'Family dubble map from workOrg'
            Fmap[rec['record']['_id']] = famMatch['matchid']
        matchOrg = config['match_originalData'].find_one({'recordId': famMatch['matchid'], 'type': 'family'})
        for rec in matchOrg['data']:
            if rec['record']['_id'] in Fmap and Fmap[rec['record']['_id']] != famMatch['matchid']:
                print famMatch['matchRefId'], 'Family dubble map from matchOrg'
            Fmap[rec['record']['_id']] = famMatch['matchid']

    print 'Matched families', cnt, 'out of', config['families'].count(), '; Mapped:', len(Fmap)
    cnt=0
    #persons
    #get map from earlier merge's
    map = config['match_originalData'].find_one({'type': 'Imap'})
    if map:
        Imap['_id'] = map['_id']
        for (k,v) in pickle.loads(map['data']).iteritems(): Imap[k] = v
    for match in config['matches'].find({'status': {'$in': list(common.statOK)}}):
        cnt += 1
        if match['workid'] in match:
            print match['pwork']['refId'], 'Person dubble map'
        Imap[match['workid']] = match['matchid']
        workOrg = config['originalData'].find_one({'recordId': match['workid'], 'type': 'person'})
        for rec in workOrg['data']:
            if rec['record']['_id'] in Imap and Imap[rec['record']['_id']] != match['matchid']:
                print match['workRefId'], 'Family dubble map from workOrg'
            Imap[rec['record']['_id']] = match['matchid']
        matchOrg = config['match_originalData'].find_one({'recordId': match['matchid'], 'type': 'person'})
        for rec in matchOrg['data']:
            if rec['record']['_id'] in Imap and Imap[rec['record']['_id']] != match['matchid']:
                print match['matchRefId'], 'Family dubble map from matchOrg'
            Imap[rec['record']['_id']] = match['matchid']

        #Find familes that person is child in
        workfam = config['families'].find_one({'children': match['workid']})
        matchfam = config['match_families'].find_one({'children': match['matchid']})
        if workfam and matchfam:
            #FIX KOLLA check status för fam-match workfam-matchfam
            if workfam['_id'] in Fmap and Fmap[workfam['_id']] != matchfam['_id']:
                print 'Fam-map child disagree', match['pwork']['refId'], match['pmatch']['refId'],
                print 'In fams', workfam['refId'], matchfam['refId']
                print 'matched child', match['pwork']['refId'], match['pmatch']['refId']
                #print workfam
                #print matchfam
                checkFam(match['pwork']['refId'], match['pmatch']['refId'])

        #Find matched OK familes that person is husb or wife in
        for famMatch in config['fam_matches'].find({'status': {'$in': list(common.statOK)},
             '$or': [{'husb.pwork._id': match['workid'],'husb.pmatch._id': match['matchid'] },
                     {'wife.pwork._id': match['workid'],'wife.pmatch._id': match['matchid'] },
                    ]}):
            if famMatch['workid'] in Fmap and Fmap[famMatch['workid']] != famMatch['matchid']:
                print 'Personmap husb/wife disagree', match['pwork']['refId'], match['pmatch']['refId'],
                print 'In fams', famMatch['workrefId'], famMatch['matchrefId']
        #KOLLA OM data i originalData också OK??

    print 'Matched persons', cnt, 'out of', config['persons'].count(), '; Mapped:', len(Imap)
    return
