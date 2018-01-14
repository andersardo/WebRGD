#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
"""
export a database as Gedcom
"""

import argparse, sys, os, datetime, re
from collections import defaultdict
import pickle
parser = argparse.ArgumentParser()
parser.add_argument("workDB", help="Working database name" )
args = parser.parse_args()
workDB = args.workDB
dbName  = os.path.basename(workDB).split('.')[0]  #No '.' or '/' in databasenames
import common
config = common.init(dbName, indexes=True)
from common import RGDadm 
import codecs, locale
locale.setlocale(locale.LC_ALL, 'en_US.UTF-8') #sorting??
sys.stdout = codecs.getwriter('UTF-8')(sys.stdout)

import json
from workFlow import workFlowUI
from dbUtils import getFamilyFromId
user = workDB.split('_')[0]
(files, dbs, workingDir, activeUser) = workFlowUI(user, None)

sourMap = {}
cIdMap = {}
for rec in config['originalData'].find({'type': 'admin'}):
    if 'cId' in rec:
        cIdMap[rec['cId']] = rec['file'].split('/')[-1].split('.')[0]
for rec in RGDadm.sourMap.find():
    sourMap[rec['_id']] = rec['val']

monInt = {
    "JAN": '01',
    "FEB": '02',
    "MAR": '03',
    "APR": '04',
    "MAY": '05',
    "JUN": '06',
    "JUL": '07',
    "AUG": '08',
    "SEP": '09',
    "OCT": '10',
    "NOV": '11',
    "DEC": '12'
}

def firstDate(date, endIntervall = False):
    datPat = re.compile(r"([\d\?]*)\s*([^\s]*)\s*([\d\?]{4})")
    m = re.match(r"(FROM|BET) (.+) (TO|AND) (.+)", date)
    if m:
        dat1 = m.group(2)
        return firstDate(dat1)
    m = re.match(r"(ABT|EST|CAL|INT|FROM|TO|AFT|BEF)\s+(.+)", date)
    if m:
        dat1 = m.group(2)
        return firstDate(dat1)
    m = re.match(datPat, date)
    if m:
        day = m.group(1)
        mon = m.group(2)
        year = m.group(3)
        if year:
            year = year.replace('?','0')
        if not mon: mon='JAN'
        if day:
            if len(day)==1: day = '0'+day
            elif day == '??': day = '01'
        else:
            day = '01'
        try:
            return int(year+monInt[mon]+day)
        except:
            return 0
    return 0

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
    plac = ''
    for e in self.children_lines():
        if e.tag() in ('PLAC'): plac = e.value()
    for e in self.children_lines():
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
        print '@'+str(val)+'@'

def printTagF(tag, val):
    if val:
        print tag,
        print '@'+str(val)+'@'

def compTagEQ(tag):
    if tag.tag() == 'NAME':
        if len(tag.children_lines())>0: return False
        else: return True
    if tag.tag() in ('BIRT', 'DEAT', 'MARR'):
        for cline in tag.children_lines():
            if cline.tag() in ('DATE', 'PLAC', 'SOUR'): continue
            else: return False
        return True
    return False

import datePrecision
def gedPrintMergeEvent(events):
        if len(events) == 1: print gedcomNoRGD(events[0])
        else:
            #make dict with quality as key and list of events as value
            Qevent = defaultdict(list)
            for gedev in events:
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
            for gedev in events:
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
"""
def gedPrintUniqueEvent(events):
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
"""
def gedPrintUniqueTag(tags):
    for tag in tags.keys():
        txt = set()
        for gedTag in tags[tag]:
            txt.add(gedTag.gedcom())
        for line in txt: print line

print "0 HEAD"
print "1 SOUR openRGD - exportGedcom.py"
today = datetime.datetime.now()
print today.strftime('1 DATE %d %b %Y').upper()
print today.strftime('2 TIME %H:%M:%S')
print "1 GEDC"
print "2 VERS 5.5"
print "2 FORM LINEAGE-LINKED"
print "1 CHAR UTF-8"
print "1 FILE RGD_"+dbName+".GED"

mapFamc = {}
mapPersId = {}
for fam in config['families'].find({}):
    famAll = getFamilyFromId(fam['_id'], config['families'], config['relations'])
    for ch in famAll['children']: mapFamc[ch] = fam['_id']

birth = {}
#mappings orgId -> mergedId
Imap = defaultdict(set)
Fmap = defaultdict(set)
reverseImap = defaultdict(set)
reverseFmap = defaultdict(set)
map = config['originalData'].find_one({'type': 'Fmap'})
if map:
    for (k,v) in pickle.loads(map['data']).iteritems(): Fmap[k] = v
else:  #initialize with identity map
    for F in config['families'].find({}, {'_id': 1}): Fmap[F['_id']].add(F['_id'])
map = config['originalData'].find_one({'type': 'Imap'})
if map:
    for (k,v) in pickle.loads(map['data']).iteritems(): Imap[k] = v
else:  #initialize with identity map
    for P in config['persons'].find({}, {'_id': 1}): Imap[P['_id']].add(P['_id'])
#reverse maps
for pers in Imap.keys():
    for P in Imap[pers]:
        reverseImap[P].add(pers)
for fam  in Fmap.keys():
    for F in Fmap[fam]:
        reverseFmap[F].add(fam)

