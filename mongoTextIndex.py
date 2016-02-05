#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import common
from matchtext import matchtext

def index(person_list, fam_list):
    mt = matchtext()

    for p in person_list.find():
        matchtxt = mt.matchtextPerson(p, person_list, fam_list)
        #print 'person', p.oid, matchtxt.decode("utf-8")
        person_list.update({'_id': p['_id']}, {'$set': {'matchtext': matchtxt}})

    #Family matchtext
    for f in fam_list.find():
        matchtxt = mt.matchtextFamily(f, person_list)
        #print 'family', f.oid, matchtext.decode("utf-8")
        fam_list.update({'_id': f['_id']}, {'$set': {'matchtext': matchtxt}})
    return

def search(q, sex, ant=5, config = None):
    hits = []
    for hit in common.config['match_persons'].find({ 'sex': sex, '$text': { '$search': q } },
                                                   { score: { '$meta': "textScore" } }
                                     ).sort( { score: { '$meta': "textScore" } } ).limit(ant):
        hits.append([hit['_id'], hit['score']])
    return hits
