# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
from collections import defaultdict
from errRelationUtils import sanity, genGraphFam, genGraphPers, repairChild, repairFam, repairRel
from mergeUtils import mergePers, mergeFam, findAndMergeDuplFams
from dbUtils import getFamilyFromId
from uiUtils import eventDisp, persDisp
from operator import itemgetter
from luceneDB import luceneDB

dubblList = []

def editList(config, typ):
    global dubblList
    tit = 'Verktyg databas: ' + config['workDB']
    doTyp = []
    if typ in ('child', 'family', 'relation', 'dubblett', 'dubblettFind'):
        doTyp.append(typ)
    else:
        dubblList = []
        doTyp.append('child')
        doTyp.append('family')
    childErrs = [['Typ', 'Person', 'Familjer', 'action']]
    famErrs = [['Typ', 'Personer', 'Familj', 'action']]
    relErrs = [['Typ', 'Person', '', 'action']]
    dubbls = []
    (cErr, fErr, rErr) = sanity(config['persons'], config['families'],
                                    config['relations'], do=doTyp)
    cErr = repairChild(cErr, config['persons'], config['families'],
                       config['relations'], config['originalData'])
    for (pers, chFams) in cErr:
        child = "%s %s" % (pers['_id'], pers['name'])
        args = {'where': 'visa', 'what': '/view/relErr', 'typ': 'child',
                'person': str(pers['_id']), 'family': str(':'.join(chFams))}
        visa = '<button onclick="doAction('+str(args)+')">Visa</button>'
        childErrs.append(['Child', child, '; '.join(chFams), visa])
    #1 husb/wife per family
    fErr = repairFam(fErr, config['persons'], config['families'],
                       config['relations'], config['originalData'])
    for (famId, persList) in fErr:
        person = []
        pids = []
        for pers in persList:
            person.append("%s %s" % (pers['_id'], pers['name']))
            pids.append(pers['_id'])
        args = {'where': 'visa', 'what': '/view/relErr', 'typ': 'partner',
                'person': str(':'.join(pids)), 'family': str(famId)}
        visa = '<button onclick="doAction('+str(args)+')">Visa</button>'
        famErrs.append(['Partner', '<br>'.join(person), famId, visa])
    rErr = repairRel(rErr, config['persons'], config['families'],
                       config['relations'], config['originalData'])
    for pid in rErr:
        pers = config['persons'].find_one({'_id': pid})
        person = "%s %s" % (pers['_id'], pers['name'])
        args = {'where': 'visa', 'what': '/view/relErr', 'typ': 'noRel',
                'person': str(pers['_id']), 'family': ''}
        visa = '<button onclick="doAction('+str(args)+')">Visa</button>'
        relErrs.append(['Inga relationer', person, '', visa])
    if typ in ('dubblett', 'dubblettFind'):
        tit = 'Dubblett editor databas: ' + config['workDB']
        if len(dubblList) == 0 or typ=='dubblettFind':
            searchDB = luceneDB(config['workDB'])
            from matchtext import matchtext
            mt_tmp = matchtext()
            tab = []
            done = []
            tab.append(['Score',u'Namn/Id', 'Kandidat Id'])
            for person in config['persons'].find():
                matchtxt = mt_tmp.matchtextPerson(person, config['persons'],
                                                  config['families'], config['relations'])
                txt = []
                for term in matchtxt.split():
                    if term.startswith('Father') or term.startswith('Mother'): continue
                    txt.append(term)
                #candidates = search(' '.join(txt), person['sex'], 4) #Lucene search
                candidates = searchDB.search(' '.join(txt), person['sex'], 4) #Lucene search
                pstr = "%s %s" % (person['_id'], person['name'])
                for (kid,score) in candidates:
                    if kid == person['_id']: continue
                    if ';'.join([person['_id'],kid]) in done: continue
                    cand = config['persons'].find_one({'_id': kid})
                    if not cand: continue
                    try:
                        if abs(int(person['birth']['date'][0:4]) - int(cand['birth']['date'][0:4])) > 10:
                            continue
                    except:pass
                    args = {'where': 'visa', 'what': '/view/relErr', 'typ': 'dubblett',
                            'person': str(person['_id']), 'family': str(kid)}
                    visa = '<button onclick="doAction('+str(args)+')">Visa</button>'
                    tab.append(["%3.0f" % (score), pstr, kid, visa])
                    done.append(';'.join([kid,person['_id']]))
            dubblList = sorted(tab, key=itemgetter(0), reverse=True)[0:25]
            findAndMergeDuplFams(config['persons'], config['families'],
                                 config['relations'], config['originalData'])
            #OBS does not update luceneDB - OK?
        dubbls = dubblList
    return (tit, childErrs, famErrs, relErrs, dubbls)

