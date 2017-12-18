#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import sys
from verifyModel import Facit
import common
from collections import defaultdict
done = {}

for (workDB, matchDB) in (('kalle_KalleA', 'kalle_demomatch'), ('kalle_KalleI', 'kalle_demomatch'),
                          ('kalle_KalleG', 'kalle_demomatch'), ('kalle_KochL', 'kalle_demomatch'),
                          ('kalle_testp1', 'kalle_testp2'), ('kalle_testp1', 'kalle_testp4'),
                          ('kalle_testp4', 'kalle_testp5'), ('kalle_testp3', 'kalle_testp5'),
                          ('kalle_testp3', 'kalle_testp4'), ('kalle_testp1', 'kalle_testp5'),
                          ('vallon_B_jan_SAFP_22', 'vallon_A_stig_SAFP_22') ):
    print workDB, matchDB
    config = common.init(workDB, matchDBName = matchDB)
    facit = Facit(config)
    facit.getFacit()
    antOK = facit.genFamTrainDataFacit()
    #antMiss = facit.genFamTrainDataMiss()
    continue
    skip = int((antOK-antMiss)*2)
    if skip < 1: skip = 1
    print 'antOK=', antOK, 'antMiss=', antMiss, 'R=', skip
    antRandomEjOK = facit.genFamTrainDataRandom(skip)
    antNotTested = facit.genFamTrainDataNotTested()
    print workDB, matchDB, 'antRandomEjOK=', antRandomEjOK, 'NotTested=', antNotTested
    print

#config = common.init('kalle_KalleA', matchDBName = 'kalle_demomatch')
#facit = Facit(config)
query = {'status': 'Miss'}
#query = {}
train = open('TrainDataAll20171208L6_5FamMiss.data', 'wb')
train.write(facit.getTraindata(query))
train.close()
