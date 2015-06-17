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
from mergeUtils import mergeOrgDataFamImport

#Previous in importUtil.py
import re
#json maps
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
#   (nfn, nsn) = parsergdnname(p)
#   pers['grpNameGiven'] = nfn
   try:
       pers['grpNameGiven'] = ' '.join(set(namMap[namestr(p)]['F'].split(',')))
   except: #pass
       pers['grpNameGiven'] = ''
       #print 'NAME not mapped F',namestr(p) 
#   pers['grpNameLast'] = nsn
   try:
       pers['grpNameLast'] = ' '.join(set(namMap[namestr(p)]['E'].split(',')))
   except: #pass
       pers['grpNameLast'] = ''
       #print 'NAME not mapped E',namestr(p) 
   try:
       pers['sex'] = p.sex()
   except:
       pers['sex'] = 'O'
   if p.birth():
      pers['birth'] = {}
#      if p.birth().date and p.birth().date in dat: pers['birth']['date'] = datMap[p.birth().date]
      try: pers['birth']['date'] = datMap[p.birth().date]
      except: pass
#          print 'DATE not mapped', p.birth().date
#      if p.birth().ndate:
#         pers['birth']['date'] = p.birth().ndate
      try: pers['birth']['normPlaceUid'] = placMap[p.birth().place]
      except: pass
#          print 'PLAC not mapped', p.birth().place
#      if (p.birth().nplace):
#         fno = parseNplace(p.birth().nplace)
#         pers['birth']['normPlaceUid'] = fno
      if (p.birth().place):
         pers['birth']['place'] = p.birth().place
      try:
          pers['birth']['source'] = sourMap[p.birth()._get_value('SOUR')]
      except: pass
#          print 'SOUR not mapped', p.birth().source
#      if (p.birth().nsource):
#         pers['birth']['source'] = p.birth().nsource
#      elif (p.birth().source):
#         pers['birth']['source'] = p.birth().source
   if p.death():
      pers['death']= {}
      try: pers['death']['date'] = datMap[p.death().date]
      except: pass
#      if p.death().ndate:
#         pers['death']['date'] = p.death().ndate
      try: pers['death']['normPlaceUid'] = placMap[p.death().place]
      except: pass
#      if p.death().nplace:
#         fno = parseNplace(p.death().nplace)
#         pers['death']['normPlaceUid'] = fno
      if p.death().place:
         pers['death']['place'] = p.death().place
      try:
          pers['death']['source'] = sourMap[p.death()._get_value('SOUR')]
      except: pass
#          print 'SOUR not mapped', p.death().source
#      if (p.death().nsource):
#         pers['death']['source'] = p.death().nsource
#      elif (p.death().source):
#         pers['death']['source'] = p.death().source
   return pers

def fam_dict(fam):
   familj = {'type': 'family'}
   fid = re.sub('0 @','',str(fam));
   fid = re.sub('@ FAM','',fid);
   familj['refId'] = 'gedcom_' + fid
   if (fam.husband() and fam.husband().pid):
      familj['husb'] = fam.husband().pid
   else: familj['husb'] = None
   if (fam.wife() and fam.wife().pid):
      familj['wife'] = fam.wife().pid
   else: familj['wife'] = None
   familj['children'] = []
   if fam.children():
       for c in fam.children(): familj['children'].append(c.pid)
   #save original _id for future use
   familj['husbOrgId'] = familj['husb']
   familj['wifeOrgId'] = familj['wife']
   familj['childrenOrgId'] = list(familj['children'])
   try:
       if fam.marriage():
           familj['marriage'] = {}
           try: familj['marriage']['date'] = datMap[fam.marriage().date]
           except: pass
#           if fam.marriage().ndate:
#               familj['marriage']['date'] = fam.marriage().ndate
           try:
               familj['marriage']['source'] = sourMap[fam.marriage()._get_value('SOUR')]
           except: pass
#               print 'SOUR not mapped', fam.marriage().source
#           if (fam.marriage().nsource):
#               familj['marriage']['source'] = fam.marriage().nsource
#           elif (fam.marriage().source):
#               familj['marriage']['source'] = fam.marriage().source
           if fam.marriage().place:
               familj['marriage']['place'] = fam.marriage().place
           try: familj['marriage']['normPlaceUid'] = placMap[fam.marriage().place]
           except: pass
#           if (fam.marriage().nplace):
#               familj['marriage']['normPlaceUid'] = fam.marriage().nplace
   except:
       pass
   return familj
###

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

#Read mappings
import json
(fndir,tmp) = os.path.split(fn)
namMap = json.load(open(fndir + '/name.dat'))
placMap = json.load(open(fndir + '/plac.dat'))
datMap = json.load(open(fndir + '/date.dat'))
sourMap = json.load(open(fndir + '/sour.dat'))
##

config = common.init(dbName, dropWorkDB=True, indexes=True)
persons = config['persons']
families = config['families']

t0 = time.time()
logging.info('Reading and parsing gedcom')

try:
    people = Gedcom(fn)
except Exception, e:
    logging.error('<h1>Fatalt fel vid import av Gedcom</h1>')
    exc_type, exc_value, exc_traceback = sys.exc_info()
    traceback.print_exception(exc_type, exc_value, exc_traceback)
    sys.exit()
