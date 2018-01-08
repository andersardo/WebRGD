# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8

from collections import defaultdict
import common
#done in UI.py
#dbName = 'pilot2'
#dbNamergd = 'pilot1'
#config = common.init(dbName, matchDBName=dbNamergd)
from utils import matchFam
from bson.objectid import ObjectId

def eventDisp(e):
    txt = ''
    for field in ('date', 'place', 'source'):
        if field in e: txt += e[field] + '<br/>'
    if txt == '': txt = '-'
    return txt

def persDisp(p):
    if p:
        txt = []
        txt.append(p['name'] + '<br/>' + p['refId'])
        try: txt.append(eventDisp(p['birth']))
        except: txt.append('-')
        try: txt.append(eventDisp(p['death']))
        except: txt.append('-')
        return txt
    else:
        return ['-','-','-']

def persMatchDisp(role, pmI, workfamId = None, matchfamId = None):
    #HACK because status of persons in database fam_match are not always updated
    try:
        pm = common.config['matches'].find_one({'workid': pmI['workid'], 'matchid': pmI['matchid']})
    except:
        pm = pmI
    #FIX color dep status
    ignargs = {'where': 'res', 'what': '/actions/ignoreRelation', 'role': role,
               'workFam': str(workfamId), 'matchFam': str(matchfamId)}
    ign1 = ''
    ign2 = ''
    if role in ('husb', 'wife', 'child'):
        try:
            ignargs['pid'] = str(pm['pwork']['_id'])
            ignargs['fid'] = str(workfamId)
            flag = common.config['flags'].find_one({"typ" : "IgnoreRelation",
                                                    'persId': ignargs['pid'], 'relTyp': role,
                                                    'famId': ignargs['fid']})
            if flag: ign1 = '<b>Ignorerad</b>'
            else:
                ign1 = '<button onclick="doAction('+str(ignargs)+')">Ignorera relation</button>'
        except: pass
        try:
            ignargs['pid'] = str(pm['pmatch']['_id'])
            ignargs['fid'] = str(matchfamId)
            flag = common.config['flags'].find_one({"typ" : "IgnoreRelation",
                                                    'persId': ignargs['pid'], 'relTyp': role,
                                                    'famId': ignargs['fid']})
            if flag: ign2 = '<b>Ignorerad</b>'
            else:
                ign2 = '<button onclick="doAction('+str(ignargs)+')">Ignorera relation</button>'
        except: pass
    #cell1 = '<br/>'.join([role.capitalize(), ign1, ign2])
    #txt = [cell1, pm['status']]
    txt = [ign1]
    if 'pwork' in pm:
        txt.extend(persDisp(pm['pwork']))
    else:
        txt.extend(['-','-','-'])
    #persmatch buttons - if statements can be optimized
    args =  {'where': 'visa', 'what': '/view/persons', 'wid': '', 'mid': ''}
    if 'pwork' in pm: args['wid'] = str(pm['pwork']['_id'])
    if 'pmatch' in pm: args['mid'] = str(pm['pmatch']['_id'])
    persButton = '<button onclick="doAction('+str(args)+')">Visa</button>'
    if (role == 'child') and (pm['status'] in common.statEjOK):
        args['what'] = '/actions/split'
        persButton += '<br><button onclick="doAction('+str(args)+')">Split</button>'
    edit = ''
    try:
        graphArgs = {'where': 'graph', 'what': '/graph', 'role': role, 
                     'wid': str(pm['pwork']['_id']), 'mid': str(pm['pmatch']['_id'])}
        edit = '<button onclick="doAction('+str(graphArgs)+')">Graf</button>'
    except:
        pass
    #txt.extend([persButton + '<br/>' + edit])
    txt.extend(['<br/>'.join([role.capitalize()+'/ '+pm['status'], persButton, edit])])
    try: txt.extend(persDisp(pm['pmatch']))
    except: txt.extend(['-','_','-'])
    txt.extend([ign2])
    return txt

