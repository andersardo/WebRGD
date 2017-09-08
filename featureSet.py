# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
"""
Calculate SVM feature vector for various choises
Normally the selected routine is called as 'SVMfeatures'
"""
from matchUtils import *
from matchtext import matchtext

def personDefault(workP=None, matchP=None, conf=None, score=None, nodeScore=None,
              famScore=None, cosScore=None, features=None, matchtxtLen=None):
    """
    Optimized version of myselNoScore
    """
    fList = []
    if features is not None:
        fList.append(features['givenName']['eq'])
        fList.append(features['lastName']['eq'])
        fList.append(features['name']['strSim'])
        fList.append(features['birthYear']['eq'])
        fList.append(features['birthYear']['neq'])
        fList.append(features['birthYear']['sim'])
        fList.append(features['birthDate']['eq'])
        fList.append(features['birthDate']['neq'])
        fList.append(features['birthDate']['sim'])
        fList.append(features['birthPlace']['eq'])
        fList.append(features['birthPlace']['neq'])
        fList.append(features['birthPlace']['sim'])
        fList.append(features['deathYear']['eq'])
        fList.append(features['deathYear']['sim'])
        fList.append(features['deathDate']['eq'])
        fList.append(features['deathDate']['sim'])
        fList.append(features['deathPlace']['sim'])
        fList.append(features['score'])
        fList.append(features['scoreNorm'])
        fList.append(features['cosSim'])
        fList.append(features['cosSimNorm'])
        fList.append(features['NodeSim'])
        fList.append(features['FamilySim'])
        return [0.0 if v is None else v for v in fList]
    #fList.append(features['givenName']['eq'])
    #0.6 below is an arbitrary limit
    if (workP['grpNameGiven'] and matchP['grpNameGiven'] and
        (compName(workP['grpNameGiven'], matchP['grpNameGiven'])) >= 0.6):
        fList.append(1)
    else: fList.append(0)
    #fList.append(features['lastName']['eq'])
    if (workP['grpNameLast'] and matchP['grpNameLast'] and
        (compName(workP['grpNameLast'], matchP['grpNameLast'])) >= 0.6):
        fList.append(1)
    else: fList.append(0)
    #fList.append(features['name']['strSim'])
    fList.append(strSim(workP['name'].replace('/',''), matchP['name'].replace('/','')))
    #fList.append(features['birthYear']['eq'])
    #fList.append(features['birthYear']['neq'])
    try:
        vals = compValAlla(workP['birth']['date'][0:4],matchP['birth']['date'][0:4])
        fList.append(vals[0])
        fList.append(vals[1])
    except:
        fList.append(None)
        fList.append(None)
    #fList.append(features['birthYear']['sim'])
    try:
        vals = dateSim(workP['birth']['date'][0:4], matchP['birth']['date'][0:4])
    except:
        vals = None
    fList.append(vals)
    #fList.append(features['birthDate']['eq'])
    #fList.append(features['birthDate']['neq'])
    try:
        vals = compValAlla(workP['birth']['date'],matchP['birth']['date'])
        fList.append(vals[0])
        fList.append(vals[1])
    except:
        fList.append(None)
        fList.append(None)
    #fList.append(features['birthDate']['sim'])
    try:
        vals = dateSim(workP['birth']['date'], matchP['birth']['date'])
    except:
        vals = None
    fList.append(vals)
    #fList.append(features['birthPlace']['eq'])
    #fList.append(features['birthPlace']['neq'])
    try:
        vals = compValAlla(workP['birth']['normPlaceUid'],matchP['birth']['normPlaceUid'])
        fList.append(vals[0])
        fList.append(vals[1])
    except:
        fList.append(None)
        fList.append(None)
    #fList.append(features['birthPlace']['sim'])
    try:
        vals = strSim(workP['birth']['place'], matchP['birth']['place'])
    except:
        vals = None
    fList.append(vals)
    #fList.append(features['deathYear']['eq'])
    try:
        vals = compValAlla(workP['death']['date'][0:4],matchP['death']['date'][0:4])
        fList.append(vals[0])
    except:
        fList.append(None)
    #fList.append(features['deathYear']['sim'])
    try:
        vals = dateSim(workP['death']['date'][0:4], matchP['death']['date'][0:4])
    except:
        vals = None
    fList.append(vals)
    #fList.append(features['deathDate']['eq'])
    try:
        vals = compValAlla(workP['death']['date'],matchP['death']['date'])
        fList.append(vals[0])
    except:
        fList.append(None)
    #fList.append(features['deathDate']['sim'])
    try:
        vals = dateSim(workP['death']['date'], matchP['death']['date'])
    except:
        vals = None
    fList.append(vals)
    #fList.append(features['deathPlace']['sim'])
    try:
        vals = strSim(workP['death']['place'], matchP['death']['place'])
    except:
        vals = None
    fList.append(vals)
    #fList.append(features['cosSim'])
    fList.append(cosScore)
    #Length normalization: maxLen = 54
    #fList.append(features['cosSimNorm'])
    #EVT use len(matchtxt) or cosSimNorm as parameter??
    #mt_tmp = matchtext()
    #matchtxt = mt_tmp.matchtextPerson(workP, conf['persons'], conf['families'])
    #fList.append(cosScore*(len(matchtxt.split())/54.0))
    fList.append(cosScore*(matchtxtLen/54.0))
    #fList.append(features['NodeSim'])
    fList.append(nodeScore)
    #fList.append(features['FamilySim'])
    fList.append(famScore)
    return [0.0 if v is None else v for v in fList]

