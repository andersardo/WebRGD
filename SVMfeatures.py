# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import common
from matchUtils import *
import os, sys, logging
from collections import defaultdict
from matchtext import matchtext
from luceneUtils import search, setupDir, index
mt_tmp = matchtext()
from dbUtils import getFamilyFromChild

#FIX! Normalization factor Lucene score
norm = {'kalle_testp1': 8.7, 'kalle_testp2': 5.1, 'kalle_testp3': 8.6,
        'kalle_testp4': 8.4, 'kalle_testp5': 10.2, 'default': 9.0}

def SVMfeatures(work, match, conf, score):
    return svmbaseline(work, match, conf, score)
#    alg = os.environ['SVMalgoritm']
#    if alg == 'all42': return svmall42(work, match, conf, score)
#    elif alg == 'baseline': return svmbaseline(work, match, conf, score)
#    elif alg == 'oldPerson': return svmbaseline(work, match, conf, score)
#    else:
#        print 'ERR unkown person algorithm:', alg
#        sys.exit()

def famSVMfeatures(work, match, conf, score):
    return svmFamily(work, match, conf)
#    alg = os.environ['famSVMalgoritm']
#    if alg == 'family': return svmFamily(work, match, conf)
#    else:
#        print 'ERR unkown family algorithm:', alg
#        sys.exit()

def cleanupVect(vect):
    return [0.0 if v is None else v for v in vect]
    cleanVect = []
    for e in v:
        if e: cleanVect.append(e)
        else: cleanVect.append(0.0)
    return cleanVect

def svmall42(work, match, config, score):
    sys.exit() #MUST BE FIXED
    features = svmfeatures(work, match)
    matchtxt = mt_tmp.matchtextPerson(work, config['persons'], config['families'])
    #lucene score
    if not score:
        candidates = search(matchtxt, work['sex'], ant=30, config=common.config) #Lucene search
        score = 0.0
        for (kid,sc) in candidates:
            #print kid, match['_id'], sc
            if str(kid) == str(match['_id']):
                score = sc
                break
    try: score = score / norm[config['matchDBname']]
    except: score = score / norm['default']
    if score == 0.0: score = 0.01 #??
    features.append(score)
    features.append(nodeSim(work, match))
    workFam = config['families'].find_one({ 'children': work['_id']})
    matchFam = config['match_families'].find_one({ 'children': match['_id']})
    features.append(familySim(workFam, config['persons'], matchFam, config['match_persons']))
    features.append(antFeaturesNorm(work, match))
    cand_matchtxt = mt_tmp.matchtextPerson(match, config['match_persons'], config['match_families'])
    features.append(cos(matchtxt, cand_matchtxt))
    #('namesim', 'Name similarity', 'numeric'),
    features.append(compName(work['name'].replace('/',''), match['name'].replace('/','')))
    nw = work['name'].split('/')
    nm = match['name'].split('/')
    #('gnamesim', 'Given name similarity', 'numeric'),
    features.append(compName(nw[0], nm[0]))
    #('lnamesim', 'Last name similarity', 'numeric'),
    features.append(compName(nw[1], nm[1]))
    #('nameedit', 'Name edit distance', 'numeric'),
    features.append(strSim(work['name'].replace('/',''), match['name'].replace('/','')))
    #('gnameedit', 'Given name edit distance', 'numeric'),
    features.append(strSim(nw[0], nm[0]))
    #('lnameedit', 'Last name edit distance', 'numeric'),
    features.append(strSim(nw[1], nm[1]))
    #('birthDate', 'Birthdate similarity'), 'numeric'),
    try: features.append(dateSim(work['birth']['date'], match['birth']['date']))
    except: features.append(dateSim(None, None))
    #('birthYear', 'Birthyear similarity'), 'numeric'),
    try: features.append(dateSim(work['birth']['date'][0:4], match['birth']['date'][0:4]))
    except: features.append(dateSim(None, None))
    #('birthPlace', 'Birthplace edit distance'), 'numeric'),
    try: features.append(strSim(work['birth']['place'], match['birth']['place']))
    except: features.append(strSim(None, None))
    #('deathDate', 'Deathdate similarity'), 'numeric'),
    try: features.append(dateSim(work['death']['date'], match['death']['date']))
    except: features.append(dateSim(None, None))
    #('deathYear', 'Deathyear similarity'), 'numeric'),
    try: features.append(dateSim(work['death']['date'][0:4], match['death']['date'][0:4]))
    except: features.append(dateSim(None, None))
    #('deathPlace' edit distnace
    try: features.append(strSim(work['death']['place'], match['death']['place']))
    except: features.append(strSim(None, None))

    return cleanupVect(features)

def svmbaseline(tmp,rgd, conf, score):
    features = []
#feature                            values
##Kön olika/lika                       0,1
    if (tmp['sex'] == rgd['sex']): features.append(1)
    else: features.append(0)

