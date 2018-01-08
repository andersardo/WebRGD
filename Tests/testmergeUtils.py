#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8

from mergeUtils import maxdict, mergeSimple, mergeEventLongest, mergeEvent

import random
import unittest

class TestMergeFunctions(unittest.TestCase):

    def test_maxdict(self):
        inp1 = {'A': 7, 'B': 6, 'C': 2, 'D': 4}
        self.assertEqual('A', maxdict(inp1))

    def test_mergeSimple(self):
        inp1 = ['A', 'B', 'C', 'B', 'D']
        self.assertEqual('B', mergeSimple(inp1))

    def test_mergeEventLongest(self):
        inp1 = [
	        {
		        "date" : "1903",
		        "source" : "F:5",
		        "place" : "Kalmar domkyrkoförsaml (H)",
		        "normPlaceUid" : "1579",
                "quality": 2
	        },
	        {
		        "date" : "19030316",
		        "source" : "F:6 s 203",
		        "place" : "Kalmar domkyrkoförs (H)",
		        "normPlaceUid" : "1579",
                "quality": 1
	        }
        ]
        ok1 = {
		    "date" : "19030316",
		    "source" : "F:6 s 203",
		    "place" : "Kalmar domkyrkoförsaml (H)",
		    "normPlaceUid" : "1579",
	    }
        self.assertEqual(ok1, mergeEventLongest(inp1))

    def test_ev(self):
        events = [
            {
		        "date" : "19030316",
		        "source" : "F:6 s 203",
		        "place" : "Kalmar domkyrkoförs (H)",
		        "normPlaceUid" : "1579"
            },
	        {
		        "date" : "18281206",
		        "source" : "CI:5 s 47",
		        "place" : "Madesjö (H)",
		        "normPlaceUid" : "2126"
            },
	        {
                "date" : "18281208",
		        "source" : "Madesjö CI:5 (1825-1848) Bild 28 / sid 47 (AID: v39772.b28.s47, NAD: SE/VALA/00241)",
		        "place" : "Madesjö (H)",
		        "normPlaceUid" : "2126"
	        },
	        {
		        "date" : "18530627",
		        "source" : "C:7 s 349",
		        "place" : "Ljungby (H)",
		        "normPlaceUid" : "2000"
	        },
	        {
		        "date" : "19030316",
		        "source" : "F:6 s 203",
		        "place" : "Kalmar domkyrkoförs (H)",
		        "normPlaceUid" : "1579"
	        }
        ]

    def test_mergeEvents(self):
        inp1 = [
	        {
		        "date" : "19030316",
		        "source" : "F:6",
		        "place" : "Kalmar domkyrkoförs (H)",
		        "normPlaceUid" : "1579",
                "quality": 2
	        },
	        {
		        "date" : "19030316",
		        "source" : "F:6 s 203",
		        "place" : "Kalmar domkyrkoförs (H)",
		        "normPlaceUid" : "1579",
                "quality": 1
	        }
        ]
        ok1 = {
		    "date" : "19030316",
		    "source" : "F:6 s 203",
		    "place" : "Kalmar domkyrkoförs (H)",
		    "normPlaceUid" : "1579",
            "quality": 1
	    }
        self.assertEqual(ok1, mergeEvent(inp1))
        inp2 = [
	        {
		        "date" : "1903",
		        "source" : "F:6",
		        "place" : "Kalmar domkyrkoförsamling (H)",
		        "normPlaceUid" : "1579",
                "quality": 1
	        },
	        {
		        "date" : "19030316",
		        "source" : "Kalmar stadsförsamling F:6 (1895-1904)",
		        "place" : "Kalmar domkyrkoförs (H)",
		        "normPlaceUid" : "1579",
                "quality": 5
	        },
	        {
		        "date" : "19030316",
		        "source" : "F:6 s 203",
		        "place" : "Kalmar domkyrkoförs (H)",
		        "normPlaceUid" : "1579",
                "quality": 1
	        }
        ]
        ok2 = {
		    "date" : "19030316",
		    "source" : "F:6 s 203",
		    "place" : "Kalmar domkyrkoförsamling (H)",
		    "normPlaceUid" : "1579",
            "quality": 1
	    }
        self.assertEqual(ok2, mergeEvent(inp2))

if __name__ == '__main__':
    suite = unittest.TestLoader().loadTestsFromTestCase(TestMergeFunctions)
    unittest.TextTestRunner(verbosity=2).run(suite)
