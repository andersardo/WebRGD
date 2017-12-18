#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import common

from collections import defaultdict
import argparse, time, sys, os, logging
logging.basicConfig(level=logging.INFO,
        format = '%(levelname)s %(module)s:%(funcName)s:%(lineno)s - %(message)s')

parser = argparse.ArgumentParser()
parser.add_argument("workDB", help="Working database name" )
parser.add_argument("matchDB", help="Database to match against")
#Featureset => svmModel + rutin to calc features
parser.add_argument("--featureset",
                    help="Basename for featureset. Used for feature extraction and SVMmodel" )
parser.add_argument("--famfeatureset",
                    help="Basename for family featureset. Used for feature extraction and SVMmodel for families" )
parser.add_argument("--noFamSVM", action='store_true', help="Do not do SVM matching for families" )

args = parser.parse_args()
workDB = args.workDB
matchDB = args.matchDB
featureSet = args.featureset
famfeatureSet = args.famfeatureset
noFamSVM = args.noFamSVM

dbName  = os.path.basename(workDB).split('.')[0]  #No '.' or '/' in databasenames
mDBname = os.path.basename(matchDB).split('.')[0]

#KOLLA imports
from matchUtils import *
from dbUtils import getFamilyFromId
from utils import matchFam, setFamOK, setEjOKfamily, setOKperson
from matchtext import matchtext
from luceneUtils import setupDir, search

mt_tmp = matchtext()

t0 = time.time()
logging.info('using db %s matching against %s', dbName, mDBname)
config = common.init(dbName, matchDBName=mDBname, indexes=True)
if featureSet:
    config['featureSet'] = featureSet
if famfeatureSet:
    config['famfeatureSet'] = famfeatureSet

common.config = config
setupDir(mDBname)

person_list = config['persons']
fam_list = config['families']

matches = config['matches']
matches.drop()
fam_matches = config['fam_matches']
fam_matches.drop()

match_person = config['match_persons']
match_family = config['match_families']

#dubltmp = defaultdict(list)
#dbltmpNs = defaultdict(float)

#lägg till inre loop som följer föräldrarna uppåt
done = set()
toDo = set()
ant=0
for pers in person_list.find({}, no_cursor_timeout=True).sort([('birth.date', -1)]):
    if pers['_id'] in done: continue
    toDo.add(pers['_id'])
#    if pers['_id']=='P_1073174': print 'yttre', pers
    breakOffScore = 34.0
    while len(toDo)>0:
        pId = toDo.pop()
        if pId in done: continue
        done.add(pId)
        p = person_list.find_one({'_id': pId})
#        if p['_id']=='P_1073174': print 'Inner', p
        matchtxt = mt_tmp.matchtextPerson(p, person_list, fam_list, config['relations'])
        if not matchtxt:
            logging.error('No matchtextdata for %s, %s',p['_id'],p['refId'])
            continue       ##########FIX!!!!!!!!!!
        candidates = search(matchtxt, p['sex'], 2) #Lucene search
        #Om inga kandidat-matcher? Inget problem dom är inte Match
        sc = 0
        for (kid,score) in candidates:
            if score < breakOffScore: break  #breakoff score point for considering  match
            if (score> sc): sc = score
            candidate = match_person.find_one({'_id': kid})
            matchdata = matchPers(p, candidate, config, score)
            #FIX EVT: lägg in mönster (autoOK, autoCheck -> EjOK) (multimatch Resolve) här
            logging.debug('Insert main matching for %s, %s',p['refId'], candidate['refId'])
            matches.insert(matchdata)
    #        if p['_id']=='P_980100': print matchdata
            ant += 1
    #se mail 'Stickprov' Juni 5 2015
    #        if matchdata['status'] in common.statEjOK: break
