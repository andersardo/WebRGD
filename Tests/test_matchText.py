#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import Tests.openRGDmongo as mdb
import unittest
import matchtext

class TestSequenceFunctions(unittest.TestCase):
    def setUp(self):
        self.mongoFunc = mdb.openRGDmongo('gedcomM', gedcomPath = './Tests/gedcomM.ged')
        #self.mongoFunc.setupDBs()
        self.mt = matchtext.matchtext()

    def test_luceneFix(self):
        self.assertEqual('heja', self.mt.luceneFix('heja'))
        self.assertEqual('heja', self.mt.luceneFix('heja+'))
        self.assertEqual('heja', self.mt.luceneFix(' heja+ '))
        self.assertEqual('heja', self.mt.luceneFix(' heja !'))
        self.assertEqual('BPlacenr1HusbyAB', self.mt.luceneFix('BPlacenr1,Husby(AB)'))
        self.assertEqual('', self.mt.luceneFix('+-&|!(){}[]^"~*?:,'))

    def test_placeText(self):
        place = {'normPlaceUid': 59, 'place': 'Lund'}
        self.assertEqual(self.mt.placeText(place), 'NPlace59')
        place = {'normPlaceUid': 'femti', 'place': 'Lund'}
        self.assertEqual(self.mt.placeText(place), 'NPlacefemti')
        place = {'place': 'Lund'}
        self.assertEqual(self.mt.placeText(place), 'PlaceLund')

    def test_eventText(self):
        event = { "date" : "19231215",
                  "source" : "Muntligt",
                  "place" : "nr 1, Husby, Husby-Sjuhundra (AB)",
                  "quality" : 10,
                  "normPlaceUid" : "1336"
              }
        self.assertEqual(self.mt.eventText(event, 'B'),
                         'BDateY1923 BDate19231215 BDateY1923BNPlace1336 BNPlace1336')
        event = { "date" : "1923",
                  "source" : "Muntligt",
                  "place" : "nr 1, Husby, Husby-Sjuhundra (AB)",
                  "quality" : 10,
                  "normPlaceUid" : "1336"
              }
        self.assertEqual(self.mt.eventText(event, 'B'),
                         'BDateY1923 BDateY1923BNPlace1336 BNPlace1336')
        event = { "date" : "19231215",
                  "source" : "Muntligt",
                  "place" : "nr 1, Husby (AB)",
                  "quality" : 10
              }
        self.assertEqual(self.mt.eventText(event, 'B'),
                         'BDateY1923 BDate19231215 BPlacenr1,Husby(AB)')

    def test_personText(self):
        self.assertEqual(self.mt.personText(None), '')
        person = {
            "_id" : "P_854294",
            "death" : {
                "date" : "20091102",
                "quality" : 10,
                "place" : "Östhammar (C)",
                "normPlaceUid" : "4307"
            },
            "name" : "Johan Bertil* /Helmersson/",
            "sex" : "M",
            "grpNameLast" : "3934",
            "birth" : {
                "date" : "19231215",
                "source" : "Källreferens till gruppen Muntligt utan källa ovanför.",
                "place" : "nr 1, Husby, Husby-Sjuhundra (AB)",
                "quality" : 10,
                "normPlaceUid" : "1336"
            },
            "grpNameGiven" : "1358 5378",
            "type" : "person",
            "refId" : "I01"
        }
        self.assertEqual(self.mt.personText(person),
                         'G1358 G5378 L3934 1358N3934N5378 Johan Bertil* /Helmersson/ BDateY1923 BDate19231215 BDateY1923BNPlace1336 BNPlace1336 DDateY2009 DDate20091102 DDateY2009DNPlace4307 DNPlace4307')

    def test_personMatchText(self):
        person = self.mongoFunc.config['persons'].find_one({'refId': 'I1372'})
        txt = self.mt.matchtextPerson(person, self.mongoFunc.config['persons'],
                                      self.mongoFunc.config['families'],
                                      self.mongoFunc.config['relations'])
        print txt
        res = u'G5378 L48 48N5378 Hans /Jakobsson/ BDateY1787 BDate17871026 BDateY1787BNPlace3785 BNPlace3785 DDateY1837 DDate18370322 DDateY1837DNPlace3785 DNPlace3785 MotherL4419 FatherBDateY1760BNPlace3785 MotherDNPlace3785 MotherChristina MotherBNPlace3785 MotherDDateY1840DNPlace3785 MotherBDateY1761 FatherL18 Mother4419N6113N704 MotherBDateY1761BNPlace3785 MotherG6113 MotherDDate18400616 FatherDNPlace3785 MotherAnna FatherBNPlace3785 Father18N5248 Mother/Falck/ FatherDDateY1790DNPlace3785 Father/Hansson/ FatherDDate17901026 FatherBDateY1760 FatherDDateY1790 MotherG704 MotherBDate17610812 FatherG5248 FatherJakob MotherDDateY1840'
        self.assertEqual(txt, res)

    """
    def test_familyText(self):
        family = {
            "_id" : "F_326282",
            "marriage" : {
                "date" : "18440122",
                "source" : "*6 Husförhörslängd Edebo",
                "quality" : 6
            },
            "type" : "family",
            "refId" : "F2212"
        }
        self.assertEqual('MDateY1844 MDate18440122', self.mt.familyText(family))

    def test_familyMatchText(self):
        pass
    """