for ind in config['persons'].find({}):
    mapPersId[ind['_id']] = ind['_id']
    #Id, Name, Sex
    print "0 @"+str(ind['_id'])+"@ INDI"  #USE refId or _id???? FIX
    try: print "1 SEX "+ind['sex']
    except: print "1 SEX U"
    printTag("1 NAME",ind['name'])
    try: birth[ind['_id']] = ind['birth']['date']
    except:  birth[ind['_id']] = 0
    #loop over all mapped ID's - see mergeUtils mergeOrgDataPers
    chanTag = None
    parsedGed = []
    #debug=False
    for uid in reverseImap[ind['_id']]:
        orgRec = config['originalData'].find_one({'recordId': uid}) # evt 'type': 'person'?
        for rec in orgRec['data']:
            printTag('1 NOTE', 'Original id ' + cIdMap.get(rec['contributionId']) + ' ' + rec['record']['refId'])
            #if rec['record']['refId'] == 'I13856': debug=True
            try:
                ged = Gedcom('/dev/null')
            except Exception, e:
                exc_type, exc_value, exc_traceback = sys.exc_info()
                traceback.print_exception(exc_type, exc_value, exc_traceback)
            parseGedcom(ged, rec['gedcom'])
            parsedGed.append(ged.individual_list()[0])
    #Events
    gedEvents = defaultdict(list)
    gedTags = defaultdict(list)
    for gedTag in parsedGed:
        for tag in gedTag.children_lines():
            if tag.level() == 1:
                if tag.tag() in ('SEX', 'FAMC', 'FAMS', 'NAME', 'BIRT', 'DEAT'):
                    continue
                elif tag.tag() in ('CHAN'):
                    chanTag = tag
                    continue
                elif tag.tag() in ('CHR', 'BURI'):
                    gedEvents[tag.tag()].append(tag)
                elif tag.tag() in ('IMMI', 'EMIG', 'RESI', 'DIV', 'EVEN', 'ADOP'):
                    #if debug: print 'Found::', tag.tag()
                    gedEvents['events'].append(tag)
                elif tag.tag() in ('OCCU', 'NOTE'):
                    gedTags[tag.tag()].append(tag)
                else: print gedcomNoRGD(tag) #??
    #Print in chronological order BIRT, CHR, DEAT, BURI, Relations, FAMS, FAMC
    if 'birth' in ind and ind['birth']['tag'] == 'BIRT':
        if 'date' in ind['birth'] or 'place' in ind['birth'] or 'source' in ind['birth']:
            print "1", mapGedcom['birth']
            for item in ('date', 'place', 'source'):
                if item in ind['birth']: printTag("2 "+mapGedcom[item],ind['birth'][item])
    if 'CHR' in gedEvents: gedPrintMergeEvent(gedEvents['CHR'])
    if 'events' in gedEvents:
        #ADOP (FAMC) fixa familjeID
        #sort chronological
        evs = defaultdict(list)
        dateNo = 0
        for ev in gedEvents['events']:
            #get date
            dateNo += 1
            for e in ev.children_lines():
                if e.tag()=='DATE': date = firstDate(e.value())
            if date==0: date = dateNo
            #if debug: print 'Using::', ev.tag(), date
            evs[date].append(ev)
        for key in sorted(evs.iterkeys()):
            for ev in evs[key]:
                #if debug: print 'Printing::', ev.tag(), key
                print gedcomNoRGD(ev)
    if 'death' in ind and ind['death']['tag'] == 'DEAT':
        if 'date' in ind['death'] or 'place' in ind['death'] or 'source' in ind['death']:
            print "1", mapGedcom['death']
            for item in ('date', 'place', 'source'):
                if item in ind['death']: printTag("2 "+mapGedcom[item],ind['death'][item])
    if 'BURI' in gedEvents: gedPrintMergeEvent(gedEvents['BURI'])
    if gedTags: gedPrintUniqueTag(gedTags)
    if ind['_id'] in  mapFamc: printTagF("1 FAMC", mapFamc[ind['_id']])
    for rel in config['relations'].find({'persId': ind['_id']}):
        #Sort chronological?
        if rel['relTyp'] == 'child': continue
        printTagF("1 FAMS",rel['famId'])
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
    for ev in ('marriage',):
        if ev not in fam: continue
        print "1", mapGedcom[ev]
        for item in ('date', 'place', 'source'):
            if item in fam[ev]: printTag("2 "+mapGedcom[item],fam[ev][item])
    #sort children according to birth-date
    for ch in sorted(fam['children'], key=lambda c: birth[c]):
        printTagI("1 CHIL", mapPersId[ch])
    if 'wife' in fam and fam['wife']: printTagI("1 WIFE",mapPersId[fam['wife']])
    if 'husb' in fam and fam['husb']: printTagI("1 HUSB",mapPersId[fam['husb']])
    #other tags
    chanTag = None
    parsedGed = []
    #loop over all mapped ID's - see mergeUtils mergeOrgDataPers !!!!!!!!!!!!!!!
    for uid in reverseFmap[famRec['_id']]:
        orgRec = config['originalData'].find_one({'recordId': uid}) # evt 'type': 'family'?
        for rec in orgRec['data']:
            printTag('1 NOTE', 'Original id ' + cIdMap.get(rec['contributionId']) + ' ' + rec['record']['refId'])
            try:
                ged = Gedcom('/dev/null')
            except Exception, e:
                exc_type, exc_value, exc_traceback = sys.exc_info()
                traceback.print_exception(exc_type, exc_value, exc_traceback)
            parseGedcom(ged, rec['gedcom'])
            parsedGed.append(ged.family_list()[0])
    for gedTag in parsedGed:
        for tag in gedTag.children_lines():
            if tag.level() == 1:
                if tag.tag() in ('CHIL', 'HUSB', 'WIFE'):
                    continue
                elif tag.tag() in ('CHAN'):
                    chanTag = tag
                    continue
                elif tag.tag() in ('MARR'):
                    continue
                else: print gedcomNoRGD(tag)
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