#            if  matchdata['status'] in common.statOK.union(common.statManuell):
#                dubltmp[p['_id']].append(candidate['_id'])
#                dbltmpNs[p['_id'], candidate['_id']] = matchdata['nodesim']
            #break if Match
            if matchdata['status'] == 'Match':
                #add parents to toDo
                rel = config['relations'].find_one({'persId': pId, 'relTyp': 'child'})
                if rel:
                    for ids in config['relations'].find({'famId': rel['famId']}):
                        toDo.add(ids['persId'])
                break
            #break if score is less than 1/3 of max score
            #if (sc/score > 2.0): break
        breakOffScore = 25.0
logging.info('%d person matchings inserted', ant)
logging.info('Time %s',time.time() - t0)

#Families match-status calculated from person match-status => No SVM
ant = 0
fams = set()
for match in  matches.find({'status': {'$in': list(common.statOK.union(common.statManuell))}}):
    for role in ('husb', 'wife', 'child'):
        tFam = []
        rFam = []
        #tFam = fam_list.find({role: match['workid']}, {'_id': 1, 'marriage.date': 1} )
        #for tRel in config['relations'].find({role: match['workid']}):
        for tRel in config['relations'].find({'relTyp': role, 'persId': match['workid']}):
            tFam.append(fam_list.find_one({'_id': tRel['famId']}, {'_id': 1, 'marriage.date': 1}))
        #rFam = match_family.find({role: match['matchid']}, {'_id': 1, 'marriage.date': 1} )
        #for tRel in config['match_relations'].find({role: match['matchid']}):
        for tRel in config['match_relations'].find({'relTyp': role, 'persId': match['matchid']}):
            rFam.append(match_family.find_one({'_id': tRel['famId']},
                                              {'_id': 1, 'marriage.date': 1}))
        tfams = []
        tDone = []
#take all combinations of families
#if several posibilities for 1 work-family keep only the the pair where marriage-date matches
        for f in tFam:
            for ff in rFam:
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
famMatchSummary = {}
for (tFamId,rFamId) in fams:    #  for all involved families
    famMatchData = matchFam(tFamId, rFamId, config)
    #if tFamId == 'F_354430': print famMatchData
    #famMatchSummary[(tFamId,rFamId)] = famMatchData['summary']
    fam_matches.insert(famMatchData)
    ant += 1
logging.info('%d family matchings inserted', ant)
logging.info('Time %s',time.time() - t0)

############################
#EVT SVM for fam-matches?
############################

logging.info('Do multimatch reduction rules')
def analyzeMatchPattern(match):
    # parameter match is a fam_match
    chMatches = set()
    parMatches = []
    #workid,matchid _id; *RefId gedcom ID
    famlist = [['F',match['workid'],match['matchid']]]
    for partner in ('husb', 'wife'):
        stat = match.get(partner,None)
        if stat['status'] in common.statOK:
            parMatches.append('OK')
            #work finns inte i match
            famlist.append(['P', match[partner]['workid'], match[partner]['matchid']])
        elif stat['status'] in common.statManuell:
            parMatches.append('Check')
            famlist.append(['P', match[partner]['workid'], match[partner]['matchid']])
        elif stat['status'] in common.statEjOK.union([None]): 
            #Kolla om det finns en annan pers match med grön status för denna partner
            if matches.find_one({'$and': [{'status': {'$in': list(common.statOK)}},
                                          {'$or': [{'_id': match[partner]['workid']},
                                                   {'_id': match[partner]['matchid']}
                                                  ] }
                                         ] } ):
                parMatches.append('NotOK2')
            elif stat: parMatches.append('NotOK')
            else: parMatches.append('?')
        else: parMatches.append('?')
    for ch in match.get('children',[]):
        if ch['status'] in common.statOK: chMatches.add('OK')
        elif ch['status'] in common.statManuell: chMatches.add('Check')
        elif ch['status'] in common.statEjOK: chMatches.add('NotOK')
        else: chMatches.add('?')
        if ch['status'] in common.statOK.union(common.statManuell):
            famlist.append(['P', ch['workid'], ch['matchid']])
    return (parMatches, chMatches, famlist)

def checkPattern1(mt1, mt2):
    (pMa, cMa, fLa) = mt1
    (pMb, cMb, fLb) = mt2
    if ( ('OK' in cMa) and ('OK' not in cMb) and
         ('OK' in pMa) and ('NotOK2' not in pMa) and
         ('NotOK' not in cMa) and ('Check' not in cMa) ):