def persTab(pId, personDB):
    tab = ['', '', '']
    if pId:
        person = personDB.find_one({'_id': pId})
        if 'refId' in person: pId = '; '.join([person['refId'], pId])
        tab[0] = "%s %s" % (pId, person['name'])
        try: tab[1] = eventDisp(person['birth'])
        except: pass
        try: tab[2] = eventDisp(person['death'])
        except: pass
    return tab

def viewChildErr(personIds, familyIds, config):
    tab = []
    drelargs = {'where': 'visa', 'what': '/actions/delRelation',
             'id1': '', 'id2': ''}
    tab.append(['',u'Namn/refId', u'Född', u'Död', '', u'Namn/refId', u'Född', u'Död', ''])
    fam1 = getFamilyFromId(familyIds[0], config['families'], config['relations'])
    fam2 = getFamilyFromId(familyIds[1], config['families'], config['relations'])
    for role in ('husb', 'wife'):
        drelargs['id1'] = str(fam1[role])
        drelargs['id2'] = str(familyIds[0])
        button = '<br><button onclick="doAction('+str(drelargs)+')">Ta bort relation</button>'
        t = [role + button]
        t.extend(persTab(fam1[role], config['persons']))
        if fam1[role]==fam2[role] and fam1[role]: t.append('Match')
        else: t.append('')
        t.extend(persTab(fam2[role], config['persons']))
        drelargs['id1'] = str(fam2[role])
        drelargs['id2'] = str(familyIds[1])
        button = '<button onclick="doAction('+str(drelargs)+')">Ta bort relation</button>'
        t.append(button)
        tab.append(t)
    try: marr1 = eventDisp(fam1['marriage'])
    except: marr1 = '-'
    try: marr2 = eventDisp(fam2['marriage'])
    except: marr2 = '-'
    fid1 = fam1['_id']
    fid2 = fam2['_id']
    if 'refId' in fam1: fid1 = '; '.join([fam1['refId'], fam1['_id']])
    if 'refId' in fam2: fid2 = '; '.join([fam2['refId'], fam2['_id']])
    args =  {'where': 'visa', 'what': '/actions/mergeFam',
             'id1': str(fam1['_id']), 'id2': str(fam2['_id'])}
    button = '<button onclick="doAction('+str(args)+')">Samma familj</button>'
    tab.append(['', fid1, marr1, '', button, fid2, marr2])
    tab.append(['', '', '', '', '', '', ''])
    done = []
    chDates = defaultdict(list)
    for chId in fam1['children']:
        k = 0
        person = config['persons'].find_one({'_id': chId}, {'birth.date': 1})
        if person and 'birth' in person and 'date' in person['birth']:
            if len(person['birth']['date']) == 8: k = int(person['birth']['date'])
            elif len(person['birth']['date']) == 4: k = int(person['birth']['date'])*10000
        chDates[k].append((1,chId))
        done.append(chId)
    for chId in fam2['children']:
        if chId in done: continue
        k = 0
        person = config['persons'].find_one({'_id': chId}, {'birth.date': 1, '_id': 0})
        if person and 'date' in person['birth']:
            if len(person['birth']['date']) == 8: k = int(person['birth']['date'])
            elif len(person['birth']['date']) == 4: k = int(person['birth']['date'])*10000
        chDates[k].append((2,chId))
    for k in sorted(chDates):
        for (pos,chId) in chDates[k]:
            drelargs['id1'] = str(chId)
            drelargs['id2'] = str(fam1['_id'])
            button = '<button onclick="doAction('+str(drelargs)+')">Ta bort relation</button>'
            t = [button]
            if pos == 1 and chId in fam2['children']:
                t.extend(persTab(chId, config['persons']))
                t.append('Match')
                t.extend(persTab(chId, config['persons']))
            elif pos==1:
                t.extend(persTab(chId, config['persons']))
                t.append('')
                t.extend(['', '', ''])
            elif pos==2:
                t.extend(['', '', ''])
                t.append('')
                t.extend(persTab(chId, config['persons']))
            drelargs['id2'] = str(fam2['_id'])
            button = '<button onclick="doAction('+str(drelargs)+')">Ta bort relation</button>'
            t.append(button)
            tab.append(t)
    graph = genGraphFam(personIds, familyIds[0], familyIds[1], config['persons'],
                        config['families'], config['relations'])
    return (tab, graph)

