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

import re
#json maps from indataValidering
namMap = {}
datMap = {}
placMap = {}
sourMap = {}

monthmap = {}
monthmap['JAN'] = '1'
monthmap['FEB'] = '2'
monthmap['MAR'] = '3'
monthmap['APR'] = '4'
monthmap['MAY'] = '5'
monthmap['JUN'] = '6'
monthmap['JUL'] = '7'
monthmap['AUG'] = '8'
monthmap['SEP'] = '9'
monthmap['OCT'] = '10'
monthmap['NOV'] = '11'
monthmap['DEC'] = '12'

def namestr(p):
    if (p):
        (fn, sn) = p.name()
        return fn + ' /' + sn + '/'
    else: return '?'

def parsergdnname(p):
    #Numeric ids
   fnid = ' '.join(set(p.rgdfname().split(',')))
   snid = ' '.join(set(p.rgdename().split(',')))
   return (fnid, snid)

#@print_timing
def pers_dict(p):
   pers = {'type': 'person'}
   pid = re.sub('0 @','',str(p));
   pid = re.sub('@ INDI','',pid);
   pers['refId'] = 'gedcom_' + pid
   pers['name'] = namestr(p)
   try:
       pers['grpNameGiven'] = ' '.join(set(namMap[namestr(p)]['F'].split(',')))
   except:
       pers['grpNameGiven'] = ''
       #print 'NAME not mapped F',namestr(p) 
   try:
       pers['grpNameLast'] = ' '.join(set(namMap[namestr(p)]['E'].split(',')))
   except:
       pers['grpNameLast'] = ''
       #print 'NAME not mapped E',namestr(p) 
   try:
       pers['sex'] = p.sex()
   except:
       pers['sex'] = 'O'
   if p.birth():
      pers['birth'] = {}
      try: pers['birth']['date'] = datMap[p.birth().date]
      except: pass
      try: pers['birth']['normPlaceUid'] = placMap[p.birth().place]
      except: pass
      if (p.birth().place):
         pers['birth']['place'] = p.birth().place
      try:
          pers['birth']['source'] = sourMap[p.birth().place+'-'+p.birth()._get_value('SOUR')]
      except:
          try:
              pers['birth']['source'] = sourMap[p.birth()._get_value('SOUR')]
          except: pass
   else:
       ###extend by using CHR event if no BIRT available
       for ev in p.other_events:
           if ev.tag == 'CHR':
               pers['birth'] = {}
               for cline in ev.line.children_lines():
                   if cline.tag() == 'DATE':
                       try: pers['birth']['date'] = datMap[cline.value()]
                       except: pass
                   elif cline.tag() == 'PLAC':
                       try: pers['birth']['normPlaceUid'] = placMap[cline.value()]
                       except: pass
                       pers['birth']['place'] = cline.value()
                   elif cline.tag() == 'SOUR':
                       ##pers['birth']['source'] = sourMap[cline.value()]
                       try:
                           pers['birth']['source'] = sourMap[pers['birth']['place']+'-'+cline.value()]
                       except:
                           try:
                               pers['birth']['source'] = sourMap[cline.value()]
                           except: pass
   if p.death():
      pers['death']= {}
      try: pers['death']['date'] = datMap[p.death().date]
      except: pass
      try: pers['death']['normPlaceUid'] = placMap[p.death().place]
      except: pass
      if p.death().place:
         pers['death']['place'] = p.death().place
      try:
          pers['death']['source'] = sourMap[p.death().place+'-'+p.death()._get_value('SOUR')]
      except:
          try:
              pers['death']['source'] = sourMap[p.death()._get_value('SOUR')]
          except: pass
   else:
       ###extend by using BURI event if no DEAT available
       for ev in p.other_events:
           if ev.tag == 'BURI':
               pers['death'] = {}
               for cline in ev.line.children_lines():
                   if cline.tag() == 'DATE':
                       try: pers['death']['date'] = datMap[cline.value()]
                       except: pass
                   elif cline.tag() == 'PLAC':
                       try: pers['death']['normPlaceUid'] = placMap[cline.value()]
                       except: pass
                       pers['death']['place'] = cline.value()
                   elif cline.tag() == 'SOUR':
                       try:
                           pers['death']['source'] = sourMap[pers['death']['place']+'-'+cline.value()]
                       except:
                           try:
                               pers['death']['source'] = sourMap[cline.value()]
                           except: pass
   return pers

