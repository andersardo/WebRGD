#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import common

from collections import defaultdict
import argparse, time, sys, os, logging
logging.basicConfig(level=logging.DEBUG,
        format = '%(levelname)s %(module)s:%(funcName)s:%(lineno)s - %(message)s')

parser = argparse.ArgumentParser()
parser.add_argument("workDB", help="Working database name" )
parser.add_argument("matchDB", help="Database to match against")

args = parser.parse_args()
workDB = args.workDB
matchDB = args.matchDB

dbName  = os.path.basename(workDB).split('.')[0]  #No '.' or '/' in databasenames
mDBname = os.path.basename(matchDB).split('.')[0]

#KOLLA imports
from matchUtils import *
from utils import matchFam, setFamOK, setEjOKfamily
from matchtext import matchtext
from luceneUtils import setupDir, search

from bson.objectid import ObjectId

mt_tmp = matchtext()

t0 = time.time()
logging.info('using db %s matching against %s', dbName, mDBname)
config = common.init(dbName, matchDBName=mDBname, indexes=True)
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

dubltmp = defaultdict(list)
dbltmpNs = defaultdict(float)

ant=0
for p in person_list.find(timeout=False):
    matchtxt = mt_tmp.matchtextPerson(p, person_list, fam_list)
    #Ta bort * och ? från matchtxt? KOLLA
    if not matchtxt:
        logging.error('No matchtextdata for %s, %s',p['_id'],p['refId'])
        continue       ##########FIX!!!!!!!!!!
    candidates = search(matchtxt, p['sex'], 3) #Lucene search
    sc = 0
    for (kid,score) in candidates:
        if (score> sc): sc = score
        candidate = match_person.find_one({'_id': ObjectId(kid)})
#OLD        matchdata = matchPers(p, candidate, config, score/8.0) #?? range of Lucene scores?
        matchdata = matchPers(p, candidate, config, score)
        #FIX EVT: lägg in mönster (autoOK, autoCheck -> EjOK) (multimatch Resolve) här
        matches.insert(matchdata)
        ant += 1
        if matchdata['status'] in common.statEjOK: break
        if  matchdata['status'] in common.statOK.union(common.statManuell):
            dubltmp[p['_id']].append(candidate['_id'])
            dbltmpNs[p['_id'], candidate['_id']] = matchdata['nodesim']
        #break if score is less than 1/3 of max score
        if (sc/score > 3.0): break
logging.info('%d person matchings inserted', ant)
logging.info('Time %s',time.time() - t0)

##From RGDfixMix
#Mönster för att upplösa multimatch för individer
#Flera OK matchindivid i tmp; alla i samma familj
#Välj den individmatch med högst nodeSim och samma födelsedatum
#  om 2 lika behåll manuell koll

ant = 0
for (ind,listrgdid) in dubltmp.iteritems():
    if len(listrgdid)>1:
        rdubl = None
        rdublNs = -1.0
        patternOK = False
        pp =person_list.find_one({'_id': ind})
#Om tvillingar kanske ta flera från person_list? för att få hela bilden?
        for rgdid in listrgdid:
            rgdpp = match_person.find_one({'_id': rgdid})
            #same birthdate
            try:
                if pp['birth']['date'] == rgdpp['birth']['date']:
                #same family
                    if not rdubl:
                        rdubl = rgdid
                    elif match_person.find_one({ 'children': rgdid})==match_person.find_one({ 'children': rdubl}):
                        #highest nodeSim
                        if dbltmpNs[ind,rgdid] > dbltmpNs[ind,rdubl]:
                            rdubl = rgdid
                        patternOK = True
                    else:
                        patternOK = False
                        break
                else:
                    patternOK = False
                    break
            except:
                patternOK = False
                break
        if patternOK:
            logging.debug('Multimatch resolved %s -> %s from %s',ind,rdubl,listrgdid)
            ant += 1
            logging.debug('set status for matches %s,* and *,%s to rEjOK',ind,rdubl)
            matches.update({'$or': [{'workid': ind},{'matchid': rdubl}]}, {'$set': {'status': 'rEjOK'}}, multi=True)
            logging.debug('set status for match %s -> %s to rOK',ind,rdubl)
            matches.update({'workid': ind, 'matchid': rdubl}, {'$set': {'status': 'rOK'}})
