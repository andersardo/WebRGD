# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import re, sys, math
from datetime import date
from matchtext import matchtext

from svmutil import *
svmModel = svm_load_model('conf/person.model')
_cache = {}

def cos(l1,l2):
    """ Similarity between two vectors = cosine for the angle between the vectors:
	cosine  = ( V1 * V2 ) / ||V1|| x ||V2|| 
	Vectors expressed as strings, split on blankspace, assume boolean weights  """
    v1 = l1.split()
    v2 = l2.split()
    s=0
    for w1 in v1:
        if w1 in v2: s+=1
    return s/(math.sqrt(len(v1))*math.sqrt(len(v2)))

def compName(n1, n2):
    """ Compare names: n1 n2 strings, blankspace separated names
        return value between -1 (mismatch) and 1 (match)
        return None if any of n1, n2 is empty
        can be used on names, normalised names
    """
    nn1 = n1.strip().split()
    nn2 = n2.strip().split()
    if (not nn1) or (not nn2): return None
    if (len(nn1) > len(nn2)):
        return (2.0 * len(set(nn2).intersection(nn1)) - len(nn2)) / float(len(nn2))
    else:
        return (2.0 * len(set(nn1).intersection(nn2)) - len(nn1)) / float(len(nn1))

def dateSim(date1, date2):
    if (not date1) or (not date2): return None
    if date1 == date2: return 1.0
    if (len(date1) == 4) or (len(date2) == 4):
        if date1[0:4] == date2[0:4]: return 1.0
        else: return -1.0
    try:
        dat1 = date(int(date1[0:4]), int(date1[4:6]), int(date1[6:8]))
    except Exception, e:
        dat1 = date(int(date1[0:4]), int(date1[4:6]), int(date1[6:8])-1)
    try:
        dat2 = date(int(date2[0:4]), int(date2[4:6]), int(date2[6:8]))
    except Exception, e:
        dat2 = date(int(date2[0:4]), int(date2[4:6]), int(date2[6:8])-1)

    d = abs((dat1-dat2).days)
    if d<30:
        return (1.0-d/15.0)
    else:
        return -1.0

def strSim(txt1, txt2):
    """
      String similarity
      returns a value between -1 and +1
    """
    if (not txt1) or (not txt2): return None
    #print 'strSim', txt1, ':', txt2
    import difflib
    s = difflib.SequenceMatcher(None, txt1, txt2).ratio()
    return 2.0 * (s - 0.5)

def eventSim(ev1, ev2):
    boost=0
    sim=0.0
    n=0
    #Kalle:
    #Möjligen skulle datum/månad vara en jämförelse och årtal en.
    #I så fall skulle bonusen kunna kopplas till årtal och församling.
    if ('date' in ev1) and ('date' in ev2):
        if (ev1['date'][0:4] == ev2['date'][0:4]):
            sim += 1.0
            n += 1
            boost=1
        else:
            sim += -1.0
            n += 1
        s = dateSim(ev1['date'], ev2['date'])
        if s is not None:
            sim += s
            n += 1
    if ('normPlaceUid' in ev1) and ('normPlaceUid' in ev2):
        if ev1['normPlaceUid'] == ev2['normPlaceUid']:
            sim += 1.0
            n += 1
            boost+=1
        else:
            sim += -1.0
            n += 1
    elif ('place' in ev1) and ('place' in ev2):   #Non normalized
        s = strSim(ev1['place'], ev2['place'])
        if s is not None:
            sim += s
            n += 1
    if (boost == 2):
        sim += 1.0
        n += 1
    return (sim, n)

def nodeSim(p1,p2):
    """ Compare 2 nodes (p1 new, p2 master(rgd))
        returns a value between -1 (unequal) and 1 (equal) """
    if (not (p1 and p2)): return 0.0  #?? OK??
##?# Cache results?
    global _cache
    key = '%s;%s' % (p1['_id'], p2['_id'])
    if key in _cache: return _cache[key]
##?
    sim=0.0
    n=0
#    if (p1['sex'] and p2['sex']):    #sex har vikt 4
#        if (p1['sex']==p2['sex']):
#            sim += 4.0
#        else:
#            sim += -4.0
#        n+=4
    boost=0
    s = compName(p1['grpNameGiven'], p2['grpNameGiven'])
    if s is not None:
        sim += s
        n += 1
        if (s>0.99): boost=1
    s = compName(p1['grpNameLast'], p2['grpNameLast'])
    if s is not None:
        sim += s
        n += 1
        if (s>0.99): boost+=1
    if (boost == 2):
        sim += 1.0
        n += 1
    for ev in ('birth', 'death'):
        if (ev in p1) and (ev in p2):
            (esim, en) = eventSim(p1[ev], p2[ev])
            sim += esim
            n += en
    if (n>0):
        _cache[key] = sim/n
        return sim/n
    else:
        _cache[key] = 0.0
        return 0.0

def familySim(pFam, person_list, rgdFam, match_person):
    """compares 2 families using nodeSim for each person in base-family
       father, mother, and all siblings plus compare family-event marriage
       returns a value between -1 (unequal) and 1 (equal) """
    if not (pFam and rgdFam): return 0.0
##?# Cache results?
    global _cache
    key = '%s;%s' % (pFam['_id'], rgdFam['_id'])
    if key in _cache:
#        print 'famSim using cached'
        return _cache[key]