def famDisp( tmpfid, rgdfid, match = None ):
    if not match:
        #match = common.config['fam_matches'].find_one({'workid': ObjectId(tmpfid), 'matchid': ObjectId(rgdfid)})
        match = common.config['fam_matches'].find_one({'workid': tmpfid, 'matchid': rgdfid})
    tab = []
    tab.append(['', '<b>'+common.config['workDB'].split('_', 1)[1]+'</b>','','',
                '', '<b>'+common.config['matchDB'].split('_', 1)[1]+'</b>','','',''])
    tab.append(['', u'Namn/refId', u'Född', u'Död', '', u'Namn/refId', u'Född', u'Död', ''])
    for role in ('husb', 'wife'):
        try: tab.append(persMatchDisp(role, match[role], match['workid'], match['matchid']))
#        except: pass
        except Exception,e:
#            print 'Except in famDisp', e
            txt = [role,'?']
            try: txt.extend(persDisp(match[role]['pwork']))
            except: txt.extend(['-','_','-'])
            txt.extend([''])
            try: txt.extend(persDisp(match[role]['pmatch']))
            except: txt.extend(['-','_','-'])
            tab.append(txt)
    marr = ['', match['workRefId']]
    try: marr.append(eventDisp(match['marriage']['work']))
    except: marr.append('-')
    flag = common.config['flags'].find_one({"typ" : "IgnoreFamilyMatch",
                                            "workFam" : match['workid'],
                                            "matchFam" : match['matchid']})
    ign = 'Marriage/ '+match['status']
    if flag: ign += '<br><b>Ingnorerad</b>'
    else: ign += ''
    #marr.extend(['-', '<button onclick="doAction('+str(args)+')">Ignore Fam Match</button>', match['matchRefId']])
    marr.extend(['', ign, match['matchRefId']])
    try: marr.append(eventDisp(match['marriage']['match']))
    except: marr.append('-')
    marr.append('')
    marr.append('')
    tab.append(marr)
    tab.append(['','','','','','','','',''])
    for ch in match['children']:
        tab.append(persMatchDisp('child', ch, match['workid'], match['matchid']))
    return tab

##########NEW
def listFamilies(filters, multi, page=1, limit=10):
    fam_matches = common.config['fam_matches']
    start = limit * page - limit
    filter = []
    for (f,v) in filters.iteritems():
        if v=='checked': filter.append(f)
    rows = [['#','Status','Ant','Id','Namn',u'Gift','-']]
    i=start
    args = {'where': 'visa', 'what': '/view/families'}
#is refId uniq within a contribution??
    aggrPipe = [{'$match': {'status': {'$in': filter}}}]
    if multi['DB2'] == 'checked':
        aggrPipe.append({'$project': {'refId': '$matchRefId', '_id': '$matchRefId',
                                  'wid': '$workid', 'mid': '$matchid',
                                  'namn': {'$concat': [ { '$ifNull': ['$husb.pmatch.name','-']},
                                                       '<br/>',
                                                       { '$ifNull': ['$wife.pmatch.name','-']} ]},
                                  'status': 1, 'marriage': '$marriage',
                                  'count': {'$concat': ['1']}}})
    else:
        aggrPipe.append({'$project': {'refId': '$workRefId', '_id': '$workRefId',
                                  'wid': '$workid', 'mid': '$matchid',
                                  'namn': {'$concat': [ { '$ifNull': ['$husb.pwork.name','-']},
                                                       '<br/>',
                                                       { '$ifNull': ['$wife.pwork.name','-']} ]},
                                  'status': 1, 'marriage': '$marriage',
                                  'count': {'$concat': ['1']}}})
    if (multi['DB1'] == 'checked'):
        aggrPipe.append({'$group': {'_id': '$refId', 'count': {'$sum': 1},
                                    'namn': {'$first': '$namn'}, 'wid': {'$first': '$wid'},
                                    'marriage': {'$first': '$marriage'},
                                    'status': {'$first': '$status'}}})
        aggrPipe.append({'$match': {'count': {'$gt': 1}}})
    if (multi['DB2'] == 'checked'):
        aggrPipe.append({'$group': {'_id': '$refId', 'count': {'$sum': 1},
                                    'namn': {'$first': '$namn'}, 'mid': {'$first': '$mid'},
                                    'marriage': {'$first': '$marriage'},
                                    'status': {'$first': '$status'}}})
        aggrPipe.append({'$match': {'count': {'$gt': 1}}})
    #fix to avoid to big bson objects
    #calc tot
    try:
        tot = len(list(fam_matches.aggregate(aggrPipe)))
    except:
        tot = '??'
    #SORT BY count, refId
    aggrPipe.append({'$sort': {'count': -1, '_id': 1}})
    aggrPipe.append({'$skip': start})
    aggrPipe.append({'$limit': limit})
    for match in fam_matches.aggregate(aggrPipe):
