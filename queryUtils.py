# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8

from luceneDB import luceneDB
from uiUtils import persDisp

def doQuery(q, config):
    searchDB = luceneDB(config['workDB'])
    res = [['Namn, Id', 'Född', 'Död', 'score', 'action']]
    for uid, score in searchDB.query(q):
        #print uid, score
        pers = config['persons'].find_one({'_id': uid})
        t = persDisp(pers)
        t[0] += '<br/>' + uid
        t.append("%3.0f" % (score))
        args = {'where': 'visa', 'what': '/view/relErr', 'typ': 'queryhits',
                'person': str(uid), 'family': ''}
        visa = '<button onclick="doAction('+str(args)+')">Visa</button>'
        t.append(visa)
        res.append(t)
    tit = u'Resultat sökning i %s efter "%s"' % (config['workDB'], q)
    #tit = config['workDB']
    return (tit, res)