#Normalized given name finns och lika                 0,1
    if (tmp['grpNameGiven'] and rgd['grpNameGiven'] and
        (compName(tmp['grpNameGiven'], rgd['grpNameGiven'])) >= 0.6): features.append(1)
    else: features.append(0)
#Normalized given name finns och olika                0,1
    if (tmp['grpNameGiven'] and rgd['grpNameGiven'] and
        (compName(tmp['grpNameGiven'], rgd['grpNameGiven'])) < 0.6): features.append(1)
    else: features.append(0)
#Normalized given name finns inte                     0,1
    features.append(compValInte(tmp['grpNameGiven'],rgd['grpNameGiven']))

#Normalized last (family) name finns och lika                 0,1
    if (tmp['grpNameLast'] and rgd['grpNameLast'] and
        (compName(tmp['grpNameLast'], rgd['grpNameLast'])) >= 0.6): features.append(1)
    else: features.append(0)
#Normalized last (family) name finns och olika                0,1
    if (tmp['grpNameLast'] and rgd['grpNameLast'] and
        (compName(tmp['grpNameLast'], rgd['grpNameLast'])) < 0.6): features.append(1)
    else: features.append(0)
#Normalized last (family) name finns inte                     0,1
    features.append(compValInte(tmp['grpNameLast'],rgd['grpNameLast']))
#Född/död år finns och lika               0,1
#Född/död år finns och olika              0,1
#Född/död år finns inte                   0,1
#Född/död datum finns och lika            0,1
#Född/död datum finns och olika           0,1
#Född/död datum finns inte                0,1
#Född/död församling finns och lika       0,1
#Född/död församling finns och olika      0,1
#Född/död församling finns inte           0,1
    for ev in ('birth', 'death'):
        if (ev in tmp) and (ev in rgd):
            if ('date' in tmp[ev]) and ('date' in rgd[ev]):
                features.extend(compValAlla(tmp[ev]['date'][0:4],rgd[ev]['date'][0:4]))
            else: features.extend([0,0,1])
            for item in ('date', 'normPlaceUid'):                        #'place'??
                if (item in tmp[ev]) and (item in rgd[ev]):
                    features.extend(compValAlla(tmp[ev][item],rgd[ev][item]))
                else: features.extend([0,0,1])
        else: features.extend([0,0,1,0,0,1,0,0,1])
    if not score:
    #Lucene score use cos instead due to problems running Java in Bootle
        #matchtxt = mt_tmp.matchtextPerson(tmp, conf['persons'], conf['families'])
        matchtxt = mt_tmp.matchtextPerson(tmp, conf['persons'], conf['families'], conf['relations'])
        #cand_matchtxt = mt_tmp.matchtextPerson(rgd, conf['match_persons'], conf['match_families'])
        cand_matchtxt = mt_tmp.matchtextPerson(rgd, conf['match_persons'], conf['match_families'],
                                               conf['match_relations'])
        score = cos(matchtxt, cand_matchtxt) * 8.0
#        candidates = search(matchtxt, tmp['sex'], ant=30,config=common.config) #Lucene search
#        score = 0.0
#        for (kid,sc) in candidates:
#            if str(kid) == str(rgd['_id']):
#                score = sc
#                break
#        if score == 0.0: score = 0.01 #??
    features.append(score / 8.0) #from earlier match.py
    #features.append(score / 200.0) #Lucene 6
#nodeSim
    features.append(nodeSim(tmp, rgd))
#familySim
    #workFam = conf['families'].find_one({ 'children': tmp['_id']})
    workFam = getFamilyFromChild(tmp['_id'], conf['families'], conf['relations'])
    #matchFam = conf['match_families'].find_one({ 'children': rgd['_id']})
    matchFam = getFamilyFromChild(rgd['_id'], conf['match_families'], conf['match_relations'])
    features.append(familySim(workFam, conf['persons'], matchFam, conf['match_persons']))

    return cleanupVect(features)

