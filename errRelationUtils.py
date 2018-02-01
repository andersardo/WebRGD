# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import sys, os
from collections import defaultdict
import codecs, locale
from dbUtils import getFamilyFromId
from matchUtils import nodeSim
from mergeUtils import mergeEvent, mergeFam, mergePers
from graphUtils import eventDisp, printNode, mapPersId
from luceneDB import luceneDB
"""
#Family SVM default
from svmutil import svm_load_model, svm_predict
#famSVMfeatures = getattr(importlib.import_module('featureSet'), config['famfeatureSet'])
from featureSet import famExtended as famSVMfeatures
svmFamModel = svm_load_model('conf/famExtended.model')
"""
#limits used in comparisons
chSameLimit = 0.5
simLimit = 0.1

def sanity(personDB, familyDB, relationDB, do=['child', 'family', 'relation']):
    #SANITY CHECKS
    childErr = []
    famErr = []
    relErr = set()
    if 'child' in do:
        #can only be child in 1 family
        aggrPipe = [
            {'$match': {'relTyp': 'child'}},
            {'$project': {'persId': '$persId', 'count': {'$concat': ['1']}}},
            {'$group': {'_id': '$persId', 'count': {'$sum': 1}}},
            {'$match': {'count': {'$gt': 1}}}
        ]
        for multiChild in relationDB.aggregate(aggrPipe):
            pers = personDB.find_one({'_id': multiChild['_id']})
            chFams = []
            for fams in relationDB.find({'relTyp': 'child', 'persId': pers['_id']}):
                famId = fams['famId']
                chFams.append(famId)
            #print 'Relation ERROR child in many families', pers['_id'], pers['name'], chFams
            childErr.append((pers, chFams))
    if 'family' in do:
        #1 husb/wife per family
        for partner in ('husb', 'wife'):
            aggrPipe = [
                {'$match': {'relTyp': partner}},
                {'$project': {'famId': '$famId', 'count': {'$concat': ['1']}}},
                {'$group': {'_id': '$famId', 'count': {'$sum': 1}}},
                {'$match': {'count': {'$gt': 1}}}]
            for multiPartner in relationDB.aggregate(aggrPipe):
                pers = []
                for r in relationDB.find({'famId': multiPartner['_id'], 'relTyp': partner}):
                    p = personDB.find_one({'_id': r['persId']})
                    pers.append(p)
                #print 'Relation ERROR Family', multiPartner['_id'], 'have', multiPartner['count'], partner, pers[0]['_id'], pers[0]['name'], pers[1]['_id'], pers[1]['name']
                famErr.append((multiPartner['_id'], pers))
    if 'relation' in do:
        #Persons without relations
        """
        Slow
        for pers in personDB.find():
            rel = relationDB.find_one({'persId': pers['_id']})
            if not rel:
                #print 'Relation WARNING Person without relations:', pers['_id'], pers['name']
                relErr.append(pers)
        """
        pers = set(personDB.find({}, {'_id': 1}).distinct('_id'))
        persRel = set(relationDB.find({}, {'_id': 0, 'persId': 1}).distinct('persId'))
        #families
        fam = set(familyDB.find({}, {'_id': 1}).distinct('_id'))
        famRel = set(relationDB.find({}, {'_id': 0, 'famId': 1}).distinct('famId'))
        relErr = pers.difference(persRel).union(fam.difference(famRel))
    return (childErr, famErr, relErr)
