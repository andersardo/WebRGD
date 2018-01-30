#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import sys
import time
from verifyModel import Facit
import common
from collections import defaultdict
import pickle

result = defaultdict(float)
resOK = []
resEjOK = []
for (workDB, matchDB) in (('kalle_KalleA', 'kalle_demomatch'), ('kalle_KalleI', 'kalle_demomatch'),
                          ('kalle_KalleG', 'kalle_demomatch'), ('kalle_KochL', 'kalle_demomatch'),
                          ('kalle_testp1', 'kalle_testp2'), ('kalle_testp1', 'kalle_testp4'),
                          ('kalle_testp4', 'kalle_testp5'), ('kalle_testp3', 'kalle_testp5'),
                          ('kalle_testp3', 'kalle_testp4'), ('kalle_testp1', 'kalle_testp5'),
                          ('vallon_B_jan_SAFP_22', 'vallon_A_stig_SAFP_22') ):
    config = common.init(workDB, matchDBName = matchDB)
    facit = Facit(config)
    facit.getFacit()
    resOK += facit.getDataFacit()
    resEjOK += facit.getDataMiss()
afile = open('dataOKTestFiles.pkl', 'wb')
pickle.dump(resOK, afile)
afile.close()
afile = open('dataEjOKTestFiles.pkl', 'wb')
pickle.dump(resEjOK, afile)
afile.close()