#        tdd = '-'
        tfd = '-'
        if match['count'] == '1':
            args['wid'] = str(match['wid'])
            args['mid'] = str(match['mid'])
            try: tfd = eventDisp(match['marriage']['work'])
            except: tfd = '-'
        elif multi['DB1'] == 'checked':
            args['wid'] = str(match['wid'])
            args['mid'] = ''
            try: tfd = eventDisp(match['marriage']['work'])
            except: tfd = '-'
            match['status'] = ''  #dont show status when multilist
        elif multi['DB2'] == 'checked':
            args['wid'] = ''
            args['mid'] = str(match['mid'])
            try: tfd = eventDisp(match['marriage']['match'])
            except: tfd = '-'
            match['status'] = ''  #dont show status when multilist
        else:
            args['wid'] = ''
            args['mid'] = ''
        refId = match['_id']
        tnm = match['namn']
        visa = '<button onclick="doAction('+str(args)+')">Visa</button>'
        i += 1
#        rows.append([i, match['status'], match['count'], refId, tnm, tfd, tdd, visa])
        rows.append([i, match['status'], match['count'], refId, tnm, tfd, visa])
    return (rows, tot)

def familyViewAll(skipAnt=0):
    res = []
    buttons = True
    wid = None
    mid = None
    from collections import OrderedDict
    import re
    filter = OrderedDict()
    for stat in (common.statOK, common.statManuell, common.statEjOK):
        for s in list(stat): filter[s] = ''
    for s in list(common.statOK): filter[s] = 'checked'
    for s in list(common.statManuell): filter[s] = 'checked'

    #hitta multimatch OK/Manuell
    (tmp, tmp1) = listFamilies(filter, {'DB1': 'checked', 'DB2': ''}, page=1, limit=1)
    if len(tmp)>1:
        #'wid': '54302ae7d6dd3d665c3cd4cd',
        visa = tmp[1][6]
        m = re.search(r"\'wid\': \'([^']+)\',", visa)
        if m:
            wid = m.group(1)
        else: print 'No wid in DB1'
    else:
        (tmp, tmp1) = listFamilies(filter, {'DB1': '', 'DB2': 'checked'}, page=1, limit=1)
        if len(tmp)>1:
            print 'DB2', tmp
            visa = tmp[1][6]
            m = re.search(r"\'mid\': \'([^']+)\',", visa)
            if m: mid = ObjectId(m.group(1))
            else: print 'No mid in DB2'
    if not wid and not mid:
        print 'No DB1, DB2'
        #Alla manuella
        mt = common.config['fam_matches'].find_one({'status': {'$in': list(common.statManuell)}},
                                                   {'matchid': 1, 'workid': 1})
        if mt:
            wid = mt['workid']
            mid = mt['matchid']
        else: return res
    matches = common.config['fam_matches'].find({'$and': [
                    {'$or': [{'workid': wid}, {'matchid': mid}]},
                    {'status': {'$in': list(common.statOK.union(common.statManuell))}}
                    ]})
    for fmatch in matches:
        ftab = famDisp(None, None, fmatch)
        res.append(([], None, None, ftab,fmatch['workid'],fmatch['matchid']))
    return res

def familyView(wid, mid):
    res = []
    if wid and mid:
        matches = common.config['fam_matches'].find({'workid': wid, 'matchid': mid})
    elif wid:
        # multilista => only statOK and statManuell
