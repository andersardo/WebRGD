#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
"""
read and parse Gedcom files into a database
Usage:
"""

import logging
logging.basicConfig(level=logging.INFO,
        format = '%(levelname)s %(module)s:%(funcName)s:%(lineno)s - %(message)s')

import common
from gedcom.gedcom import Gedcom
from importUtils import pers_dict, fam_dict, loadMaps
import pickle

import re

def namestr(p):
    if (p):
        (fn, sn) = p.name()
        return fn + ' /' + sn + '/'
    else: return '?'


import argparse, time, sys, os, traceback
parser = argparse.ArgumentParser()
parser.add_argument("user", help="Username (added to databasename)" )
parser.add_argument("fn", help="Gedcom filnamn (also used as databasename)" )
parser.add_argument("--update", help="Force update of database", action="store_true")
args = parser.parse_args()
user = args.user
fn = args.fn
if not os.path.isfile(fn):
    logging.error('<h1>Validerade Gedcom-filen %s saknas</h1>', fn)
    sys.exit()

#use username and first part of filename as databasename
dbName = user + '_' + os.path.basename(fn).split('.')[0]
logging.info('Using database %s importing from file %s', dbName, fn)

(fndir,tmp) = os.path.split(fn)
errMsg = loadMaps(fndir)
if errMsg: logging.info(errMsg)
config = common.init(dbName, dropWorkDB=True, indexes=True)
persons = config['persons']
families = config['families']

t0 = time.time()
logging.info('Reading and parsing gedcom')

try:
    people = Gedcom(fn)
except Exception, e:
    logging.error('<h1>FATALT fel vid import av Gedcom</h1>')
    exc_type, exc_value, exc_traceback = sys.exc_info()
    traceback.print_exception(exc_type, exc_value, exc_traceback)
    sys.exit()
logging.info('Time %s',time.time() - t0)

contributionFile = fn #TEMP #FIX #Handle versions
contributionId = common.get_id('A')
config['originalData'].insert({'type': 'admin', 'created': time.time(),
                               'file': contributionFile, 'cId': contributionId})

#modify Gedcom links by adding unique contributionId to them
noChange = ('INDI', 'FAM', 'FAMS', 'FAMC', 'CHIL')
patRepl = re.compile(r'@([^@]+)@')
def fixGedcom(l):
	if l.value().startswith('@') and l.tag() not in noChange:
		l._value = patRepl.sub('@'+contributionId+r'-\1@', l.value(), 1)
	for line in l.children_lines():
		fixGedcom(line)

gedRecs = ''
for (key,rec) in people.record_dict().iteritems():
    if rec.type() != 'Individual' and rec.type() != 'Family':
        #print 'Key=', key, 'Type=', rec.type()
#FIX nested records!
        fixGedcom(rec)
        if rec.xref():
           rec._xref = patRepl.sub('@'+contributionId+r'-\1@', rec.xref(), 1)
        gedRecs += rec.gedcom() + "\n"
config['originalData'].insert({'type': 'gedcomRecords', 'data': [gedRecs]})

#Only accept families with more than 1 member => count family members
familyMembers = {}
for fam in people.family_list():
    familyMembers[str(fam)] = 0
    if (fam.husband()): familyMembers[str(fam)] += 1
    if (fam.wife()): familyMembers[str(fam)] += 1

#UPDATE Fmap, Imap with identity maps
logging.info('Persons')
for person in people.individual_list():
    pp = pers_dict(person)
    orgData = { 'type': 'person', 'data': [] }
    fixGedcom(person)
    #orgData['data'].append({'contributionId': contributionId, 'record': pp,
    #                         'gedcom': person.gedcom()})
    orgData['contributionId'] = contributionId
    orgData['record'] = pp
    orgData['gedcom'] = person.gedcom()
    pp['_id'] = common.get_id('P')
    person.pid = persons.insert( pp )
    orgData['recordId'] = person.pid
    config['originalData'].insert(orgData)
    fam =  person.get_parent_families()  #Children
    if fam:
# - FIX to handle list!!
        familyMembers[str(fam[0])] += 1
logging.info('Time %s',time.time() - t0)

