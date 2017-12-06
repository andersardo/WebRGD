#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
"""
export a database as Gedcom
"""

import argparse, sys, os, datetime
from collections import defaultdict
parser = argparse.ArgumentParser()
parser.add_argument("workDB", help="Working database name" )
args = parser.parse_args()
workDB = args.workDB
dbName  = os.path.basename(workDB).split('.')[0]  #No '.' or '/' in databasenames
#print 'using db', dbName
import common
config = common.init(dbName, indexes=True)

import codecs, locale
locale.setlocale(locale.LC_ALL, 'en_US.UTF-8') #sorting??
sys.stdout = codecs.getwriter('UTF-8')(sys.stdout)
#####################
#map cId to file
#Read mappings per file
import json
from workFlow import workFlowUI
from dbUtils import getFamilyFromId
from pymongo import MongoClient
#namMap = {}
#placMap = {}
#dateMap = {}
sourMap = {}
user = workDB.split('_')[0]
(files, dbs, workingDir, activeUser) = workFlowUI(user, None)
#print user, activeUser
#print 'F',files
#print 'DB', dbs
cIdMap = {}
client = MongoClient()
for db in dbs:
    datab = client[db]
    #print datab
    for rec in datab.originalData.find({'type': 'admin'}):
            #print ' R', rec
            if 'cId' in rec:
                cIdMap[rec['cId']] = rec['file'].split('/')[-1].split('.')[0]
                (fndir,tmp) = os.path.split(rec['file'])
                #print db, fndir, rec['cId']
                try:
                    #placMap.update(json.load(open(fndir + '/plac.dat')))
                    #dateMap.update(json.load(open(fndir + '/date.dat')))
                    #sourMap[rec['cId']] = json.load(open(fndir + '/sour.dat'))
                    sourMap.update(json.load(open(fndir + '/sour.dat')))
                    #print 'loaded', type(namMap[rec['cId']]), type(placMap[rec['cId']]), type(dateMap[rec['cId']]), type(sourMap[rec['cId']])
                except:
                    pass
#def cId2file(id):
#        return cIdMap.get(id)
#print 'A_923', cId2file('A_923')
#print 'ejdb', cId2file('ejdb')
#####################

from gedcom.gedcom import *

def parseGedcom(ged,frag):
        number = 1
        for line in frag.splitlines( ):
            #ged._parse_line(number,line.decode("utf-8"))
            ged._parse_line(number,line)
            number += 1
        for e in ged.line_list():
            e._init()

def gedcomNoRGD(self):
    """ Return GEDCOM code for this line and all of its sub-lines """
    result = unicode(self)
    #print 'gedcomNoRGD', result
    plac = ''
    for e in self.children_lines():
        if e.tag() in ('PLAC'): plac = e.value()
    for e in self.children_lines():
        if e.tag() in ('RGDF', 'RGDE', 'RGDP', 'RGDD'): continue
        result += '\n' + e.gedcom()
        if e.tag() in ('SOUR'):
                #use mapped or not?
                try:
                        t = sourMap[plac+'-'+e.value()]
                        result += u'\n2 NOTE RGD källa: ' + t
                except:
                        pass
    return result

mapGedcom = {'birth': 'BIRT', 'death': 'DEAT', 'marriage': 'MARR',
             'date': 'DATE', 'place': 'PLAC', 'source': 'SOUR'}
#mapTag = {v: k for k, v in mapGedcom.iteritems()}

month = {'01': 'JAN', '02': 'FEB', '03': 'MAR', '04': 'APR', '05': 'MAY', '06': 'JUN',
         '07': 'JUL', '08': 'AUG', '09': 'SEP', '10': 'OCT', '11': 'NOV', '12': 'DEC'}

def gedcomDate(date):
    if len(date) == 4: return date
    else: return ' '.join([str(int(val[6:8])), month[val[4:6]], val[0:4]])

def printTag(tag, val):
    if val:
        if tag == '2 DATE':
            #format according GEDCOM standard
            if len(val) == 8:
                val = ' '.join([str(int(val[6:8])), month[val[4:6]], val[0:4]])
            elif len(val) != 4: print 'ERROR date', len(val), val
        print tag,
        print val