#        matches = common.config['fam_matches'].find({'workid': ObjectId(wid)})
        matches = common.config['fam_matches'].find({'$and': [{'workid': wid},
                {'status': {'$in': list(common.statOK.union(common.statManuell))}}]})
    elif mid:
        # multilista => only statOK and statManuell
#        matches = common.config['fam_matches'].find({'matchid': ObjectId(mid)})
        matches = common.config['fam_matches'].find({'$and': [{'matchid': mid},
                {'status': {'$in': list(common.statOK.union(common.statManuell))}}]})
    else: matches = []
    for fmatch in matches:
        ftab = famDisp(None, None, fmatch)
        res.append(([], None, None, ftab,fmatch['workid'],fmatch['matchid']))
    return res

def listPersons(filters, multi, page=1, limit=10):
    matches = common.config['matches']
    #line_count = matches.find({}).count()
    #total = int(line_count / limit)+1
    start = limit * page - limit
    filter = []
    for (f,v) in filters.iteritems():
        if v=='checked': filter.append(f)
    rows = [['#','Status','Ant','Id','Namn',u'Född',u'Död','-']]
    i=start
    args = {'where': 'visa', 'what': '/view/persons'}
#is refId uniq within a contribution??
    aggrPipe = [{'$match': {'status': {'$in': filter}}}]
    if multi['DB2'] == 'checked':
        aggrPipe.append({'$project': {'refId': '$pmatch.refId', '_id': '$pmatch.refId',
                                  'wid': '$workid', 'mid': '$matchid',
                                  'namn': '$pmatch.name', 'status': 1,
                                  'birth': '$pmatch.birth', 'death': '$pmatch.death',
                                  'count': {'$concat': ['1']}}})
    else:
        aggrPipe.append({'$project': {'refId': '$pwork.refId', '_id': '$pwork.refId',
                                  'wid': '$workid', 'mid': '$matchid',
                                  'namn': '$pwork.name', 'status': 1,
                                  'birth': '$pwork.birth', 'death': '$pwork.death',
                                  'count': {'$concat': ['1']}}})
    #SORT BY refId
    if multi['DB1'] == 'checked':
        aggrPipe.append({'$group': {'_id': '$refId', 'count': {'$sum': 1},
                                    'namn': {'$first': '$namn'}, 'wid': {'$first': '$wid'},
                                    'status': {'$first': '$status'}}})
        aggrPipe.append({'$match': {'count': {'$gt': 1}}})
    if multi['DB2'] == 'checked':
        aggrPipe.append({'$group': {'_id': '$refId', 'count': {'$sum': 1},
                                    'namn': {'$first': '$namn'}, 'mid': {'$first': '$mid'},
                                    'status': {'$first': '$status'}}})
        aggrPipe.append({'$match': {'count': {'$gt': 1}}})
    try:
        tot = len(list(matches.aggregate(aggrPipe)))
    except:
        tot = '??'
    #SORT BY count, refId
    aggrPipe.append({'$sort': {'count': -1, '_id': 1}})
    aggrPipe.append({'$skip': start})
    aggrPipe.append({'$limit': limit})
    for match in matches.aggregate(aggrPipe):
        #print match
#FIX id, refId, namn, sum, född. död, visa
        if match['count'] == '1':
            args['wid'] = str(match['wid'])
            args['mid'] = str(match['mid'])
        elif multi['DB1'] == 'checked':
            args['wid'] = str(match['wid'])
            args['mid'] = ''
            match['status'] = ''
        elif multi['DB2'] == 'checked':
            args['wid'] = ''
            args['mid'] = str(match['mid'])
            match['status'] = ''
        else:
            args['wid'] = ''
            args['mid'] = ''
        refId = match['_id']
        tnm = match['namn']
        try: tfd = eventDisp(match['birth'])
        except: tfd = '-'
        try: tdd = eventDisp(match['death'])
        except: tdd = '-'
        visa = '<button onclick="doAction('+str(args)+')">Visa</button>'
        i += 1
        rows.append([i, match['status'], match['count'], refId, tnm, tfd, tdd, visa])
    return (rows,tot)

