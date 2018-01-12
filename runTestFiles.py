#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import sys
import time
from verifyModel import Facit
import common
from collections import defaultdict
result = defaultdict(float)
partResult = {}
for (workDB, matchDB) in (('kalle_KalleA', 'kalle_demomatch'), ('kalle_KalleI', 'kalle_demomatch'),
                          ('kalle_KalleG', 'kalle_demomatch'), ('kalle_KochL', 'kalle_demomatch'),
                          ('kalle_testp1', 'kalle_testp2'), ('kalle_testp1', 'kalle_testp4'),
                          ('kalle_testp4', 'kalle_testp5'), ('kalle_testp3', 'kalle_testp5'),
                          ('kalle_testp3', 'kalle_testp4'), ('kalle_testp1', 'kalle_testp5'),
                          ('vallon_B_jan_SAFP_22', 'vallon_A_stig_SAFP_22') ):
    config = common.init(workDB, matchDBName = matchDB)
    facit = Facit(config)
    facit.getFacit()
    #res = facit.verify(doMatch=True)
    res = facit.verify(doMatch=True, famFeature='famExtended', command='matchDeb')
    #facit.listMisses()
    partResult[workDB+';'+matchDB] = res
    result['ant'] += 1
    for k in res.keys():
        result[k] += res[k]
print
print 'SUMMED RESULTS'
for k in result.keys():
    print k, result[k] #, 'mean=', result[k]/result['ant']
#recall = result['matchOKinFacit']/result['Facit']
#precision =  result['matchOKinFacit']/result['matchOK']
#fscore = 2.0 * precision * recall / (precision + recall)
#print '  Precision=', precision, 'Recall=', recall, 'F-score=', fscore
felOK = result['matchOK'] - result['matchOKinFacit']
missadeOK = result['Facit'] - result['matchOKinFacit']
totFel = felOK + missadeOK
print '  fel matchade OK=', felOK, 'missade OK=', missadeOK, 'Tot=',totFel,totFel-result['matchManinFacit'], '(', totFel*100.0/result['Facit'], '%)' 
#print ';'.join([result['Facit'],result['matchOKinFacit'],result['matchOK'],result['matchManinFacit'],result['matchMan']])
