#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
"""
export a database as Gedcom
"""

import argparse, sys, os, datetime
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
    for e in self.children_lines():
        if e.tag() in ('RGDF', 'RGDE', 'RGDP', 'RGDD'): continue
        result += '\n' + e.gedcom()
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
    for ch in fam['children']: mapFamc[ch] = fam['RGDid']

birth = {}
for ind in config['persons'].find({}):
    mapPersId[ind['_id']] = ind['RGDid']
    #basedata
    print "0 @"+str(ind['RGDid'])+"@ INDI"  #USE refId or RGDid???? FIX
    print "1 SEX "+ind['sex']
    printTag("1 NAME",ind['name'])
    try: birth[ind['_id']] = ind['birth']['date']
    except:  birth[ind['_id']] = 0
    for ev in ('birth', 'death'):
        if ev in ind:
            if 'date' in ind[ev] or 'place' in ind[ev] or 'source' in ind[ev]:
                print "1", mapGedcom[ev]
                for item in ('date', 'place', 'source'):
                    if item in ind[ev]: printTag("2 "+mapGedcom[item],ind[ev][item])
    if ind['_id'] in  mapFamc: printTagF("1 FAMC", mapFamc[ind['_id']])
    for fam in config['families'].find(
        {'$or': [ {'husb': ind['_id']},
                  {'wife': ind['_id']}
                  ]},
        {'RGDid': True, 'marriage': True}
        ).sort([('marriage.date', 1)]):
        printTagF("1 FAMS",fam['RGDid'])
    #Other tags
    chanTag = None
    orgData =  config['originalData'].find_one({'recordId': ind['_id']})
    parsedGed = []
    for rec in orgData['data']:
        printTag('1 NOTE', 'original GEDCOMid ' + rec['record']['refId'])
        try:
            ged = Gedcom('/dev/null')
        except Exception, e:
            exc_type, exc_value, exc_traceback = sys.exc_info()
            traceback.print_exception(exc_type, exc_value, exc_traceback)
        parseGedcom(ged, rec['gedcom'])
        parsedGed.append(ged.individual_list()[0])
    for gedTag in parsedGed:
        for tag in gedTag.children_lines():
            if tag.level() == 1:
                if tag.tag() in ('SEX', 'RGDF', 'RGDE', 'RGDP', 'RGDD', 'RGDS', 'FAMC', 'FAMS'):
                    continue
                elif tag.tag() in ('CHAN'):
                    chanTag = tag
		    continue
                elif tag.tag() in ('NAME', 'BIRT', 'DEAT'):
                    if not compTagEQ(tag): print gedcomNoRGD(tag)
#                    else: print 'Skipped', tag.tag(), tag.value(), tag.gedcom(), '!!'
                elif tag.tag() in ('OCCU'):
                    #Merge
                    print gedcomNoRGD(tag)
                else: print gedcomNoRGD(tag)
    #CHAN-tag
    if len(parsedGed) == 1 and chanTag:
        print gedcomNoRGD(chanTag)
    else:
        print '1 CHAN'
        print today.strftime('2 DATE %d %b %Y').upper() # 27 DEC 2011
        print today.strftime('3 TIME %H:%M:%S')         # 07:52:26

for fam in config['families'].find({}):
    #basedata
    print "0 @"+str(fam['RGDid'])+"@ FAM"
    for ev in ('marriage',):
        if ev not in fam: continue
        print "1", mapGedcom[ev]
        for item in ('date', 'place', 'source'):
            if item in fam[ev]: printTag("2 "+mapGedcom[item],fam[ev][item])
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
        printTag('1 NOTE', 'original GEDCOMid ' + rec['record']['refId'])
	if 'gedcom' in rec:
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
                if tag.tag() in ('RGDP', 'RGDD', 'RGDS', 'CHIL', 'HUSB', 'WIFE'):
                    continue
                elif tag.tag() in ('CHAN'):
                    chanTag = tag
		    continue
                elif tag.tag() in ('MARR'):
                    if not compTagEQ(tag): print gedcomNoRGD(tag)
#                    else: print 'Skipped', tag.tag(), tag.value(), tag.gedcom(), '!!'
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
	print rec,
print "0 TRLR"
