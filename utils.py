# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8

from matchUtils import matchPers
from dbUtils import getFamilyFromId
import common
import logging

def getIndivid(db, uid):
    if (uid == 'None'): return [None]
    q = "SELECT * FROM "+db+".individ WHERE I_uid="+uid
    c.execute(q)
    return c.fetchall()

def getMatchPers(wid, mid, conf):
    if ((wid is None) or (mid is None)): return None
    pmatch = conf['matches'].find_one({'workid': wid, 'matchid': mid})
#    if pmatch:     print '  getMatchPers', uid, rgduid, pmatch['status']
#    else:     print '  getMatchPers', uid, rgduid, 'NoMatch'
    return pmatch

def matchNamnFDate(tind, trgd):
    if (tind['sex'] != trgd['sex']): return False
    if ('birth' in tind) and ('birth' in trgd):
        if ('date' in tind['birth']) and ('date' in trgd['birth']):
            if (tind['birth']['date'] == trgd['birth']['date']):
                return True
    #TEST if distance between dates small
        #return True
#To uncertain?
#    if ((tind['grpNameGiven'] == trgd['grpNameGiven']) and (tind['grpNameLast'] == trgd['grpNameLast'])):
#        return True
    return False

def matchFam(tFamId, rFamId, conf):
    #KOLLA EVT accept match as optional param and not do everything?
    famMatchData = {}
    famMatchSummary = {}
    famMatchData['workid'] = tFamId
    famMatchData['matchid'] = rFamId

    stat = set()
    ##AAFIX
    #tFam = conf['families'].find_one({'_id': tFamId})
    tFam = getFamilyFromId( tFamId, conf['families'], conf['relations'])
    #rFam =conf['match_families'].find_one({'_id': rFamId})
    rFam = getFamilyFromId( rFamId, conf['match_families'], conf['match_relations'])
    famMatchData['workRefId'] = tFam['refId']
    famMatchData['matchRefId'] = rFam['refId']
    #famMatchData['marriage'] = {}
    #if 'marriage' in tFam: famMatchData['marriage']['work'] = tFam['marriage']
    #if 'marriage' in rFam: famMatchData['marriage']['match'] = rFam['marriage']
    antPartner = 0
    for partner in ('husb','wife'):
        famMatchData[partner] = {'status': 'notMatched'}
        famMatchSummary[partner] = {'status': 'notMatched'}
        if (tFam[partner]) and (rFam[partner]):
            antPartner += 1
            st = getMatchPers(tFam[partner],rFam[partner], conf)
            if not st:
                logging.debug('do matchPers %s %s %s', partner, tFam, rFam)
                st = matchPers( conf['persons'].find_one({'_id': tFam[partner]}),
                            conf['match_persons'].find_one({'_id': rFam[partner]}), conf )
                #logging.debug('Insert new unmatched parent %s %s %s',
                #              st['pwork']['refId'], st['pmatch']['refId'],st['status'])
                conf['matches'].insert(st)
            famMatchData[partner] = st
            famMatchSummary[partner] = st['status']
            if (st['status'] in common.statOK): stat.add('Match')
            elif (st['status'] in common.statManuell): stat.add('Manuell')
            elif (st['status'] in common.statEjOK): stat.add('EjMatch')
        elif tFam[partner]:
            famMatchData[partner]['pwork'] = conf['persons'].find_one({'_id': tFam[partner]})
        elif rFam[partner]:
            famMatchData[partner]['pmatch'] = conf['match_persons'].find_one({'_id': rFam[partner]})
    #Children ...
    tmpchilds = tFam['children']  #getChildrenUid('TmpMatch', str(tmpfuid))
    rgdchilds = rFam['children']  #getChildrenUid('RGD', str(rgdfuid))
    rgddone=[]
    tmpdone=[]
    if not tmpchilds: tmpchilds=[]
    if not rgdchilds: rgdchilds=[]