def printTagI(tag, val):
    if val:
        print tag,
#        print '@I-'+str(val)+'@'
        print '@'+str(val)+'@'

def printTagF(tag, val):
    if val:
        print tag,
#        print '@F-'+str(val)+'@'
        print '@'+str(val)+'@'

def compTagEQ(tag):
    if tag.tag() == 'NAME':
        if len(tag.children_lines())>0: return False
        else: return True
    if tag.tag() in ('BIRT', 'DEAT', 'MARR'):
        for cline in tag.children_lines():
            if cline.tag() in ('DATE', 'PLAC', 'SOUR', 'RGDP', 'RGDD', 'RGDS'): continue
            else: return False
        return True
    return False

import datePrecision
def gedPrintMergeEvent(events):
    #print 'gedPrintMergeEvent', events
    #for evtag in events.keys():  #Evt sort order?
    for evtag in ('BIRT', 'CHR', 'DEAT', 'BURI', 'MARR'):
        #print 'Doing', evtag
        if not evtag in events: continue
        #print 'gedPrintMerge', evtag, len(events[evtag])
        if len(events[evtag]) == 1: print gedcomNoRGD(events[evtag][0])
        else:
            #make dict with quality as key and list of events as value
            Qevent = defaultdict(list)
            for gedev in events[evtag]:
                qual = 10  #unmarked default quality
                plac = ''
                for cline in gedev.children_lines():
                    if cline.tag() == 'PLAC': plac = cline.value()
                for cline in gedev.children_lines():
                    if cline.tag() == 'SOUR':
                        try:
                            if sourMap[plac+'-'+cline.value()].startswith('*'):
                                qual = int(cline.value()[1])
                        except:
                            pass
                        break
                Qevent[qual].append(gedev)
            bestqual = min(Qevent.keys())
            if len(Qevent[bestqual]) == 1: useEvent = Qevent[bestqual][0]
            else:
                #for gedev in Qevent[bestqual]:
                    #give priority to event from master-file
                    #info not available her :-(
                useEvent = Qevent[bestqual][0]
            spanQdate = 1000000
            for cline in useEvent.children_lines():
                if cline.tag() == 'DATE':
                    Qdate = cline.value()
                    (dummy, spanQdate) = datePrecision.date2span(cline.value())
                    break
            for gedev in events[evtag]:
                for cline in gedev.children_lines():
                    if cline.tag() == 'DATE':
                        (dummy, span) = datePrecision.date2span(cline.value())
                        if span < spanQdate:
                            #print 'Better date?', cline.value(), '_', span, 'or', Qdate, '_', spanQdate
                            Qdate = cline.value()
                            Pdate = datePrecision.date2span(cline.value())
            for cline in useEvent.children_lines():
                if cline.tag() == 'DATE': cline._value = Qdate  #HACK
            print gedcomNoRGD(useEvent)
    #print 'Exit'

def gedPrintUniqueEvent(events):
    #print 'gedPrintUniqueEvent'
    for ev in events.keys():
        if len(events[ev]) == 1: print gedcomNoRGD(events[ev][0])
        else:
            textrepr = []
            for gedev in events[ev]:
                evtype = ''
                evplac = ''
                evdate = ''
                evsour = ''
                for cline in gedev.children_lines():
                    if cline.tag() == 'TYPE': evtype = cline.gedcom()
                    elif cline.tag() == 'PLAC': evplac = cline.gedcom()
                    elif cline.tag() == 'DATE': evdate = cline.gedcom()
                    elif cline.tag() == 'SOUR': evsour = cline.gedcom()
                evtext = evtype + evplac + evdate + evsour
                if evtext in textrepr: continue  #not unique
                else:
                    textrepr.append(evtext)
                    print gedcomNoRGD(gedev)

def gedPrintUniqueTag(tags, tagsToPrint = []):
    #print 'gedPrintUniqueTag', tags
    for tag in tags.keys():
        if tag not in tagsToPrint: continue
        txt = set()
        for gedTag in tags[tag]:
            txt.add(gedTag.gedcom())
        for line in txt: print line
    #print