#############################################################
#print 'Only keep families with more than 1 member'
for fam in people.family_list():
#Redmine 543
#    if familyMembers[str(fam)] > 1:
        (ff, relations) = fam_dict(fam)
        orgData = { 'type': 'family', 'data': [] }
        fixGedcom(fam)
        #orgData['data'].append({'contributionId': contributionId, 'record': ff,
        #                        'gedcom': fam.gedcom()})
        orgData['contributionId'] = contributionId
        orgData['record'] = ff
        orgData['gedcom'] = fam.gedcom()
        ff['_id'] = common.get_id('F')
        fam.pid = families.insert( ff )
        for rel in relations:
            rel['famId'] = ff['_id']
        orgData['relation'] = relations
        config['relations'].insert_many(relations)
        orgData['recordId'] = fam.pid
        config['originalData'].insert(orgData)
#    else: print 'SkipFam', str(fam), 'with', familyMembers[str(fam)], 'member'
logging.info('Time %s',time.time() - t0)

logging.info('Cleaning by applying patterns and rules')
#Find and merge duplicate persons. How? Not done here

#Find and merge families where husb and wife are same
#  and marriages do not conflict
logging.info('Merge families where husb and wife are same persons')
from collections import defaultdict
from mergeUtils import mergeEvent
d = defaultdict(set)
##AAFIX
#for f in config['relations'].find({'husb': {'$ne': None}, 'wife': {'$ne': None}},
#                       {'_id': 1, 'husb': 1, 'wife': 1}):
#   d[f['husb'], f['wife']].add(f['_id'])
for husb in config['relations'].find({'husb': {'$exists': True}}):
    for wife in config['relations'].find({'$and':
                        [{'_id': husb['_id']}, {'wife': {'$exists': True}}]}):
        d[husb['husb'], wife['wife']].add(husb['_id'])
for s in d.values():
    if len(s)>=2:
      fdubl = list(s)
      #generate Fmap
      Fmap = {}
      for F in families.find({}, {'_id': 1} ): Fmap[F['_id']] = F['_id']
      #merge all into fdubl[0]
      marrEvents = []
      F = families.find_one({'_id': fdubl[0]}, {'refId': 1, '_id': 1})
      FrefId = F['refId']
      #USE for fd in fdubl[1:]:
      for fd in fdubl:
          if fd == fdubl[0]: continue
          #FIX check marriage dates - see pattern notes
          #Enl Rolf: Marr  datum kan vara olika eller blanka
          fam2beMerged = families.find_one({'_id': fd})
          if 'marriage' in fam2beMerged: marrEvents.append(fam2beMerged['marriage'])
          logging.info('Merging family %s into %s', fam2beMerged['refId'], FrefId)
          #push orgData to new family
          #config['originalData'].update_one({'recordId': F['_id']},
          #                              {'$push': {'data':
          #                                         {'contributionId': contributionId,
          #                                          'record': fam2beMerged }}})
          #remove family KOLLA FIXA
          families.delete_one({'_id': fam2beMerged['_id']})
          #config['originalData'].delete_one({'recordId': fam2beMerged['_id']})
          config['relations'].delete_many({'$and': [{'_id': fam2beMerged['_id']},
                                               {'$or': [{'husb': {'$exists': True}},
                                                        {'wife': {'$exists': True}}]}
                                           ]})
          #move children to new family
          config['relations'].update_many({'_id': fam2beMerged['_id']},
                                          {'$set': {'_id': F['_id'], 'famRefId': F['refId']}})
      #merge all marriage events
      #orgRecord = config['originalData'].find_one({'recordId': F['_id']})
      families.update_one({'_id': F['_id']}, {'$set':
                                              {'marriage': mergeEvent(marrEvents)}})
      #SAVE Fmap
      config['originalData'].insert_one({'type': 'Fmap', 'data': pickle.dumps(Fmap)})
logging.info('Time %s',time.time() - t0)
logging.info('Indexing %s in Lucene', dbName)
from luceneUtils import setupDir, index
setupDir(dbName)
index(config['persons'],config['families'])
logging.info('Time %s',time.time() - t0)
