# -*- coding: utf-8 -*-
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import NoAlertPresentException
import unittest, time, re

class openRGD_workflow(unittest.TestCase):
    def setUp(self):
        self.driver = webdriver.Chrome()
        self.driver.implicitly_wait(5)
        self.base_url = "http://localhost:8084/"
        self.verificationErrors = []
        self.accept_next_alert = True

    def _back2start(self):
        try:
            self.driver.find_element_by_link_text("Tillbaka till startsida").click()
        except:
            self.driver.find_element_by_link_text("Tillbaks till startsida").click()
        self.assertEqual(True, u"Startsida - arbetsflöde" in self.driver.find_element_by_tag_name("BODY").text)

    def step00_login(self):
        self.driver.get(self.base_url + 'login')
        self.driver.find_element_by_name("username").clear()
        self.driver.find_element_by_name("username").send_keys("aatest")
        self.driver.find_element_by_name("password").clear()
        self.driver.find_element_by_name("password").send_keys("RGDtest")
        self.driver.find_element_by_css_selector("button[type=\"submit\"]").click()
        self.assertEqual(True, u"Startsida - arbetsflöde" in self.driver.find_element_by_tag_name("BODY").text)

    def step01_ladda_upp(self):
        self.driver.find_element_by_name("gedcomfile").clear()
        self.driver.find_element_by_name("gedcomfile").send_keys("/home/anders/work/RGD/RGDdev/Tests/gedcom1.ged")
        self.driver.find_element_by_css_selector("input[type=\"submit\"]").click()
        self.assertEqual(True, u"uploaded successfully" in self.driver.find_element_by_tag_name("BODY").text)
        self.assertEqual(True, u"Resultatlänkar" in self.driver.find_element_by_tag_name("BODY").text)
        self.assertEqual(True, u"Klar" in self.driver.find_element_by_tag_name("BODY").text)
        self._back2start()
        self.driver.find_element_by_name("gedcomfile").clear()
        self.driver.find_element_by_name("gedcomfile").send_keys("/home/anders/work/RGD/RGDdev/Tests/gedcom2.ged")
        self.driver.find_element_by_css_selector("input[type=\"submit\"]").click()
        self.assertEqual(True, u"uploaded successfully" in self.driver.find_element_by_tag_name("BODY").text)
        self.assertEqual(True, u"Resultatlänkar" in self.driver.find_element_by_tag_name("BODY").text)
        self.assertEqual(True, u"Klar" in self.driver.find_element_by_tag_name("BODY").text)
        self._back2start()

    def step02_dubblett(self):
        Select(self.driver.find_element_by_name("workDB")).select_by_visible_text("aatest_gedcom1")
        self.driver.find_element_by_css_selector("form[name=\"xlDubl\"] > p > input[type=\"submit\"]").click()
        self.assertEqual(True, u"Resultat i RGDXL.txt" in self.driver.find_element_by_tag_name("BODY").text)
        self._back2start()

    def step02a_alt_dubblett(self):
        Select(self.driver.find_element_by_css_selector("form[name=\"listDubl\"] > select[name=\"workDB\"]")).select_by_visible_text("aatest_gedcom1")
        self.driver.find_element_by_css_selector("form[name=\"listDubl\"] > p > input[type=\"submit\"]").click()
        self.assertEqual(True, u"Visa listan" in self.driver.find_element_by_tag_name("BODY").text)
        self._back2start()

    def step04_matchning(self):
        Select(self.driver.find_element_by_xpath("(//select[@name='workDB'])[3]")).select_by_visible_text("aatest_gedcom1")
        Select(self.driver.find_element_by_name("matchDB")).select_by_visible_text("aatest_gedcom2")
        self.driver.find_element_by_xpath("//input[@value='Matcha!']").click()
        self.assertEqual(True, u"Matching All done" in self.driver.find_element_by_tag_name("BODY").text)
        self._back2start()

    def step05_manuell_matchning(self):
        Select(self.driver.find_element_by_css_selector("form[name=\"manualMatch\"] > select[name=\"workDB\"]")).select_by_visible_text("aatest_gedcom1")
        for i in range(60):
            try:
                if self.is_element_present(By.CSS_SELECTOR, "#db2manualMatch > select[name=\"matchDB\"]"): break
            except: pass
            time.sleep(1)
        else: self.fail("time out")
        Select(self.driver.find_element_by_css_selector("#db2manualMatch > select[name=\"matchDB\"]")).select_by_visible_text("aatest_gedcom2")
        self.driver.find_element_by_css_selector("#db2manualMatch > p > input[type=\"submit\"]").click()
        self.assertEqual(True, u"RGD Familjelista" in self.driver.find_element_by_tag_name("BODY").text)
        self._back2start()

    def step06_skillnader(self):
        Select(self.driver.find_element_by_css_selector("form[name=\"listSkillnad\"] > select[name=\"workDB\"]")).select_by_visible_text("aatest_gedcom1")
        for i in range(60):
            try:
                if self.is_element_present(By.CSS_SELECTOR, "#db2listSkillnad > select[name=\"matchDB\"]"): break
            except: pass
            time.sleep(1)
        else: self.fail("time out")
        Select(self.driver.find_element_by_css_selector("#db2listSkillnad > select[name=\"matchDB\"]")).select_by_visible_text("aatest_gedcom2")
        self.driver.find_element_by_css_selector("#db2listSkillnad > p > input[type=\"submit\"]").click()
        self.assertEqual(True, u"RGD Skillnad Personlista" in self.driver.find_element_by_tag_name("BODY").text)
        self._back2start()

    def step06a_likheter(self):
        Select(self.driver.find_element_by_css_selector("form[name=\"famMatches\"] > select[name=\"workDB\"]")).select_by_visible_text("aatest_gedcom1")
        for i in range(60):
            try:
                if self.is_element_present(By.CSS_SELECTOR, "#db2famMatches > select[name=\"matchDB\"]"): break
            except: pass
            time.sleep(1)
        else: self.fail("time out")
        Select(self.driver.find_element_by_css_selector("#db2famMatches > select[name=\"matchDB\"]")).select_by_visible_text("aatest_gedcom2")
        self.driver.find_element_by_css_selector("#db2famMatches > p > input[type=\"submit\"]").click()

    def step07_sammanslagning(self):
        self.driver.get(self.base_url)
        for i in range(60):
            try:
                 if self.is_element_present(By.CSS_SELECTOR, "form[name=\"merge\"] > select[name=\"workDB\"]"): break
            except: pass
            time.sleep(1)
        else: self.fail("time out")
        Select(self.driver.find_element_by_css_selector("form[name=\"merge\"] > select[name=\"workDB\"]")).select_by_visible_text("aatest_gedcom1")
        for i in range(60):
            try:
                if self.is_element_present(By.CSS_SELECTOR, "#db2merge > select[name=\"matchDB\"]"): break
            except: pass
            time.sleep(1)
        else: self.fail("time out")
        Select(self.driver.find_element_by_css_selector("#db2merge > select[name=\"matchDB\"]")).select_by_visible_text("aatest_gedcom2")
        self.driver.find_element_by_css_selector("#db2merge > p > input[type=\"submit\"]").click()
        self.assertEqual(True, u"Indexing" in self.driver.find_element_by_tag_name("BODY").text)
        self._back2start()

    def step08_gedcom(self):
        Select(self.driver.find_element_by_xpath("(//select[@name='workDB'])[8]")).select_by_visible_text("aatest_gedcom1")
        self.driver.find_element_by_xpath("//input[@value='Ladda ner']").click()

    def step99_logout(self):
        self.driver.get(self.base_url)
        self.driver.find_element_by_link_text("Visa lagrad information").click()
        self._back2start()
        self.driver.find_element_by_link_text("Logga ut").click()
        self.assertEqual(True, "Logga in" in self.driver.find_element_by_tag_name("BODY").text)

    def _steps(self):
        for name in sorted(dir(self)):
            if name.startswith("step"):
                yield name, getattr(self, name)

    def stest(self):
        self.step00_login()
        self.step07_sammanslagning()

    def test_steps(self):
        for name, step in self._steps():
            print 'Doing', name
            try:
                step()
            except Exception as e:
                self.fail("{} failed ({}: {})".format(step, type(e), e))

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