#        print 'Pattern 1 hits'
        return True
    return False

def checkPattern2(mtList):
    (pMa, cMa, fLa) = mtList[0]
    (pMb, cMb, fLb) = mtList[1]
    #Om ingen av familjerna har färgade barn (bara vita eller inga alls)
    st = cMa.union(cMb)
    if ( ('OK' not in st) and ('Check' not in st) and ('NotOK' not in st) and ('Manuell' not in st) ):
        #logging.debug( 'Pattern 2 hits')
        return True
    else: return False

def checkPattern3(mt1):
    (pMa, cMa, fLa) = mt1
    if ( (len(pMa)==2) and (pMa[0]=='OK') and (pMa[1]=='OK') ):
        #logging.debug( 'Pattern 3 hits')
        return True
    return False

for multiiter in (1,2):
    dubl = defaultdict(list) #Only use 1 list - since we have uniq id's
    #List all multi-matches
    #Maybe use aggregation to save only real multimatches??
    for match in fam_matches.find({'status': {'$in': list(common.statOK.union(common.statManuell))}}):
        dubl[match['workid']].append(match)
        dubl[match['matchid']].append(match)

    #Kalles mönster för multimatch-fix
    #PATTERN1
    #Kalle email 2014-02-27
    #Det finns bara GRÖNA barn i EN AV FAMILJERNA och
    #Den FAMILJEN har minst EN GRÖN FÖRÄLDER och
    #ALLA BARNEN i den FAMILJEN är GRÖNA eller VITA
    #Om en RÖD förälder in den familjen och den föräldern har en annan grön match (NotOK2) => BRYT
    #PATTERN2
    #Kalle email 2014-03-20
    #Reducerings regel 2 för Multimatch:
    #Om ingen av familjerna har färgade barn (bara vita eller inga alls)
    #if ( ('OK' not in cMa) and ('Check' not in cMa) and ('NotOK' not in cMa) and ('Nm/Fd' not in cMa) and
    #     ('OK' not in cMb) and ('Check' not in cMb) and ('NotOK' not in cMb) and ('Nm/Fd' not in cMb) ):
    #PATTERN3
    #Får familjen med 2 gröna föräldrar OK (jag kan inte se någon situation när
    #två familjer skulle kunna ha två gröna föräldrar)
    #    if ( (len(pMa)==2) and (pMa[0]=='OK') and (pMa[1]=='OK') ):
    #        OK
    #    elif ( (len(pMb)==2) and (pMb[0]=='OK') and (pMb[1]=='OK') ):
    #        OK
    for (ind,dlist) in dubl.iteritems():
        if len(dlist)>=2:
            mts = []
            for dbl in dlist:
                mts.append(analyzeMatchPattern(dbl))
            #Now mts=[(pM, cM, fL), (pM, cM, fL)]
            (pMa, cMa, fLa) = mts[0]
            (pMb, cMb, fLb) = mts[1]
            if checkPattern1(mts[0], mts[1]):
                #for (typ, workid, matchid, wrefid, mrefid) in fLb:
                for (typ, workid, matchid) in fLb:
                    if typ == 'F':
                        setEjOKfamily(str(workid), str(matchid), code = 'rEjOK')
                        break
                setFamOK(None, None, config, famlist = fLa, button = False)
            elif checkPattern1(mts[1], mts[0]):
                #for (typ, workid, matchid, wrefid, mrefid) in fLa:
                for (typ, workid, matchid) in fLa:
                    if typ == 'F':
                        setEjOKfamily(str(workid), str(matchid), code = 'rEjOK')
                        break
                setFamOK(None, None, config, famlist = fLb, button = False)
            elif checkPattern2(mts):
                if checkPattern3(mts[0]):
                    setFamOK(None, None, config, famlist = fLa, button = False)
                elif checkPattern3(mts[1]):
                    setFamOK(None, None, config, famlist = fLb, button = False)