def personView(wid, mid):
    #show personMatch
    res = []
##BUG FIX handle as families: if wid & mid elif mid elif wid ...
#    if mid:
#        matches = common.config['matches'].find({'workid': ObjectId(wid), 'matchid': ObjectId(mid)})
#    else:
#        matches = common.config['matches'].find({'workid': ObjectId(wid)})
##
    if wid and mid:
        matches = common.config['matches'].find({'workid': wid, 'matchid': mid})
    elif wid:
        """
        AA0 debug
        # multilista => only statOK and statManuell
        matches = common.config['matches'].find({'$and': [{'workid': wid},
                {'status': {'$in': list(common.statOK.union(common.statManuell))}}]})
        """
        matches = common.config['matches'].find({'workid': wid})

    elif mid:
        # multilista => only statOK and statManuell
        matches = common.config['matches'].find({'$and': [{'matchid': mid},
                {'status': {'$in': list(common.statOK.union(common.statManuell))}}]})
    else: matches = []
##
    for pmatch in matches:
        #print 'Doing', pmatch['pwork']['refId'], pmatch['pmatch']['refId']
        #FIX Filter om match-status
        prow = persMatchDisp('Person', pmatch)
        #show familyMatch
        mid = pmatch['matchid']
#        if not wid: wid = str(pmatch['workid'])
        wid = str(pmatch['workid'])
        ftab = []
        #Match exists children?
        #print 'children'
        fmatch = common.config['fam_matches'].find_one({'children.pwork._id': wid, 'children.pmatch._id': mid})
        if fmatch: ftab = famDisp(None, None, fmatch)
        else:  #HUSB
            #print 'husb'
            fmatch = common.config['fam_matches'].find_one({'husb.pwork._id': wid, 'husb.pmatch._id': mid})
            if fmatch: ftab = famDisp(None, None, fmatch)
            else:  #WIFE
                #print 'wife',wid,mid
                fmatch = common.config['fam_matches'].find_one({'wife.pwork._id': wid, 'wife.pmatch._id': mid})
                if fmatch: ftab = famDisp(None, None, fmatch)
                else:
                    #print 'matchchild'
                    #wfamid = common.config['families'].find_one({'children': ObjectId(wid)}, {'_id': True})
                    wfamid = common.config['relations'].find_one({'relTyp': 'child', 'persId': wid})
                    #mfamid = common.config['match_families'].find_one({'children': mid}, {'_id': True})
                    mfamid = common.config['match_relations'].find_one({'relTyp': 'child', 'persId': wid})
                    if wfamid and mfamid:
                        try:
                            fmatch = matchFam(wfamid['famId'], mfamid['famId'], common.config)
                            ftab = famDisp(None, None, fmatch)
                        except: pass
                    else:
                        #print 'matchhusb'
                        #wfamid = common.config['families'].find_one({'husb': ObjectId(wid)}, {'_id': True})
                        #mfamid = common.config['match_families'].find_one({'husb': mid}, {'_id': True})
                        wfamid = common.config['relations'].find_one({'relTyp': 'husb', 'persId': wid})
                        mfamid = common.config['match_relations'].find_one({'relTyp': 'husb', 'persId': wid})
                        if wfamid and mfamid:
                            try:
                                fmatch = matchFam(wfamid['famId'], mfamid['famId'], common.config)
                                ftab = famDisp(None, None, fmatch)
                            except: pass
                        else:
                            #print 'matchwife'
                            #wfamid = common.config['families'].find_one({'wife': ObjectId(wid)}, {'_id': True})
                            #mfamid = common.config['match_families'].find_one({'wife': mid}, {'_id': True})
                            wfamid = common.config['relations'].find_one({'relTyp': 'wife', 'persId': wid})
                            mfamid = common.config['match_relations'].find_one({'relTyp': 'wife', 'persId': wid})
                            if wfamid and mfamid:
                                try:
                                    fmatch = matchFam(wfamid['famId'], mfamid['famId'], common.config)
                                    ftab = famDisp(None, None, fmatch)
                                except: pass

        if fmatch:
            #print 'fmatch', fmatch['workRefId'], fmatch['matchRefId']
