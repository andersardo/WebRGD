#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import sys, codecs, locale
locale.setlocale(locale.LC_ALL, 'en_US.UTF-8') #sorting??
sys.stdout = codecs.getwriter(locale.getpreferredencoding())(sys.stdout)
from collections import defaultdict
import argparse, time, sys, os, math

parser = argparse.ArgumentParser()
parser.add_argument("workDB", help="Working database name" )
parser.add_argument("matchDB", help="Database to match against")
parser.add_argument("workdir", help="Working directory" )

args = parser.parse_args()
workDB = args.workDB
matchDB = args.matchDB
workdir = args.workdir

dbName  = os.path.basename(workDB).split('.')[0]  #No '.' or '/' in databasenames
mDBname = os.path.basename(matchDB).split('.')[0]

if dbName != mDBname:
    print 'Oh no! must be same DB', dbName, mDBname
    sys.exit()
t0 = time.time()
print 'Letar efter dubbletter i databas', dbName

#Read Kalles long file 
kdubl  = {}
dubl = []
ks = 0
i = 0
ant = 0
antboth = 0
minks = 1000000
maxks = 0

"""
import traceback
try:
    xl = codecs.open(workdir+'/'+dbName.split('_',1)[1]+'/RGDXL.txt', "r", "utf-8")
    for l in xl.readlines():
        l = l.rstrip()
        if i == 1: (ks,t) = l.split(':')
        if i == 2:
            (id1,n1) = l.split(',', 1) 
        if i == 3:
            (id2,n2) = l.split(',', 1) 
        if i == 4:
            if l.rstrip(): print 'Out of sync', l
            minks = int(ks)
            if int(ks)>maxks: maxks = int(ks)
            i = 0
            ant += 1
            kdubl[';'.join(['gedcom_'+id1,'gedcom_'+id2])] = {'XL': int(ks), 'gedId1': id1, 'gedId2': id2,
                         'namn1': n1, 'namn2': n2}
            #kdubl[';'.join(['gedcom_'+id1,'gedcom_'+id2])] = int(ks)
        i += 1
    print 'Hittat', len(kdubl), 'kandidater i RGDXL.txt'
    if maxks == 0: maxks = 1
except Exception, e:
#    print '<h1>Fatalt fel</h1>'
#    exc_type, exc_value, exc_traceback = sys.exc_info()
#    traceback.print_exception(exc_type, exc_value, exc_traceback)
    maxks = 1
    minks = 0
    print 'Cant find the file', dbName.split('_',1)[1]+'/RGDXL.txt'
##    sys.exit()
"""
#New structureded list
import json, traceback
try:
    print 'trying', workdir+'/'+dbName.split('_',1)[1]+'/dbxl.dat'
    kdublTmp = json.load(open(workdir+'/'+dbName.split('_',1)[1]+'/dbxl.dat'))
    #while testing - FIX so that kdublTmp is not needed.
    for (key, val) in kdublTmp.iteritems():
        (id1,id2) = key.split(';')        
        kdubl[';'.join(['gedcom_'+id1,'gedcom_'+id2])] = {}
        kdubl[';'.join(['gedcom_'+id1,'gedcom_'+id2])]['XL'] = val
    maxks = max(kdublTmp.values())
    if maxks == 0: maxks = 1
    minks = min(kdublTmp.values())
    print 'Hittat', len(kdubl), 'kandidater i dbxl.dat'    
except Exception, e:                                                                            
#    logging.error('<h1>Fatalt fel vid import av Gedcom</h1>')
#    exc_type, exc_value, exc_traceback = sys.exc_info()
#    traceback.print_exception(exc_type, exc_value, exc_traceback)
    maxks = 1 
    minks = 0
    print 'Cant find the file', dbName.split('_',1)[1]+'/dbxl.dat'
#

#KOLLA imports
import common
from matchUtils import *
from utils import matchFam, setFamOK
from matchtext import matchtext
from luceneUtils import setupDir, search
from bson.objectid import ObjectId

mt_tmp = matchtext()

config = common.init(dbName, matchDBName=mDBname, indexes = True)
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
        return (n, sim/n)
    else:
        return (0, 0.0)

