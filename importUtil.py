#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8

import re

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
#        return fn + ' ' + sn
        return fn + ' /' + sn + '/'  #To facilitate GEDCOM export
    else: return '?'

def parsergdnname(p):
    #Numeric ids
   fnid = ' '.join(set(p.rgdfname().split(',')))
   snid = ' '.join(set(p.rgdename().split(',')))
   return (fnid, snid)

def parseNplace(np):
   return np

#@print_timing
def pers_dict(p):
   pers = {'type': 'person'}
   pid = re.sub('0 @','',str(p));
   pid = re.sub('@ INDI','',pid);
   pers['refId'] = 'gedcom_' + pid
   pers['name'] = namestr(p)
   (nfn, nsn) = parsergdnname(p)
   pers['grpNameGiven'] = nfn
   pers['grpNameLast'] = nsn
   try:
       pers['sex'] = p.sex()
   except:
       pers['sex'] = 'O'
   if p.birth():
      pers['birth'] = {}
      if p.birth().ndate:
         pers['birth']['date'] = p.birth().ndate
      if (p.birth().nplace):
         fno = parseNplace(p.birth().nplace)
         pers['birth']['normPlaceUid'] = fno
      if (p.birth().place):
         pers['birth']['place'] = p.birth().place
      if (p.birth().nsource):
         pers['birth']['source'] = p.birth().nsource
      elif (p.birth().source):
         pers['birth']['source'] = p.birth().source
   if p.death():
      pers['death']= {}
      if p.death().ndate:
         pers['death']['date'] = p.death().ndate
      if p.death().nplace:
         fno = parseNplace(p.death().nplace)
         pers['death']['normPlaceUid'] = fno
      if p.death().place:
         pers['death']['place'] = p.death().place
      if (p.death().nsource):
         pers['death']['source'] = p.death().nsource
      elif (p.death().source):
         pers['death']['source'] = p.death().source
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
           if fam.marriage().ndate:
               familj['marriage']['date'] = fam.marriage().ndate
           if (fam.marriage().nsource):
               familj['marriage']['source'] = fam.marriage().nsource
           elif (fam.marriage().source):
               familj['marriage']['source'] = fam.marriage().source
           if fam.marriage().place:
               familj['marriage']['place'] = fam.marriage().place
           if (fam.marriage().nplace):
               familj['marriage']['normPlaceUid'] = fam.marriage().nplace
   except:
       pass
   return familj