#checkOK??
    famMatchData['children'] = []
    famMatchSummary['children'] = set()
    #All matched persons
    for cond in ( [common.statOK,'Match'], [common.statManuell,'Manuell'],
                  [common.statEjOK, 'EjMatch'] ):
        for chtmp in tmpchilds:
            iuid = chtmp    #['I_uid']
            if iuid in tmpdone: continue
            for chrgd in rgdchilds:
                rgdiuid = chrgd   #['I_uid']
                if rgdiuid in rgddone: continue
                #FIXif chtmp['sex'] != chrgd['sex']: continue  #FIX
                st = getMatchPers(iuid,rgdiuid, conf)
                if (st and (st['status'] in cond[0])):
                    stat.add(cond[1])
                    rgddone.append(rgdiuid)
                    tmpdone.append(iuid)
                    try:
                        st['sort'] = st['pwork']['birth']['date']
                    except:
                        try:
                            st['sort'] = st['pmatch']['birth']['date']
                        except:
                            st['sort'] = '0'
                    famMatchData['children'].append(st)
                    famMatchSummary['children'].add(st['status'])
                    break
    #Try to find match
    for chtmp in tmpchilds:
        iuid = chtmp #['I_uid']
        if iuid in tmpdone: continue
        for chrgd in rgdchilds:
            rgdiuid = chrgd #['I_uid']
            if rgdiuid in rgddone: continue
            st = getMatchPers(iuid,rgdiuid, conf)
            if (st and (st['status'] == 'split')): continue
            #FIXif chtmp['sex'] != chrgd['sex']: continue  #FIX
            mt = matchPers( conf['persons'].find_one({'_id': iuid}),
                            conf['match_persons'].find_one({'_id': rgdiuid}), conf )
            if mt['status'] in common.statOK.union(common.statManuell):
                logging.debug('Inserting new unmatched child %s %s %s',
                              mt['status'], mt['pwork']['refId'], mt['pmatch']['refId'])
##WHY THIS??
                conf['matches'].insert(mt) #FIX Problem when doing dublMatch
                if mt['status'] in common.statOK: stat.add('Match')
                else: stat.add('Manuell')
                rgddone.append(rgdiuid)
                tmpdone.append(iuid)
                try:
                    mt['sort'] = mt['pwork']['birth']['date']
                except:
                    try:
                        mt['sort'] = mt['pmatch']['birth']['date']
                    except:
                        mt['sort'] = '0'
                famMatchData['children'].append(mt)
                famMatchSummary['children'].add(mt['status'])
                break
            t = conf['persons'].find_one({'_id': iuid})
            r = conf['match_persons'].find_one({'_id': rgdiuid})
            if matchNamnFDate( t, r ):
                stat.add('Manuell')
                rgddone.append(rgdiuid)
                tmpdone.append(iuid)
                try:
                    sort = t['birth']['date']
                except:
                    try:
                        sort = r['birth']['date']
                    except:
                        sort = '0'
                famMatchData['children'].append({'status': 'rManuell', 'workid': iuid, 'pwork': t,
                                                 'matchid': rgdiuid, 'pmatch': r, 'sort': sort})
                famMatchSummary['children'].add('rManuell')
                #Insert into matches also? Can't do setOKperson if pair not in person matches
                mt['status'] = 'rManuell'
                logging.debug('Inserting new name/date matched child %s %s %s',
                              mt['status'], mt['pwork']['refId'], mt['pmatch']['refId'])
                conf['matches'].insert(mt) #FIX Problem when doing dublMatch
                break
    #All the rest
    for chtmp in tmpchilds:
        if chtmp in tmpdone: continue
        p = conf['persons'].find_one({'_id': chtmp})
        try:
            sort = p['birth']['date']
        except:
            sort = '0'
        famMatchData['children'].append({'status': '', 'workid': chtmp, 'pwork': p, 'sort': sort})
    for chtmp in rgdchilds:
        if chtmp in rgddone: continue
        p = conf['match_persons'].find_one({'_id': chtmp})
        try:
            sort = p['birth']['date']
        except:
            sort = '0'
        famMatchData['children'].append({'status': '', 'matchid': chtmp, 'pmatch': p, 'sort': sort})

    #calc status
    if (('EjMatch' in stat) and ('Match' in stat)):
        status = 'Manuell'
    elif 'Manuell' in stat:
        status = 'Manuell'
    elif (('EjMatch' in stat) and ('Manuell' not in stat) and ('Match' not in stat)):
        status = 'EjMatch'
    elif (('EjMatch' not in stat) and ('Manuell' not in stat) and ('Match' in stat)):
        status = 'Match'
    else: status = 'Manuell'
    famMatchData['status'] = status
    famMatchSummary['status'] = status
    famMatchSummary['children'] = list(famMatchSummary['children'])
    famMatchData['summary'] = famMatchSummary
    #sort children in birth-order
    famMatchData['children'] = sorted(famMatchData['children'], key=lambda c: c['sort'])
    return famMatchData

def updateFamMatch(flist, conf):
    for fl in flist:  #Updatera familjestatus
#Om familjen inte matchad?
        for match in conf['fam_matches'].find({'workid': fl}, {'workid': True, 'matchid': True, 'workRefId': True, 'matchRefId': True, 'status': True}):
##EVT testa på status FamEjOK??
            if match['status'] == 'FamEjOK': continue