#############Below used for matchOptimization and different models################
#lucene v. 6.5 scaling
lucene6_5ScaleFactor = 100.0

def personAllFeatures(workP, matchP, conf, score, nodeScore, famScore, cosScore):
    features = {}
    features['NodeSim'] = nodeScore
    features['FamilySim'] = famScore
    features['antNodeFeatures'] = antFeaturesNorm(workP, matchP)
    mt_tmp = matchtext()
    matchtxt = mt_tmp.matchtextPerson(workP, conf['persons'], conf['families'])
    cand_matchtxt = mt_tmp.matchtextPerson(matchP, conf['match_persons'], conf['match_families'])
    features['matchtext1'] = matchtxt
    features['matchtext2'] = cand_matchtxt
    features['cosSim'] = cosScore
    #Length normalization: maxLen = 54
    features['cosSimNorm'] = features['cosSim']*(len(matchtxt.split())/54.0)
    try:   #score kan vara Null
        #features['score'] = score/8.8 #NORMALIZATION factor???
        #features['scoreNorm'] = (score/8.8)*(len(matchtxt.split())/54.0)
        #incl cuttoff (depends on scaleFactor)
        features['score'] = score/lucene6_5ScaleFactor
        if features['score'] > 1.0: features['score'] = 1.0
        features['scoreNorm'] = (score/lucene6_5ScaleFactor)*(len(matchtxt.split())/54.0)
        if features['scoreNorm'] > 1.0: features['scoreNorm'] = 1.0
    except:
        features['score'] = 0
        features['scoreNorm'] = 0

    #Kön olika/lika                       0,1
    if (workP['sex'] == matchP['sex']): features['sex'] = 1
    else: features['sex'] = 0

    #NAME
    features['name'] = {}
    features['givenName'] = {}
    features['lastName'] = {}
    #('namesim', 'Name similarity', 'numeric'),
    features['name']['noData'] = 0
    features['name']['sim'] = compName(workP['name'].replace('/',''), matchP['name'].replace('/',''))
    if features['name']['sim'] is None:
        features['name']['sim'] = 0
        features['name']['noData'] = 1
    nw = workP['name'].split('/')
    nm = matchP['name'].split('/')
    #('gnamesim', 'Given name similarity', 'numeric'),
    features['givenName']['sim'] = compName(nw[0], nm[0])
    if features['givenName']['sim'] is None: features['givenName']['sim'] = 0
    #('lnamesim', 'Last name similarity', 'numeric'),
    features['lastName']['sim'] = compName(nw[1], nm[1])
    if features['lastName']['sim'] is None: features['lastName']['sim'] = 0
    #('nameedit', 'Name string similarity', 'numeric'),
    features['name']['strSim'] = strSim(workP['name'].replace('/',''), matchP['name'].replace('/',''))
    if features['name']['strSim'] is None: features['name']['strSim'] = 0
    #('gnameedit', 'Given name string similarity', 'numeric'),
    features['givenName']['strSim'] = strSim(nw[0], nm[0])
    if features['givenName']['strSim'] is None: features['givenName']['strSim'] = 0
    #('lnameedit', 'Last name string similarity', 'numeric'),
    features['lastName']['strSim'] = strSim(nw[1], nm[1])
    if features['lastName']['strSim'] is None: features['lastName']['strSim'] = 0
    #Normalized given name finns och lika                 0,1
    if (workP['grpNameGiven'] and matchP['grpNameGiven'] and
        (compName(workP['grpNameGiven'], matchP['grpNameGiven'])) >= 0.6):
        features['givenName']['eq'] = 1
    else: features['givenName']['eq'] = 0
    #Normalized given name finns och olika                0,1
    if (workP['grpNameGiven'] and matchP['grpNameGiven'] and
        (compName(workP['grpNameGiven'], matchP['grpNameGiven'])) < 0.6):
        features['givenName']['neq'] = 1
    else: features['givenName']['neq'] = 0
    #Normalized given name finns inte                     0,1
    features['givenName']['noData'] = compValInte(workP['grpNameGiven'],matchP['grpNameGiven'])
    #Normalized last (family) name finns och lika                 0,1
    if (workP['grpNameLast'] and matchP['grpNameLast'] and
        (compName(workP['grpNameLast'], matchP['grpNameLast'])) >= 0.6): features['lastName']['eq'] = 1
    else: features['lastName']['eq'] = 0
    #Normalized last (family) name finns och olika                0,1
    if (workP['grpNameLast'] and matchP['grpNameLast'] and
        (compName(workP['grpNameLast'], matchP['grpNameLast'])) < 0.6): features['lastName']['neq'] = 1
    else: features['lastName']['neq'] = 0
    #Normalized last (family) name finns inte                     0,1
    features['lastName']['noData'] = compValInte(workP['grpNameLast'],matchP['grpNameLast'])

    #Född/död år finns och lika               0,1
    #Född/död år finns och olika              0,1
    #Född/död år finns inte                   0,1
    #Född/död datum finns och lika            0,1
    #Född/död datum finns och olika           0,1
    #Född/död datum finns inte                0,1
    #Född/död församling finns och lika       0,1
    #Född/död församling finns och olika      0,1
    #Född/död församling finns inte           0,1
    #BIRTH default values
    features['birthYear'] = {}
    features['birthDate'] = {}
    features['birthPlace'] = {}
    features['birthYear']['eq'] = 0
    features['birthYear']['neq'] = 0
    features['birthYear']['noData'] = 1
    features['birthDate']['eq'] = 0
    features['birthDate']['neq'] = 0
    features['birthDate']['noData'] = 1
    features['birthPlace']['eq'] = 0
    features['birthPlace']['neq'] = 0
    features['birthPlace']['noData'] = 1
    try:
        vals = compValAlla(workP['birth']['date'][0:4],matchP['birth']['date'][0:4])
        features['birthYear']['eq'] = vals[0]
        features['birthYear']['neq'] = vals[1]
        features['birthYear']['noData'] = vals[2]
    except: pass
    try:
        vals = compValAlla(workP['birth']['date'],matchP['birth']['date'])
        features['birthDate']['eq'] = vals[0]
        features['birthDate']['neq'] = vals[1]
        features['birthDate']['noData'] = vals[2]
    except: pass
    try:
        vals = compValAlla(workP['birth']['normPlaceUid'],matchP['birth']['normPlaceUid'])
        features['birthPlace']['eq'] = vals[0]
        features['birthPlace']['neq'] = vals[1]
        features['birthPlace']['noData'] = vals[2]
    except: pass
    #('birthDate', 'Birthdate similarity'), 'numeric'),
    try: features['birthDate']['sim'] = dateSim(workP['birth']['date'], matchP['birth']['date'])
    except: features['birthDate']['sim'] = dateSim(None, None)
    if features['birthDate']['sim'] is None: features['birthDate']['sim'] = 0
    #('birthYear', 'Birthyear similarity'), 'numeric'),
    try: features['birthYear']['sim'] = dateSim(workP['birth']['date'][0:4], matchP['birth']['date'][0:4])
    except: features['birthYear']['sim'] = dateSim(None, None)
    if features['birthYear']['sim'] is None: features['birthYear']['sim'] = 0
    #('birthPlace', 'Birthplace string similarity'), 'numeric'),
    try: features['birthPlace']['sim'] = strSim(workP['birth']['place'], matchP['birth']['place'])
    except: features['birthPlace']['sim'] = strSim(None, None)
    if features['birthPlace']['sim'] is None: features['birthPlace']['sim'] = 0
    #DEATH default values
    features['deathYear'] = {}
    features['deathDate'] = {}
    features['deathPlace'] = {}
    features['deathYear']['eq'] = 0
    features['deathYear']['neq'] = 0
    features['deathYear']['noData'] = 1
    features['deathDate']['eq'] = 0
    features['deathDate']['neq'] = 0
    features['deathDate']['noData'] = 1
    features['deathPlace']['eq'] = 0
    features['deathPlace']['neq'] = 0
    features['deathPlace']['noData'] = 1
    try:
        vals = compValAlla(workP['death']['date'][0:4],matchP['death']['date'][0:4])
        features['deathYear']['eq'] = vals[0]
        features['deathYear']['neq'] = vals[1]
        features['deathYear']['noData'] = vals[2]
    except: pass
    try:
        vals = compValAlla(workP['death']['date'],matchP['death']['date'])
        features['deathDate']['eq'] = vals[0]
        features['deathDate']['neq'] = vals[1]
        features['deathDate']['noData'] = vals[2]
    except: pass
    try:
        vals = compValAlla(workP['death']['normPlaceUid'],matchP['death']['normPlaceUid'])
        features['deathPlace']['eq'] = vals[0]
        features['deathPlace']['neq'] = vals[1]
        features['deathPlace']['noData'] = vals[2]
    except: pass
    #('deathDate', 'Deathdate similarity'), 'numeric'),
    try: features['deathDate']['sim'] = dateSim(workP['death']['date'], matchP['death']['date'])
    except: features['deathDate']['sim'] = dateSim(None, None)
    if features['deathDate']['sim'] is None: features['deathDate']['sim'] = 0
    #('deathYear', 'Deathyear similarity'), 'numeric'),
    try: features['deathYear']['sim'] = dateSim(workP['death']['date'][0:4], matchP['death']['date'][0:4])
    except: features['deathYear']['sim'] = dateSim(None, None)
    if features['deathYear']['sim'] is None: features['deathYear']['sim'] = 0
    #('deathPlace' edit similarity'
    try: features['deathPlace']['sim'] = strSim(workP['death']['place'], matchP['death']['place'])
    except: features['deathPlace']['sim'] = strSim(None, None)
    if features['deathPlace']['sim'] is None: features['deathPlace']['sim'] = 0
    return features