"""
def mergeFam(fId1, fId2, personDB, familyDB, relationDB, origDB):
#    print '  Merging families', fId2, 'into', fId1
    origDB.update_one({'recordId': fId1, 'type': 'family'},
                      {'$push': {'map': fId2}})
    #Test fId1:husb/wife == fId2:husb/wife -- evt merge persons?
    for r in relationDB.find({'famId': fId2}):
        relationDB.delete_one(r)
        del(r['_id'])
        r['famId'] = fId1
        relationDB.replace_one(r, r, upsert=True)
    #merge marriage events
    marrEvents = []
    for fid in (fId1, fId2):
        marr = familyDB.find_one({'_id': fid}, {'_id': False, 'marriage': True})
        if marr: marrEvents.append(marr['marriage'])
    if marrEvents:
        #print 'marrEvents', marrEvents
        familyDB.update_one({'_id': fId1}, {'$set':
                                    {'marriage': mergeEvent(marrEvents)}})
    familyDB.delete_one({'_id': fId2}) #delete family fId2
    # FIX Need name of DB:
    searchDB = luceneDB(personDB.full_name.split('.')[0])
    searchDB.deleteRec(fId2)
    return

def mergePers(pId1, pId2, personDB, familyDB, relationDB, origDB):
    #print '  Merging persons', pId2, 'into', pId1
    origDB.update_one({'recordId': pId1, 'type': 'person'},
                      {'$push': {'map': pId2}})
    for r in relationDB.find({'persId': pId2}):
        relationDB.delete_one(r)
        del(r['_id'])
        r['persId'] = pId1
        relationDB.replace_one(r, r, upsert=True)
    #merge birth/death
    for ev in ('birth', 'death'):
        Events = []
        for pid in (pId1, pId2):
            event = personDB.find_one({'_id': pid}, {'_id': False, ev: True})
            if event: Events.append(event[ev])
        if Events:
            personDB.update_one({'_id': pId1}, {'$set':
                                         {ev: mergeEvent(Events)}})
    personDB.delete_one({'_id': pId2}) #delete person pId2
    # FIX Need name of DB:
    searchDB = luceneDB(personDB.full_name.split('.')[0])
    searchDB.deleteRec(pId2)
    #Evt check if pId1 barn i två familjer och inga problem => delete den familjen
    return
"""
def repairChild(childErr, personDB, familyDB, relationDB, origDB):
    notFixed = []
    for (pers, chFams) in childErr:
        merged = []
        for i in range(len(chFams)):
            if chFams[i] in merged: continue
            for j in range(i+1, len(chFams)):
                work = getFamilyFromId(chFams[i], familyDB, relationDB)
                match = getFamilyFromId(chFams[j], familyDB, relationDB)
                if not work or not match: continue
                minCh = float(min(len(work['children']), len(match['children'])))
                if minCh > 0.0:
                    sameChildren = set(work['children']).intersection(set(match['children']))
                    chSame = len(sameChildren)/minCh
                else:
                    chSame=1
                husbSame = (work['husb'] == match['husb'])
                wifeSame = (work['wife'] == match['wife'])
                husbSimilarity = nodeSim(personDB.find_one({'_id': work['husb']}),
                                         personDB.find_one({'_id': match['husb']}))
                wifeSimilarity = nodeSim(personDB.find_one({'_id': work['wife']}),
                                         personDB.find_one({'_id': match['wife']}))
                if husbSame: otherSimilarity = wifeSimilarity
                else:  otherSimilarity = husbSimilarity
                ##
                #p1 majority of children same
                #   1 partner same or Null
                #   other partner similar
                parentsCond = ( (husbSame or wifeSame) )
                if parentsCond and otherSimilarity>simLimit and chSame>=chSameLimit:
                    mergeFam( chFams[i], chFams[j], personDB, familyDB,
                              relationDB, origDB, updateLucene=True)
                    merged.append(chFams[j])
                    print 'p1 merged', chFams[i], chFams[j]
                    continue
                #
                #p1a majority of children same
                #   1 partner against Null
                #   other partner similar
                parentsCond = ( (((work['husb'] and not match['husb']) or
                                 (not work['husb'] and match['husb'])) and
                                 wifeSimilarity > simLimit )
                                or
                                (((work['wife'] and not match['wife']) or
                                 (not work['wife'] and match['wife'])) and
                                 husbSimilarity > simLimit )
                              )
                if parentsCond and chSame>=chSameLimit:
                    mergeFam( chFams[i], chFams[j], personDB, familyDB,
                              relationDB, origDB, updateLucene=True)
                    merged.append(chFams[j])
                    print 'p1a merged', chFams[i], chFams[j]
                    continue
                #
                #p2 majority of children same
                #   both partners against Null
                parentsCond = ( ((work['husb'] and not match['husb']) or
                                 (not work['husb'] and match['husb']))
                                and
                                ((work['wife'] and not match['wife']) or
                                 (not work['wife'] and match['wife']))
                              )
                if parentsCond and chSame>=chSameLimit:
                    mergeFam( chFams[i], chFams[j], personDB, familyDB,
                              relationDB, origDB, updateLucene=True)
                    merged.append(chFams[j])
                    print 'p2 merged', chFams[i], chFams[j]
                    continue
                #
                #p3 majority of children same
                #   both partners same
                #p4 children same or against Null
                #   1 partner same or Null
                #   other partner similar
                ##
                #p5: 1 family without parents
                if (((work['husb'] is None) and (work['wife'] is None)) or
                    ((match['husb'] is None) and (match['wife'] is None))):
                    mergeFam( chFams[i], chFams[j], personDB, familyDB,
                              relationDB, origDB, updateLucene=True)
                    merged.append(chFams[j])
                    print 'p5 merged', chFams[i], chFams[j]
                    continue
                """
                #p6: wife or husb same
                pat = False
                for partner in ('husb', 'wife'):
                    if work[partner]==match[partner] and work[partner] is not None:
                        #What if other partner not equal? MergePers? ??
                        pat = True
                        break
                if pat:
                    mergeFam( chFams[i], chFams[j], personDB, familyDB, relationDB, origDB)
                    merged.append(chFams[j])
                    print 'p6 merged', chFams[i], chFams[j]
                    continue
                #
                !!!!!!!!!!!  Uppdaterar matches, fam_matches
                config = {'persons': personDB, 'match_persons': personDB,
                          'families': familyDB, 'match_families': familyDB,
                          'relations': relationDB, 'match_relations': relationDB,
                          #dummies
                          'matches': personDB, 'fam_matches': personDB}
                v = famSVMfeatures(work, match, config) ##FIX import default famExtended
                p_labels, p_acc, p_vals = svm_predict([0],[v],svmFamModel,options="-b 1")
                svmstat = p_vals[0][0]
                #print '  SVM=', svmstat, ';', chFams[i], chFams[j]
                if svmstat > 0.66:
                     #print 'Pattern 3: (SVM) found', pers['_id'], pers['name'], chFams[i], chFams[j]
                     mergeFam(chFams[i], chFams[j], personDB, familyDB, relationDB, origDB)
                     merged.append(chFams[j])
                     continue
                """
                print 'Not repaired', pers['_id'], pers['name'], chFams[i], chFams[j]
                print '  ',
                for x in (minCh, chSame, husbSame, wifeSame, husbSimilarity, wifeSimilarity, otherSimilarity): print x,
                print
                notFixed.append((pers, chFams))
    return notFixed

