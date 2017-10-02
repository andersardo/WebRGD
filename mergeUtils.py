# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import re
from collections import defaultdict
import common
from dbUtils import getFamilyFromId
from utils import matchFam
import pickle

Imap = {}
Fmap = {}
reverseImap = defaultdict(set)
reverseFmap = defaultdict(set)
Fignore = []

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

def mergeSimple(items):
    it={}  #use defaultdict?
    for item in items:
        if item in it: it[item] += 1
        else: it[item]=1
    return maxdict(it)

def mergeEvent(events):
    #FIX Change to use quality
    evDict = {}
    for field in ("date", "source", "place", "normPlaceUid"):
        ll = []
        for ev in events:
            if field in ev:
                ll.append(ev[field])
        if ll:
           evDict[field] = mergeSimple(ll)
    return evDict

def mergeOrgDataPers(personUid, personDB, originalDataDB):
    #reverseImap matchId -> set(person uids)
    persDict = {}
    rawData = defaultdict(list)
    for uid in reverseImap[personUid]:
        orgRec = originalDataDB.find_one({'recordId': personUid}) # evt 'type': 'person'?
        for field in ('name', 'sex', 'grpNameLast', 'grpNameGiven', 'birth', 'death'):
            if field in orgRec['record']:
                rawData[field].append(orgRec['record'][field])
    #simple fields
    for field in ('name', 'sex', 'grpNameLast', 'grpNameGiven'):
        if field in rawData:
            persDict[field] = mergeSimple(rawData[field])
    #events
    for ev in ('birth', 'death'):
        persDict[ev] = mergeEvent(rawData[ev])
    return persDict

def mergeOrgDataFam(recordid, families, originalData):
    """ Merge orginalData for 'recordid' into a
        combined record used in RGD.
        marriage uses maxdict to determine which value to keep"""
    rawdataMar = []
    famDict = {}
    for uid in reverseFmap[recordid]:
        orgRec = originalDataDB.find_one({'recordId': recordid}) # evt 'type': 'person'?
        if 'marriage' in orgRec['record']:
            rawdataMar.append(orgRec['record'][field])
    famDict['marriage'] = mergeEvent(rawdataMar)
    return famDict

def checkFam(wid,mid):
  #wid, mid family refId 
  fams = set()
  for role in ('husb', 'wife', 'children'):
    tFam = common.config['families'].find({role: wid}, {'_id': 1, 'marriage.date': 1} )
    rFam = common.config['match_families'].find({role: mid}, {'_id': 1, 'marriage.date': 1} )
    tfams = []
    tDone = []
    #take all combinations of families
    #if several posibilities for 1 work-family keep only the the pair where marriage-date matches
    for f in tFam: #work
        for ff in rFam: #match
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
    for (tFamId,rFamId) in fams:    #  for all involved families do new matchning
        print 'checking',tFamId,rFamId
        famMatchData = matchFam(tFamId, rFamId, config)
        if common.config['fam_matches'].find({'workid': tFamId, 'matchid': rFamId}).count() == 0:
            if famMatchData['status'] in common.statOK.union(common.statManuell):
                #fam_matches.insert(famMatchData)
                print 'NY MATCH',famMatchData['workRefId'],famMatchData['workRefId'],famMatchData['status']

def createMap(config):
#?does not work?    global Fmap, Imap
    #creates map work -> match for all statusOK
    #
    cnt=0
    #families
    #get map from earlier merge's
    map = config['match_originalData'].find_one({'type': 'Fmap'})
    if map:
        Fmap['_id'] = map['_id']
        for (k,v) in pickle.loads(map['data']).iteritems(): Fmap[k] = v
    for famMatch in config['fam_matches'].find({'status': {'$in': list(common.statOK)}}):
        if famMatch['workid'] in Fignore: continue
        cnt += 1
        if famMatch['workid'] in Fmap and Fmap[famMatch['workid']] != famMatch['matchid']:
            print 'Family', famMatch['workRefId'], 'in dbI matches 2 families in dbII => ignore dbI family'
            print 'WARNING dbI family', famMatch['workRefId'], 'is NOT beeing merged into dbII'
            del Fmap[famMatch['workid']]
            Fignore.append(famMatch['workid'])
            continue
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

    print 'Matched families', cnt, 'out of', config['families'].count(), '; Mappings:', len(Fmap)
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
                print 'Personmap husb/wife disagree', match['pwork']['refId'], match['pmatch']['refId']
                #print '  In fams', famMatch['workrefId'], famMatch['matchrefId']
        #KOLLA OM data i originalData också OK??
        #reverse maps
        for pers in Imap.keys(): reverseImap[Imap[pers]].add(pers)
        for fam  in Fmap.keys(): reverseImap[Fmap[fam]].add(fam)
            

    print 'Matched persons', cnt, 'out of', config['persons'].count(), '; Mappings:', len(Imap)
    return