def featureList(features, useFeatures, useSubFeatures):
    fList = []
    for item in  useFeatures:
        if type(features[item]) is dict:
            for subitem in useSubFeatures:
                if subitem in features[item]:
                    if features[item][subitem] is None:
                        print 'ERR val=None', item, subitem
                    else:
                        fList.append(features[item][subitem])
        else:
            if features[item] is None:
                print 'ERR val=None', item
            else:
                fList.append(features[item])
    return [0.0 if v is None else v for v in fList]

useFeatures = {
    'all44': ['name', 'givenName', 'lastName',
             'birthYear', 'birthDate', 'birthPlace', 'deathYear', 'deathDate', 'deathPlace', 
             'antNodeFeatures', 'NodeSim', 'FamilySim', 'cosSim', 'cosSimNorm',
             'score', 'scoreNorm'],
    'noMiss44': ['name', 'givenName', 'lastName',
             'birthYear', 'birthDate', 'birthPlace', 'deathYear', 'deathDate', 'deathPlace', 
             'antNodeFeatures', 'NodeSim', 'FamilySim', 'cosSim', 'cosSimNorm',
             'score', 'scoreNorm'],
    'sel35': ['name', 'givenName', 'lastName',
             'birthYear', 'birthDate', 'birthPlace', 'deathYear', 'deathDate', 'deathPlace', 
             'antNodeFeatures', 'NodeSim', 'FamilySim', 'cosSim', 'cosSimNorm',
             'score', 'scoreNorm'],
    'sel19': ['name', 'givenName', 'lastName',
             'birthYear', 'birthDate', 'birthPlace', 'deathYear', 'deathDate', 'deathPlace', 
             'antNodeFeatures', 'NodeSim', 'FamilySim', 'cosSim', 'cosSimNorm',
             'score', 'scoreNorm']
}
useSubFeatures = {
    'all44': ['eq', 'neq', 'sim', 'strSim', 'noData'],
    'noMiss44': ['eq', 'neq', 'sim', 'strSim', 'noData'],
    'sel35': ['eq', 'neq', 'sim', 'strSim'],
    'sel19': ['sim', 'strSim'] 
}

