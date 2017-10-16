# -*- coding: utf-8 -*-
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import NoAlertPresentException
import unittest, time, re
from pymongo import MongoClient

class openRGD_workflow(unittest.TestCase):
    def setUp(self):
        self.driver = webdriver.Chrome()
        self.driver.implicitly_wait(5)
        self.base_url = "http://localhost:8084/"
        self.verificationErrors = []
        self.accept_next_alert = True
        self.driver.get(self.base_url + 'login')
        self.driver.find_element_by_name("username").clear()
        self.driver.find_element_by_name("username").send_keys("aatest")
        self.driver.find_element_by_name("password").clear()
        self.driver.find_element_by_name("password").send_keys("RGDtest")
        self.driver.find_element_by_css_selector("button[type=\"submit\"]").click()
        self.assertEqual(True, u"Startsida - arbetsflöde" in self.driver.find_element_by_tag_name("BODY").text)

    def test_ladda_upp(self):
        self.driver.find_element_by_name("gedcomfile").clear()
        self.driver.find_element_by_name("gedcomfile").send_keys("/home/anders/work/RGD/RGDdev/Tests/gedcom1.ged")
        self.driver.find_element_by_name("sour").click()
        self.driver.find_element_by_css_selector("input[type=\"submit\"]").click()
        resultPage = self.driver.find_element_by_tag_name("BODY").text
        self.assertEqual(True, u"uploaded successfully" in resultPage)
        self.assertEqual(True, u"Resultatlänkar" in resultPage)
        #self.driver.find_element_by_link_text("").click()
        self.assertEqual(True, u"Log av indatavalidering" in resultPage)
        self.assertEqual(True, u"RGDN.txt - Namnfel eller namn som saknas i namndatabasen, men finns med avvikande kön" in resultPage)
        self.assertEqual(True, u"RGDO.txt - Ortnamn / Platser som ej kunnat identifieras som församlingar i GEDCOM filen" in resultPage)
        self.assertEqual(True, u"RGDD.txt - Dubblett sökning" in resultPage)
        self.assertEqual(True, u"RGDK.CSV - Saknade källor" in resultPage)
        self.assertEqual(True, u"Indexing aatest_gedcom1 in Lucene" in resultPage)
        self.assertEqual(True, u"Klar" in resultPage)
        """
        Testa innehåll i filer??? Problem med datum!
        self.driver.get(self.base_url + 'getFile?fil=./files/aatest/gedcom1/Log')
        print self.driver.find_element_by_tag_name("BODY").text
        print '##########################################################################'
        self.driver.get(self.base_url + 'getFile?fil=./files/aatest/gedcom1/RGDN.txt')
        print self.driver.find_element_by_tag_name("BODY").text
        """

    def is_element_present(self, how, what):
        try: self.driver.find_element(by=how, value=what)
        except NoSuchElementException as e: return False
        return True

    def tearDown(self):
        self.driver.get(self.base_url)
        self.driver.find_element_by_link_text("Logga ut").click()
        self.driver.quit()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":
    unittest.main()
