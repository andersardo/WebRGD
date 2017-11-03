# -*- coding: utf-8 -*-
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import NoAlertPresentException
import unittest, time, re
import codecs
import subprocess
import os

class openRGD_VisaLikheter(unittest.TestCase):
    def setUp(self):
        options = webdriver.ChromeOptions()
        #options.add_argument("--headless") #No display during tests
        options.add_experimental_option("prefs", {
            "download": {
                "default_directory": u"/home/anders/work/RGD/RGDdev/Tests",
                "prompt_for_download": False,
                "directory_upgrade": True,
                "extensions_to_open": ""
                },
        })
        self.driver = webdriver.Chrome(chrome_options=options)

        #self.driver = webdriver.Chrome()
        self.driver.implicitly_wait(5)
        self.base_url = "http://localhost:8083/"
        self.verificationErrors = []
        self.accept_next_alert = True
        self.driver.get(self.base_url + 'login')
        self.driver.find_element_by_name("username").clear()
        self.driver.find_element_by_name("username").send_keys("aatest")
        self.driver.find_element_by_name("password").clear()
        self.driver.find_element_by_name("password").send_keys("RGDtest")
        self.driver.find_element_by_css_selector("button[type=\"submit\"]").click()
        self.assertEqual(True, u"Startsida - arbetsflöde" in self.driver.find_element_by_tag_name("BODY").text)

    def _back2start(self):
        try:
            self.driver.find_element_by_link_text("Tillbaka till startsida").click()
        except:
            self.driver.find_element_by_link_text("Tillbaks till startsida").click()
        self.assertEqual(True, u"Startsida - arbetsflöde" in self.driver.find_element_by_tag_name("BODY").text)

    def testVisaLikhet(self):
#    def step01_ladda_upp(self):
        self.driver.find_element_by_name("gedcomfile").clear()
        self.driver.find_element_by_name("gedcomfile").send_keys("/home/anders/work/RGD/RGDdev/Tests/likhetA.ged")
        self.driver.find_element_by_css_selector("input[type=\"submit\"]").click()
        self.assertEqual(True, u"uploaded successfully" in self.driver.find_element_by_tag_name("BODY").text)
        self.assertEqual(True, u"Resultatlänkar" in self.driver.find_element_by_tag_name("BODY").text)
        self.assertEqual(True, u"Klar" in self.driver.find_element_by_tag_name("BODY").text)
        self._back2start()
        self.driver.find_element_by_name("gedcomfile").clear()
        self.driver.find_element_by_name("gedcomfile").send_keys("/home/anders/work/RGD/RGDdev/Tests/likhetB.ged")
        self.driver.find_element_by_css_selector("input[type=\"submit\"]").click()
        self.assertEqual(True, u"uploaded successfully" in self.driver.find_element_by_tag_name("BODY").text)
        self.assertEqual(True, u"Resultatlänkar" in self.driver.find_element_by_tag_name("BODY").text)
        self.assertEqual(True, u"Klar" in self.driver.find_element_by_tag_name("BODY").text)
        self._back2start()

#    def step04_matchning(self):
        Select(self.driver.find_element_by_xpath("(//select[@name='workDB'])[3]")).select_by_visible_text("aatest_likhetA")
        Select(self.driver.find_element_by_name("matchDB")).select_by_visible_text("aatest_likhetB")
        self.driver.find_element_by_xpath("//input[@value='Matcha!']").click()
        self.assertEqual(True, u"Matching All done" in self.driver.find_element_by_tag_name("BODY").text)
        self._back2start()

#    def step06a_likheter(self):
        Select(self.driver.find_element_by_css_selector("form[name=\"famMatches\"] > select[name=\"workDB\"]")).select_by_visible_text("aatest_likhetA")
        for i in range(60):
            try:
                if self.is_element_present(By.CSS_SELECTOR, "#db2famMatches > select[name=\"matchDB\"]"): break
            except: pass
            time.sleep(1)
        else: self.fail("time out")
        Select(self.driver.find_element_by_css_selector("#db2famMatches > select[name=\"matchDB\"]")).select_by_visible_text("aatest_likhetB")
        self.driver.find_element_by_css_selector("#db2famMatches > p > input[type=\"submit\"]").click()
        downloadFile = './Tests/downloadFamMatches'
        #wait for dowload to finish
        for i in range(60):
            try:
                if os.path.exists(downloadFile): break
            except: pass
            time.sleep(1)
        else: self.fail("time out")
        #diff
        proc = subprocess.Popen(["./Tests/ExcelCompare/bin/excel_cmp",
                                 downloadFile, "./Tests/likhetAB_OK.xlsx"],
                                stdout=subprocess.PIPE)
        stdout, stderr = proc.communicate()
        self.assertEquals(proc.wait(), 0)
        #remove downloaded file
        os.remove(downloadFile)
#    def step99_logout(self):
        self.driver.get(self.base_url)
        self.driver.find_element_by_link_text("Visa lagrad information").click()
        self._back2start()
        self.driver.find_element_by_link_text("Logga ut").click()
        self.assertEqual(True, "Logga in" in self.driver.find_element_by_tag_name("BODY").text)

    def is_element_present(self, how, what):
        try: self.driver.find_element(by=how, value=what)
        except NoSuchElementException as e: return False
        return True

    def is_alert_present(self):
        try: self.driver.switch_to_alert()
        except NoAlertPresentException as e: return False
        return True

    def close_alert_and_get_its_text(self):
        try:
            alert = self.driver.switch_to_alert()
            alert_text = alert.text
            if self.accept_next_alert:
                alert.accept()
            else:
                alert.dismiss()
            return alert_text
        finally: self.accept_next_alert = True

    def tearDown(self):
        self.driver.quit()
        self.assertEqual([], self.verificationErrors)

if __name__ == "__main__":
    unittest.main()
