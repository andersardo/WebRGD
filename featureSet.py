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