def haveChild(pid):
    if fam_list.find({'children': pid}).count() > 0: return True
    else: return False

def haveParent(pid):
    if fam_list.find({'$or': [{'husb': pid}, {'wife': pid}]}).count() > 0:
        return True
    else: return False

def commonParents(p1,p2):
    p1F = fam_list.find_one({'children': p1})
    if not p1F: return 0
    p2F = fam_list.find_one({'children': p2})
    if not p2F: return 0
    ant = 0
    for p in ('husb', 'wife'):
        if (p in p1F) and (p in p2F):
            if p1F[p] == p2F[p]: ant += 1
    return ant

def sameFamily(p1,p2):
    for fam in fam_list.find({'$or': [{'husb': p1}, {'wife': p1}]}, {'_id': 1}):
        if fam_list.find_one({'_id': fam['_id'], 'children': p2}): return True
    for fam in fam_list.find({'$or': [{'husb': p2}, {'wife': p2}]}, {'_id': 1}):
        if fam_list.find_one({'_id': fam['_id'], 'children': p1}): return True
    return False

def birthDateOK(p1, p2, limit):
    try:
        date1 = int(p1['birth']['date'][0:4])
        date2 = int(p2['birth']['date'][0:4])
    except:
        #No dates to compare
        return True
    if abs(date1-date2) <= 10: return True
    else: return False

ant = 0
kant = 0
done = []
pant = 0
matchant = 0
maxSortVal = 0
minScore = 100000
for p in person_list.find().batch_size(50):
    pant += 1
    ptid = (time.time() - t0)/pant
#    print 'Time:',time.time() - t0, pant, ptid, p['refId'],p['name']

    matchtxt = mt_tmp.matchtextPerson(p, person_list, fam_list)
    #Ta bort * och ? från matchtxt? KOLLA
    if not matchtxt:
        print 'No matchtextdata',p['_id'],p['refId']
        continue       ##########FIX!!!!!!!!!!
    candidates = search(matchtxt, p['sex'], 10) #Lucene search
#    sc = 0
    for (candId,score) in candidates:
        candId = ObjectId(candId)
        if (candId == p['_id']):
            #same person - insert dummy match med status EjOK
            matches.insert({'workid': p['_id'], 'matchid': p['_id'], 'status': 'EjOK'})
            continue
#Dont match A->B and B->A
        key = str(p['_id'])+':'+str(candId)
        if key in done: continue
        done.append(key)
        key = str(candId)+':'+str(p['_id'])
        if key in done: continue
        done.append(key)
        #ingen förälder gemensam
        if commonParents(p['_id'], candId) > 0: continue
        #ingår i samma relation (Far/Son, Mor/Dotter)
        if sameFamily(p['_id'], candId):
            continue

        matchant += 1
        candidate = match_person.find_one({'_id': candId})
#        matchdata = matchPers(p, candidate, config, score/8.0) #?? range of Lucene scores?
        if not birthDateOK(p, candidate, 10):
            #print 'Skip birthdates not OK'
            continue
        #else: print 'Birthdates OK'
        (nsQ,nodeScore) = nodeSimQ(p, candidate)
        if nodeScore<0.25: continue
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
        if (haveChild(p['_id']) and not haveParent(p['_id']) and
            not haveChild(candId) and haveParent(candId)): s1 = 1.0
        elif (not haveChild(p['_id']) and haveParent(p['_id']) and
              haveChild(candId) and not haveParent(candId)): s1 = 1.0

        sortV = sortV * (((s+1.0)/4.0)+0.5) * s1
        if sortV>maxSortVal: maxSortVal = sortV
        if score<minScore: minScore = score
        matchdata = {}
        matchdata['status'] = 'dubl'
        matchdata['workid'] = p['_id']
        matchdata['pwork'] = p
        matchdata['matchid'] = candidate['_id']
        matchdata['pmatch'] = candidate
        matchdata['score'] = score
        matchdata['nodesim'] = nodeScore
        matchdata['Match'] = sortV
        if ';'.join([p['refId'], candidate['refId']]) in kdubl:
            matchdata['XL'] = float(kdubl[ ';'.join([p['refId'], candidate['refId']]) ]['XL']) / maxks
            del kdubl[ ';'.join([p['refId'], candidate['refId']]) ]
            kant += 1
        elif ';'.join([candidate['refId'], p['refId']]) in kdubl:
            matchdata['XL'] = float(kdubl[ ';'.join([candidate['refId'], p['refId']]) ]['XL']) / maxks
            del kdubl[ ';'.join([candidate['refId'], p['refId']]) ]
            kant += 1
        else:  matchdata['XL'] = (float(minks)/2.0) / maxks
        matches.insert(matchdata)
        ant += 1