def viewPartnerErr(personIds, familyIds, config):
    tab = []
    tab.append(['',u'Namn/refId', u'Född', u'Död', 'Familj', u'Namn/refId', u'Född', u'Död'])
    t = ['']
    t.extend(persTab(personIds[0], config['persons']))
    fam = config['families'].find_one({'_id': familyIds}, {'refId': 1})
    fid = fam['_id']
    if 'refId' in fam: fid = '; '.join([fam['refId'], fam['_id']])
    args =  {'where': 'visa', 'what': '/actions/mergePers',
             'id1': str(personIds[0]), 'id2': str(personIds[1])}
    button = '<br><button onclick="doAction('+str(args)+')">Samma person</button>'
    t.append(fid + button)
    t.extend(persTab(personIds[1], config['persons']))
    tab.append(t)
    graph = genGraphPers(personIds[0], personIds[1], familyIds, config['persons'],
                        config['families'], config['relations'])
    return (tab,graph)

def viewNoRelErr(personIds, familyIds, config):
    #from luceneUtils import setupDir, search
    #setupDir(config['workDB'])
    searchDB = luceneDB(config['workDB'])
    from matchtext import matchtext
    mt_tmp = matchtext()
    person = config['persons'].find_one({'_id': personIds})
    matchtxt = mt_tmp.matchtextPerson(person, config['persons'],
                                      config['families'], config['relations'])
    candidates = searchDB.search(matchtxt, person['sex'], 5) #Lucene search
    tab = []
    tab.append(['Score',u'Namn/refId', u'Född', u'Död', '', u'Namn/refId', u'Född', u'Död'])
    for (kid,score) in candidates:
        if kid == personIds: continue
        cand = config['persons'].find_one({'_id': kid})
        if not cand: continue
        try:
            if abs(int(person['birth']['date'][0:4]) - int(cand['birth']['date'][0:4])) > 10:
                continue
        except:pass
        t = []
        t.append("%3.0f" % (score))
        t.extend(persTab(personIds, config['persons']))
        args =  {'where': 'visa', 'what': '/actions/mergePers',
                 'id1': str(personIds), 'id2': str(kid)}
        button = '<br><button onclick="doAction('+str(args)+')">Samma person</button>'
        t.append(button)
        t.extend(persTab(kid, config['persons']))
        tab.append(t)
    return (tab,'')

def viewDubbl(personId, kandidateId, config, find=False):
    tab = []
    tab.append(['',u'Namn/refId', u'Född', u'Död', '', u'Namn/refId', u'Född', u'Död'])
    t = ['']
    t.extend(persTab(personId, config['persons']))
    args =  {'where': 'visa', 'what': '/actions/mergePers',
             'id1': str(personId), 'id2': str(kandidateId)}
    button = '<br><button onclick="doAction('+str(args)+')">Samma person</button>'
    t.append(button)
    t.extend(persTab(kandidateId, config['persons']))
    tab.append(t)
    graph = genGraphPers(personId, kandidateId, None, config['persons'],
                        config['families'], config['relations'])
    return (tab,graph)

def viewQueryHits(Id, config):
    tab = []
    tab.append(['',u'Namn/refId', u'Född', u'Död', ''])
    t = ['']
    if Id.startswith('P_'):
        t.extend(persTab(Id, config['persons']))
        graph = genGraphPers(Id, None, None, config['persons'],
                        config['families'], config['relations'])
    elif Id.startswith('F_'):
        t.extend([Id, '', ''])
        graph = ''
    t.append('')
    tab.append(t)
    return (tab,graph)

def doGenGraphFam(persId, famId1, famId2, config):
    return genGraphFam(persId, famId1, famId2, config['persons'],
                       config['families'], config['relations'])

def doGenGraphPers(persId1, persId2, config):
    return genGraphPers(persId1, persId2, config['persons'], config['families'],
                        config['relations'])

def doMergePers(persId1, persId2, config):
    res = 'Not implemented fully'
    mergePers(persId1, persId2, config['persons'], config['families'],
             config['relations'], config['originalData'])
    return res

def doMergeFam(famId1, famId2, config):
    res = 'Not implemented fully'
    mergeFam(famId1, famId2, config['persons'], config['families'],
             config['relations'], config['originalData'], updateLucene=True)
    return res

def doRemoveFam(famId, config):
    res = 'Not implemented fully'
    #verify no relations
    #check originalData record
    config['families'].delete_one({'_id': famId})
    return res
