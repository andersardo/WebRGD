#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import sys
from verifyModelTensor import Facit
import common
from collections import defaultdict
import json
data = []
labels = []
done = {}

for (workDB, matchDB) in (('kalle_KalleA', 'kalle_demomatch'), ('kalle_KalleI', 'kalle_demomatch'),
                          ('kalle_KalleG', 'kalle_demomatch'), ('kalle_KochL', 'kalle_demomatch'),
                          ('kalle_testp1', 'kalle_testp2'), ('kalle_testp1', 'kalle_testp4'),
                          ('kalle_testp4', 'kalle_testp5'), ('kalle_testp3', 'kalle_testp5'),
                          ('kalle_testp3', 'kalle_testp4'), ('kalle_testp1', 'kalle_testp5'),
                          ('vallon_B_jan_SAFP_22', 'vallon_A_stig_SAFP_22') ):
    config = common.init(workDB, matchDBName = matchDB)
    facit = Facit(config)
    facit.getFacit()
    antOK = facit.genTrainDataFacit()
    antMiss = facit.genTrainDataMiss()
    print workDB, matchDB, 'antMiss=', antMiss
    #continue
    skip = int((antOK-antMiss)*2)
    if skip < 1: skip = 1
    print 'antOK=', antOK, 'antMiss=', antMiss, 'R=', skip
    antRandomEjOK = facit.genTrainDataRandom(skip)
    antNotTested = facit.genTrainDataNotTested()
    (d,l) = facit.getTraindataTensor()
    data.extend(d)
    labels.extend(l)
    print workDB, matchDB, 'antRandomEjOK=', antRandomEjOK, 'NotTested=', antNotTested
    print

train = open('TensorDataAll20171208PersDef.data', 'wb')
train.write(json.dumps((data,labels)))
train.close()
