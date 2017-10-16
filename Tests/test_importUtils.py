# -*- coding: utf-8 -*-
import unittest, time, re, os
from pymongo import MongoClient
#from gedcom.gedcom import Gedcom
#from importUtils import pers_dict, fam_dict, loadMaps

class importUtils(unittest.TestCase):
    def setUp(self):
        client = MongoClient()
        client.drop_database('aatest_gedcom1')
        self.db = client['aatest_gedcom1']
        os.system('mongorestore --quiet --drop Tests/dump')
        #self.people = Gedcom('Tests/gedcom1.ged')

    def test_families(self):
        families = {} #refId as key, databasedict  without _id as value
        families["F0001"]= { "marriage" : { "date" : "18081108", "source" : "*3 Vamlingbo CI:1", "place" : "Sundre (I)", "quality" : 3, "normPlaceUid" : "3304" }, "type" : "family", "refId" : "F0001" }
        families["F0002"]= { "marriage" : { "date" : "18161031", "quality" : 10 }, "type" : "family", "refId" : "F0002" }
        families["F0003"]= { "marriage" : { "date" : "1788", "source" : "*3 Vamlingbo CI:1", "place" : "Vamlingbo (I)", "quality" : 3, "normPlaceUid" : "3785" }, "type" : "family", "refId" : "F0003" }
        families["F0004"]= { "marriage" : { "date" : "17911129", "source" : "*3 Vamlingbo CI:1", "place" : "Vamlingbo (I)", "quality" : 3, "normPlaceUid" : "3785" }, "type" : "family", "refId" : "F0004" }
        for id in families.keys():
            fam = self.db.families.find_one({'refId': id}, {'_id': False})
            self.assertEqual(fam, families[id])

    def test_persons(self):
        persons = {} #refId as key, databasedict without _id as value
        persons['I1076'] = { "name" : u"Pehr /Weström Persson/", "sex" : "M", "grpNameLast" : "135034 1", "birth" : { "date" : "17920329", "source" : "*3 Vamlingbo CI:1", "place" : "Vamlingbo (I)", "quality" : 3, "normPlaceUid" : "3785" }, "grpNameGiven" : "8095", "type" : "person", "refId" : "I1076" }
        persons['I1077'] = { "name" : u"Olof Nils /Weström Persson/", "sex" : "M", "grpNameLast" : "135034 1", "birth" : { "date" : "17961028", "source" : "*3 Vamlingbo CI:1", "place" : "Vamlingbo (I)", "quality" : 3, "normPlaceUid" : "3785" }, "grpNameGiven" : "7798 7587", "type" : "person", "refId" : "I1077" }
        persons['I10923'] = { "death" : { "date" : "18380203", "source" : "*3 Vamlingbo CI:1", "place" : "Vamlingbo (I)", "quality" : 3, "normPlaceUid" : "3785" }, "name" : u"Per /Weström/", "sex" : "M", "grpNameLast" : "135034", "birth" : { "date" : "17650112", "quality" : 10, "place" : "Sundre (I)", "normPlaceUid" : "3304" }, "grpNameGiven" : "8095", "type" : "person", "refId" : "I10923" }
        persons['I1372'] = { "death" : { "date" : "18370322", "source" : "*3 Vamlingbo CI:1", "place" : "Vamlingbo (I)", "quality" : 3, "normPlaceUid" : "3785" }, "name" : "Hans /Jakobsson/", "sex" : "M", "grpNameLast" : "48", "birth" : { "date" : "17871026", "source" : "*3 Vamlingbo CI:1", "place" : "Vamlingbo (I)", "quality" : 3, "normPlaceUid" : "3785" }, "grpNameGiven" : "5378", "type" : "person", "refId" : "I1372" }
        persons['I1374'] = { "name" : "Johannes /Jakobsson/", "sex" : "M", "grpNameLast" : "48", "birth" : { "date" : "17900321", "source" : "*3 Vamlingbo CI:1", "place" : "Vamlingbo (I)", "quality" : 3, "normPlaceUid" : "3785" }, "grpNameGiven" : "5378", "type" : "person", "refId" : "I1374" }
        persons['I15444'] = { "death" : { "date" : "18150626", "source" : "*3 Vamlingbo CI:1", "place" : "Vamlingbo (I)", "quality" : 3, "normPlaceUid" : "3785" }, "name" : "Catharina /Persdotter/", "sex" : "F", "grpNameLast" : "1", "birth" : { "date" : "17840523", "source" : "*6 Sundre CI:1", "place" : "Sundre (I)", "quality" : 6, "normPlaceUid" : "3304" }, "grpNameGiven" : "5700", "type" : "person", "refId" : "I15444" }
        persons['I20138'] = { "death" : { "date" : "18690526", "source" : "*6 Vamlingbo C:1860-1888", "place" : "Vamlingbo (I)", "quality" : 6, "normPlaceUid" : "3785" }, "name" : "Lena Lisa /Hansdotter/", "sex" : "F", "grpNameLast" : "18", "birth" : { "date" : "17930210", "source" : "*3 Alva CI:2", "place" : "Alva (I)", "quality" : 3, "normPlaceUid" : "57" }, "grpNameGiven" : "2551 4426", "type" : "person", "refId" : "I20138" }
        persons['I3439'] = { "death" : { "date" : "18400616", "source" : "*3 Vamlingbo CI:1", "place" : "Vamlingbo (I)", "quality" : 3, "normPlaceUid" : "3785" }, "name" : "Anna Christina /Falck/", "sex" : "F", "grpNameLast" : "4419", "birth" : { "date" : "17610812", "source" : "*4 Vamlingbo AI:1, Sidan 118", "place" : "Vamlingbo (I)", "quality" : 4, "normPlaceUid" : "3785" }, "grpNameGiven" : "704 6113", "type" : "person", "refId" : "I3439" }
        persons['I6381'] = { "death" : { "date" : "17901026", "source" : "*3 Vamlingbo CI:1", "place" : "Vamlingbo (I)", "quality" : 3, "normPlaceUid" : "3785" }, "name" : "Jakob /Hansson/", "sex" : "M", "grpNameLast" : "18", "birth" : { "date" : "1760", "source" : "*4 Vamlingbo AI:1, Sidan 33", "place" : "Vamlingbo (I)", "quality" : 4, "normPlaceUid" : "3785" }, "grpNameGiven" : "5248", "type" : "person", "refId" : "I6381" }
        persons['I7777'] = { "name" : "Syster /Jakobsson/", "sex" : "F", "grpNameLast" : "48", "birth" : { "date" : "17880321", "source" : "*3 Vamlingbo CI:1", "place" : "Vamlingbo (I)", "quality" : 3, "normPlaceUid" : "3785" }, "grpNameGiven" : "9319", "type" : "person", "refId" : "I7777" }
        persons['I8888'] = { "name" : "Bror /Jakobsson/", "sex" : "M", "grpNameLast" : "48", "birth" : { "date" : "17890321", "source" : "*3 Vamlingbo CI:1", "place" : "Vamlingbo (I)", "quality" : 3, "normPlaceUid" : "3785" }, "grpNameGiven" : "1667", "type" : "person", "refId" : "I8888" }
        for id in persons.keys():
            pers = self.db.persons.find_one({'refId': id}, {'_id': False})
            self.assertEqual(pers, persons[id])
        """
        for person in self.people.individual_list():
            pp = pers_dict(person)
            del(persons[pp['refId']]['grpNameGiven'])
            del(persons[pp['refId']]['grpNameLast'])
            self.assertEqual(pp, persons[pp['refId']])
        """

    def test_relations(self):
        """
        { "famId" : "F_15436", "persId" : "P_44175", "relTyp" : "husb" }
{ "famId" : "F_15436", "persId" : "P_44177", "relTyp" : "wife" }
{ "famId" : "F_15437", "persId" : "P_44175", "relTyp" : "husb" }
{ "famId" : "F_15437", "persId" : "P_44178", "relTyp" : "wife" }
{ "famId" : "F_15438", "persId" : "P_44180", "relTyp" : "husb" }
{ "famId" : "F_15438", "persId" : "P_44179", "relTyp" : "wife" }
{ "famId" : "F_15438", "persId" : "P_44175", "relTyp" : "child" }
{ "famId" : "F_15438", "persId" : "P_44182", "relTyp" : "child" }
{ "famId" : "F_15438", "persId" : "P_44181", "relTyp" : "child" }
{ "famId" : "F_15438", "persId" : "P_44176", "relTyp" : "child" }
{ "famId" : "F_15439", "persId" : "P_44174", "relTyp" : "husb" }
{ "famId" : "F_15439", "persId" : "P_44179", "relTyp" : "wife" }
{ "famId" : "F_15439", "persId" : "P_44172", "relTyp" : "child" }
{ "famId" : "F_15439", "persId" : "P_44173", "relTyp" : "child" }
        """
        pass

    def test_original(self):
        pass

    def tearDown(self):
        pass

if __name__ == "__main__":
    unittest.main()