logging.info('%d Multimatch individer (tvillingar/syskon) fixade', ant)
logging.info('Time %s',time.time() - t0)

#Ta bort individ med status Manuell om finns OK och  nodeSim < 0 och SVM < 0.5
ant=0
for mt in matches.find({'status': {'$in': list(common.statOK)}}, {'_id': 1, 'workid': 1}):
    for check in matches.find({'status': {'$in': list(common.statManuell)}, 'workid': mt['workid'],
                               'nodesim': {'$lte': 0.0}, 'svmscore': {'$lt': 0.5}
                               }):
        logging.debug('Set person %s status=rEjOK',check['refId'])
        matches.update({'_id': check['_id']}, {'$set': {'status': 'rEjOK'}})
        ant += 1
logging.info('%d Individer med status Manuell => rEjOK fixade', ant)
logging.info('Time %s',time.time() - t0)

#Families match-status calculated from person match-status => No SVM
ant = 0
fams = set()
#for match in  matches.find({'status': {'$in': list(common.statOK)}}): #for all person-matches
#Consider also perosnmatches with status Manuall FIX!!
for match in  matches.find({'status': {'$in': list(common.statOK.union(common.statManuell))}}): #for all person-matches
    for role in ('husb', 'wife', 'children'):
        tFam = fam_list.find({role: match['workid']}, {'_id': 1, 'marriage.date': 1} )
        rFam = match_family.find({role: match['matchid']}, {'_id': 1, 'marriage.date': 1} )
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
for (tFamId,rFamId) in fams:    #  for all involved families
    famMatchData = matchFam(tFamId, rFamId, config)
    fam_matches.insert(famMatchData)
    ant += 1
logging.info('%d family matchings inserted', ant)
logging.info('Time %s',time.time() - t0)