##?
    gfSc = 0.0
    n = 0

    for parent in ('husb','wife'):
        if (parent in pFam) and (parent in rgdFam):
            gfSc += nodeSim(person_list.find_one({'_id': pFam[parent]}), match_person.find_one({'_id': rgdFam[parent]}))
            n += 1
        #print 'parent',gfSc, n
    #print 'ant Children',len(pFam['children']),len(rgdFam['children'])
    if len(pFam['children'])<=len(rgdFam['children']):
        for chId in pFam['children']:
            max = -2.0
            for chIdR in rgdFam['children']:
                cns = nodeSim(person_list.find_one({'_id': chId}),match_person.find_one({'_id': chIdR}))
                if cns>max: max = cns
                ##print '   doing Ch',chId, chIdR,cns
            ##print 'Max NSCh', max
            gfSc += max
            n += 1
            #print 'child',gfSc, n
    else:
        for chIdR in rgdFam['children']:
            max = -2.0
            for chId in pFam['children']:
                cns = nodeSim(person_list.find_one({'_id': chId}),match_person.find_one({'_id': chIdR}))
                if cns>max: max = cns
                ##print '   doing Ch',chId, chIdR,cns
            ##print 'Max NSCh', max
            gfSc += max
            n += 1
            #print 'child',gfSc, n
    #Marriage
    if ('marriage' in pFam) and ('marriage' in rgdFam):
        (esim, en) = eventSim(pFam['marriage'], rgdFam['marriage'])
        gfSc += esim
        n += en

    fSc = 0.0
    if n>0: fSc = gfSc/n
    #print '    FS', fSc, 'N', n
    _cache[key] = fSc
    return fSc

def compValEq(v1, v2):
    if (v1 and v2 and (v1 == v2)): return 1
    else: return 0

def compValNeq(v1, v2):
    if (v1 and v2 and (v1 != v2)): return 1
    else: return 0

def compValInte(v1, v2):
    if ((not v1) or (not v2)): return 1
    else: return 0

def compDValEq(v1m, v2m, v1d, v2d):
    if (v1m and v2m and v1d and v2d and (v1m == v2m) and (v1d == v2d)): return 1
    else: return 0

def compDValNeq(v1m, v2m, v1d, v2d):
    if (v1m and v2m and v1d and v2d and (v1m != v2m) and (v1d != v2d)): return 1
    else: return 0

def compDValInte(v1m, v2m, v1d, v2d):
    if ((not v1m) or (not v2m) or (not v1d) or (not v2d)): return 1
    else: return 0

def compValAlla(v1, v2):
    """
    Compare in-values and return a list of 0/1 in the order
    both exist and equal, both exist and not equal, does not exist
    """
    if ((not v1) or (not v2)): return [0,0,1]
    elif v1 == v2: return [1,0,0]
    else: return [0,1,0]

def svmfeatures(tmp,rgd):
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
#Född/död år finns och lika               0,1
#Född/död år finns och olika              0,1
#Född/död år finns inte                   0,1
#Född/död datum finns och lika            0,1
#Född/död datum finns och olika           0,1
#Född/död datum finns inte                0,1
#Född/död församling finns och lika       0,1
#Född/död församling finns och olika      0,1
#Född/död församling finns inte           0,1
    return features

def TESTsvmfeatures(tmp, rgd):
    features = []
#feature                            values
##Kön olika/lika                       0,1
    if (tmp['sex'] == rgd['sex']): features.append(1)
    else: features.append(0)
    return features

def antFeaturesNorm(tmp,rgd):
    ant = 0
    maxAnt = 0
    for field in ('grpNameGiven', 'grpNameLast'):
        maxAnt += 1
        if (field in tmp) and (field in rgd): ant += 1
    for ev in ('birth', 'death'):
        maxAnt += 3
        if (ev in tmp) and (ev in rgd):
            for item in ('date', 'normPlaceUid','place'):
                if (item in tmp[ev]) and (item in rgd[ev]): ant += 1
    #Max 8?
    #print 'antF', ant, 'max', maxAnt
    return float(ant)/maxAnt

from SVMfeatures import SVMfeatures
def matchPers(p1, rgdP, conf, score = None):
    nodeScore = nodeSim(p1, rgdP)
        #print '    NS',nodeScore,
        #Only calculate famScore if NS and score above cut-off??
        #Test with facit!
#    print 'inMatchPers', p1['_id'], p1['refId'], rgdP['_id'], rgdP['refId']
    pFam = conf['families'].find_one({ 'children': p1['_id']}) #find fam if p in 'children'
    rgdFam = conf['match_families'].find_one({ 'children': rgdP['_id']})
    famScore = familySim(pFam, conf['persons'], rgdFam, conf['match_persons']) 
    feat = SVMfeatures(p1, rgdP, conf, score)
#    p_labels, p_acc, p_vals = svm_predict([0],[feat],svmModel,options="-b 1 -q")
    p_labels, p_acc, p_vals = svm_predict([0],[feat],svmModel,options="-b 1")
    svmstat = p_vals[0][0]

    # Use also score, NS, FS, and coscw? to determine status? See below!
    status = 'Manuell'
    if svmstat<0.1: status = 'EjMatch'
    if svmstat>0.9: status = 'Match'
    #print i,':   ', p['_id'], kid, status, svmstat, score/5.0, nodeScore, famScore, featureScore
#        if ((svmstat>0.5) or ((k['score']>25.0) and (nodeScore>0.75) and (famScore>0.75))):
#            pass #or do something??

    matchdata = {}
    matchdata['workid'] = p1['_id']
    matchdata['pwork'] = p1
    matchdata['matchid'] = rgdP['_id']
    matchdata['pmatch'] = rgdP
    matchdata['score'] = score
    matchdata['nodesim'] = nodeScore
    matchdata['familysim'] = famScore
    matchdata['svmscore'] = svmstat
    matchdata['status'] = status
    return matchdata