#print "Expires: 0"
#print "Cache-Control: must-revalidate, post-check=0, pre-check=0" 
#print "Content-Type: application/force-download"
#print "Content-Type: application/octet-stream"
#print "Content-Type: application/download"
#print 'Content-Disposition: attachment; filename="RGD.GED"'
#print

print "0 HEAD"
print "1 SOUR openRGD - exportGedcom.py"
today = datetime.datetime.now()
print today.strftime('1 DATE %d %b %Y').upper()
print today.strftime('2 TIME %H:%M:%S')
print "1 GEDC"
print "2 VERS 5.5"
print "2 FORM LINEAGE-LINKED"
print "1 CHAR UTF-8"

mapFamc = {}
mapPersId = {}
for fam in config['families'].find({}):
    famAll = getFamilyFromId(fam['_id'], config['families'], config['relations'])
    for ch in famAll['children']: mapFamc[ch] = fam['_id']

birth = {}
for ind in config['persons'].find({}):
    mapPersId[ind['_id']] = ind['_id']
    #basedata
    print "0 @"+str(ind['_id'])+"@ INDI"  #USE refId or _id???? FIX
    try: print "1 SEX "+ind['sex']
    except: print "1 SEX U"
    printTag("1 NAME",ind['name'])
    try: birth[ind['_id']] = ind['birth']['date']
    except:  birth[ind['_id']] = 0
##    for ev in ('birth', 'death'):
##        if ev in ind:
##            if 'date' in ind[ev] or 'place' in ind[ev] or 'source' in ind[ev]:
##                print "1", mapGedcom[ev]
##                for item in ('date', 'place', 'source'):
##                    if item in ind[ev]: printTag("2 "+mapGedcom[item],ind[ev][item])
    if ind['_id'] in  mapFamc: printTagF("1 FAMC", mapFamc[ind['_id']])
    """
    for fam in config['families'].find(
        {'$or': [ {'husb': ind['_id']},
                  {'wife': ind['_id']}
                  ]},
        {'_id': True, 'marriage': True}
        ).sort([('marriage.date', 1)]):
        printTagF("1 FAMS",fam['_id'])
    """
    for rel in config['relations'].find({'persId': ind['_id']}):
        if rel['relTyp'] == 'child': continue
        printTagF("1 FAMS",rel['famId'])
    #Other tags
    chanTag = None
    orgData =  config['originalData'].find_one({'recordId': ind['_id']})
    parsedGed = []
    for rec in orgData['data']:
        printTag('1 NOTE', 'Original id ' + cIdMap.get(rec['contributionId']) + ' ' + rec['record']['refId'].replace('gedcom_',''))
        try:
            ged = Gedcom('/dev/null')
        except Exception, e:
            exc_type, exc_value, exc_traceback = sys.exc_info()
            traceback.print_exception(exc_type, exc_value, exc_traceback)
        parseGedcom(ged, rec['gedcom'])
        parsedGed.append(ged.individual_list()[0])
    gedMergeEvent = {}
    gedUniqueEvent = {}
    gedUniqueTag = {}
    for gedTag in parsedGed:
        for tag in gedTag.children_lines():
            if tag.level() == 1:
                if tag.tag() in ('SEX', 'RGDF', 'RGDE', 'RGDP', 'RGDD', 'RGDS', 'FAMC', 'FAMS'):
                    continue
                elif tag.tag() in ('CHAN'):
                    chanTag = tag
                    continue
##                elif tag.tag() in ('NAME', 'BIRT', 'DEAT'):
                elif tag.tag() in ('NAME'):
#                    if not compTagEQ(tag): print gedcomNoRGD(tag)
#                    else: print 'Skipped', tag.tag(), tag.value(), tag.gedcom(), '!!'
                    continue
