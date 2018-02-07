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

logging.info('Persons')
for person in people.individual_list():
    pp = pers_dict(person)
    fixGedcom(person)
    pp['_id'] = common.get_id('P')
    person.pid = persons.insert( pp )
    orgData = { 'type': 'person', 'recordId': person.pid, 'data': [] }
    orgData['data'].append({'contributionId': contributionId, 'record': pp,
                             'gedcom': person.gedcom()})
    config['originalData'].insert(orgData)
    fam =  person.get_parent_families()  #Children
    if fam:
# - FIX to handle list!!
        familyMembers[str(fam[0])] += 1
logging.info('Time %s',time.time() - t0)

#############################################################
#print 'Only keep families with more than 1 member'
logging.info('Families')
for fam in people.family_list():
#Keep all families - see Redmine 543
#    if familyMembers[str(fam)] > 1:
        (ff, relations) = fam_dict(fam)
        fixGedcom(fam)
        ff['_id'] = common.get_id('F')
        fam.pid = families.insert( ff )
        for rel in relations:
            rel['famId'] = ff['_id']
        try:
            config['relations'].insert_many(relations)
        except:
            pass
        orgData = { 'type': 'family', 'recordId': fam.pid, 'relation': relations, 'data': [] }
        orgData['data'].append({'contributionId': contributionId, 'record': ff,
                                'gedcom': fam.gedcom()})
        config['originalData'].insert(orgData)
#    else: print 'SkipFam', str(fam), 'with', familyMembers[str(fam)], 'member'
logging.info('Time %s',time.time() - t0)

logging.info('Cleaning by applying patterns and rules')
#Find and merge duplicate persons. How? Not done here

from mergeUtils import findAndMergeDuplFams
from luceneDB import luceneDB
searchDB = luceneDB(dbName)
searchDB.dummyIndex()  #Index must be available for findAndMergeDuplFams to work
findAndMergeDuplFams(config['persons'], config['families'], config['relations'],
                     config['originalData'])
logging.info('Time %s',time.time() - t0)
logging.info('Indexing %s in Lucene', dbName)
searchDB.index(config['persons'],config['families'],config['relations'])
logging.info('Time %s',time.time() - t0)
#stats
antPers = config['persons'].find().count()
antFam = config['families'].find().count()
logging.info('STATS:: Imported persons: %d, families %d', antPers, antFam)
