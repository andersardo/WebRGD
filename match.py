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
else:
    config['famfeatureSet'] = 'famExtended'  #Default - best performance

common.config = config
setupDir(mDBname)

person_list = config['persons']
fam_list = config['families']

matches = config['matches']
matches.drop()
fam_matches = config['fam_matches']
fam_matches.drop()
config['flags'].drop()

match_person = config['match_persons']
match_family = config['match_families']

dubltmp = defaultdict(list)
dbltmpNs = defaultdict(float)

ant=0
for p in person_list.find({}, no_cursor_timeout=True):
    matchtxt = mt_tmp.matchtextPerson(p, person_list, fam_list, config['relations'])
    if not matchtxt:
        logging.error('No matchtextdata for %s, %s',p['_id'],p['refId'])
        continue       ##########FIX!!!!!!!!!!
    candidates = search(matchtxt, p['sex'], 2) #Lucene search
    #Om inga kandidat-matcher? Inget problem dom är inte Match
    sc = 0
    for (kid,score) in candidates:
        #do not break if little data - WORSE
        #if score < 35.0 and (len(matchtxt.split())>10): break  #breakoff score point for considering  match
        if score < 30.0: break  #breakoff score point for considering  match
        if (score> sc): sc = score
        candidate = match_person.find_one({'_id': kid})
        ##PROBLEM FIX Lucene indexing
        if not candidate: continue
        ##PROBLEM
        matchdata = matchPers(p, candidate, config, score)
        #FIX EVT: lägg in mönster (autoOK, autoCheck -> EjOK) (multimatch Resolve) här
        #logging.debug('Insert main matching for %s, %s',p['refId'], candidate['refId'])
        matches.insert(matchdata)
#        if p['_id']=='P_980100': print matchdata
        ant += 1
#se mail 'Stickprov' Juni 5 2015
#        if matchdata['status'] in common.statEjOK: break
        if  matchdata['status'] in common.statOK.union(common.statManuell):
            dubltmp[p['_id']].append(candidate['_id'])
            dbltmpNs[p['_id'], candidate['_id']] = matchdata['nodesim']
        #break if Match
        if matchdata['status'] == 'Match': break
        #break if score is less than 1/3 of max score
        if (sc/score > 3.0): break
logging.info('%d person matchings inserted', ant)
logging.info('Time %s',time.time() - t0)

#Families match-status calculated from person match-status => No SVM
ant = 0
fams = set()
for match in  matches.find({'status': {'$in': list(common.statOK.union(common.statManuell))}}):
    for role in ('husb', 'wife', 'child'):
        tFam = []
        rFam = []
        for tRel in config['relations'].find({'relTyp': role, 'persId': match['workid']}):
            tFam.append(fam_list.find_one({'_id': tRel['famId']}, {'_id': 1, 'marriage.date': 1}))
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
    fam_matches.insert(famMatchData)
    ant += 1
logging.info('%d family matchings inserted', ant)
logging.info('Time %s',time.time() - t0)

############################
#EVT SVM for fam-matches?
############################
if noFamSVM: #default
    logging.info('Matching All done')
    sys.exit()
logging.info('Doing SVM family match, incl split-ifying')

from uiUtils import nameDiff, eventDiff
from utils import updateFamMatch
if 'famfeatureSet' in config:
    famSVMfeatures = getattr(importlib.import_module('featureSet'), config['famfeatureSet'])
    svmFamModel = svm_load_model('conf/' + config['famfeatureSet'] + '.model')
else:
    famSVMfeatures = getattr(importlib.import_module('featureSet'), 'famBaseline')
    svmFamModel = svm_load_model('conf/famBaseline.model')
antChanged = 0
antSVM = 0
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
            #logging.debug('Set status split for %s, %s',
            #              workchild['refId'], matchchild['refId'])
            config['matches'].update({'_id': mt['_id']}, {'$set': {'status': 'split'}})
    if changes:
        updateFamMatch((fmatch['workid'],), config)
        antChanged += 1
        #FIX if not Manuell: continue
    v = famSVMfeatures(work, match, config)
    #logging.debug('SVMvect=%s, work=%s, match=%s', v, work, match)
    p_labels, p_acc, p_vals = svm_predict([0],[v],svmFamModel,options="-b 1")
    svmstat = p_vals[0][0]
    if svmstat>0.9:
        #logging.debug('setFamOK workid=%s %s matchid=%s %s', fmatch['workid'],
        #              fmatch['workRefId'], fmatch['matchid'], fmatch['matchRefId'])
        #logging.debug('svmstat=%s vect=%s', svmstat, v)
        setFamOK(fmatch['workid'], fmatch['matchid'], config)
        antSVM += 1
logging.info('%d families updated after split; %d set to OK by SVM', antChanged, antSVM)
logging.info('Time %s',time.time() - t0)

#Rule:
#  if all children Green
#     and 1 partner Green and 1 partner Yellow
#   set family to OK
logging.info('Fix manual families')
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
logging.info('Matching All done')
#stats
pOK = config['matches'].find({'status': {'$in': list(common.statOK)}}).count()
fOK = config['fam_matches'].find({'status': {'$in': list(common.statOK)}}).count()
pMan = config['matches'].find({'status': {'$in': list(common.statManuell)}}).count()
fMan = config['fam_matches'].find({'status': {'$in': list(common.statManuell)}}).count()
logging.info('STATS:: Matches: %d persons, %d families; Manuell: %d persons, %d families',
             pOK, fOK, pMan, fMan)
