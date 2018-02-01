# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import re
from collections import defaultdict
import common
from luceneDB import luceneDB
#from dbUtils import getFamilyFromId
#from utils import matchFam
#import pickle
from operator import itemgetter
Imap = defaultdict(set)
Fmap = defaultdict(set)
#reverseImap = defaultdict(set)
#reverseFmap = defaultdict(set)
Fignore = []  #??

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
    ev['tag'] = mergeEvents[0]['tag']
    return ev


def createMapSimple(config):
    #creates map work -> match for all statusOK
    global Imap, Fmap
    Imap.clear()
    Fmap.clear()
    cnt=0
    for famMatch in config['fam_matches'].find({'status': {'$in': list(common.statOK)}}):
        if famMatch['workid'] in Fignore: continue
        cnt += 1
        Fmap[famMatch['workid']].add(famMatch['matchid'])
    print 'Matched families', cnt, 'out of', config['families'].count(), '; Mappings:', len(Fmap)
    cnt=0
    for match in config['matches'].find({'status': {'$in': list(common.statOK)}}):
        cnt += 1
        Imap[match['workid']].add(match['matchid'])
    print 'Matched persons', cnt, 'out of', config['persons'].count(), '; Mappings:', len(Imap)
    for pers in Imap.keys():
        if len(Imap[pers])>1: return True  # err = True
    for fam  in Fmap.keys():
        if len(Fmap[fam])>1: return True   # err = True
    return False

def mergePers(pId1, pId2, personDB, familyDB, relationDB, origDB):
    print '  Merging persons', pId2, 'into', pId1
    origDB.update_one({'recordId': pId1, 'type': 'person'},
                      {'$push': {'map': pId2}})
    for r in relationDB.find({'persId': pId2}):
        relationDB.delete_one(r)
        del(r['_id'])
        r['persId'] = pId1
        relationDB.replace_one(r, r, upsert=True)
    #merge birth/death
    for ev in ('birth', 'death'):
        Events = []
        for pid in (pId1, pId2):
            event = personDB.find_one({'_id': pid}, {'_id': False, ev: True})
            if event: Events.append(event[ev])
        if Events:
            personDB.update_one({'_id': pId1}, {'$set':
                                         {ev: mergeEvent(Events)}})
    personDB.delete_one({'_id': pId2}) #delete person pId2
    # FIX Need name of DB:
    searchDB = luceneDB(personDB.full_name.split('.')[0])
    searchDB.deleteRec(pId2)
    #Evt check if pId1 barn i två familjer och inga problem => delete den familjen
    return

def mergeFam(fId1, fId2, personDB, familyDB, relationDB, origDB, updateLucene=False):
    print '  Merging families', fId2, 'into', fId1
    origDB.update_one({'recordId': fId1, 'type': 'family'},
                      {'$push': {'map': fId2}})
    #Test fId1:husb/wife == fId2:husb/wife -- evt merge persons?
    partners = {}
    partners['husb']=set()
    partners['wife']=set()
    for fid in (fId1, fId2):
        for r in relationDB.find({'famId': fid,
                                 '$or': [{'relTyp': 'husb'}, {'relTyp': 'wife'}]}):
            partners[r['relTyp']].add(r['persId'])
    for partner in ('husb', 'wife'):
        if len(partners[partner]) == 2:
            p = list(partners[partner])
            mergePers(p[0], p[1], personDB, familyDB, relationDB, origDB)
    for r in relationDB.find({'famId': fId2}):
        relationDB.delete_one(r)
        del(r['_id'])
        r['famId'] = fId1
        relationDB.replace_one(r, r, upsert=True)
    #merge marriage events
    marrEvents = []
    for fid in (fId1, fId2):
        marr = familyDB.find_one({'_id': fid}, {'_id': False, 'marriage': True})
        if marr: marrEvents.append(marr['marriage'])
    if marrEvents:
        #print 'marrEvents', marrEvents
        familyDB.update_one({'_id': fId1}, {'$set':
                                    {'marriage': mergeEvent(marrEvents)}})
    familyDB.delete_one({'_id': fId2}) #delete family fId2
    if updateLucene:
        searchDB = luceneDB(personDB.full_name.split('.')[0])
        searchDB.deleteRec(fId2)
    return

