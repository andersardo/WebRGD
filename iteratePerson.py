#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import sys
from verifyModel import Facit
import common
for (workDB, matchDB) in (('kalle_KalleA', 'kalle_demomatch'), ('kalle_KalleI', 'kalle_demomatch'),
                          ('kalle_KalleG', 'kalle_demomatch'), ('kalle_KochL', 'kalle_demomatch'),
                          ('vallon_B_jan_SAFP_11', 'vallon_A_stig_SAFP_11') ):
    config = common.init(workDB, matchDBName = matchDB)
    facit = Facit(config)

    facit.getFacit()
    (user, fil) = workDB.split('_', 1)
    if user == 'kalle': continue
    facit.importGedcom(fil, user=user)

    (user, fil) = matchDB.split('_', 1)
    facit.importGedcom(fil, user=user)

    facit.verify(doMatch=True, famFeature='famExtended')
    #antOK = facit.genTrainDataFacit()
    #antMiss = facit.genTrainDataMiss()
    #print 'antMiss=', antMiss
    #skip = int((antOK-antMiss)*2)
    #if skip < 1: skip = 1
    #print 'antOK=', antOK, 'antMiss=', antMiss, 'R=', skip
    #antRandom = facit.genTrainDataRandom(skip)
    #print workDB, matchDB, 'Random=', antRandom

#query = {'status': 'Miss'}
#query = {}
#train = open('Train20171104.data', 'wb')
#train.write(facit.getTraindata(query))
#train.close()