logging.info('Time %s',time.time() - t0)

#FIX
#Uppdatera ursprung - global database
#UrspId = args.fn (filnamn på GEDfil)
#UrspDate = NOW
# Ger U_uid
#q = "INSERT INTO RGD.ursprung SET UrspDate=NOW(),UrspId='"+args.fn+"'"
#c.execute(q)
#U_uid = db.insert_id()
#U_uid = 7
#Get id; store Id and fn

contributionFile = fn #TEMP #FIX #Handle versions
contributionId = common.getRGDid('A')
config['originalData'].insert({'type': 'admin', 'created': time.time(),
                               'file': contributionFile, 'cId': contributionId})
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

#DISABLE??
#Only accept families with more than 1 member => count family members
familyMembers = {}
for fam in people.family_list():
    familyMembers[str(fam)] = 0
    if (fam.husband()): familyMembers[str(fam)] += 1
    if (fam.wife()): familyMembers[str(fam)] += 1

logging.info('Persons')
for person in people.individual_list():
    pp = pers_dict(person)
#check if person without data? FIX?
#    t=''
#    for f in ('Nfnamn','Nenamn','namn','NFDate','NFFors','FFors','NDDate','NDFors','DFors'):
#        if f in pp: t += pp[f]
#    tst = t.replace('?','').replace('*','').replace('/',' ').replace('(',' ').replace(')',' ').strip()
#    if not tst:
#        print 'Not using person', pp['gedcomId']
#        person.pid = None
#        continue
    orgData = { 'type': 'person', 'data': [] }
    fixGedcom(person)
    orgData['data'].append({'contributionId': contributionId, 'record': pp,
                             'gedcom': person.gedcom()})
    pp['RGDid'] = common.getRGDid('P')
    person.pid = persons.insert( pp )
    orgData['recordId'] = person.pid
    config['originalData'].insert(orgData)
##    fam = person.get_parent_family()  #Children
##standard distribution of simplepyged
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
        ff = fam_dict(fam)
        orgData = { 'type': 'family', 'data': [] }
        fixGedcom(fam)
        orgData['data'].append({'contributionId': contributionId, 'record': ff,
                                'gedcom': fam.gedcom()})
        ff['RGDid'] = common.getRGDid('F')
        fam.pid = families.insert( ff )
        orgData['recordId'] = fam.pid
        config['originalData'].insert(orgData)
#    else: print 'SkipFam', str(fam), 'with', familyMembers[str(fam)], 'member'
#logging.info('Time %s',time.time() - t0)

logging.info('Cleaning by applying patterns and rules')
#Find and merge duplicate persons. How? Not done here

#Find and merge families where husb and wife are same
#  and marriages do not conflict
logging.info('Merge families where husb and wife are same persons')
from collections import defaultdict
d = defaultdict(set)
##d[None,None]=set()
##finds all families
##for f in families.find({}, {'_id': 1, 'husb': 1, 'wife': 1}):
#Also finds duplicates where one of Wife,Husb is None.
#  No dont do that; see mail from Kalle 2014-02-01 KOLLA
#Rolf: Jag tycker att sammanslagning göres när båda föräldrar är identiska,
#       dock ej om partner är blank
#finds families where husb and wife is not None KOLLA!
for f in families.find({'husb': {'$ne': None}, 'wife': {'$ne': None}},
                       {'_id': 1, 'husb': 1, 'wife': 1}):

   d[f['husb'], f['wife']].add(f['_id'])
##Delete entries where both Wife and Husb is None
##del(d[None,None])
for s in d.values():
    if len(s)>=2:
      fdubl = list(s)
      #merge all into fdubl[0]
      for fd in fdubl:
          if fd == fdubl[0]: continue
          #FIX check marriage dates - see pattern notes
          #Enl Rolf: Marr  datum kan vara olika eller blanka
          p1 = families.find_one({'_id': fd})
          logging.info('Merging family %s (%s into %s)', p1['refId'], fd, fdubl[0])
          config['originalData'].update({'recordId': fdubl[0]},
                                        {'$push': {'data': 
                                                   {'contributionId': contributionId,
                                                    'record': p1 }}}, safe=True)
          families.remove(fd)
          config['originalData'].remove({'recordId': fd})
      families.update({'_id': fdubl[0]},
                      mergeOrgDataFamImport(fdubl[0], config['families'],
                                            config['originalData']))
logging.info('Time %s',time.time() - t0)

#Redmine 543
#print 'Delete persons without family'
#d = set()
#for f in families.find():
#   if f['husb']:
#       d.add(f['husb'])
#   if f['wife']:
#       d.add(f['wife'])
#   for ch in f['children']: d.add(ch)
#for p in persons.find():
#    if p['_id'] in d: continue
#    print 'Deleting person with no family', p['refId'],'(',p['_id'],')'
#    persons.remove(p['_id'])
#    config['originalData'].remove({'recordId': p['_id']})
#print 'Time',time.time() - t0

logging.info('Indexing %s in Lucene', dbName)
from luceneUtils import setupDir, index
setupDir(dbName)
index(config['persons'],config['families'])
logging.info('Time %s',time.time() - t0)
