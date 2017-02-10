#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
"""
ABT =About, meaning the date is not exact.
CAL =Calculated mathematically, for example, from an event date and age.
EST =Estimated based on an algorithm using some other event date. 
INT = interpreted from knowledge about the associated date phrase included in parentheses
Date Range
AFT =Event happened after the given date.
BEF =Event happened before the given date.
BET =Event happened some time between date 1 AND date 2.
Date Period
FROM =Indicates the beginning of a happening or state.
TO =Indicates the ending of a happening or state.

use span to indicate how precise a date is

<DAY> = 1..31 | ??
<MONTH> = JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC
<YEAR> = 4 digits | digits \?
<DATEdmy> = <DAY> <MONTH> <YEAR>
<DATEmy> = <MONTH> <YEAR> | ?? <MONTH> <YEAR> 
<DATEy> =  <YEAR>
<DATE> = <DATEy> | <DATEmy> | <DATEdmy>
<caDATE> = ABT|EST|CAL|INT <DATE>
<rangeDATEca> = FROM|TO|AFT|BEF <DATE>
<rangeDATEtot> = FROM <DATE> TO <DATE | BET <DATE> AND <DATE>
<rangeDATE> = <rangeDATEca> |<rangeDATEtot>
"""
import sys
import re

monInt = {
    "JAN": '01',
    "FEB": '02',
    "MAR": '03',
    "APR": '04',
    "MAY": '05',
    "JUN": '06',
    "JUL": '07',
    "AUG": '08',
    "SEP": '09',
    "OCT": '10',
    "NOV": '11',
    "DEC": '12'
}

def date2span(date, endIntervall = False):
    datPat = re.compile(r"([\d\?]*)\s*([^\s]*)\s*([\d\?]{4})")
    spanFactor = {'ABT': 5, 'EST': 5, 'CAL': 2, 'INT': 3}
    m = re.match(r"(FROM|BET) (.+) (TO|AND) (.+)", date)
    if m:
        dat1 = m.group(2)
        dat2 = m.group(4)
        #print 'R1', dat1, 'R2', dat2
        (val1, span1) = date2span(dat1)
        (val2, span2) = date2span(dat2, True)
        return (None, (val2 - val1)+max(span1, span2))
    m = re.match(r"(ABT|EST|CAL|INT)\s+(.+)", date)
    if m:
        dat1 = m.group(2)
        #print 'R1', dat1
        (t, sp) = date2span(dat1)
        span = sp + spanFactor[m.group(1)]
        #if m.group(1) in ('ABT', 'EST'):
        #    span = sp + 5*sp
        #else:
        #    span = sp + 2*sp
        return (None, span)
    m = re.match(r"(FROM|TO|AFT|BEF)\s+(.+)", date)
    if m:
        dat1 = m.group(2)
        #print 'R0', m.group(1), 'R1', dat1
        (t, span) = date2span(dat1)
        return (None, (span + 10000) * 5)
    m = re.match(datPat, date)
    if m:
        day = m.group(1)
        mon = m.group(2)
        year = m.group(3)
        #print 'D=', day, 'M=', mon, 'Y=', year
        if year:
            if '??' in year: span = 1000000
            elif '?' in year: span = 100000
            else: span = 10000
            if endIntervall: year = year.replace('?','9')
            else: year = year.replace('?','0')
            #print 'year replaced', year
        if mon: span = 100
        else:
            if endIntervall: mon='DEC'
            else: mon='JAN'
        if day:
            span = 1
            if len(day)==1: day = '0'+day
            elif day == '??':
                if endIntervall: day = '99'
                else: day = '01'
                span = 100
        else:
            if endIntervall: day = '99'
            else: day = '01'
        try:
            return (int(year+monInt[mon]+day), span)
        except:
            return (0,1000000)
    return (0,1000000)

if __name__=="__main__":
    testData = [
        "26 JAN 2017",
        "AUG 1720",
        "?? AUG 1720",
        "1694",
        "AFT 26 JAN 2017",
        "AFT AUG 1720",
        "AFT 1694",
        "BEF 26 JAN 2017",
        "BEF AUG 1720",
        "BEF 1694",
        "EST 26 JAN 2017",
        "EST AUG 1720",
        "EST 1694",
        "CAL 26 JAN 2017",
        "CAL AUG 1720",
        "CAL 1694",
        "BET 26 JAN 1690 AND 2 FEB 1694",
        "BET 26 MAR 1690 AND SEP 1694",
        "BET MAR 1690 AND 23 SEP 1694",
        "BET 1690 AND SEP 1694",
        "BET MAR 1690 AND 1694",
        "BET 1690 AND 1694",
        "INT 24 FEB 1717 ()",
        "BET 17?? AND 1749",
        "EST 179?",
        "BET 11 APR 1694 AND 12 APR 1694",
        "INT 1700 (stax efter sekelskiftet)",
        "ABT 172?",
        "BET 166? AND 1680",
        "BET 166? AND 168?"
    ]
    for t in testData:
        #print t
        (tmp, span) = date2span(t)
        print span, t
    sys.exit()

    """
    #OLD code
    for t in testData.keys():
        c = date2completness(t)
        if c==testData[t]: res = 'OK:'
        else: res = 'ERR:'
        print res, t, 'completeness:',
        print 'target=', testData[t], 'Result=', c
        #print

    def date2completness(date):
        ##Ej komplett saknar INT; osäkra completeness värden
        datPat = re.compile(r"(\d*)\s*(.*)\s*(\d{4})")
        #pat = re.compile(r"(FROM|BET) (.+) (TO|AND) (.+)")
        #m = re.match(pat, date)
        m = re.match(r"(FROM|BET) (.+) (TO|AND) (.+)", date)
        if m:
            dat1 = m.group(2)
            dat2 = m.group(4)
            #print 'R1', dat1, 'R2', dat2
            c1 = date2completness(dat1)
            c2 = date2completness(dat2)
            return max(c1,c2) + 1
        m = re.match(r"(ABT|EST|CAL) (.+)", date)
        if m:
            dat1 = m.group(2)
            #print 'R1', dat1
            return date2completness(dat1) + 1
        m = re.match(r"(FROM|TO|AFT|BEF) (.+)", date)
        if m:
            dat1 = m.group(2)
            #print 'R1', dat1
            return  date2completness(dat1) + 2
        m = re.match(datPat, date)
        if m:
            day = m.group(1)
            mon = m.group(2)
            year = m.group(3)
            #print 'D=', day, 'M=', mon, 'Y=', year
            if day and mon and year: return 1
            elif mon and year: return 4
            elif year: return 7
            else: return 10
        return 99
    """