#AA LOGGING STATS HOW MANY RESOLVED
logging.info('Time %s',time.time() - t0)

if noFamSVM: #default
    logging.info('Matching All done')
    sys.exit()

logging.info('Doing SVM family match, incl split-ifying')
from uiUtils import nameDiff, eventDiff
from utils import updateFamMatch
#USE famfeatureSet!!
if 'famfeatureSet' in config:
    famSVMfeatures = getattr(importlib.import_module('featureSet'), config['famfeatureSet'])
    svmFamModel = svm_load_model('conf/' + config['famfeatureSet'] + '.model')
else:
    #from SVMfeatures import famSVMfeatures
    #svmFamModel = svm_load_model('conf/family.model')
    famSVMfeatures = getattr(importlib.import_module('featureSet'), 'famBaseline')
    svmFamModel = svm_load_model('conf/famBaseline.model')
antChanged = 0
antSVM = 0
##for fmatch in config['fam_matches'].find({'status': {'$in': list(common.statManuell)}}).batch_size(50):
###Test all family matches gives better results
for fmatch in config['fam_matches'].find().batch_size(50):
    work = getFamilyFromId(fmatch['workid'] , config['families'], config['relations'])
    match = getFamilyFromId(fmatch['matchid'], config['match_families'], config['match_relations'])
    #Run through children and change 'EjMatch' for unreasonable matches to 'split'
    changes = False
    for mt in config['matches'].find({'$and': [
              {'status': {'$in': list(common.statEjOK)}},
              {'workid': {'$in': work['children']}},
              {'matchid': {'$in': match['children']}}
              ]}):
        #AA GET EVENTS
        workchild = config['persons'].find_one({'_id': mt['workid']})
        matchchild = config['match_persons'].find_one({'_id': mt['matchid']})
        if (nameDiff(workchild, matchchild) and 
            eventDiff(workchild, matchchild, ('birth','death'), ('date',))):
            changes = True
            logging.debug('Set status split for %s, %s',
                          workchild['refId'], matchchild['refId'])
            config['matches'].update({'_id': mt['_id']}, {'$set': {'status': 'split'}})
    if changes:
        updateFamMatch((fmatch['workid'],), config)
        antChanged += 1
        #FIX if not Manuell: continue
    v = famSVMfeatures(work, match, config)
    logging.debug('SVMvect=%s, work=%s, match=%s', v, work, match)
    p_labels, p_acc, p_vals = svm_predict([0],[v],svmFamModel,options="-b 1")
    svmstat = p_vals[0][0]
    if svmstat>0.9:
        logging.debug('setFamOK workid=%s %s matchid=%s %s', fmatch['workid'],
                      fmatch['workRefId'], fmatch['matchid'], fmatch['matchRefId'])
        logging.debug('svmstat=%s vect=%s', svmstat, v)
        setFamOK(fmatch['workid'], fmatch['matchid'], config)
        antSVM += 1
logging.info('%d families updated after split; %d set to OK by SVM', antChanged, antSVM)
logging.info('Time %s',time.time() - t0)

#Rule:
#  if all children Green
#     and 1 partner Green and 1 partner Yellow
#   set family to OK
logging.info('Fix fams manual')
antFixed = 0
for fmatch in config['fam_matches'].find({'status': {'$in': list(common.statManuell)}}):
    husb = fmatch['summary']['husb']
    wife = fmatch['summary']['wife']
    if type(husb) is dict or type(wife) is dict: continue
    if not set(fmatch['summary']['children']).difference(common.statOK): 
        if husb in common.statOK and wife in common.statManuell:
            setOKperson(fmatch['wife']['workid'], fmatch['wife']['matchid'])
            antFixed += 1
        elif husb in common.statManuell and wife in common.statOK:
            setOKperson(fmatch['husb']['workid'], fmatch['husb']['matchid'])
            antFixed += 1
logging.info('%d family matchings Manuell -> OK', antFixed)
logging.info('Time %s',time.time() - t0)
#for f in fam_matches.find({'workid': 'F_354430'}):
#    print f
logging.info('Matching All done')
