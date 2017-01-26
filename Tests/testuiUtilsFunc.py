#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8

#Testing simple functions that don't need access to databases

from uiUtils import nameDiff, eventDiff

import random
import unittest

class TestSequenceFunctions(unittest.TestCase):

    def test_nameDiff(self):
        p1 = {	"death" : {
		"date" : "19030316",
		"source" : "F:6 s 203",
		"place" : "Kalmar domkyrkoförs (H)",
		"normPlaceUid" : "1579"
	},
	"name" : "Helena /Olofsdotter/",
	"sex" : "F",
	"grpNameLast" : "13",
	"birth" : {
		"date" : "18281206",
		"source" : "CI:5 s 47",
		"place" : "Madesjö (H)",
		"normPlaceUid" : "2126"
	},
	"grpNameGiven" : "4426",
	"type" : "person",
	"refId" : "gedcom_14-71",
	"RGDid" : "P_280"
}
        p2 = {	"death" : {
		"date" : "19030316",
		"source" : "Kalmar stadsförsamling F:6 (1895-1904) Bild 211 / sid 203 (AID: v173967.b211.s203, NAD: SE/VALA/00177)",
		"place" : "Kalmar domkyrkoförs (H)",
		"normPlaceUid" : "1579"
	},
	"name" : "Lena /Olofsdotter/",
	"sex" : "F",
	"grpNameLast" : "13",
	"birth" : {
		"date" : "18281208",
		"source" : "Madesjö CI:5 (1825-1848) Bild 28 / sid 47 (AID: v39772.b28.s47, NAD: SE/VALA/00241)",
		"place" : "Madesjö (H)",
		"normPlaceUid" : "2126"
	},
	"grpNameGiven" : "4426",
	"type" : "person",
	"refId" : "gedcom_26-13632",
	"RGDid" : "P_9752"
}
        p3 = {	"name" : "Johanna Sofia /Johansdotter/",
	"sex" : "F",
	"grpNameLast" : "16",
	"birth" : {
		"date" : "18530627",
		"source" : "C:7 s 349",
		"place" : "Ljungby (H)",
		"normPlaceUid" : "2000"
	},
	"grpNameGiven" : "5420 9044",
	"type" : "person",
	"refId" : "gedcom_14-73",
	"RGDid" : "P_282"
}

        p4 = {	"name" : "Johanna Sofia /Gabrielsson/",
	"sex" : "F",
	"grpNameLast" : "404",
	"birth" : {
		"date" : "18530627",
		"source" : "C:7",
		"place" : "Ljungby (H)",
		"normPlaceUid" : "2000"
	},
	"grpNameGiven" : "5420 9044",
	"type" : "person",
	"refId" : "gedcom_26-4103",
	"RGDid" : "P_4135"
}
        p5 = {	"name" : "Brita Stina /Andreasdotter/",
	"sex" : "F",
	"grpNameLast" : "6",
	"birth" : {
		"date" : "18250408",
		"source" : "Bredared AI:12 s.85",
		"place" : "Bredared (P)",
		"normPlaceUid" : "350"
	},
	"grpNameGiven" : "1449 6113",
	"type" : "person",
	"refId" : "gedcom_17-11173",
	"RGDid" : "P_2427924"
}
        p6 = {	"name" : "Britta Stina /Andreasdotter/",
	"sex" : "F",
	"grpNameLast" : "11",
	"birth" : {
		"date" : "18250408",
		"source" : "Bredared C:2 sid 285",
		"place" : "Bredared (P), Hult",
		"normPlaceUid" : "350"
	},
	"death" : {
		"date" : "19030316",
		"source" : "F:6 s 203",
		"place" : "Kalmar domkyrkoförs (H)",
		"normPlaceUid" : "1579"
	},
	"grpNameGiven" : "1449 6113",
	"type" : "person",
	"refId" : "gedcom_9672",
	"RGDid" : "P_1452371"
}
        self.assertFalse(nameDiff(p1, p1))
        self.assertFalse(nameDiff(p1, p2))
        self.assertFalse(nameDiff(p2, p1))
        self.assertTrue(nameDiff(p3,p4))
        self.assertTrue(nameDiff(p1,p4))
        self.assertTrue(nameDiff(p3,p2))
        self.assertTrue(nameDiff(p5,p6))
#ska flyttas till egen
        self.assertFalse(eventDiff(p1, p1, ['birth','death']))
        self.assertTrue(eventDiff(p1, p2, ['birth','death']))
        self.assertTrue(eventDiff(p2, p1, ['birth','death']))
        self.assertFalse(eventDiff(p3, p4, ['birth','death']))
        self.assertTrue(eventDiff(p1, p3, ['birth','death']))
        self.assertTrue(eventDiff(p2, p4, ['birth','death']))
        self.assertFalse(eventDiff(p5, p6, ['birth','death']))
        self.assertTrue(eventDiff(p6, p1, ['birth','death']))
        self.assertTrue(eventDiff(p6, p1, ['death','birth']))
        print '========'
        self.assertFalse(eventDiff(p6, p1, ['death']))
        self.assertTrue(eventDiff(p6, p1, ['birth']))
       
if __name__ == '__main__':
    suite = unittest.TestLoader().loadTestsFromTestCase(TestSequenceFunctions)
    unittest.TextTestRunner(verbosity=2).run(suite)
