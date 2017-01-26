#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
"""
Lucene supports escaping special characters that are part of the query syntax.
 The current list special characters are

+ - && || ! ( ) { } [ ] ^ " ~ * ? : \

To escape these character use the \ before the character.
str.translate does not work on Unicode objects
"""
class matchtext:
    """
    calculates the text representation used for initial selection of match-kandidates
    """
    def __init__(self):
        #caching does not improve speed of lucene indexing
        pass

    def luceneFix(self, text):
        for c in '+-&|!(){}[]^"~*?:':
            text = text.replace(c,'')
        return text.strip()

    def placeText(self, event):
        txt = ''
        if 'normPlaceUid' in event:
        #event['normPlaceUid'] can be either INT or STR
            try:
                txt = 'NPlace' + str(event['normPlaceUid'])
            except Exception, e:
                txt = 'NPlace' + event['normPlaceUid']
        elif 'place' in event:
            txt = 'Place' + event['place']
        return txt.replace(' ','')

    def eventText(self, event, prefix):
        txt = ''
        if 'date' in event:
            txt += prefix+'DateY' + event['date'][0:4]
            if len(event['date'])==8:
                txt += ' '+prefix+'Date' + event['date']
            if 'normPlaceUid' in event:
                txt += ' '+prefix+'DateY' + event['date'][0:4] + prefix + self.placeText(event)
        if self.placeText(event):
            txt += ' '+prefix + self.placeText(event)
        return txt.strip()

    ###########################
    def personText(self, pers):
        if not pers: return ''  #tmp FIX for inconsistensies in DB
        mtxt = ''
        namelist=[]
        if 'grpNameGiven' in pers:
            for nn in pers['grpNameGiven'].split(' '):
                mtxt += ' G' + nn
                namelist.append(nn)
        if 'grpNameLast' in pers:
            for nn in pers['grpNameLast'].split(' '):
                mtxt += ' L' + nn
                namelist.append(nn)
        if namelist: mtxt += ' ' + 'N'.join(sorted(namelist))

        if 'name' in pers:
            #mtxt += ' ' + pers['name'].replace('*','').replace('/',' ').replace('(',' ').replace('[','').replace(']','').replace(')',' ')
            mtxt += ' ' + pers['name']
        #    mtxt += ' sex' + p.sex()
        for ev in ('birth', 'death'):
            if ev in pers:
                if ev == 'birth': prefix = 'B'
                elif ev == 'death': prefix = 'D'
                mtxt += ' ' + self.eventText(pers[ev], prefix)

        return mtxt.strip()

    ###########################
    def familyText(self, fam):
        if 'marriage' in fam:
            mtxt = self.eventText(fam['marriage'], 'M')
        else:
            mtxt = ''
        return mtxt

    ###########################
    def matchtextPerson(self, p, pers_list, fam_list):
            matchtext = self.personText(p)
            #Add father and mother
            mtxt = set()
            fam = fam_list.find_one({ 'children': p['_id']}) #find fam if p in 'children'
            if fam:
                if fam['husb']:
                    for item in self.personText(pers_list.find_one({'_id': fam['husb']})).split():
                        mtxt.add('Father'+item)
                if fam['wife']:
                    for item in self.personText(pers_list.find_one({'_id': fam['wife']})).split():
                        mtxt.add('Mother'+item)
            matchtext += ' ' + ' '.join(mtxt)
            return self.luceneFix(matchtext)

    ###########################
    def matchtextFamily(self, fam, pers_list):
            matchtext = self.familyText(fam)
            mtxt = set()
            #Add HUSB o WIFE
            if fam['husb']:
                for item in self.personText(pers_list.find_one({'_id': fam['husb']})).split():
                    mtxt.add('HUSB'+item)
            if fam['wife']:
                for item in self.personText(pers_list.find_one({'_id': fam['wife']})).split():
                    mtxt.add('WIFE'+item)
            #Add children
            for ch in fam['children']:
                for item in self.personText(pers_list.find_one({'_id': ch})).split():
                    mtxt.add('CHILD' + item)
            matchtext += ' ' + ' '.join(mtxt)
            return self.luceneFix(matchtext)
