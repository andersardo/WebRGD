import sys, os
from bson.objectid import ObjectId

import codecs, locale
locale.setlocale(locale.LC_ALL, 'en_US.UTF-8') #sorting??
sys.stdout = codecs.getwriter('UTF-8')(sys.stdout)

mapPersId = {}
dummyFam = {
	"_id" : "F_dummy",
	"refId" : "             F?             ",
	"marriage" : {},
	"husb" : "P_dummy",
	"type" : "family",
	"children" : []
}
dummyPers = {
	"_id" : "P_dummy",
	"name" : "             ?             ",
	"sex" : "M",
	"type" : "person",
	"refId" : " ",
}

def eventDisp(e):
    txt = ''
#    for field in ('date', 'place', 'source'):
    for field in ('date', 'place'):
        if field in e: txt += e[field] + '<br/>'
    if txt == '': txt = '-'
    return txt

#IDE add match to lable as another table cell; collor from match

def printNode(indId, attrs, persons, gvFil):
    global mapPersId
    if not indId: return
    ind = persons.find_one({'_id': indId})
    if not ind: ind = dummyPers
    if ind['_id'] in mapPersId: return
    mapPersId[ind['_id']] = ind['_id']
    txt2 = ''
    for ev in ('birth', 'death'):
        if ev in ind:
            txt2 += '<TR><TD>'+ev+':</TD><TD><FONT POINT-SIZE="8.0">'+eventDisp(ind[ev])+'</FONT></TD></TR>'
    lab2 = '<FONT POINT-SIZE="8.0"><TABLE BORDER="0" CELLBORDER="0" CELLSPACING="0" CELLPADDING="0"><TR><TD COLSPAN="2">'+ ind['name']+'</TD></TR>'
    lab2 += '<TR><TD COLSPAN="2">' + ind['refId'] + '</TD></TR>'
    lab2 += txt2
    lab2 += '</TABLE></FONT>'
#FIX URLs in graph for matches (mid)???
    gvFil.write(ind['_id'] + '[URL="/graph?wid='+str(ind['_id'])+'" target=_blank, label=<'+lab2+'>, '+attrs+'];')
    gvFil.write("\n")
    return

def genGraph(centerPersonId, families, persons, directory, title):
    global mapPersId
    centerPersonId = ObjectId(centerPersonId)
    filnamn = directory+'/graph.gv'
    gvFil = open(filnamn, 'wb')
    gvFil = codecs.getwriter('UTF-8')(gvFil)

    gvFil.write('digraph G {charset=utf8; overlap=false; rankdir = LR; ratio = compress; ranksep = 0.25; nodesep = 0.03;fontname=Helvetica; fontsize=16; fontcolor=black; label="'+title+'"; labelloc=t;')
    gvFil.write("\n")
    mapFamc = {}
    mapPersId = {}
    persList = set()
    famList = []
    partnerList = []
    for f in families.find({'$or': [{'husb': centerPersonId},
                                    {'wife':  centerPersonId} ]}):
        famList.append(f)
    NoParents = True
    for f in families.find({'children': centerPersonId}):
        NoParents = False
        famList.append(f)
    if NoParents: 
        dummyFam['children'] = [centerPersonId]
        famList.append(dummyFam)
    for fam in famList:
        for partner in ('husb', 'wife'):
            if partner in fam:
                #persList.add(fam[partner])
                #partnerList.append(fam[partner])
                if fam[partner] == centerPersonId:
                    printNode(fam[partner], 'shape="tab", style=filled, fillcolor="aquamarine"', persons, gvFil)
                else:
                    printNode(fam[partner], 'shape="folder", style=filled, fillcolor="lightyellow"', persons, gvFil)
        prev = None
        for ch in fam['children']:
            mapFamc[ch] = fam['_id']
            if ch == centerPersonId:
                printNode(ch, 'shape="tab", style=filled, fillcolor="aquamarine"', persons, gvFil)
            else:
                printNode(ch, 'shape="box", style=filled, fillcolor="whitesmoke"', persons, gvFil)
            if prev:
                gvFil.write(mapPersId[prev]+' -> '+mapPersId[ch]+' [style=invis, label="", len=0.02];'+"\n")
            prev = ch
        gvFil.write('{rank=same; ')
        for ch in fam['children']:
            gvFil.write(mapPersId[ch] + '; ')
        gvFil.write("}\n")

    for fam in famList:
        txt = '<FONT POINT-SIZE="8.0">' + fam['refId'] + '<br/>'
        for ev in ('marriage',):
            if ev in fam:
                txt += ev+':'+eventDisp(fam[ev]).replace('<br>',', ')
        txt += '</FONT>'
#No URL for family-graphs
        gvFil.write(fam['_id'] + '[label=<'+txt+'>, style=filled, shape="note"];')
        gvFil.write("\n")
        for ch in fam['children']:
            gvFil.write(fam['_id'] + '->' + mapPersId[ch])
            gvFil.write("\n")
        if 'wife' in fam and fam['wife']:
            gvFil.write(mapPersId[fam['wife']] + '->' + fam['_id'])
            gvFil.write("\n")
        if 'husb' in fam and fam['husb']:
            gvFil.write(mapPersId[fam['husb']] + '->' + fam['_id'])
            gvFil.write("\n")
    gvFil.write( "}\n" )
    gvFil.close()
    os.system('dot -Tsvg -O '+filnamn)
    fil = open(filnamn+'.svg' , 'rb')
    graph = fil.read()
    fil.close()
    return graph