def all44(workP=None, matchP=None, conf=None, score=None, nodeScore=None,
          famScore=None, cosScore=None, features=None, matchtxtLen=None):
    if features is None:
        features = personAllFeatures(workP, matchP, conf, score, nodeScore, famScore, cosScore)
    return featureList(features,
            ['name', 'givenName', 'lastName',
             'birthYear', 'birthDate', 'birthPlace', 'deathYear', 'deathDate', 'deathPlace', 
             'antNodeFeatures', 'NodeSim', 'FamilySim', 'cosSim', 'cosSimNorm',
             'score', 'scoreNorm'],
            ['eq', 'neq', 'sim', 'strSim', 'noData']
    )

def noMiss44(workP, matchP, conf, score, nodeScore, famScore, cosScore):
    return all44(workP, matchP, conf, score, nodeScore, famScore, cosScore)

def sel35(workP=None, matchP=None, conf=None, score=None, nodeScore=None,
          famScore=None, cosScore=None, features=None, matchtxtLen=None):
    if features is None:
        features = personAllFeatures(workP, matchP, conf, score, nodeScore, famScore, cosScore)
    return featureList(features,
                       useFeatures['sel35'], useSubFeatures['sel35'])

def sel35NF(workP=None, matchP=None, conf=None, score=None, nodeScore=None,
            famScore=None, cosScore=None, features=None, matchtxtLen=None):
    if features is None:
        features = personAllFeatures(workP, matchP, conf, score, nodeScore, famScore, cosScore)
    return featureList(features,
                       useFeatures['sel35'], useSubFeatures['sel35'])

