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
fam_matches = config['fam_matches']

match_person = config['match_persons']
match_family = config['match_families']

dubltmp = defaultdict(list)
dbltmpNs = defaultdict(float)

ant=0

p = person_list.find_one({'refId': 'gedcom_16-11917'})
print p['name']
#print p
matchtxt = mt_tmp.matchtextPerson(p, person_list, fam_list)
if not matchtxt:
    logging.error('No matchtextdata for %s, %s',p['_id'],p['refId'])
candidates = search(matchtxt, p['sex'], 5) #Lucene search
sc = 0
for (kid,score) in candidates:
    pp = match_person.find_one({'_id': ObjectId(kid)})
    print kid, score, pp['refId'], pp['name']
    #print pp
