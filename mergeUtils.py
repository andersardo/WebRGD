# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import re
from collections import defaultdict
import common
from dbUtils import getFamilyFromId
from utils import matchFam
import pickle
from operator import itemgetter
Imap = defaultdict(set)
Fmap = defaultdict(set)
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

def mergeEventLongest(events):
    evDict = {}
    for field in ("date", "source", "place", "normPlaceUid"):
        ll = []
        for ev in events:
            if field in ev:
                ll.append(ev[field])
        if ll:
           evDict[field] = mergeSimple(ll)
    return evDict

def mergeEvent(events):
    #Use quality to select which events to merge
    events.sort(key = itemgetter('quality'))
    mergeEvents = [events[0]]
    for ev in events[1:]:
        if ev['quality'] == mergeEvents[0]['quality']: mergeEvents.append(ev)
        else: break
    ev = mergeEventLongest(mergeEvents)
    ev['quality'] = mergeEvents[0]['quality']
    return ev

def mergeOrgDataPers(personUid, personDB, originalDataDB):
    #reverseImap matchId -> set(person uids)
    persDict = {}
    rawData = defaultdict(list)
    for uid in reverseImap[personUid]:
        orgRec = originalDataDB.find_one({'recordId': personUid}) # evt 'type': 'person'?
        for field in ('name', 'sex', 'grpNameLast', 'grpNameGiven', 'birth', 'death'):
            if field in orgRec['data'][0]['record']:
                rawData[field].append(orgRec['data'][0]['record'][field])
    #simple fields
    for field in ('name', 'sex', 'grpNameLast', 'grpNameGiven'):
        if field in rawData:
            persDict[field] = mergeSimple(rawData[field])
    #events
    for ev in ('birth', 'death'):
        try:
            persDict[ev] = mergeEvent(rawData[ev])
        except:
            pass  #KOLLA tomma events ska finnas i DB
    return persDict

def mergeOrgDataFam(recordid, families, originalData):
    """ Merge orginalData for 'recordid' into a
        combined record used in RGD.
        marriage uses maxdict to determine which value to keep"""
    rawdataMar = []
    famDict = {}
    for uid in reverseFmap[recordid]:
        orgRec = originalDataDB.find_one({'recordId': recordid}) # evt 'type': 'person'?
        if 'marriage' in orgRec['data'][0]['record']:    #KOLLA - finns fler records i listan
            rawdataMar.append(orgRec['data'][0]['record'][field])
    try:
        famDict['marriage'] = mergeEvent(rawdataMar)
    except:
        pass  #KOLLA om tomma events ska finnas i DB 
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
                print 'NY MATCH - NOT SAVED',famMatchData['workRefId'],famMatchData['workRefId'],famMatchData['status']

def createMap(config):
    #creates map work -> match for all statusOK
    #
    cnt=0
    #families
    #get map from earlier merge's
    map = config['match_originalData'].find_one({'type': 'Fmap'})
    if map:
        Fmap['_id'] = map['_id']
        for (k,v) in pickle.loads(map['data']).iteritems(): Fmap[k] = v
    else:  #initialize with identity map from match
        for F in config['match_families'].find({}, {'_id': 1}): Fmap[F['_id']].add(F['_id'])
    for famMatch in config['fam_matches'].find({'status': {'$in': list(common.statOK)}}):
        if famMatch['workid'] in Fignore: continue
        cnt += 1
        if famMatch['workid'] in Fmap and Fmap[famMatch['workid']] != famMatch['matchid']:
            print 'Family', famMatch['workid'], 'in dbI matches', Fmap[famMatch['workid']], famMatch['matchid'], 'in dbII'
            print 'NO IGNORE'
            #del Fmap[famMatch['workid']]
            #Fignore.append(famMatch['workid'])
            #continue
        Fmap[famMatch['workid']].add(famMatch['matchid'])
        workOrg = config['originalData'].find_one({'recordId': famMatch['workid'], 'type': 'family'})
        for rec in workOrg['data']:
            Fmap[rec['record']['_id']].add(famMatch['matchid'])
        matchOrg = config['match_originalData'].find_one({'recordId': famMatch['matchid'], 'type': 'family'})
        for rec in matchOrg['data']:
            Fmap[rec['record']['_id']].add(famMatch['matchid'])

    print 'Matched families', cnt, 'out of', config['families'].count(), '; Mappings:', len(Fmap)
    cnt=0
    #persons
    #get map from earlier merge's
    map = config['match_originalData'].find_one({'type': 'Imap'})
    if map:
        Imap['_id'] = map['_id'] #KOLLA Imap is dict of set
        for (k,v) in pickle.loads(map['data']).iteritems(): Imap[k] = v
    else:  #initialize with identity map from match
        for P in config['match_persons'].find({}, {'_id': 1}): Imap[P['_id']].add(P['_id'])
    for match in config['matches'].find({'status': {'$in': list(common.statOK)}}):
        cnt += 1
        if match['workid'] in match:
            print match['pwork']['refId'], 'Person dubble map'
        Imap[match['workid']].add(match['matchid'])
        workOrg = config['originalData'].find_one({'recordId': match['workid'], 'type': 'person'})
        for rec in workOrg['data']:
            Imap[rec['record']['_id']].add(match['matchid'])
        matchOrg = config['match_originalData'].find_one({'recordId': match['matchid'], 'type': 'person'})
        for rec in matchOrg['data']:
            Imap[rec['record']['_id']].add(match['matchid'])
        """
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
        """
        #reverse maps
        for pers in Imap.keys():
            for P in Imap[pers]:
                reverseImap[P].add(pers)
        for fam  in Fmap.keys():
            for F in Fmap[fam]:
                reverseImap[F].add(fam)


    print 'Matched persons', cnt, 'out of', config['persons'].count(), '; Mappings:', len(Imap)
    return