def sel19(workP=None, matchP=None, conf=None, score=None, nodeScore=None,
          famScore=None, cosScore=None, features=None, matchtxtLen=None):
    if features is None:
        features = personAllFeatures(workP, matchP, conf, score, nodeScore, famScore, cosScore)
    return featureList(features,
                       useFeatures['sel19'], useSubFeatures['sel19'])

def baseline(workP, matchP, conf, score, nodeScore=None, famScore=None, cosScore=None):
    #implement baseline as reference. Original best model
    import SVMfeatures
    return SVMfeatures.SVMfeatures(workP, matchP, conf, score)

def baseline2(workP=None, matchP=None, conf=None, score=None, nodeScore=None,
              famScore=None, cosScore=None, features=None, matchtxtLen=None):
    fList = []
    if features is None:
        features = personAllFeatures(workP, matchP, conf, score, nodeScore, famScore, cosScore)
    fList.append(features['sex']) #1
    fList.append(features['givenName']['eq']) #2
    fList.append(features['givenName']['neq'])
    fList.append(features['givenName']['noData']) #4
    fList.append(features['lastName']['eq'])
    fList.append(features['lastName']['neq']) #6
    fList.append(features['lastName']['noData'])
    fList.append(features['birthYear']['eq']) #8
    fList.append(features['birthYear']['neq'])
    fList.append(features['birthYear']['noData']) #10
    fList.append(features['birthDate']['eq'])
    fList.append(features['birthDate']['neq']) #12
    fList.append(features['birthDate']['noData'])
    fList.append(features['birthPlace']['eq']) #14
    fList.append(features['birthPlace']['neq'])
    fList.append(features['birthPlace']['noData']) #16
    fList.append(features['deathYear']['eq'])
    fList.append(features['deathYear']['neq']) #18
    fList.append(features['deathYear']['noData'])
    fList.append(features['deathDate']['eq']) #20
    fList.append(features['deathDate']['neq'])
    fList.append(features['deathDate']['noData']) #22
    fList.append(features['deathPlace']['eq'])
    fList.append(features['deathPlace']['neq']) #24
    fList.append(features['deathPlace']['noData'])
    fList.append(features['score']) #26
    fList.append(features['NodeSim'])
    fList.append(features['FamilySim']) #28
    return [0.0 if v is None else v for v in fList]