def repairFam(famErr, personDB, familyDB, relationDB, origDB):
    #1 husb/wife per family
    notFixed = []
    for (famId, persList) in famErr:
        pStr = ''
        for p in persList:
            pStr += p['_id']+' '+p['name']+';'
        print pStr
        ##
        #p1 1 partner samma eller None
        #   other partner inga andra relations
        #p2 1 av dubl partner har inga andra rel
        p1Rel = relationDB.find({'persId': persList[0]['_id']}).count()
        p2Rel = relationDB.find({'persId': persList[1]['_id']}).count()
        #pSimilarity = nodeSim(personDB.find_one({'_id': persList[0]}),
        #                      personDB.find_one({'_id': persList[1]}))
        pSimilarity = nodeSim(persList[0], persList[1])
        if (p1Rel==1 or p2Rel==1) and pSimilarity>simLimit:
            mergePers(persList[0]['_id'], persList[1]['_id'], personDB, familyDB, relationDB, origDB)
            print 'p2 merged', pStr
            continue
        #p3 1 av dubl partner utan andra relationer och i en familj utan andra relationer
        ##
        print '  Not repaired', pStr, p1Rel, p2Rel, pSimilarity
        notFixed.append((famId, persList))
    return notFixed

def repairRel(relErr, personDB, familyDB, relationDB, origDB):
    #Persons/families without relations
    rErr = relErr.copy()
    for id in relErr:
        if id.startswith('F_'):
            #delete families without relations
            print 'Deleting family without relations', id
            familyDB.delete_one({'_id': id})
            rErr.remove(id)
    return rErr

def printFams(famList, centerPersonId, centerFamId, gvFil, personDB, familyDB, relationDB):
    global mapPersId
    mapPersId.clear()
    mapFamc = {}
    persList = set()
    for famId in famList:
        fam = getFamilyFromId(famId, familyDB, relationDB)
        for famrel in relationDB.find({'famId': famId}):
            if famrel['relTyp']=='child': continue
            if famrel['persId'] == centerPersonId:
                printNode(famrel['persId'], 'shape="tab", style=filled, fillcolor="aquamarine"', personDB, gvFil)
            else:
                printNode(famrel['persId'], 'shape="tab", style=filled, fillcolor="lightyellow"', personDB, gvFil)
        """
        for partner in ('husb', 'wife'):
            if partner in fam:
                if fam[partner] == centerPersonId:
                    printNode(fam[partner], 'shape="tab", style=filled, fillcolor="aquamarine"', personDB, gvFil)
                else:
                    printNode(fam[partner], 'shape="folder", style=filled, fillcolor="lightyellow"', personDB, gvFil)
        """
        prev = None
        for ch in fam['children']:
            mapFamc[ch] = fam['_id']
            if ch == centerPersonId:
                printNode(ch, 'shape="tab", style=filled, fillcolor="aquamarine"', personDB, gvFil)
            else:
                printNode(ch, 'shape="box", style=filled, fillcolor="whitesmoke"', personDB, gvFil)
            if prev:
                gvFil.write(mapPersId[prev]+' -> '+mapPersId[ch]+' [style=invis, label="", len=0.02];'+"\n")
            prev = ch
        gvFil.write('{rank=same; ')
        for ch in fam['children']:
            gvFil.write(mapPersId[ch] + '; ')
        gvFil.write("}\n")

    for famId in famList:
        fam = getFamilyFromId(famId, familyDB, relationDB)
        txt = '<FONT POINT-SIZE="8.0">' + fam['refId'] + '<br/>'
        if 'marriage' in fam:
                txt += 'Marriage:'+eventDisp(fam['marriage']).replace('<br>',', ')
        txt += '</FONT>'
        if famId == centerFamId:
            gvFil.write(fam['_id'] + '[label=<'+txt+'>, style=filled, fillcolor="aquamarine", shape="note"];')
            gvFil.write("\n")
        else:
            gvFil.write(fam['_id'] + '[label=<'+txt+'>, style=filled, shape="note"];')
            gvFil.write("\n")
        for ch in fam['children']:
            gvFil.write(fam['_id'] + '->' + mapPersId[ch])
            gvFil.write("\n")
        for famrel in relationDB.find({'famId': famId}):
            if famrel['relTyp']=='child': continue
            gvFil.write(mapPersId[famrel['persId']] + '->' + fam['_id'])
            gvFil.write("\n")
        """
        if 'wife' in fam and fam['wife']:
            gvFil.write(mapPersId[fam['wife']] + '->' + fam['_id'])
            gvFil.write("\n")
        if 'husb' in fam and fam['husb']:
            gvFil.write(mapPersId[fam['husb']] + '->' + fam['_id'])
            gvFil.write("\n")
        """