def fam_dict(fam):
   """
    Extract info about a family and relations into separate dicts
   """
   familj = {'type': 'family'}
   relations = []
   fid = re.sub('0 @','',str(fam));
   fid = re.sub('@ FAM','',fid);
   familj['refId'] = 'gedcom_' + fid
   if (fam.husband() and fam.husband().pid):
      #familj['husb'] = fam.husband().pid
      relations.append({'relTyp': 'husb', 'persId': fam.husband().pid})
   if (fam.wife() and fam.wife().pid):
      #familj['wife'] = fam.wife().pid
      relations.append({'relTyp': 'wife', 'persId': fam.wife().pid})
   #familj['children'] = []
   if fam.children():
       for c in fam.children():
           #familj['children'].append(c.pid)
           relations.append({'relTyp': 'child', 'persId': c.pid})
   ##save original _id for future use
   #familj['husbOrgId'] = familj['husb']
   #familj['wifeOrgId'] = familj['wife']
   #familj['childrenOrgId'] = list(familj['children'])
   try:
       if fam.marriage():
           familj['marriage'] = {}
           try: familj['marriage']['date'] = datMap[fam.marriage().date]
           except: pass
           if fam.marriage().place:
               familj['marriage']['place'] = fam.marriage().place
           try: familj['marriage']['normPlaceUid'] = placMap[fam.marriage().place]
           except: pass
           try:
              familj['marriage']['source'] = sourMap[fam.marriage().place+'-'+fam.marriage()._get_value('SOUR')]
           except:
              try:
                  familj['marriage']['source'] = sourMap[fam.marriage()._get_value('SOUR')]
              except: pass
   except:
       pass
   return (familj,relations)

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

#Read mappings from indataValidering
import json
(fndir,tmp) = os.path.split(fn)
try: namMap = json.load(open(fndir + '/name.dat'))
except: logging.info('ERROR - namnfil saknas')
try: placMap = json.load(open(fndir + '/plac.dat'))
except: logging.info('ERROR - platsfil saknas')
try: datMap = json.load(open(fndir + '/date.dat'))
except: logging.info('ERROR - datumfil saknas')
try: sourMap = json.load(open(fndir + '/sour.dat'))
except: logging.info('ERROR - sourcefil saknas')

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
    orgData = { 'type': 'person', 'data': [] }
    fixGedcom(person)
    orgData['data'].append({'contributionId': contributionId, 'record': pp,
                             'gedcom': person.gedcom()})
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
        orgData['data'].append({'contributionId': contributionId, 'record': ff,
                                'gedcom': fam.gedcom()})
        ff['_id'] = common.get_id('F')
        fam.pid = families.insert( ff )
        for rel in relations:
            rel['famId'] = ff['_id']
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
      #merge all into fdubl[0]
      F = families.find_one({'_id': fdubl[0]}, {'refId': 1, '_id': 1})
      FrefId = F['refId']
      for fd in fdubl:
          if fd == fdubl[0]: continue
          #FIX check marriage dates - see pattern notes
          #Enl Rolf: Marr  datum kan vara olika eller blanka
          p1 = families.find_one({'_id': fd})
          logging.info('Merging family %s into %s', p1['refId'], FrefId)
          #push orgData to new family
          config['originalData'].update_one({'recordId': F['_id']},
                                        {'$push': {'data':
                                                   {'contributionId': contributionId,
                                                    'record': p1 }}})
          #remove family
          families.delete_one({'_id': p1['_id']})
          config['originalData'].delete_one({'recordId': p1['_id']})
          config['relations'].delete_many({'$and': [{'_id': p1['_id']},
                                               {'$or': [{'husb': {'$exists': True}},
                                                        {'wife': {'$exists': True}}]}
                                           ]})
          #move children to new family
          config['relations'].update_many({'_id': p1['_id']},
                                          {'$set': {'_id': F['_id'], 'famRefId': F['refId']}})
      #merge all marriage events
      orgRecord = config['originalData'].find_one({'recordId': F['_id']})
      families.update_one({'_id': F['_id']}, {'$set':
                                              {'marriage': mergeEvent('marriage', orgRecord)}})
logging.info('Time %s',time.time() - t0)
logging.info('Indexing %s in Lucene', dbName)
from luceneUtils import setupDir, index
setupDir(dbName)
index(config['persons'],config['families'])
logging.info('Time %s',time.time() - t0)