#Disable this rule for the time beeing
"""
#Family rules to resolve manual status to EjMatch
logging.info('Testing for NotOK families')
##copied from below
#from SVMfeatures import famSVMfeatures
from uiUtils import nameDiff, eventDiff
from utils import updateFamMatch
#svmFamModel = svm_load_model('conf/family.model')
for fmatch in config['fam_matches'].find({'status': {'$in': list(common.statManuell)}}):
    #Why use refID and not _id?
    work = config['families'].find_one({'refId': fmatch['workRefId']})
    match = config['match_families'].find_one({'refId': fmatch['matchRefId']})
    changes = False
    for mt in config['matches'].find({'$and': [
              {'status': {'$in': list(common.statEjOK)}},
              {'workid': {'$in': work['children']}},
              {'matchid': {'$in': match['children']}}
              ]}):
        if (nameDiff(mt['pwork'], mt['pmatch']) and 
            eventDiff(mt['pwork'], mt['pmatch'], ('birth','death'), ('date',))):
            changes = True
            logging.debug('Set status split for %s, %s',
                          mt['pwork']['refId'], mt['pmatch']['refId'])
            config['matches'].update({'_id': mt['_id']}, {'$set': {'status': 'split'}})
    if changes:
        updateFamMatch((fmatch['workid'],), config)
        #FIX if not Manuell: continue
#rule
#No green child
#    logging.debug('No green child')
    noRuleMatch = False
    for ch in fmatch['children']:
        if ch['status'] in common.statOK:
            noRuleMatch = True
            break
    if noRuleMatch: continue
#No green parent??
#    logging.debug('No green parent')
    noRuleMatch = False
    for partner in ('husb','wife'):
        try:
            if fmatch[partner]['status'] in common.statOK:
                noRuleMatch = True
                break
        except: pass
    if noRuleMatch: continue
    ruleMatch = False
#either of
# * Max diff between children birth year > 60 years
#    logging.debug('Max diff between children birth year > 60 years')
    maxBirth = 0
    minBirth = 10000
    for ch in fmatch['children']:
#        logging.debug('ch=%s', ch)
        try:
            if int(ch['pwork']['birth']['date'][0:4]) > maxBirth:
                maxBirth = int(ch['pwork']['birth']['date'][0:4])
            if int(ch['pwork']['birth']['date'][0:4]) < minBirth:
                minBirth = int(ch['pwork']['birth']['date'][0:4])
        except: pass
        try:
            if int(ch['pmatch']['birth']['date'][0:4]) > maxBirth:
                maxBirth = int(ch['pmatch']['birth']['date'][0:4])
            if int(ch['pmatch']['birth']['date'][0:4]) < minBirth:
                minBirth = int(ch['pmatch']['birth']['date'][0:4])
        except: pass
#    logging.debug('maxBirth=%s minBirth=%s', maxBirth, minBirth)
    if maxBirth:
        if (maxBirth-minBirth) > 60: ruleMatch = True
        else:
# * husb death - minBirth > 90
#            logging.debug('husb death - minBirth > 90')
            try:
                if int(fmatch['husb']['pwork']['death']['date'][0:4]) - minBirth > 90:
                    ruleMatch = True
            except: pass
            try:
                if int(fmatch['husb']['pmatch']['death']['date'][0:4]) - minBirth > 90:
                    ruleMatch = True
            except: pass
# * wife death to child birth > 60
#        logging.debug('wife death to child birth > 60')
        if not ruleMatch:
            try:
                if int(fmatch['wife']['pwork']['death']['date'][0:4]) - minBirth > 60:
                    ruleMatch = True
            except: pass
            try:
                if int(fmatch['wife']['pmatch']['death']['date'][0:4]) - minBirth > 60:
                    ruleMatch = True
            except: pass
#=>  FamiljenNotOK
    if ruleMatch:
        logging.debug('Family rule set NotOK matches')
        setEjOKfamily(str(fmatch['workid']), str(fmatch['matchid']), code='rEjOK')
##
"""

############################
#EVT SVM for fam-matches?
############################

logging.info('Do multimatch reduction rules')

