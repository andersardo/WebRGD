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

    if workDB in done: continue
    config = common.init(workDB, matchDBName = matchDB)
    facit = Facit(config)
    #facit.getFacit()
    (user, fil) = workDB.split('_', 1)
    #if not fil.startswith('test'):
    #    print 'skipping', workDB, matchDB
    #    continue
    facit.importGedcom(fil, user=user)
    done[workDB] = 1
    if matchDB in done: continue
    (user, fil) = matchDB.split('_', 1)
    facit.importGedcom(fil, user=user)
    done[matchDB] = 1
