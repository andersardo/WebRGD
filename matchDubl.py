#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import common

from collections import defaultdict
import argparse, time, sys, os

parser = argparse.ArgumentParser()
parser.add_argument("workDB", help="Working database name" )
parser.add_argument("matchDB", help="Database to match against")

args = parser.parse_args()
workDB = args.workDB
matchDB = args.matchDB

dbName  = os.path.basename(workDB).split('.')[0]  #No '.' or '/' in databasenames
mDBname = os.path.basename(matchDB).split('.')[0]

if dbName != mDBname:
    print 'Oh no! must be same DB', dbName, mDBname
    sys.exit()

#KOLLA imports
from matchUtils import *
from utils import matchFam, setFamOK
from matchtext import matchtext
from luceneUtils import setupDir, search

from bson.objectid import ObjectId

mt_tmp = matchtext()

t0 = time.time()
print 'Looking for duplicates in db', dbName
config = common.init(dbName, matchDBName=mDBname, indexes=True)
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

def nodeSimQ(p1,p2):
    """ Compare 2 nodes (p1 new, p2 master(rgd))
        returns a value between -1 (unequal) and 1 (equal) """
    if (not (p1 and p2)): return (0, 0.0)  #?? OK??
##?# Cache results?
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
    if (n>0): return (n, sim/n)
    else: return (0, 0.0)

def haveChild(pers):
    if fam_list.find({'children': pers['_id']}).count() > 0: return True
    else: return False

def haveParent(pers):
    if fam_list.find({'$or': [{'husb': pers['_id']}, {'wife': pers['_id']}]}).count() > 0:
        return True
    else: return False

def commonParents(p1,p2):
    p1F = fam_list.find_one({'children': p1['_id']})
    if not p1F: return 0
    p2F = fam_list.find_one({'children': p2['_id']})
    if not p2F: return 0
    ant = 0
    for p in ('husb', 'wife'):
        if (p in p1F) and (p in p2F):
            if p1F[p] == p2F[p]: ant += 1
    return ant

def sameFamily(p1,p2):
    for fam in fam_list.find({'$or': [{'husb': p1['_id']}, {'wife': p1['_id']}]}):
        if fam_list.find_one({'_id': fam['_id'], 'children': p2['_id']}): return True
    for fam in fam_list.find({'$or': [{'husb': p2['_id']}, {'wife': p2['_id']}]}):
        if fam_list.find_one({'_id': fam['_id'], 'children': p1['_id']}): return True
    return False

ant=0
done=[]
for p in person_list.find().batch_size(50):
    matchtxt = mt_tmp.matchtextPerson(p, person_list, fam_list)
    #Ta bort * och ? från matchtxt? KOLLA
    if not matchtxt:
        print 'No matchtextdata',p['_id'],p['refId']
        continue       ##########FIX!!!!!!!!!!
    candidates = search(matchtxt, p['sex'], 10) #Lucene search
    sc = 0
    for (kid,score) in candidates:
##DUBL?
        if (ObjectId(kid) == p['_id']):
            #same person - insert dummy match med status EjOK
            matches.insert({'workid': p['_id'], 'matchid': p['_id'], 'status': 'EjOK'})
            continue
#Dont match A->B and B->A
        key = str(p['_id'])+':'+str(ObjectId(kid))
        if key in done: continue
        done.append(key)
        key = str(ObjectId(kid))+':'+str(p['_id'])
        if key in done: continue
        done.append(key)

        if (score> sc): sc = score
        candidate = match_person.find_one({'_id': ObjectId(kid)})
        #ingen förälder gemensam
        if commonParents(p, candidate) > 0: continue
        #ingår i samma relation (Far/Son, Mor/Dotter)
        if sameFamily(p, candidate):
#            print 'sameFamily'
            continue

        matchdata = matchPers(p, candidate, config, score/8.0) #?? range of Lucene scores?
#DUBL sort score sortV
        (nsQ,nodeScore) = nodeSimQ(p, candidate)
        sortV = nsQ * nodeScore * score

        #prioritera samma födelsedatum
        s=0.0
        if 'birth' in p and 'birth' in candidate:
            if 'date' in p['birth'] and 'date' in candidate['birth']:
                s = dateSim(p['birth']['date'], candidate['birth']['date'])
                if s is None:
                    s = 0.0

        #prioritera par med 1 utan barn och 1 utan föräldrar
        s1 = 0.5
        if (haveChild(p) and not haveParent(p) and
            not haveChild(candidate) and haveParent(candidate)): s1 = 1.0
        elif (not haveChild(p) and haveParent(p) and
            haveChild(candidate) and not haveParent(candidate)): s1 = 1.0

        sortV = sortV * (((s+1.0)/4.0)+0.5) * s1

        matchdata['sortDubl'] = sortV
        matches.insert(matchdata)
        ant += 1
print ant, 'person matchings inserted'
print 'Time:',time.time() - t0
print 'Klart'
print
print '<h2><a href="/listDubl?workDB='+workDB+'&matchDB='+workDB+'">Visa listan</a></h2>'