def analyzeMatchPattern(match):
    chMatches = set()
    parMatches = []
    famlist = [['F',match['workid'],match['matchid'],
                match['workRefId'],match['matchRefId']]]
    for partner in ('husb', 'wife'):
        stat = match.get(partner,None)  #None if it doesn't exist  FIX!
        if stat['status'] in common.statOK:
            parMatches.append('OK')
            famlist.append(['P', match[partner]['pwork']['_id'], match[partner]['pmatch']['_id'],
                            match[partner]['pwork']['refId'], match[partner]['pmatch']['refId']])
        elif stat['status'] in common.statManuell:
            parMatches.append('Check')
            famlist.append(['P', match[partner]['pwork']['_id'], match[partner]['pmatch']['_id'],
                            match[partner]['pwork']['refId'], match[partner]['pmatch']['refId']])
        elif stat['status'] in common.statEjOK.union([None]): 
            #Kolla om det finns en annan pers match med grön status för denna partner
            if matches.find_one({'$and': [{'status': {'$in': list(common.statOK)}},
                                          {'$or': [{'_id': match[partner]['pwork']['_id']},
                                                   {'_id': match[partner]['pmatch']['_id']}
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
            famlist.append(['P', ch['pwork']['_id'], ch['pmatch']['_id'],
                            ch['pwork']['refId'], ch['pmatch']['refId']])
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
    #??
    #   ny implementering
    #   patt = [[{'parentsOK': x, 'parentsMan': y, 'parentsEjOK': z, 'parentEjOK_OK': v,
    #            'childrenOK': x, 'childrenMan': y, 'childrenEjOK': z},
    #            {'parentsOK': x, 'parentsMan': y, 'parentsEjOK': z, 'parentEjOK_OK': v,
    #            'childrenOK': x, 'childrenMan': y, 'childrenEjOK': z},
    #           ]]
    #??pattern = []
    #??
    #PATTERN1
    #Kalle email 2014-02-27
    #Det finns bara GRÖNA barn i EN AV FAMILJERNA och
    #Den FAMILJEN har minst EN GRÖN FÖRÄLDER och
    #ALLA BARNEN i den FAMILJEN är GRÖNA eller VITA
    #Om en RÖD förälder in den familjen och den föräldern har en annan grön match (NotOK2) => BRYT
    #??pattern.append([{},{}])
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
#        if len(dlist)==2:
        if len(dlist)>=2:
            mts = []
            for dbl in dlist:
#                logging.debug('dbl work=%s match=%s', dbl['workRefId'], dbl['matchRefId'])
                mts.append(analyzeMatchPattern(dbl))
            #Now mts=[(pM, cM, fL), (pM, cM, fL)]
            (pMa, cMa, fLa) = mts[0]
            (pMb, cMb, fLb) = mts[1]
            if checkPattern1(mts[0], mts[1]):
                for (typ, workid, matchid, wrefid, mrefid) in fLb:
                    if typ == 'F':
                        setEjOKfamily(str(workid), str(matchid), code = 'rEjOK')
                        break
                setFamOK(None, None, config, famlist = fLa, button = False)
            elif checkPattern1(mts[1], mts[0]):
                for (typ, workid, matchid, wrefid, mrefid) in fLa:
                    if typ == 'F':
                        setEjOKfamily(str(workid), str(matchid), code = 'rEjOK')
                        break
                setFamOK(None, None, config, famlist = fLb, button = False)
            elif checkPattern2(mts):
                if checkPattern3(mts[0]):
                    setFamOK(None, None, config, famlist = fLa, button = False)
                elif checkPattern3(mts[1]):
                    setFamOK(None, None, config, famlist = fLb, button = False)

logging.info('Time %s',time.time() - t0)
logging.info('Doing SVM family match, incl split-ifying')
from SVMfeatures import famSVMfeatures
from uiUtils import nameDiff, eventDiff
from utils import updateFamMatch
svmFamModel = svm_load_model('conf/family.model')
for fmatch in config['fam_matches'].find({'status': {'$in': list(common.statManuell)}}):
    #Why use refID and not _id?
    work = config['families'].find_one({'refId': fmatch['workRefId']})
    match = config['match_families'].find_one({'refId': fmatch['matchRefId']})
##Disable this part if done above
#Run through children and change 'EjMatch' for unreasonable matches to 'split'
    changes = False
    for mt in config['matches'].find({'$and': [
              {'status': {'$in': list(common.statEjOK)}},
              {'workid': {'$in': work['children']}},
              {'matchid': {'$in': match['children']}}
              ]}):
        if (nameDiff(mt['pwork'], mt['pmatch']) and 
            eventDiff(mt['pwork'], mt['pmatch'], ('birth','death'), ('date',))):
            changes = True
            logging.debug('Set status split for %s, %s',
                          mt['pwork']['refId'], mt['pmatch']['refId'])
            config['matches'].update({'_id': mt['_id']}, {'$set': {'status': 'split'}})
    if changes:
        updateFamMatch((fmatch['workid'],), config)
        #FIX if not Manuell: continue
##
    v = famSVMfeatures(work, match, config, None)
    logging.debug('SVMvect=%s, work=%s, match=%s', v, work, match)
    p_labels, p_acc, p_vals = svm_predict([0],[v],svmFamModel,options="-b 1")
    svmstat = p_vals[0][0]
    if svmstat>0.9:
        logging.debug('setFamOK workid=%s %s matchid=%s %s', fmatch['workid'],
                      fmatch['workRefId'], fmatch['matchid'], fmatch['matchRefId'])
        logging.debug('svmstat=%s vect=%s', svmstat, v)
        setFamOK(fmatch['workid'], fmatch['matchid'], config)
logging.info('Time %s',time.time() - t0)
logging.info('Matching All done')
