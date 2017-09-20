#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8

#Testing parsing Gedcom and generation person/family/relations records

import random
import unittest
from importUtils import pers_dict, fam_dict, loadMaps
from gedcom.gedcom import Gedcom
import json

fndir = 'Tests'
#namMap = json.load(open(fndir + '/name.dat'))
#placMap = json.load(open(fndir + '/plac.dat'))
#datMap = json.load(open(fndir + '/date.dat'))
#sourMap = json.load(open(fndir + '/sour.dat'))
errMsg = loadMaps(fndir)
data = Gedcom(fndir + '/data.ged')
facitPers = {}

facitPers['gedcom_P_1477231'] = {'death': {'place': u'Vamlingbo, Gotland, Sverige',
                                           'date': u'18370402', 'quality': 10,
                                           'normPlaceUid': u'Vamlingbo, Gotland'},
                                 'name': u'Hans /Jakobsson/', 'sex': u'M',
                                 'grpNameLast': u'48', 'grpNameGiven': u'5378',
                                 'birth': {'place': u'Sippmanna, Vamlingbo (I), Gotland, Sverige',
                                           'date': u'1787', 'quality': 10,
                                           'normPlaceUid': u'Sippmanna, Vamlingbo (I)'},
                                 'type': 'person', 'refId': 'gedcom_P_1477231'}
facitPers['gedcom_P_1477234'] = {'death': {'date': u'18400616', 'quality': 10,
                                           'place': u'Sippmanna, Vamlingbo (I), Gotland, Sverige',
                                           'normPlaceUid': u'Sippmanna, Vamlingbo (I)'},
                                 'name': u'Anna Kristina /Johansdotter Falck/', 'sex': u'F',
                                 'grpNameLast': u'12 4419','grpNameGiven': u'704 6113',
                                 'birth': {'date': u'17610812', 'quality': 10,
                                           'place': u'Vamlingbo, Gotland, Sverige',
                                           'normPlaceUid': u'Vamlingbo, Gotland'},
                                 'type': 'person', 'refId': 'gedcom_P_1477234'}
facitPers['gedcom_P_1477236'] = {'death': {'date': u'18380203', 'quality': 3,
                                           'place': u'Vamlingbo (I)', 'normPlaceUid': u'3785',
                                           'source': u'*3 Vamlingbo CI:1'},
                                 'name': u'Per /Westr\xf6m/', 'sex': u'M',
                                 'grpNameLast': u'135034','grpNameGiven': u'8095',
                                 'birth': {'date': u'17650112',  'quality': 10,
                                           'place': u'Sundre (I)', 'normPlaceUid': u'3304'},
                                 'type': 'person', 'refId': 'gedcom_P_1477236'}
facitPers['gedcom_P_1477237'] = {'name': u'Peter /Westr\xf6m Persson/', 'sex': u'M',
                                 'grpNameLast': u'135034 1', 'grpNameGiven': u'8095',
                                 'type': 'person', 'refId': 'gedcom_P_1477237'}
facitPers['gedcom_P_1477238'] = {'death': {'quality': 10},
                                 'name': u'Olof /Westr\xf6m Persson/', 'sex': u'M',
                                 'grpNameLast': u'135034 1', 'grpNameGiven': u'7798', 
                                 'birth': {'place': u'Vamlingbo (I)', 'normPlaceUid': u'3785',
                                           'source': u'*3 Vamlingbo CI:1', 'quality': 3},
                                 'type': 'person', 'refId': 'gedcom_P_1477238'}

for person in data.individual_list():
    pp = pers_dict(person)
    person.pid = pp['refId']
    #print "facitPers['"+pp['refId']+"'] = "+str(pp)

facitFamily = {}
facitFamily['gedcom_F_494757'] = {'marriage': {'quality': 10}, 'type': 'family', 'refId': 'gedcom_F_494757'}
facitFamily['gedcom_F_494758'] = {'marriage': {'place': u'Vamlingbo (I)', 'normPlaceUid': u'3785',
                                               'date': u'17911129', 'quality': 3,
                                               'source': u'*3 Vamlingbo CI:1'},
                                  'type': 'family', 'refId': 'gedcom_F_494758'}
facitFamily['gedcom_F_494759'] = {'type': 'family', 'refId': 'gedcom_F_494759'}

facitRelation = {}
facitRelation['gedcom_F_494757'] = [{'persId': 'gedcom_P_1477234', 'relTyp': 'wife'}]
facitRelation['gedcom_F_494758'] = [{'persId': 'gedcom_P_1477236', 'relTyp': 'husb'},
                                    {'persId': 'gedcom_P_1477234', 'relTyp': 'wife'},
                                    {'persId': 'gedcom_P_1477237', 'relTyp': 'child'},
                                    {'persId': 'gedcom_P_1477238', 'relTyp': 'child'}]
facitRelation['gedcom_F_494759'] = [{'persId': 'gedcom_P_1477234', 'relTyp': 'wife'},
                                    {'persId': 'gedcom_P_1477231', 'relTyp': 'child'}]

class TestSequenceFunctions(unittest.TestCase):

    def test_personRecord(self):
        for person in data.individual_list():
            pp = pers_dict(person)
            if pp['refId'] in facitPers.keys():
                self.assertEqual(pp, facitPers[pp['refId']])

    def test_familyRecord(self):
        for fam in data.family_list():
            (ff, relations) = fam_dict(fam)
            if ff['refId'] in facitFamily.keys():
                self.assertEqual(ff, facitFamily[ff['refId']])
            if ff['refId'] in facitRelation.keys():
                self.assertEqual(relations, facitRelation[ff['refId']])
                pass


if __name__ == '__main__':
    suite = unittest.TestLoader().loadTestsFromTestCase(TestSequenceFunctions)
    unittest.TextTestRunner(verbosity=2).run(suite)
