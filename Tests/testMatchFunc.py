#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8

#Testing simple functions that don't need access to databases

from utils import matchNamnFDate
from matchUtils import cos, compName, dateSim, strSim, eventSim, nodeSim
from matchUtils import compValEq, compValNeq, compValInte
from matchUtils import compDValEq, compDValNeq, compDValInte, compValAlla
from matchUtils import svmfeatures, antFeaturesNorm

import random
import unittest

class TestSequenceFunctions(unittest.TestCase):

    def test_matchNamnFDate(self):
        t1 = {'sex': 'M', 'birth': {'date': '19750102'} }
        t2 = {'sex': 'M', 'birth': {'date': '19760102'} }
        t3 = {'sex': 'F', 'birth': {'date': '19770102'} }
        t4 = {'sex': 'F', 'birth': {'date': '19750102'} }
        t5 = {'sex': 'M' }
        t6 = {'sex': 'M', 'birth': {'date': '1975'} }
        self.assertTrue(matchNamnFDate(t1, t1))
        self.assertFalse(matchNamnFDate(t1, t2))
        self.assertFalse(matchNamnFDate(t1, t3))
        self.assertFalse(matchNamnFDate(t1, t4))
        self.assertFalse(matchNamnFDate(t1, t5))
        self.assertFalse(matchNamnFDate(t1, t6))

    def test_cos(self):
        v1 = 't1 t2 t3 t4'
        v2 = 't5 t6 t7'
        v3 = 't8'
        v4 = ''
        v5 = 't2 t4'
        v6 = 't3'
        self.assertEqual(cos(v1, v1), 1.0)
        self.assertEqual(cos(v1, v2), 0.0)
        self.assertEqual(cos(v1, v3), 0.0)
        self.assertEqual(cos(v3, v1), 0.0)
        self.assertEqual(cos(v3, v3), 1.0)
        self.assertAlmostEqual(cos(v1, v5), 0.707, places=3)
        self.assertAlmostEqual(cos(v1, v6), 0.5, places=3)
        self.assertAlmostEqual(cos(v1, v5+' '+v6), 0.866, places=3)
        self.assertAlmostEqual(cos(v1, v5+v6), 0.354, places=3)
        self.assertRaises(ZeroDivisionError, cos, v1, v4)
        self.assertRaises(ZeroDivisionError, cos, v4, v1)

    def test_compName(self):
        n1 = 'n1'
        n2 = 'n1 n2'
        n3 = 'n3'
        n4 = 'n4 n5 n6 n7 n8'
        n5 = 'n4 n6 n8'
        n6 = 'n9 n10 n11 n12'
        self.assertEqual(compName(n1, n1), 1.0)
        self.assertEqual(compName(n4, n4), 1.0)
        self.assertEqual(compName(n1, n3), -1.0)
        self.assertEqual(compName(n2, n5), -1.0)
        self.assertAlmostEqual(compName(n1, n2), 1.0, places=3) #??
        self.assertAlmostEqual(compName(n4, n5), 1.0, places=3) #??
        self.assertAlmostEqual(compName(n4, n5+' '+n3), 0.5, places=3) #??
        self.assertAlmostEqual(compName(n2+' '+n4, n3+' '+n5), 0.5, places=3) #??
        self.assertAlmostEqual(compName(n2+' '+n5, n4), 0.2, places=3) #??
        self.assertAlmostEqual(compName(n1+' '+n4, n1+' '+n6), -0.6, places=3) #??
        self.assertIsNone(compName(n2, ''))
        self.assertIsNone(compName('', n2))
        self.assertIsNone(compName(n2, None))
        self.assertIsNone(compName(None, None))

    def test_dateSim(self):
        d1 = '1975'
        d2 = '19750205'
        d3 = '19761213'
        d4 = '19761201'
        d5 = '19770105'
        d6 = '19761224'
        d7 = '1976'
        self.assertEqual(dateSim(d1,d1), 1.0)
        self.assertEqual(dateSim(d2,d2), 1.0)
        self.assertEqual(dateSim(d1,d2), 1.0)
        self.assertEqual(dateSim(d2,d3), -1.0)
        self.assertEqual(dateSim(d1,d7), -1.0)
        self.assertAlmostEqual(dateSim(d3,d4), 0.2, places=3)
        self.assertAlmostEqual(dateSim(d4,d3), 0.2, places=3)
        self.assertAlmostEqual(dateSim(d3,d5), -0.533, places=3)
        self.assertAlmostEqual(dateSim(d3,d6), 0.267, places=3)

    def test_strSim(self):
        n1 = 'n1'
        n2 = 'n1 n2'
        n3 = 'n3'
        n4 = 'n4 n5 n6 n7 n8'
        n5 = 'n4 n6 n8'
        n6 = 'n9 n10 n11 n12'
        self.assertEqual(strSim(n1, n1), 1.0)
        self.assertEqual(strSim(n4, n4), 1.0)
        self.assertEqual(strSim(n1, n3), 0.0)
        #self.assertEqual(strSim(n2, n5), -1.0)
        self.assertAlmostEqual(strSim(n1, n2), 0.143, places=3) #??
        self.assertAlmostEqual(strSim(n4, n5), 0.455, places=3) #??
        self.assertAlmostEqual(strSim(n4, n5+' '+n3), 0.44, places=3) #??
        self.assertAlmostEqual(strSim(n2+' '+n4, n3+' '+n5), 0.29, places=3) #??
        self.assertAlmostEqual(strSim(n2+' '+n5, n4), 0.286, places=3) #??
        self.assertAlmostEqual(strSim(n1+' '+n4, n1+' '+n6), 0.176, places=3) #??
        self.assertIsNone(strSim(n2, ''))
        self.assertIsNone(strSim('', n2))
        self.assertIsNone(strSim(n2, None))
        self.assertIsNone(strSim(None, None))

    def test_eventSim(self):
        print 'Not implemented yet'
        pass

    def test_nodeSim(self):
        print 'Not implemented yet'
        pass

    def test_compValEq(self):
        v1 = 7
        v2 = 9
        self.assertEqual(compValEq(v1,v1), 1)
        self.assertEqual(compValEq(v1,v2), 0)
        self.assertEqual(compValEq(v1,None), 0)
        self.assertEqual(compValEq(None, v2), 0)
        self.assertEqual(compValEq(None, None), 0)

    def test_compValNeq(self):
        v1 = 7
        v2 = 9
        self.assertEqual(compValNeq(v1,v1), 0)
        self.assertEqual(compValNeq(v1,v2), 1)
        self.assertEqual(compValNeq(v1,None), 0)
        self.assertEqual(compValNeq(None, v2), 0)
        self.assertEqual(compValNeq(None, None), 0)

    def test_compValInte(self):
        v1 = 7
        v2 = 9
        self.assertEqual(compValInte(v1,v1), 0)
        self.assertEqual(compValInte(v1,v2), 0)
        self.assertEqual(compValInte(v1,None), 1)
        self.assertEqual(compValInte(None, v2), 1)
        self.assertEqual(compValInte(None, None), 1)

    def test_compDValEq(self):
        v1 = 7
        v2 = 9
        self.assertEqual(compDValEq(v1,v1,v1,v1), 1)
        self.assertEqual(compDValEq(v1,v2,v1,v2), 0)
        self.assertEqual(compDValEq(v1,None,v1,None), 0)
        self.assertEqual(compDValEq(None,v2,None,v2), 0)
        self.assertEqual(compDValEq(None,None,None,None), 0)
        self.assertEqual(compDValEq(v1,v1,None,None), 0)

    def test_compDValNeq(self):
        v1 = 7
        v2 = 9
        self.assertEqual(compDValNeq(v1,v1,v1,v1), 0)
        self.assertEqual(compDValNeq(v1,v2,v1,v2), 1)
        self.assertEqual(compDValNeq(v1,None,v1,None), 0)
        self.assertEqual(compDValNeq(None, v2,None,v2), 0)
        self.assertEqual(compDValNeq(None, None,None,None), 0)
        self.assertEqual(compDValNeq(v1,v2,None,None), 0)

    def test_compDValInte(self):
        v1 = 7
        v2 = 9
        self.assertEqual(compDValInte(v1,v1,v1,v1), 0)
        self.assertEqual(compDValInte(v1,v2,v1,v2), 0)
        self.assertEqual(compDValInte(v1,None,v1,None), 1)
        self.assertEqual(compDValInte(None, v2,None,v2), 1)
        self.assertEqual(compDValInte(None, None,None,None), 1)
        self.assertEqual(compDValInte(v1,v1,None,None), 1)
        self.assertEqual(compDValInte(v1,v1,v2,None), 1)

    def test_compValAlla(self):
        v1 = 7
        v2 = 9
        self.assertEqual(compValAlla(v1,v1), [1,0,0])
        self.assertEqual(compValAlla(v1,v2), [0,1,0])
        self.assertEqual(compValAlla(v1,None), [0,0,1])
        self.assertEqual(compValAlla(None, v2), [0,0,1])
        self.assertEqual(compValAlla(None, None), [0,0,1])

    def test_svmfeatures(self):
        print 'Not implemented yet'
        pass

    def test_antFeaturesNorm(self):
        print 'Not implemented yet'
        pass
       
if __name__ == '__main__':
    suite = unittest.TestLoader().loadTestsFromTestCase(TestSequenceFunctions)
    unittest.TextTestRunner(verbosity=2).run(suite)
