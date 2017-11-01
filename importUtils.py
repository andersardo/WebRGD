#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
"""
<
"""

import re
"""
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

def parsergdnname(p):
    #Numeric ids
   fnid = ' '.join(set(p.rgdfname().split(',')))
   snid = ' '.join(set(p.rgdename().split(',')))
   return (fnid, snid)
"""
#json maps from indataValidering
import json
namMap = {}
datMap = {}
placMap = {}
sourMap = {}

def loadMaps(dir):
    global namMap, datMap, placMap, sourMap
    err = ''
    #Read mappings from indataValidering
    try: namMap = json.load(open(dir + '/name.dat'))
    except: err += "ERROR - namnfil saknas\n"
    try: placMap = json.load(open(dir + '/plac.dat'))
    except: err += "ERROR - platsfil saknas\n"
    try: datMap = json.load(open(dir + '/date.dat'))
    except: err += "ERROR - datumfil saknas\n"
    try: sourMap = json.load(open(dir + '/sour.dat'))
    except: err += "ERROR - sourcefil saknas\n"
    return err

def namestr(p):
    if (p):
        (fn, sn) = p.name()
        return fn + ' /' + sn + '/'
    else: return '?'

def _handleEvent(ev):
    res = {}
    for cline in ev.line.children_lines():
        if cline.tag() == 'DATE':
            try: res['date'] = datMap[cline.value()]
            except: pass
        elif cline.tag() == 'PLAC':
            try: res['normPlaceUid'] = placMap[cline.value()]
            except: pass
            res['place'] = cline.value()
        elif cline.tag() == 'SOUR':
            try:
                res['source'] = sourMap[res['place']+'-'+cline.value()]
            except:
                try:
                    res['source'] = sourMap[cline.value()]
                except: pass
    #if 'source' in res and res['source'].startswith('*'):
    #    res ['quality'] = int(res['source'][1])
    #else: res ['quality'] = 10
    try:
        if res['source'].startswith('*'):
            res ['quality'] = int(res['source'][1])
    except:
        res ['quality'] = 10
    return res

"""
Quality for sources
                            if sourMap[plac+'-'+cline.value()].startswith('*'):
                                qual = int(cline.value()[1])
"""

def pers_dict(p):
   global namMap, datMap, placMap, sourMap
   pers = {'type': 'person'}
   pid = re.sub('0 @','',str(p));
   pid = re.sub('@ INDI','',pid);
   pers['refId'] = pid
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
      pers['birth'] = _handleEvent(p.birth())
   else:
       ###extend by using CHR event if no BIRT available
       for ev in p.other_events:
           if ev.tag == 'CHR':
               pers['birth'] = _handleEvent(ev)
   if p.death():
      pers['death']= _handleEvent(p.death())
   else:
       ###extend by using BURI event if no DEAT available
       for ev in p.other_events:
           if ev.tag == 'BURI':
               pers['death'] = _handleEvent(ev)
   return pers

def fam_dict(fam):
   """
    Extract info about a family and relations into separate dicts
   """
   familj = {'type': 'family'}
   relations = []
   fid = re.sub('0 @','',str(fam));
   fid = re.sub('@ FAM','',fid);
   familj['refId'] = fid
   if (fam.husband() and fam.husband().pid):
      relations.append({'relTyp': 'husb', 'persId': fam.husband().pid})
   if (fam.wife() and fam.wife().pid):
      relations.append({'relTyp': 'wife', 'persId': fam.wife().pid})
   if fam.children():
       for c in fam.children():
           relations.append({'relTyp': 'child', 'persId': c.pid})
   #KOLLA need try ... except?
   try:
       if fam.marriage():
           familj['marriage'] = _handleEvent(fam.marriage())
   except:
       pass
   return (familj,relations)