def mysel(workP=None, matchP=None, conf=None, score=None, nodeScore=None,
          famScore=None, cosScore=None, features=None, matchtxtLen=None):
    fList = []
    if features is None:
        features = personAllFeatures(workP, matchP, conf, score, nodeScore, famScore, cosScore)
    fList.append(features['givenName']['eq'])
    #fList.append(features['givenName']['neq'])
    fList.append(features['lastName']['eq'])
    #fList.append(features['lastName']['neq'])
    fList.append(features['name']['strSim'])
    fList.append(features['birthYear']['eq'])
    fList.append(features['birthYear']['neq'])
    fList.append(features['birthYear']['sim'])
    fList.append(features['birthDate']['eq'])
    fList.append(features['birthDate']['neq'])
    fList.append(features['birthDate']['sim'])
    fList.append(features['birthPlace']['eq'])
    fList.append(features['birthPlace']['neq'])
    fList.append(features['birthPlace']['sim'])
    fList.append(features['deathYear']['eq'])
    #fList.append(features['deathYear']['neq'])
    fList.append(features['deathYear']['sim'])
    fList.append(features['deathDate']['eq'])
    #fList.append(features['deathDate']['neq'])
    fList.append(features['deathDate']['sim'])
    #fList.append(features['deathPlace']['eq'])
    #fList.append(features['deathPlace']['neq'])
    fList.append(features['deathPlace']['sim'])
    fList.append(features['score'])
    fList.append(features['scoreNorm'])
    fList.append(features['cosSim'])
    fList.append(features['cosSimNorm'])
    fList.append(features['NodeSim'])
    fList.append(features['FamilySim'])
    #fList.append(features['antNodeFeatures'])
    return [0.0 if v is None else v for v in fList]

def myselNF2(workP=None, matchP=None, conf=None, score=None, nodeScore=None,
             famScore=None, cosScore=None, features=None, matchtxtLen=None):
    return mysel(workP, matchP, conf, score, nodeScore, famScore, cosScore, features)