def genGraphFam(persId, famId1, famId2, personDB, familyDB, relationDB):
    #global mapPersId
    #mapPersId = {}
    filnamn = persId+'.gv'
    title = 'Error relations'
    gvFil = open(filnamn, 'wb')
    gvFil = codecs.getwriter('UTF-8')(gvFil)
    gvFil.write('digraph G {charset=utf8; overlap=false; rankdir = LR; ratio = compress; ranksep = 0.25; nodesep = 0.03;fontname=Helvetica; fontsize=16; fontcolor=black; label="'+title+'"; labelloc=t;')
    gvFil.write("\n")
    famList = set()
    for fam in (famId1, famId2):
        famList.add(fam)
        for partner in ('husb', 'wife'):
            if partner in fam:
                for r in relationDB.find({'persId': fam[partner]}):
                    famList.add(r['famId'])
    for r in relationDB.find({'persId': persId, 'relTyp': 'child'}): famList.add(r['famId'])
    printFams(famList, persId, '', gvFil, personDB, familyDB, relationDB)
    gvFil.write( "}\n" )
    gvFil.close()
    os.system('dot -Tsvg -O '+filnamn)
    #print '  Img', filnamn
    fil = open(filnamn+'.svg' , 'rb')
    graph = fil.read()
    fil.close()
    return graph

def genGraphPers(persId1, persId2, famId, personDB, familyDB, relationDB):
    filnamn = persId1+'.gv'
    title = 'Error relations'
    gvFil = open(filnamn, 'wb')
    gvFil = codecs.getwriter('UTF-8')(gvFil)
    gvFil.write('digraph G {charset=utf8; overlap=false; rankdir = LR; ratio = compress; ranksep = 0.25; nodesep = 0.03;fontname=Helvetica; fontsize=16; fontcolor=black; label="'+title+'"; labelloc=t;')
    gvFil.write("\n")
    famList = set()
    for persId in (persId1, persId2):
        for r in relationDB.find({'persId': persId}):
            famList.add(r['famId'])
    printFams(famList, '', famId, gvFil, personDB, familyDB, relationDB)
    gvFil.write( "}\n" )
    gvFil.close()
    os.system('dot -Tsvg -O '+filnamn)
    #print '  Img', filnamn
    fil = open(filnamn+'.svg' , 'rb')
    graph = fil.read()
    fil.close()
    return graph

if __name__=="__main__":
    import codecs, locale
    locale.setlocale(locale.LC_ALL, 'en_US.UTF-8') #sorting??
    sys.stdout = codecs.getwriter('UTF-8')(sys.stdout)
    import common
    DB = 'anders_DavidEkedahlMaster'
    conf = common.init(DB, matchDBName = DB, indexes=True)
    personDB = conf['persons']
    familyDB = conf['families']
    relationDB = conf['relations']
    origDB = conf['originalData']
    (childErr, famErr, relErr) = sanity(personDB, familyDB, relationDB)
    print 'Only child in one family', len(childErr)
    notFixed = repairChild(childErr, personDB, familyDB, relationDB, origDB)
    print 'Not fixed', len(notFixed)

    print 'Multi husb/wife in one family'
    repairFam(famErr, personDB, familyDB, relationDB, origDB)

    print 'Rel err'
    repairRel(relErr, personDB, familyDB, relationDB, origDB)