def findAndMergeDuplFams(personDB, familyDB, relationDB, origDB):
    print 'Check and merge duplicate families'
    #Check for duplicate families
    #Find and merge families where husb and wife are same
    #  and marriages do not conflict(?)
    aggr = [{"$group": {"_id": {'persId': '$persId', 'relTyp': '$relTyp'},
                        "uniqueIds": { "$addToSet": "$famId" },
                        "count": { "$sum": 1 } } },
            { "$match": { "count": { "$gt": 1 } } }
    ]
    #{ "_id" : { "persId" : "P_1009483", "relTyp" : "wife" },
    #  "uniqueIds" : [ "F_374130", "F_374127" ], "count" : 2 }
    for fams in relationDB.aggregate(aggr):
        if fams['_id']['relTyp']=='wife': partner = 'husb'
        else: partner = 'wife'
        famId1 = fams['uniqueIds'][0]
        famId1Partner = relationDB.find_one({'relTyp': partner, 'famId': famId1},
                                                     {'_id': 0, 'persId': 1})
        for famId2 in fams['uniqueIds'][1:]:
            #check partners
            famId2Partner = relationDB.find_one({'relTyp': partner, 'famId': famId2},
                                                         {'_id': 0, 'persId': 1})
            if (famId1Partner is None) and (famId2Partner is None):
                fam1Chil = relationDB.find({'relTyp': 'child', 'famId': famId1}).count()
                fam2Chil = relationDB.find({'relTyp': 'child', 'famId': famId2}).count()
                if fam1Chil==0 and fam2Chil==0:
                    mergeFam(famId1, famId2, personDB, familyDB, relationDB, origDB)
            elif (famId1Partner and famId2Partner and
                  (famId1Partner['persId'] == famId2Partner['persId']) ):
                #Conflicting marriages?
                mergeFam(famId1, famId2, personDB, familyDB, relationDB, origDB)
    return