##EVT
            famMatchData = matchFam(match['workid'], match['matchid'], conf)
            logging.debug('Uppdating Fam-match %s %s Old=%s New=%s', match['workRefId'],
                          match['matchRefId'], match['status'], famMatchData['status'])
            conf['fam_matches'].delete_one({'_id': match['_id']})
            conf['fam_matches'].insert_one(famMatchData)
    return ''

def setFamOK(wid, mid, conf, famlist = None, button = False):
    fam_matches = conf['fam_matches']
    matches = conf['matches']
    families = conf['families']
    if button:
        kod ='OK'
        negKod = 'EjOK'
    else:
        kod = 'rOK'
        negKod = 'rEjOK'
    logging.debug('work=%s match=%s button=%s kod=%s', wid, mid, button, kod)
    if not famlist:
        #make famList from wid, mid (familyIDs)
        #Sanity check
        if fam_matches.find({'workid': wid, 'matchid': mid}).count() != 1:
            logging.error('SetFamOK - not exactly 1 fam_match for %s %s', wid, mid) #ERR?
        match = fam_matches.find_one({'workid': wid, 'matchid': mid})
        famlist = [['F',match['workid'],match['matchid'],match['workRefId'],match['matchRefId']]]
        for partner in ('husb', 'wife'):
            try:  #FIX? if only one of pwork and pmatch exists??
                famlist.append(['P', match[partner]['pwork']['_id'],
                                match[partner]['pmatch']['_id'],
                               match[partner]['pwork']['refId'],
                               match[partner]['pmatch']['refId']])
            except:
                pass
                #logging.debug('SetFamOK - No match for %s %s %s', wid, mid, partner) #byt till pass
        for ch in match.get('children',[]):
            try:  #FIX?
                famlist.append(['P', ch['workid'], ch['matchid']])
            except:
                if 'pwork' in ch:
                    pass
                    #print 'SetFamOK - No match for child work', ch['pwork']['refId']  #byt till pass
                elif 'pmatch' in ch:
                    pass
                    #print 'SetFamOK - No match for child match', ch['pmatch']['refId'] #byt till pass
                else:
                    logging.error('SetFamOK - No match for child %s', ch)   #byt till pass  ERR?
    indlist = []
    tmpfuid = '0'
    #logging.debug('famlist=%s', famlist)
    for pair in famlist:
        if pair[0]=='P':  #individ
            setOKperson(pair[1], pair[2], button = button) #Also updates family status
    stat = ''
    for pair in famlist:
        if pair[0]=='F': #familj
            #Force all familymatches to negKod except workid, matchid
            workid = pair[1]
            matchid = pair[2]
            #Set all family pairs famA,* and *,famB to negKod
            for famMatch in fam_matches.find({'$or': [{'workid': workid},{'matchid': matchid}]},
                                             {'workid': 1, 'matchid': 1}):
                if famMatch['workid']==workid and famMatch['matchid']==matchid: continue
                else: setEjOKfamily(str(famMatch['workid']), str(famMatch['matchid']), code=negKod)
    return ''

#FIX
def setOKfamily(wid, mid):
    return setFamOK(wid, mid, common.config, famlist = None, button = True)

def setEjOKfamily(wid, mid, code='EjOK'):
#Villkor: inget matchat barn eller båda föräldrarna matchade.
#Ska testas + evt felmeddelande
    logging.debug('setEjOKfamily %s %s %s', wid, mid, code)    #res =
    match = common.config['fam_matches'].find_one({'workid': wid, 'matchid': mid})
    if not match: return ''
    if (('husb' in match) and (match['husb']['status'] in common.statOK) and
        ('wife' in match) and (match['wife']['status'] in common.statOK)):
        return 'Not allowed: Both husb and wife matched'
    for ch in match['children']:
        if ch['status'] in common.statOK:
            return 'Not allowed: Matched child'
    res = ''
    for ch in match['children']:
        if ch['status'] in common.statManuell:
            #setEjOKperson calls updateFam
            res += setEjOKperson(str(ch['workid']), str(ch['matchid']), code=code)
    common.config['fam_matches'].update_one({'workid': wid, 'matchid': mid}, {'$set': {'status': 'FamEjOK'}})
    return res