#            res.append((prow, str(pmatch['workid']), str(pmatch['matchid']),
#                        ftab, fmatch['workRefId'], fmatch['matchRefId']))
            res.append((prow, str(pmatch['workid']), str(pmatch['matchid']),
                        ftab, fmatch['workid'], fmatch['matchid']))
        else: res.append((prow, str(pmatch['workid']), str(pmatch['matchid']), ftab,None,None))
    return res

def getFlags(personId, famId, role):
    #flags = [['oid','refId','famRefId','text'],[personId, '', '', 'Showing flags for']]
    flags = [['oid','refId','famId','text'],[personId, '', '', 'Showing flags for']]
    for flag in common.config['flags'].find({'workid': ObjectId(personId)}):
        #flags.append(flag)
        flags.append([flag['personid'],'',flag['famid'],flag['text']])
    return flags

"""
def addFlag(personId, famId, fltext):
    common.config['flags'].insert({'workid': ObjectId(personId), 'personid': personId,
                                   'famid': famId,'text': fltext})
    return getFlags(personId, famId, '')
"""
def ignoreRelation(persId, famId, role, workFam=None, matchFam=None):
    common.config['flags'].insert_one({'typ': 'IgnoreRelation', 'persId': persId, 'relTyp': role,
                                   'famId': famId})
    res = 'Flagga: IgnoreRelation person=%s roll=%s familj=%s<br>' % (persId, role, famId)
    common.config['flags'].insert_one({'typ': 'IgnoreFamilyMatch', 'workFam': workFam,
                                   'matchFam': matchFam})
    res += 'Flagga: IgnoreFamilyMatch %s -> %s<br>' % (workFam, matchFam)
    return res

#Skillnad
def nameDiff(work, match):
#    return ( (work['grpNameGiven'] != match['grpNameGiven']) or
#             (work['grpNameLast']  != match['grpNameLast']) )
#Redo so that only real diffs count, 'Lars Erik' and 'Lars' is OK
    for grp in ('grpNameGiven', 'grpNameLast'):
        s1 = set(work[grp].split())
        s2 = set(match[grp].split())
        if s1.issubset(s2) or s2.issubset(s1): pass
        else: return True
    return False

def nameGroupDiff(work, match):
    return ( (work['grpNameGiven'] != match['grpNameGiven']) or
             (work['grpNameLast']  != match['grpNameLast']) )

def nameSpellDiff(work, match):
    return (work['name'].lower().replace('*','').replace('(','').replace(')','').replace(' ','') !=
            match['name'].lower().replace('*','').replace('(','').replace(')','').replace(' ',''))

def eventDiff(work, match, events, items = ('date', 'normPlaceUid')):
    for typ in events:
        if (typ in work) and (typ in match):
#            for item in ('date', 'normPlaceUid'):
            for item in items:
                try:
                    if work[typ][item] != match[typ][item]:
                        if (item=='date') and (len(work[typ][item])==4 or
                                               len(match[typ][item])==4 ):
                            if work[typ][item][0:4] != match[typ][item][0:4]:
                                return True
                        else: return True
                except:
                    pass
    return False

def eventDiffD(work, match, events):
    for typ in events:
        print 'Doing', typ
        if (typ in work) and (typ in match):
            print 'exists'
            for item in ('date', 'normPlaceUid'):
                print 'doing', item
                try:
                    print work[typ][item], match[typ][item]
                    if work[typ][item] != match[typ][item]:
                        if (item=='date') and (len(work[typ][item])==4 or
                                               len(match[typ][item])==4 ):
                            print 'len 4',work[typ][item][0:4], match[typ][item][0:4]
                            if work[typ][item][0:4] != match[typ][item][0:4]:
                                return True
                        else: return True
                except:
                    print 'except'
                    pass
    return False