"""
#Merge dupl families
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

def mergeOrgDataPers(personUid, originalDataDB):
    #reverseImap matchId -> set(person uids)
    persDict = {'type': 'person', 'refId': personUid}
    rawData = defaultdict(list)
    for uid in reverseImap[personUid]:
        orgRec = originalDataDB.find_one({'recordId': uid}) # evt 'type': 'person'?
        for field in ('name', 'sex', 'grpNameLast', 'grpNameGiven', 'birth', 'death'):
            if field in orgRec['data'][0]['record']:
                rawData[field].append(orgRec['data'][0]['record'][field])
    #simple fields
    for field in ('name', 'sex', 'grpNameLast', 'grpNameGiven'):
        if field in rawData:
            persDict[field] = mergeSimple(rawData[field])
    #events
    events = defaultdict(list)
    for evTyp in ('birth', 'death'):
        for ev in rawData[evTyp]:
            events[ev['tag']].append(ev)
    if 'BIRT' in events:
        persDict['birth'] = mergeEvent(events['BIRT'])
    elif 'CHR' in events:
        persDict['birth'] = mergeEvent(events['CHR'])
    if 'DEAT' in events:
        persDict['death'] = mergeEvent(events['DEAT'])
    elif 'BURI' in events:
        persDict['death'] = mergeEvent(events['BURI'])
    return persDict

def mergeOrgDataFam(recordid, originalDataDB):
   #  Merge orginalData for 'recordid' into a
   #  combined record used in RGD.
   #  marriage uses maxdict to determine which value to keep
    rawdataMar = []
    famDict = {'type': 'family', 'refId': recordid}
    for uid in reverseFmap[recordid]:
        orgRec = originalDataDB.find_one({'recordId': uid}) # evt 'type': 'family'?
        if 'marriage' in orgRec['data'][0]['record']:    #KOLLA - finns fler records i listan
            rawdataMar.append(orgRec['data'][0]['record']['marriage'])
    try:
        famDict['marriage'] = mergeEvent(rawdataMar)
    except:
        pass  #KOLLA om tomma events ska finnas i DB
    return famDict

def mergeOrgDataRel(recordPid, recordFid, originalDataDB, relIgnore):
    # Merge orginalData for 'recordid' into a
    #    combined record used in RGD.
    #rawRels = defaultdict(list)
    rels = []
    for fid in reverseFmap[recordFid]:
        #for pid in reverseImap[recordPid]:
            orgRec = originalDataDB.find_one({'recordId': fid}) # evt 'type': 'family'?
            for r in orgRec['relation']:
                if '_id' in r: del r['_id']

                found = False
                for rr in relIgnore:
                    if cmp(rr, r) == 0:
                        found = True
                        break
                if found: continue
                if r['persId'] in Imap: r['persId'] = next(iter(Imap[r['persId']])) #FIX!!
                if r['famId'] in Fmap: r['famId'] = next(iter(Fmap[r['famId']]))    #FIX!!
                #rawRels[r['relTyp']].append(r) #Howmany?? choose best
                rels.append(r)
    #print 'rels=', rels
    return rels

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
        #print 'checking',tFamId,rFamId
        famMatchData = matchFam(tFamId, rFamId, config)
        if common.config['fam_matches'].find({'workid': tFamId, 'matchid': rFamId}).count() == 0:
            if famMatchData['status'] in common.statOK.union(common.statManuell):
                #fam_matches.insert(famMatchData)
                print 'NY MATCH - NOT SAVED',famMatchData['workRefId'],famMatchData['workRefId'],famMatchData['status']

def createMap(config):
    #creates map work -> match for all statusOK
    #
    err = False
    cnt=0
    #families
    #get map from earlier merge's
    map = config['match_originalData'].find_one({'type': 'Fmap'})
    if map:
        #Fmap['_id'] = map['_id']
        for (k,v) in pickle.loads(map['data']).iteritems(): Fmap[k] = v
    else:  #initialize with identity map from match
        for F in config['match_families'].find({}, {'_id': 1}): Fmap[F['_id']].add(F['_id'])
    for famMatch in config['fam_matches'].find({'status': {'$in': list(common.statOK)}}):
        if famMatch['workid'] in Fignore: continue
        cnt += 1
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
        #Imap['_id'] = map['_id'] #KOLLA Imap is dict of set
        for (k,v) in pickle.loads(map['data']).iteritems(): Imap[k] = v
    else:  #initialize with identity map from match
        for P in config['match_persons'].find({}, {'_id': 1}): Imap[P['_id']].add(P['_id'])
    for match in config['matches'].find({'status': {'$in': list(common.statOK)}}):
        cnt += 1
        Imap[match['workid']].add(match['matchid'])
        workOrg = config['originalData'].find_one({'recordId': match['workid'], 'type': 'person'})
        for rec in workOrg['data']:
            Imap[rec['record']['_id']].add(match['matchid'])
        matchOrg = config['match_originalData'].find_one({'recordId': match['matchid'], 'type': 'person'})
        for rec in matchOrg['data']:
            Imap[rec['record']['_id']].add(match['matchid'])
    print 'Matched persons', cnt, 'out of', config['persons'].count(), '; Mappings:', len(Imap)
    #reverse maps
    for pers in Imap.keys():
        if len(Imap[pers])>1:
            #print 'Multimatch person dbI', pers, '; dbI', Imap[pers]
            err = True
        for P in Imap[pers]:
            reverseImap[P].add(pers)
    for fam  in Fmap.keys():
        if len(Fmap[fam])>1:
            #print 'Multimatch family dbI', fam, 'dbII', Fmap[fam]
            err = True
        for F in Fmap[fam]:
            reverseFmap[F].add(fam)
    return err
"""