print 'Totalt', pant, 'personer i databasen'
#print matchant,'personpar testade;',ant, u'personpar sparade. Överlapp:', kant
#Save the rest of RGDXL-list
ant = 0
antnf = 0
for (key,val) in kdubl.iteritems():
    (p1,p2) = key.split(';')
    p = match_person.find_one({'refId': p1})
    candidate = match_person.find_one({'refId': p2})
    if not p:
        print 'Ej hittad i databasen', key
        antnf += 1
        continue
    if not candidate:
        print 'Ej hittad i databasen', key
        antnf += 1
        continue
    (nsQ,nodeScore) = nodeSimQ(p, candidate)
    #if nodeScore<0.25: continue
    sortV = nsQ * nodeScore * minScore
    #prioritera samma födelsedatum
    s=0.0
    if 'birth' in p and 'birth' in candidate:
        if 'date' in p['birth'] and 'date' in candidate['birth']:
            s = dateSim(p['birth']['date'], candidate['birth']['date'])
            if s is None:
                s = 0.0
    #prioritera par med 1 utan barn och 1 utan föräldrar
    s1 = 0.5
    if (haveChild(p['_id']) and not haveParent(p['_id']) and
        not haveChild(candId) and haveParent(candId)): s1 = 1.0
    elif (not haveChild(p['_id']) and haveParent(p['_id']) and
          haveChild(candId) and not haveParent(candId)): s1 = 1.0
    sortV = sortV * (((s+1.0)/4.0)+0.5) * s1
    if sortV>maxSortVal: maxSortVal = sortV
    matchdata = {}
    matchdata['status'] = 'dubl'
    matchdata['workid'] = p['_id']
    matchdata['pwork'] = p
    matchdata['matchid'] = candidate['_id']
    matchdata['pmatch'] = candidate
    matchdata['score'] = minScore
    matchdata['nodesim'] = nodeScore
    matchdata['Match'] = sortV
    matchdata['XL'] = val['XL'] / maxks
    matches.insert(matchdata)
    ant += 1
print 'Adderade', ant, u'endast i RGDXL'
print u'Utrensade ur databasen, men i RGDXL', antnf
#Normalize SortVal
ant = 0
for mt in matches.find({'status': 'dubl'}):
    sortV = mt['Match'] / maxSortVal
##    matches.update({'_id': mt['_id']}, {'$set': {'Match': sortV}})
    matches.update({'_id': mt['_id']}, {'$set': {'Match': sortV}})
    ant += 1
print 'Normalized', ant, 'records'
#sort algorithms
for mt in matches.find({'status': 'dubl'}):
    #mean of XL, Match
#    alg1 = (mt['XL'] + mt['Match']) / 2.0
##    #multiply XL, Match
##    alg2 = mt['XL'] * mt['Match']
# Harmonic mean
    try:
        alg2 = 2.0 * mt['XL'] * mt['Match'] / (mt['XL'] + mt['Match'])
    except:
        alg2 = 0.0
    # sqrt(x2 + y2)
#    alg3 = math.sqrt( mt['XL']* mt['XL'] + mt['Match']*mt['Match'])
    matches.update({'_id': mt['_id']}, {'$set': {'Snitt': alg2}})
print 'Time:',time.time() - t0
print 'Klart'
print
print '<h2><a href="/listDublExp?workDB='+workDB+'&matchDB='+workDB+'">Visa listan</a></h2>'