def listPersonSkillnad(page=1, limit=10, diffType=None):
    start = limit * page - limit
    matches = common.config['matches']
    rows = [['#',u'Namn/refId', u'Född', u'Död','|', u'Namn/refId', u'Född', u'Död','Visa']]
    i = 0
    args = {'where': 'visa', 'what': '/view/persons', 'buttons': 'No'}
    for persMatch in matches.find({'status':
          {'$in': list(common.statOK.union(common.statManuell))}}):
#        if (nameDiff(persMatch['pwork'], persMatch['pmatch']) or
        if (nameSpellDiff(persMatch['pwork'], persMatch['pmatch']) or
            eventDiff(persMatch['pwork'], persMatch['pmatch'], ('birth','death') )) :
#Limit list to certain type of diff
            #date
            if ((diffType == 'Datum') and 
                not eventDiff(persMatch['pwork'], persMatch['pmatch'],
                              ('birth','death'), ('date',))): continue
            #place
            if ((diffType == 'Plats') and 
                not eventDiff(persMatch['pwork'], persMatch['pmatch'],
                              ('birth','death'), ('normPlaceUid',))): continue
            #name
            if ((diffType == 'Namn') and
                not nameDiff(persMatch['pwork'], persMatch['pmatch'])): continue
            #name-groups
            if ((diffType == 'NamnGrupp') and
                not nameGroupDiff(persMatch['pwork'], persMatch['pmatch'])): continue
            #name-groups
            if ((diffType == 'NamnStavning') and
                not nameSpellDiff(persMatch['pwork'], persMatch['pmatch'])): continue
#end limit
            i += 1
            if i<=start: continue
            if i>start+limit:
                continue
#                break
            row = [str(i)]
            row1 = persDisp(persMatch['pwork'])
            row2 = persDisp(persMatch['pmatch'])
#Code below depends on order of what persMatch returns
#0=name, 1=birth, 2=death
            if persMatch['pwork']['name'].replace('*','').replace('/',' ').replace('(',' ').replace(')',' ') == persMatch['pmatch']['name'].replace('*','').replace('/',' ').replace('(',' ').replace(')',' '):
                row.append(row1[0])
            else:
                row.append('<b>' + row1[0] + '</b>')
            if eventDiff(persMatch['pwork'], persMatch['pmatch'], ('birth',) ):
                row.append(row1[1])
            else:
                row.append('')
            if eventDiff(persMatch['pwork'], persMatch['pmatch'], ('death',) ):
                row.append(row1[2])
            else:
                row.append('')
            row.append('|')

            if persMatch['pwork']['name'].replace('*','').replace('/',' ').replace('(',' ').replace(')',' ') == persMatch['pmatch']['name'].replace('*','').replace('/',' ').replace('(',' ').replace(')',' '):
#                row.append('')
                #row.append(row2[0].split('gedcom_',1)[1]) #Need gedcomId
                row.append(row2[0]) #Need gedcomId
            else:
                row.append(row2[0])
            if eventDiff(persMatch['pwork'], persMatch['pmatch'], ('birth',) ):
                row.append(row2[1])
            else:
                row.append('')
            if eventDiff(persMatch['pwork'], persMatch['pmatch'], ('death',) ):
                row.append(row2[2])
            else:
                row.append('')
            args['wid'] = str(persMatch['workid'])
            args['mid'] = str(persMatch['matchid'])
            row.append('<button onclick="doAction('+str(args)+')">Visa</button>')
            rows.append(row)
    return (rows,i)

def listFamiljeSkillnad(page):
    return ([['<h4>NOT implemented yet</h4>','']], 0)

def dbfind(db, q):
    #return 'Q:' + q.field
    mapQ = {'ID': '_id', 'name': 'name', 'refId': 'refId',
            'Birth date': 'birth.date'}
    import pprint
    query = {}
    #if q.field == 'refId': query['refId'] = q.val
    #else: return '???'
#    res = common.admClient[db][q.coll].find_one(query)
    query = {mapQ[q.field]: q.val}
    resTxt = ''
    for res in common.admClient[db][q.coll].find(query):
        resTxt += pprint.pformat(res) + "\n\n"
    if not resTxt:
        resTxt = 'No hits'
    return '<pre>' + resTxt + '</pre>'