##                elif tag.tag() in ('CHR', 'BURI'):
                elif tag.tag() in ('BIRT', 'CHR', 'DEAT', 'BURI'):
                    print '1 NOTE openRGD Merge ', tag.tag()
                    if tag.tag() in gedMergeEvent:
                        gedMergeEvent[tag.tag()].append(tag)
                    else:
                        gedMergeEvent[tag.tag()] = [tag]
                elif tag.tag() in ('IMMI', 'EMIG', 'RESI', 'DIV', 'EVEN'):
                    #print 'Unique events ', tag.tag()
                    if tag.tag() in gedUniqueEvent:
                        gedUniqueEvent[tag.tag()].append(tag)
                    else:
                        gedUniqueEvent[tag.tag()] = [tag]
                elif tag.tag() in ('OCCU', 'NOTE'):
                    #print 'Unique tags ', tag.tag()
                    if tag.tag() in gedUniqueTag:
                        gedUniqueTag[tag.tag()].append(tag)
                    else:
                        gedUniqueTag[tag.tag()] = [tag]
                else: print gedcomNoRGD(tag)
    if gedMergeEvent: gedPrintMergeEvent(gedMergeEvent)
    if gedUniqueTag: gedPrintUniqueTag(gedUniqueTag, ['OCCU'])
    if gedUniqueEvent: gedPrintUniqueEvent(gedUniqueEvent)
    if gedUniqueTag: gedPrintUniqueTag(gedUniqueTag, ['NOTE'])
    #CHAN-tag
    if len(parsedGed) == 1 and chanTag:
        print gedcomNoRGD(chanTag)
    else:
        print '1 CHAN'
        print today.strftime('2 DATE %d %b %Y').upper() # 27 DEC 2011
        print today.strftime('3 TIME %H:%M:%S')         # 07:52:26

for famRec in config['families'].find({}):
    fam = getFamilyFromId(famRec['_id'], config['families'], config['relations'])
    #basedata
    print "0 @"+str(fam['_id'])+"@ FAM"
    #for ev in ('marriage',):
    #    if ev not in fam: continue
    #    print "1", mapGedcom[ev]
    #    for item in ('date', 'place', 'source'):
    #        if item in fam[ev]: printTag("2 "+mapGedcom[item],fam[ev][item])
    #sort according to birth-date
    for ch in sorted(fam['children'], key=lambda c: birth[c]):
        printTagI("1 CHIL", mapPersId[ch])
    if 'wife' in fam and fam['wife']: printTagI("1 WIFE",mapPersId[fam['wife']])
    if 'husb' in fam and fam['husb']: printTagI("1 HUSB",mapPersId[fam['husb']])
    #other tags
    chanTag = None
    orgData =  config['originalData'].find_one({'recordId': fam['_id']})
    parsedGed = []
    for rec in orgData['data']:
        printTag('1 NOTE', 'Original id ' + cIdMap.get(rec['contributionId']) + ' ' + rec['record']['refId'].replace('gedcom_',''))
	if 'gedcom' in rec:
            try:
               ged = Gedcom('/dev/null')
            except Exception, e:
               exc_type, exc_value, exc_traceback = sys.exc_info()
               traceback.print_exception(exc_type, exc_value, exc_traceback)
            parseGedcom(ged, rec['gedcom'])
            parsedGed.append(ged.family_list()[0])
    gedMergeEvent = {}
    for gedTag in parsedGed:
        for tag in gedTag.children_lines():
            if tag.level() == 1:
                if tag.tag() in ('RGDP', 'RGDD', 'RGDS', 'CHIL', 'HUSB', 'WIFE'):
                    continue
                elif tag.tag() in ('CHAN'):
                    chanTag = tag
                    continue
                elif tag.tag() in ('MARR'):
                    if tag.tag() in gedMergeEvent:
                        gedMergeEvent[tag.tag()].append(tag)
                    else:
                        gedMergeEvent[tag.tag()] = [tag]
#                    if not compTagEQ(tag): print gedcomNoRGD(tag)
#                    else: print 'Skipped', tag.tag(), tag.value(), tag.gedcom(), '!!'
                else: print gedcomNoRGD(tag)
    if gedMergeEvent: gedPrintMergeEvent(gedMergeEvent)
    #CHAN-tag
    if len(parsedGed) == 1 and chanTag:
        print gedcomNoRGD(chanTag)
    else:
        print '1 CHAN'
        print today.strftime('2 DATE %d %b %Y').upper() # 27 DEC 2011
        print today.strftime('3 TIME %H:%M:%S')         # 07:52:26

gedR = config['originalData'].find_one({'type': 'gedcomRecords'})
for rec in gedR['data']:
	if rec: print rec,
print "0 TRLR"