"""
parametersMap = [
('sex', u'Kön olika/lika', '{ true, false }'),
('ngname=', 'Normalized given name finns och lika','{ true, false }'),
('ngname!=','Normalized given name finns och olika','{ true, false }'),
('ngname',  'Normalized given name finns inte','{ true, false }'),
('nlname=', 'Normalized last name finns och lika','{ true, false }'),
('nlname!=','Normalized last name finns och olika','{ true, false }'),
('nlname',  'Normalized last name finns inte','{ true, false }'),
('Byear=', u'Född år finns och lika', '{ true, false }'),
('Byear!=', u'Född år finns och olika', '{ true, false }'),
('Byear', u'Född år finns inte', '{ true, false }'),
('Bdate=', u'Född datum finns och lika', '{ true, false }'),
('Bdate!=', u'Född datum finns och olika', '{ true, false }'),
('Bdate', u'Född datum finns inte', '{ true, false }'),
('Bplace=', u'Född församling finns och lika', '{ true, false }'),
('Bplace!=', u'Född församling finns och olika', '{ true, false }'),
('Bplace', u'Född församling finns inte', '{ true, false }'),
('Dyear=', u'Död år finns och lika', '{ true, false }'),
('Dyear!=', u'Död år finns och olika', '{ true, false }'),
('Dyear', u'Död år finns inte', '{ true, false }'),
('Ddate=', u'Död datum finns och lika', '{ true, false }'),
('Ddate!=', u'Död datum finns och olika', '{ true, false }'),
('Ddate', u'Död datum finns inte', '{ true, false }'),
('Dplace=', u'Död församling finns och lika', '{ true, false }'),
('Dplace!=', u'Död församling finns och olika', '{ true, false }'),
('Dplace', u'Död församling finns inte', '{ true, false }'),
('score', 'Lucene score', 'numeric'),
('ns', 'NodeSim', 'numeric'),
('fs', 'FamilySim', 'numeric'),

('ant', 'Ant features', 'numeric'),
('cos', 'cos similarity matchtext', 'numeric'),
('namesim', 'Name similarity', 'numeric'),
('gnamesim', 'Given name similarity', 'numeric'),
('lnamesim', 'Last name similarity', 'numeric'),
('nameedit', 'Name edit distance', 'numeric'),
('gnameedit', 'Given name edit distance', 'numeric'),
('lnameedit', 'Last name edit distance', 'numeric'),
('birthDate', 'Birthdate similarity', 'numeric'),
('birthYear', 'Birthyear similarity', 'numeric'),
('birthPlace', 'Birthplace similarity', 'numeric'),
('deathDate', 'Deathdate similarity', 'numeric'),
('deathYear', 'Deathyear similarity', 'numeric'),
('deathPlace', 'Deathplace similarity', 'numeric'),

('match?', 'Match or not?', '{ yes, no }')
]
"""

def svmFamily(work, match, config):
#    work = config['families'].find_one({'refId': wid})
#    match = config['match_families'].find_one({'refId': mid})
    if not work or not match: return None
    fmatch = config['fam_matches'].find_one({'workid': work['_id'], 'matchid': match['_id']})
    logging.debug('fmatch=%s', fmatch)
    if not fmatch:
        from utils import matchFam
        fmatch = matchFam(work['_id'], match['_id'], config)
        logging.debug('fmatch=%s', fmatch)
    features = []
    #famSim
    features.append(familySim(work, config['persons'], match, config['match_persons']))
    #matchtext cos sim?
    #green Parents 0, 0.5, 1
    #yellow Parents 0, 0.5, 1
    #red Parents 0, 0.5, 1
    green = 0.0
    yellow = 0.0
    red = 0.0
    for partner in ('husb','wife'):
        try:
#FIX MODEL!!!
#            if fmatch['partner']['status'] in common.statOK: green += 0.5
#            elif fmatch['partner']['status'] in common.statManuell: yellow += 0.5
#            elif fmatch['partner']['status'] in common.statEjOK: red += 0.5
            if fmatch[partner]['status'] in common.statOK: green += 0.5
            elif fmatch[partner]['status'] in common.statManuell: yellow += 0.5
            elif fmatch[partner]['status'] in common.statEjOK: red += 0.5
        except: pass
    features.append(green)
    features.append(yellow)
    features.append(red)
    #green children 0 - 1
    #yellow children 0 - 1
    #red children 0 - 1
    #white children 0 - 1
    chstat = defaultdict(int)
    antch = 0.0
    for ch in fmatch['children']:
        antch += 1.0
        if ch['status'] in common.statOK: chstat['green'] += 1
        elif ch['status'] in common.statManuell: chstat['yellow'] += 1
        elif ch['status'] in common.statEjOK: chstat['red'] += 1
        elif ch['status'] == "": chstat['white'] += 1
        logging.debug('in loop %s %s', ch['status'], chstat)
    logging.debug('fmatch=%s, antch=%s, chstat=%s', len(fmatch['children']), antch, chstat)
    if antch==0: antch=1.0 #avoid division by 0
    features.append(float(chstat['green'])/antch)
    features.append(float(chstat['yellow'])/antch)
    features.append(float(chstat['red'])/antch)
    features.append(float(chstat['white'])/antch)
    #marriage datesim
    try: features.append(dateSim(work['marriage']['date'], match['marriage']['date']))
    except: features.append(dateSim(None, None))
    #marriage placesim
    try: features.append(strSim(work['marriage']['place'], match['marriage']['place']))
    except: features.append(strSim(None, None))
    #cos-sim fammatchtext - kanske inte - barn ofta olika!
    
    return cleanupVect(features)