def setOKperson(wid, mid, button = True):
    indlist = []
    if button:
        kod ='OK'
        negKod = 'EjOK'
    else:
        kod = 'rOK'
        negKod = 'rEjOK'
    for mt in common.config['matches'].find({'$or': [{'workid': wid},{'matchid': mid}]},
                  {'workid': True, 'matchid': True, 'status': True}):
        #logging.debug('In setOKperson found match %s', mt['_id'])
        if mt['workid']==wid and  mt['matchid']==mid:
            if common.checkStatusUpdate(mt['status'], kod):
                logging.debug('wid=%s mid=%s status old=%s new=%s',
                              wid, mid, mt['status'], kod)
                common.config['matches'].update_one({'_id': mt['_id']}, {'$set': {'status': kod}})
                if mt['status'] in common.statOK: continue  #No other updates needed
                indlist.append(mt['workid'])
                ##TEST
                #check if wid,mid med i något familjepar som inte är med i fam_matches
                #   insert minimum fam_match med status 'candidate' - svårt!!
                #   it gets updated later by updateFamMatch
                for role in ('husb', 'wife', 'children'):
                    tFam = common.config['families'].find({role: wid}, {'_id': 1} )
                    rFam = common.config['match_families'].find({role: mid}, {'_id': 1} )
                    for f in tFam:
                        for ff in rFam:
                            if not common.config['fam_matches'].find_one({'workid': f['_id'], 'matchid': ff['_id']}):
                                common.config['fam_matches'].insert_one(matchFam(f['_id'], ff['_id'], common.config))
                           #Check multifam-resolution From match.py
                ##TEST
        elif common.checkStatusUpdate(mt['status'], negKod):
            logging.debug('wid=%s mid=%s status old=%s new=%s',
                          mt['workid'], mt['matchid'], mt['status'], negKod)
            common.config['matches'].update_one({'_id': mt['_id']}, {'$set': {'status': negKod}})
            if mt['status'] in common.statEjOK: continue  #No other updates needed
            indlist.append(mt['workid'])
    if not indlist:
        #logging.debug('No status changing updates')
        return 'No status changing updates'
    flist = set()
    for id in indlist:  #get list of involved individuals
        for fam in common.config['families'].find({'$or': [{'husb': id}, {'wife': id}, {'children': id}]}, {'_id': True}):
            flist.add(fam['_id'])
    return updateFamMatch(flist, common.config)

def setEjOKperson(wid, mid, code='EjOK'):
    mt = common.config['matches'].find_one({'workid': wid, 'matchid': mid})
    if not common.checkStatusUpdate(mt['status'], code): return 'setEjOKperson Update not done'
    logging.debug('setEjOKperson work=%s match=%s code=%s', wid, mid, code)
    common.config['matches'].update_one({'_id': mt['_id']}, {'$set': {'status': code}})
    indlist = [wid]
    flist = set()
    for id in indlist:  #get list of involved individuals
        for fam in common.config['families'].find({'$or': [{'husb': id}, {'wife': id}, {'children': id}]}, {'_id': True}):
            flist.add(fam['_id'])
    return updateFamMatch(flist, common.config)

def kopplaLoss(personId, famId, role):
    #update family
    fam = common.config['families'].find_one({'_id': famId})
    #assert fam not None
    if role in ('husb', 'wife'):
        #assert fam[role] == personId
        #print fam
        fam[role] = None
    elif role == 'child':
        #assert personId in fam['children']
        fam['children'].remove(personId)
    else: pass  #ERR
#    common.config['families'].update_one({'_id': famId}, fam)
    common.config['families'].replace_one({'_id': famId}, fam)
#??
    #update originalData
    fam = common.config['originalData'].find_one({'recordId': famId})
    #assert fam not None
    for rec in fam['data']:
        if role in ('husb', 'wife'):
            #assert fam[role]?? == personId
            rec['record'][role] = None
        elif role == 'child':
            #assert personId in fam['children']??
            rec['record']['children'].remove(personId)
        else: pass  #ERR
#    common.config['originalData'].update_one({'recordId': famId}, fam)
    common.config['originalData'].replace_one({'recordId': famId}, fam)
#??
    #update fam_matches
    famMatch = common.config['fam_matches'].find_one({'workid': famId})
    flist = set()
    flist.add(famId)
    return updateFamMatch(flist, common.config) #funkar inte - ger fel i fam_matches

def split(wid, mid):
    """ Precondition personmatch in status statEjOK
    Parameters: wid workid, mid matchid for persons
    Algorithm:
      delete personmatch wid,mid
      generate famList from wid,mid
      update family matches for famList
    """
##    common.config['matches'].remove({'workid': wid, 'matchid': mid})
#TEST
    common.config['matches'].update_one({'workid': wid, 'matchid': mid},
                                    {'$set': {'status': 'split'}})
#TEST
    famList = set()
    for doc in common.config['fam_matches'].find({'$or':
                [{'husb.workid': wid, 'husb.matchid': mid},
                 {'wife.workid': wid, 'wife.matchid': mid}]}, {'workid': 1}):
        famList.add(doc['workid'])
    for doc in common.config['fam_matches'].find({'children.workid': wid}):
        for ch in doc['children']:
            if 'matchid' in ch and ch['matchid'] == mid:
                famList.add(doc['workid'])
                break
    logging.debug('split %s %s %s', wid,mid, famList)
    return updateFamMatch(famList, common.config)