def myselNoScore(workP=None, matchP=None, conf=None, score=None, nodeScore=None, famScore=None, cosScore=None, features=None):
    fList = []
    if features is None:
        features = personAllFeatures(workP, matchP, conf, score, nodeScore, famScore, cosScore)
    fList.append(features['givenName']['eq'])
    #fList.append(features['givenName']['neq'])
    fList.append(features['lastName']['eq'])
    #fList.append(features['lastName']['neq'])
    fList.append(features['name']['strSim'])
    fList.append(features['birthYear']['eq'])
    fList.append(features['birthYear']['neq'])
    fList.append(features['birthYear']['sim'])
    fList.append(features['birthDate']['eq'])
    fList.append(features['birthDate']['neq'])
    fList.append(features['birthDate']['sim'])
    fList.append(features['birthPlace']['eq'])
    fList.append(features['birthPlace']['neq'])
    fList.append(features['birthPlace']['sim'])
    fList.append(features['deathYear']['eq'])
    #fList.append(features['deathYear']['neq'])
    fList.append(features['deathYear']['sim'])
    fList.append(features['deathDate']['eq'])
    #fList.append(features['deathDate']['neq'])
    fList.append(features['deathDate']['sim'])
    #fList.append(features['deathPlace']['eq'])
    #fList.append(features['deathPlace']['neq'])
    fList.append(features['deathPlace']['sim'])
    #fList.append(features['score'])
    #fList.append(features['scoreNorm'])
    fList.append(features['cosSim'])
    fList.append(features['cosSimNorm'])
    fList.append(features['NodeSim'])
    fList.append(features['FamilySim'])
    #fList.append(features['antNodeFeatures'])
    return [0.0 if v is None else v for v in fList]

def sou2():
    pass
#> Vi har pratat om att även använda källan i matchningen. Den korta varianten
#> som jag lägger i sou2 och som kan innehålla asterisk är likformad och kunde
#> lämpa sig för jämförelse.

##########################FAMILY#################
import logging #??
import common
from collections import defaultdict
def famBaseline(work, match, config):
#def svmFamily(work, match, config):
#    work = config['families'].find_one({'refId': wid})
#    match = config['match_families'].find_one({'refId': mid})
    if not work or not match: return None
    fmatch = config['fam_matches'].find_one({'workid': work['_id'], 'matchid': match['_id']})
    #logging.debug('fmatch=%s', fmatch)
    if not fmatch:
        from utils import matchFam
        fmatch = matchFam(work['_id'], match['_id'], config)
        #logging.debug('fmatch=%s', fmatch)
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
        #logging.debug('in loop %s %s', ch['status'], chstat)
    #logging.debug('fmatch=%s, antch=%s, chstat=%s', len(fmatch['children']), antch, chstat)
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
    return [0.0 if v is None else v for v in features]
    #return cleanupVect(features)

def famExtended(work, match, config):
    features = famBaseline(work, match, config)
    #husb, wife: childfam status none,red,yellow,green
    green = 0.0
    yellow = 0.0
    red = 0.0
    for partner in ('husb', 'wife'):
        #find family where partner child
        try:
            #work
            #wife.workid ger ObjectId: work[partner]['workid']
            #db.families.findOne({'children': ObjectId("58b456c77077b94d64947818")})
            tFam = config['families'].find_one({'children': work[partner]['workid']})
            workFamId = tFam['_id']
            #samma för match
            tFam = config['match_families'].find_one({'children': work[partner]['matchid']})
            matchFamId = tFam['_id']
            #get status for fam-match
            fmatch = config['fam_matches'].find_one({'workid': workFamId, 'matchid': matchFamId})
            if fmatch['status'] in common.statOK: green += 0.5
            elif fmatch['status'] in common.statManuell: yellow += 0.5
            elif fmatch['status'] in common.statEjOK: red += 0.5
        except:
            pass
    features.append(green)
    features.append(yellow)
    features.append(red)
    #for children use status families where they are husb,wife
    #average over childstatus - white,green,yellow,red
    return [0.0 if v is None else v for v in features]

