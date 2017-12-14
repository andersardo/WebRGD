#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
"""
"""
import os
import sys
from collections import defaultdict
import pickle
import common
from pymongo import MongoClient
from matchtext import matchtext
from featureSet import personDefault, famExtended, famBaseline   #, baseline
from matchUtils import nodeSim, familySim, cos
from dbUtils import getFamilyFromId, getFamilyFromChild
from luceneUtils import search, setupDir, index

class Facit:
    """
    Handles golden truth
    Generate database Facit from matches, fam_matches for a fully
    completed matching
    database Facit
       dbI
          gedcom: original gedcom file + sour, name, date, plac .dat files
          dbII:   golden truth from matching
                  fields: type(family, person), dbIgedmID, dbIIgedcomID
          dbIIgedcom: original gedcom file + sour, name, date, plac .dat files

    algorithm Verify
    for dbI in Facit:
      for dbII in dbI:
        set up original gedcom data (with new name?)
        import
        extract golden truth from Facit
        run match
        analyze
        print results

    Results
    for person,family:
      #FacitOK found
      #FacitOK not found
      #FacitOK matched as manual
      precision, recall, F-score
      list of FacitOK not found
      list of matchedNotOK that are FacitOK
    """
    def __init__(self, config):
        client = MongoClient()
        self.Facit = client['Facit']
        self.dbI = config['workDB']
        self.dbII = config['matchDB']
        self.dbIpers = config['persons']
        self.dbIIpers = config['match_persons']
        self.facitDB = self.dbI+'Facit'
        #self.trainDB = self.dbI+'T'
        self.matches = config['matches']
        self.fam_matches = config['fam_matches']
        self.statOK = ['Match', 'OK', 'rOK']
        self.statManuell = ['Manuell', 'rManuell']
        self.OK = {'person': {}, 'family': {}}
        self.config = config
        self.mt_tmp = matchtext()
        self.workMap = {}
        self.matchMap = {}

    def runCommand(self, cmd):
        print 'OScmd:', cmd
        os.system(cmd)

    def importGedcom(self, name, gedcomPath=None, user='anders'):
        filesUserDir = 'files/'+user
        filesDir = 'files/'+user+'/'+name+'/'
        self.runCommand('rm -rf '+filesDir)
        self.runCommand('mkdir -p '+filesDir)
        if gedcomPath:
            self.runCommand('cp '+gedcomPath+' '+filesDir+name.replace('_', '')+'.ged')
        else:
            self.runCommand('cp '+name+'.ged '+filesDir)
        self.runCommand('python indataValidering.py '+filesUserDir+' '+name+'.ged')
        self.runCommand('python importGedcom.py '+user+' '+filesDir+name+'.ged_UTF8')

    def loadDB(self, path, name):
        self.runCommand('mongorestore --drop --db '+name+' '+path)

    def tSnabb(self):
        setupDir(self.dbII)  #lucene
        antOK=0
        tot=0
        foundOK = {}
        for fam in self.config['families'].find({}, no_cursor_timeout=True):
            tot+=1
            #print 'Fam', fam['refId'], fam['_id']
            matchtxt = self.mt_tmp.matchtextFamily(fam, self.config['families'],
                                                   self.config['persons'],
                                                   self.config['relations'])
            if not matchtxt:
                logging.error('No matchtextdata for %s, %s',fam['_id'],fam['refId'])
                continue       ##########FIX!!!!!!!!!!
            antS=2
            candidates = search(matchtxt, 'FAM', antS) #Lucene search
            for c in candidates:
                f = self.config['match_families'].find_one({'_id': c[0]})
                score = c[1]
                if fam['refId'] == 'F9577': print fam['refId'], score, f['refId']
                keys = [fam['refId']+';'+f['refId']]
                try: keys.append(self.workMap[fam['refId']]+';'+f['refId'])
                except: pass
                try: keys.append(fam['refId']+';'+self.matchMap[f['refId']])
                except: pass
                try: keys.append(self.workMap[fam['refId']]+';'+self.matchMap[f['refId']])
                except: pass
                found = False
                if fam['refId'] == 'F279': print c[0], keys
                for key in keys:
                    if key in self.OK['family']:
                        print score, key, 'OK'
                        foundOK[key] = score
                        antOK+=1
                        found = True
                        #??break
                if not found: print score, keys, 'NO'
        print 'Sum', antS, len(self.OK['family']), antOK, 'tot=', tot
        for k in self.OK['family'].keys():
            if k not in foundOK: print k

    def updateFacit(self):
        """
        Removes all entries in Facit for matches with matchDB
        Add all OK person- and family-matches to Facit
        Updates self.OK
        Saves current status to ./files/Facit/
        """
        self.Facit[self.facitDB].remove({'matchDb': self.dbII})
        for match in self.matches.find({'status': {'$in': self.statOK}},
                            {'pwork.refId': 1, 'pmatch.refId': 1}):
            self.Facit[self.facitDB].insert_one({'type': 'person', 'matchDb': self.dbII,
                                  'dbIgedcomID': match['pwork']['refId'],
                                  'dbIIgedcomID': match['pmatch']['refId']})
        for match in self.fam_matches.find({'status': {'$in': self.statOK}},
                                      {'workRefId': 1, 'matchRefId': 1}):
            self.Facit[self.facitDB].insert_one({'type': 'family', 'matchDb': self.dbII,
                                  'dbIgedcomID': match['workRefId'],
                                  'dbIIgedcomID': match['matchRefId']})
        self.getFacit()
        return

    def getFacit(self):
        """
        get golden truth from Facit to self.OK
        """
        self.OK = {'person': {}, 'family': {}}
        for f in self.Facit[self.facitDB].find({'matchDb': self.dbII}):
            #self.OK[f['type']][f['dbIgedcomID']+';'+f['dbIIgedcomID']] = 1
            self.OK[f['type']][f['dbIgedcomID'].replace('gedcom_', '')+';'+f['dbIIgedcomID'].replace('gedcom_', '')] = 1
        print 'Facit: persons=', len(self.OK['person']), 'families=', len(self.OK['family'])
        #Fix merged families!
        wMap = {}
        mMap = {}
        map = self.config['originalData'].find_one({'type': 'Fmap'})
        if map:
            for (k,v) in pickle.loads(map['data']).iteritems():
                if k != v[0]:
                    #get refId
                    f = self.config['originalData'].find_one({'type': 'family', 'recordId': k})
                    fMap = self.config['originalData'].find_one({'type': 'family', 'recordId': v[0]})
                    wMap[f['data'][0]['record']['refId']] = fMap['data'][0]['record']['refId']
                    #if len(f['data'])>1: print 'wf',len(f['data'])
                    #if len(fMap['data'])>1: print 'wfMap',len(fMap['data'])
                    #if (f['data'][0]['record']['refId'] == 'F15' or
                    #   f['data'][0]['record']['refId'] == 'F16' or
                    #   fMap['data'][0]['record']['refId'] == 'F15' or
                    #   fMap['data'][0]['record']['refId'] == 'F16'):
                    #    print f['data'][0]['record']['refId'], wMap[f['data'][0]['record']['refId']]
        map = self.config['match_originalData'].find_one({'type': 'Fmap'})
        if map:
            for (k,v) in pickle.loads(map['data']).iteritems():
                if k != v[0]:
                    #get refId
                    f = self.config['match_originalData'].find_one({'type': 'family', 'recordId': k})
                    fMap = self.config['match_originalData'].find_one({'type': 'family', 'recordId': v[0]})
                    mMap[f['data'][0]['record']['refId']] = fMap['data'][0]['record']['refId']
                    #if len(f['data'])>1: print 'mf',len(f['data'])
                    #if len(fMap['data'])>1: print 'mfMap',len(fMap['data'])
                    #if (f['data'][0]['record']['refId'] == '1-1383' or
                    #   f['data'][0]['record']['refId'] == '1-6078' or
                    #   fMap['data'][0]['record']['refId'] == '1-1383' or
                    #   fMap['data'][0]['record']['refId'] == '1-6078'):
                    #    print f['data'][0]['record']['refId'], mMap[f['data'][0]['record']['refId']]
        self.workMap = {v: k for k, v in wMap.iteritems()}
        self.matchMap = {v: k for k, v in mMap.iteritems()}
        #print self.matchMap['1-1383']
        return

    def verify(self, doMatch=True, persFeature= '', famFeature='', command='match'):
        #NEEDS reload of gedcom???
        print '  Doing', self.dbI, self.dbII
        if doMatch:
            featureOpt = ' '
            if persFeature:
                featureOpt += ' --featureset ' + persFeature
            if famFeature:
                featureOpt += ' --famfeatureset ' + famFeature
            if command == 'match':
                self.runCommand('python match.py '+ featureOpt + ' '+self.dbI+' '+self.dbII)
            elif  command == 'famMatch':
                #self.runCommand('python -m cProfile -o prof.cprof matchSnabb.py '+ featureOpt + ' '+self.dbI+' '+self.dbII)
                self.runCommand('python famMatch.py '+ featureOpt + ' '+self.dbI+' '+self.dbII)
        doneOK = []
        antOK=0
        antMan=0
        antOKinFacit=0
        antManinFacit=0
        for match in self.matches.find():
            if match['status'] in list(self.statOK):
                antOK += 1
                if match['pwork']['refId']+';'+match['pmatch']['refId'] in self.OK['person']:
                    antOKinFacit += 1
                    doneOK.append(match['pwork']['refId']+';'+match['pmatch']['refId'])
            elif match['status'] in list(self.statManuell):
                antMan += 1
                if match['pwork']['refId']+';'+match['pmatch']['refId'] in self.OK['person']:
                    antManinFacit += 1
                    doneOK.append(match['pwork']['refId']+';'+match['pmatch']['refId'])
        print '  Facit OK=', len(self.OK['person']), 'Done=', len(doneOK)
        print '  OK=', antOK, 'in Facit=', antOKinFacit
        print '  Man=', antMan, 'in Facit=', antManinFacit
        print '  felOK=', antOK-antOKinFacit, ' missedOK=', len(self.OK['person'])-antOKinFacit
        res = {'Facit': len(self.OK['person']), 'matchOK': antOK, 'matchOKinFacit': antOKinFacit,
               'matchMan': antMan, 'matchManinFacit': antManinFacit}
        try:
            recall = float(antOKinFacit)/len(self.OK['person'])
            precision =  float(antOKinFacit)/antOK
            fscore = 2.0 * precision * recall / (precision + recall)
            print '  Precision=', precision, 'Recall=', recall, 'F-score=', fscore
            res['Precision'] = precision
            res['Recall'] = recall
            res['F-score'] = fscore
        except:
            pass
        return res

    def SVMvect(self, features, label):
        selected = label
        n = 0
        for f in features:
            selected += ' ' + str(n+1) +':' + str(f)
            n += 1
        return selected

    def genRawDataFacit(self):
        """
        Generate default feature vectors and matchtext strings for pairs from dbI and dbII
        Facit determines which are OK, positive examples
        """
        setupDir(self.dbII)  #lucene
        rawData = {}
        for okPair in self.OK['person']:
            rawData[okPair] = {}
            (pI, pII) = okPair.split(';')
            p1 = self.config['persons'].find_one({'refId': pI})
            rgdP = self.config['match_persons'].find_one({'refId': pII})
            nodeScore = nodeSim(p1, rgdP)
            rawData[okPair]['nodeScore'] = nodeScore
            pFam = getFamilyFromChild(p1['_id'], self.config['families'], self.config['relations'])
            rgdFam = getFamilyFromChild(rgdP['_id'], self.config['match_families'], self.config['match_relations'])
            famScore = familySim(pFam, self.config['persons'],
                                 rgdFam, self.config['match_persons']) 
            rawData[okPair]['famScore'] = famScore
            cand_matchtxt = self.mt_tmp.matchtextPerson(rgdP, self.config['match_persons'],
                                                              self.config['match_families'],
                                                        self.config['match_relations'])
            rawData[okPair]['pIImatchtext'] = cand_matchtxt
            matchtxt = self.mt_tmp.matchtextPerson(p1, self.config['persons'],
                                                       self.config['families'],
                                                   self.config['relations'])
            rawData[okPair]['pImatchtext'] = matchtxt
            cosScore = cos(matchtxt, cand_matchtxt)
            rawData[okPair]['cosScore'] = cosScore
            feat = personDefault(p1, rgdP, self.config, None, nodeScore,
                           famScore, cosScore, matchtxtLen=len(matchtxt.split()))
            #feat = baseline(p1, rgdP, self.config, self.getLuceneScore(p1, rgdP, self.config))
            rawData[okPair]['feat'] = feat
        return rawData

    def getLuceneScore(self, tmp, rgd, conf):
        matchtxt = self.mt_tmp.matchtextPerson(tmp, conf['persons'],
                                               conf['families'], conf['relations'])
        #cand_matchtxt = self.mt_tmp.matchtextPerson(rgd, conf['match_persons'],
        #                                    conf['match_families'], conf['match_relations'])
        candidates = search(matchtxt, tmp['sex'], ant=30) #Lucene search
        score = 0.0
        n = 0
        for (kid,sc) in candidates:
            n+=1
            if str(kid) == str(rgd['_id']):
                score = sc
                break
        #print tmp['refId'], rgd['refId'], n, score
        if score == 0.0: return None
        return score

    def genTrainDataFacit(self):
        """
        Generate default feature vectors for pairs from dbI and dbII
        Facit determines which are OK, positive examples
        """
        setupDir(self.dbII)  #lucene
        for okPair in self.OK['person']:
            (pI, pII) = okPair.split(';')
            p1 = self.config['persons'].find_one({'refId': pI})
            rgdP = self.config['match_persons'].find_one({'refId': pII})

            nodeScore = nodeSim(p1, rgdP)
            pFam = getFamilyFromChild(p1['_id'], self.config['families'], self.config['relations'])
            rgdFam = getFamilyFromChild(rgdP['_id'], self.config['match_families'], self.config['match_relations'])
            famScore = familySim(pFam, self.config['persons'],
                                 rgdFam, self.config['match_persons']) 
            cand_matchtxt = self.mt_tmp.matchtextPerson(rgdP, self.config['match_persons'],
                                                              self.config['match_families'],
                                                        self.config['match_relations'])
            matchtxt = self.mt_tmp.matchtextPerson(p1, self.config['persons'],
                                                       self.config['families'],
                                                   self.config['relations'])
            cosScore = cos(matchtxt, cand_matchtxt)
            feat = personDefault(p1, rgdP, self.config, None, nodeScore,
                           famScore, cosScore, matchtxtLen=len(matchtxt.split()))

            #feat = baseline(p1, rgdP, self.config, self.getLuceneScore(p1, rgdP, self.config))
            self.Facit['traindata'].update(
                {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII},
                {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII,
                 'type': 'person', 'status': 'Facit', 'SVMvector': self.SVMvect(feat, '+1')
                }, upsert=True)
        return len(self.OK['person'])

    def genTrainDataMiss(self):
        """
        Generate default feature vectors for pairs from dbI and dbII
        Matches not found in Facit and Manual matches define these negative examples
        """
        setupDir(self.dbII)  #lucene
        ant = 0
        for match in self.matches.find({'status': {'$in': list(common.statOK.union(common.statManuell))}}):
            if match['pwork']['refId']+';'+match['pmatch']['refId'] not in self.OK['person']:
                pI = match['pwork']['refId']
                pII = match['pmatch']['refId']
                p1 = match['pwork']
                rgdP = match['pmatch']

                nodeScore = nodeSim(p1, rgdP)
                pFam = getFamilyFromChild(p1['_id'], self.config['families'], self.config['relations'])
                rgdFam = getFamilyFromChild(rgdP['_id'], self.config['match_families'], self.config['match_relations'])
                famScore = familySim(pFam, self.config['persons'], rgdFam,
                                           self.config['match_persons']) 
                cand_matchtxt = self.mt_tmp.matchtextPerson(rgdP, self.config['match_persons'],
                                                                  self.config['match_families'],
                                                            self.config['match_relations'])
                matchtxt = self.mt_tmp.matchtextPerson(p1, self.config['persons'],
                                                           self.config['families'],
                                                       self.config['relations'])
                cosScore = cos(matchtxt, cand_matchtxt)
                feat = personDefault(p1, rgdP, self.config, None, nodeScore,
                               famScore, cosScore, matchtxtLen=len(matchtxt.split()))

                #feat = baseline(p1, rgdP, self.config, self.getLuceneScore(p1, rgdP, self.config))
                ant += 1
                self.Facit['traindata'].update(
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII},
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII,
                     'type': 'person', 'status': 'Miss', 'SVMvector': self.SVMvect(feat, '-1')
                    }, upsert=True)
        return ant

    def genTrainDataRandom(self, ant):
        """
        Generate default feature vectors for pairs from dbI and dbII
        Select randomly from EjOK matches
        """
        #find out how many to generate
        #The following aggregation operation randomly selects 3 documents from the collection:
        #db.users.aggregate(   [ { $sample: { size: 3 } } ])
        tot=0
        """
        funkar inte minnes eller timeout-problem
        agr = [{ '$match': {'status': {'$in': list(common.statEjOK)}}},
               { '$sample': { 'size': ant } }]
        res = list(self.matches.aggregate(agr))
        for match in res:
        """
        databaseSize = self.matches.find({'status': {'$in': list(common.statEjOK)}}).count()
        skipRecords = int(databaseSize/ant)
        print 'DBstatEjOK=', databaseSize, 'skipRecs=', skipRecords
        i = 0
        for match in self.matches.find({'status': {'$in': list(common.statEjOK)}}, no_cursor_timeout=True):
            if match['pwork']['refId']+';'+match['pmatch']['refId'] not in self.OK['person']:
                if i < skipRecords:
                    i+=1
                    continue
                i = 0
                pI = match['pwork']['refId']
                pII = match['pmatch']['refId']
                p1 = match['pwork']
                rgdP = match['pmatch']

                nodeScore = nodeSim(p1, rgdP)
                pFam = getFamilyFromChild(p1['_id'], self.config['families'], self.config['relations'])
                rgdFam = getFamilyFromChild(rgdP['_id'], self.config['match_families'], self.config['match_relations'])
                famScore = familySim(pFam, self.config['persons'], rgdFam,
                                           self.config['match_persons']) 
                cand_matchtxt = self.mt_tmp.matchtextPerson(rgdP, self.config['match_persons'],
                                                                  self.config['match_families'],
                                                            self.config['match_relations'])
                matchtxt = self.mt_tmp.matchtextPerson(p1, self.config['persons'],
                                                           self.config['families'],
                                                       self.config['relations'])
                cosScore = cos(matchtxt, cand_matchtxt)
                feat = personDefault(p1, rgdP, self.config, None, nodeScore,
                               famScore, cosScore, matchtxtLen=len(matchtxt.split()))

                #feat = baseline(p1, rgdP, self.config, self.getLuceneScore(p1, rgdP, self.config))
                tot+=1
                self.Facit['traindata'].update(
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII},
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII,
                     'type': 'person', 'status': 'RandomEjOK', 'SVMvector': self.SVMvect(feat, '-1')
                    }, upsert=True)
        return tot

    def genTrainDataNotTested(self, ant=5000):
        """
        Generate default feature vectors for pairs from dbI and dbII
        Select randomly from EjOK matches

        find out how many to generate
        The following aggregation operation randomly selects 3 documents from the collection:
        db.users.aggregate(   [ { $sample: { size: 3 } } ])
        """
        databaseSize = self.dbIpers.find().count()
        traindataSize = self.Facit['traindata'].find({'dbI': self.dbI, 'dbII': self.dbII}).count()
        antToDo = len(self.OK['person']) * 2 - (traindataSize - len(self.OK['person']))
        if antToDo > ant: antToDo = ant
        elif antToDo <= 0: antToDo = 10
        tot=0
        for p1 in self.dbIpers.aggregate([{ '$sample': { 'size': antToDo*2} }]):
            for rgdP in self.dbIIpers.aggregate([{ '$sample': { 'size': 2} }]):
                pI = p1['refId']
                pII = rgdP['refId']

                if self.Facit['traindata'].find_one({'dbI': self.dbI, 'dbII': self.dbII,
                                                     'dbIrefId': pI, 'dbIIrefId': pII}):
                    continue
                nodeScore = nodeSim(p1, rgdP)
                pFam = getFamilyFromChild(p1['_id'], self.config['families'], self.config['relations'])
                rgdFam = getFamilyFromChild(rgdP['_id'], self.config['match_families'], self.config['match_relations'])
                famScore = familySim(pFam, self.config['persons'], rgdFam,
                                     self.config['match_persons']) 
                cand_matchtxt = self.mt_tmp.matchtextPerson(rgdP, self.config['match_persons'],
                                                            self.config['match_families'],
                                                            self.config['match_relations'])
                matchtxt = self.mt_tmp.matchtextPerson(p1, self.config['persons'],
                                                       self.config['families'],
                                                       self.config['relations'])
                cosScore = cos(matchtxt, cand_matchtxt)
                feat = personDefault(p1, rgdP, self.config, None, nodeScore,
                                     famScore, cosScore, matchtxtLen=len(matchtxt.split()))

                #feat = baseline(p1, rgdP, self.config, self.getLuceneScore(p1, rgdP, self.config))
                tot+=1
                self.Facit['traindata'].update(
                     {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII},
                     {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII,
                     'type': 'person', 'status': 'NotTested', 'SVMvector': self.SVMvect(feat, '-1')
                  }, upsert=True)
                if tot >= antToDo: return tot
                continue
        return tot

    def genFamTrainDataFacit(self):
        """
        Generate default (famExtended, famBaseline) feature vectors for pairs from dbI and dbII
        Facit determines which are OK, positive examples
        """
        workMap = defaultdict(list)
        matchMap = defaultdict(list)
        map = self.config['originalData'].find_one({'type': 'Fmap'})
        if map:
            workMap['_id'] = map['_id']
            for (k,v) in pickle.loads(map['data']).iteritems(): workMap[k] = v
        map = self.config['match_originalData'].find_one({'type': 'Fmap'})
        if map:
            matchMap['_id'] = map['_id']
            for (k,v) in pickle.loads(map['data']).iteritems(): matchMap[k] = v
        ant=0
        print 'workMap', len(workMap)
        print 'matchMap', len(matchMap)
        for okPair in self.OK['family']:
            (pI, pII) = okPair.split(';')
            fix = False
            p1 = self.config['families'].find_one({'refId': pI})
            if not p1:
                fix = True
                orig = self.config['originalData'].find_one({'type': 'family', 'data.record.refId': pI}, {"recordId" : 1})
                try:
                    p1 = self.config['families'].find_one({'_id': workMap[orig['recordId']][0]})
                except: p1 = None
                if not p1:
                    print 'Not found pI', pI
                    continue
            fI = getFamilyFromId(p1['_id'], self.config['families'],
                                     self.config['relations'])
            rgdP = self.config['match_families'].find_one({'refId': pII})
            if not rgdP:
                fix = True
                orig = self.config['match_originalData'].find_one({'type': 'family', 'data.record.refId': pII}, {"recordId" : 1})
                try:
                    rgdP = self.config['match_families'].find_one({'_id': matchMap[orig['recordId']][0]})
                except:
                    rgdP = None
                if not rgdP:
                    print 'Not Found pII', pII
                    continue
            fII = getFamilyFromId(rgdP['_id'], self.config['match_families'],
                                     self.config['match_relations'])
            feat = famExtended(fI, fII, self.config)
            #feat = famBaseline(fI, fII, self.config)
            if not feat: continue
            if fix:
                print 'Org', okPair, 'Inserting new', fI['refId'], fII['refId']
                self.Facit[self.facitDB].update({'type': 'family', 'matchDb': self.dbII,
                                    'dbIgedcomID': fI['refId'], 'dbIIgedcomID': fII['refId']},
                                    {'type': 'family', 'matchDb': self.dbII,
                                     'dbIgedcomID': fI['refId'], 'dbIIgedcomID': fII['refId']},
                                                upsert=True)
            ant+=1
            self.Facit['famtraindata'].update(
                {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII},
                {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII,
                 'type': 'familyExtended', 'status': 'Facit', 'SVMvector': self.SVMvect(feat, '+1')
                }, upsert=True)
        return ant

    def genFamTrainDataMiss(self):
        """
        Generate default (famExtended, famBaseline) feature vectors for pairs from dbI and dbII
        Matches not found in Facit and Manual matches define these negative examples
        """
        ant = 0
        for match in self.fam_matches.find({'status': {'$in': list(common.statOK.union(common.statManuell))}}):
            if match['workRefId']+';'+match['matchRefId'] not in self.OK['family']:
                pI = match['workRefId']
                pII = match['matchRefId']
                fI = getFamilyFromId(match['workid'], self.config['families'],
                                     self.config['relations'])
                fII = getFamilyFromId(match['matchid'], self.config['match_families'],
                                     self.config['match_relations'])
                feat = famExtended(fI, fII, self.config)
                #feat = famBaseline(fI, fII, self.config)
                if not feat: continue
                ant += 1
                self.Facit['famtraindata'].update(
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII},
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII,
                     'type': 'familyExtended', 'status': 'Miss', 'SVMvector': self.SVMvect(feat, '-1')
                    }, upsert=True)
        return ant

    def genFamTrainDataRandom(self, ant):
        """
        Generate default feature vectors for pairs from dbI and dbII
        Select randomly from EjOK matches
        """
        #find out how many to generate
        #The following aggregation operation randomly selects 3 documents from the collection:
        #db.users.aggregate(   [ { $sample: { size: 3 } } ])
        tot=0
        databaseSize = self.fam_matches.find({'status': {'$in': ['EjMatch', 'FamEjOK']}}).count()
        skipRecords = int(databaseSize/ant)
        print 'DBstatEjOK=', databaseSize, 'skipRecs=', skipRecords
        i = 0
        for match in self.fam_matches.find({'status': {'$in': ['EjMatch', 'FamEjOK']}}, no_cursor_timeout=True):
            #print 'Doing', match['workRefId'], match['matchRefId']
            if match['workRefId']+';'+match['matchRefId'] not in self.OK['family']:
                #print '1'
                if i < skipRecords:
                    #print 'skip', i
                    i+=1
                    continue
                #print '2'
                pI = match['workRefId']
                pII = match['matchRefId']
                fI = getFamilyFromId(match['workid'], self.config['families'],
                                     self.config['relations'])
                fII = getFamilyFromId(match['matchid'], self.config['match_families'],
                                     self.config['match_relations'])
                #print 'Fam', fI['refId'], fII['refId']
                feat = famExtended(fI, fII, self.config)
                #feat = famBaseline(fI, fII, self.config)
                if not feat: continue
                #print 'Feat OK'
                tot+=1
                self.Facit['famtraindata'].update(
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII},
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII,
                     'type': 'familyExtended', 'status': 'Random', 'SVMvector': self.SVMvect(feat, '-1')
                    }, upsert=True)
        return tot

    def genFamTrainDataNotTested(self, ant=2000):
        """
        Generate default feature vectors for pairs from dbI and dbII
        Select randomly from EjOK matches

        find out how many to generate
        The following aggregation operation randomly selects 3 documents from the collection:
        db.users.aggregate(   [ { $sample: { size: 3 } } ])
        """
        tot=0
        databaseSize = self.config['families'].find().count()
        traindataSize = self.Facit['famtraindata'].find({'dbI': self.dbI, 'dbII': self.dbII}).count()
        antToDo = len(self.OK['family']) * 2 - (traindataSize - len(self.OK['family']))
        if antToDo > ant: antToDo = ant
        elif antToDo <= 0: antToDo = 5
        print 'antToDo', antToDo
        tot=0
        for f1 in self.config['families'].aggregate([{ '$sample': { 'size': antToDo*2} }]):
            for f2 in self.config['match_families'].aggregate([{ '$sample': { 'size': 2} }]):
                pI = f1['refId']
                pII = f2['refId']
                if self.Facit['famtraindata'].find_one({'dbI': self.dbI, 'dbII': self.dbII,
                                                     'dbIrefId': pI, 'dbIIrefId': pII}):
                    continue
                fI = getFamilyFromId(f1['_id'], self.config['families'],
                                     self.config['relations'])
                fII = getFamilyFromId(f2['_id'], self.config['match_families'],
                                     self.config['match_relations'])
                feat = famExtended(fI, fII, self.config)
                #feat = famBaseline(fI, fII, self.config)
                if not feat: continue
                tot+=1
                self.Facit['famtraindata'].update(
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII},
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII,
                     'type': 'familyExtended', 'status': 'NotTested', 'SVMvector': self.SVMvect(feat, '-1')
                    }, upsert=True)
                if tot >= antToDo: return tot
                continue
        return tot

    def getFamTraindata(self, query = {}):
        traindata = ''
        for ex in self.Facit['famtraindata'].find(query, {'SVMvector': True}):
            traindata += ex['SVMvector']+"\n"
        return traindata

    def getTraindata(self, query = {}):
        traindata = ''
        for ex in self.Facit['traindata'].find(query, {'SVMvector': True}, no_cursor_timeout=True):
            traindata += ex['SVMvector']+"\n"
        return traindata

    def prMiss(self, match, reason):
        print reason
        print match['pwork']['refId'], match['pwork']['name'],
        try: print "(", match['pwork']['grpNameGiven'], ';', match['pwork']['grpNameLast'], ')',
        except: pass
        try: print 'B:', match['pwork']['birth']['date'],
        except: print '-',
        try: print match['pwork']['birth']['place'],
        except: pass
        try: print 'D:', match['pwork']['death']['date'],
        except: print '-',
        try: print match['pwork']['death']['place'],
        except: pass
        print
        print match['pmatch']['refId'], match['pmatch']['name'],
        try: print '(', match['pmatch']['grpNameGiven'], ';', match['pmatch']['grpNameLast'], ')',
        except: pass
        try: print 'B:', match['pmatch']['birth']['date'],
        except: print '-',
        try: print match['pmatch']['birth']['place'],
        except: pass
        try: print 'D:', match['pmatch']['death']['date'],
        except: print '-',
        try: print match['pmatch']['death']['place'],
        except: pass
        print
        for f in ('status', 'familysim', 'nodesim', 'cosScore', 'svmscore'):
            print f, match.get(f), ',',
        print
        print

    def prNotTested(self, text):
        (dbIid,dbIIid) = text.split(';')
        match = {}
        match['pwork'] = self.dbIpers.find_one({'refId': dbIid})
        match['pmatch'] = self.dbIIpers.find_one({'refId': dbIIid})
        self.prMiss(match, 'Not tested ' + text)
        #print

    def listMisses(self):
        doneOK = []
        for match in self.matches.find({'status': {'$in': self.statOK}}):
            if match['pwork']['refId']+';'+match['pmatch']['refId'] in self.OK['person']:
                doneOK.append(match['pwork']['refId']+';'+match['pmatch']['refId'])
            else:
                self.prMiss(match, 'OK but not in Facit')
        for key in self.OK['person'].keys():
            if key not in doneOK:
                (idI, idII) = key.split(';')
                match = self.matches.find_one({'pwork.refId': idI, 'pmatch.refId': idII})
                if match:
                    self.prMiss(match, 'notOK but in Facit')
                else:
                    self.prNotTested(key)

    def statMisses(self):
        from matchUtils import antFeaturesNorm
        doneOK = []
        OKantFeat = []
        MissantFeat = []
        NoTestantFeat = []
        for match in self.matches.find({'status': {'$in': self.statOK}}):
            if match['pwork']['refId']+';'+match['pmatch']['refId'] in self.OK['person']:
                doneOK.append(match['pwork']['refId']+';'+match['pmatch']['refId'])
                OKantFeat.append(antFeaturesNorm(match['pwork'], match['pmatch']))
            else:
                #self.prMiss(match, 'OK but not in Facit')
                MissantFeat.append(antFeaturesNorm(match['pwork'], match['pmatch']))
        for key in self.OK['person'].keys():
            if key not in doneOK:
                (idI, idII) = key.split(';')
                match = self.matches.find_one({'pwork.refId': idI, 'pmatch.refId': idII})
                if match:
                    #self.prMiss(match, 'notOK but in Facit')
                    MissantFeat.append(antFeaturesNorm(match['pwork'], match['pmatch']))
                else:
                    #self.prNotTested(key)
                    NoTestantFeat.append(antFeaturesNorm(self.dbIpers.find_one({'refId': idI}),
                                         self.dbIIpers.find_one({'refId': idII})))
        print 'OK', sum(OKantFeat)/len(OKantFeat)
        print 'Miss', sum(MissantFeat)/len(MissantFeat)
        print 'OK', sum(NoTestantFeat)/len(NoTestantFeat)

    def testMisses(self):
        from matchUtils import matchPers
        from luceneUtils import setupDir, search
        setupDir(self.dbII)
        from matchtext import matchtext
        mt_tmp = matchtext()
        doneOK = []
        for match in self.matches.find({'status': {'$in': self.statOK}}):
            if match['pwork']['refId']+';'+match['pmatch']['refId'] in self.OK['person']:
                doneOK.append(match['pwork']['refId']+';'+match['pmatch']['refId'])
        for key in self.OK['person'].keys():
            if key not in doneOK:
                (idI, idII) = key.split(';')
                match = self.matches.find_one({'pwork.refId': idI, 'pmatch.refId': idII})
                if not match:
                    print 'Testing', key
                    matchdata = matchPers(self.dbIpers.find_one({'refId': idI}),
                                          self.dbIIpers.find_one({'refId': idII}),
                                          self.config)
                    for f in ('status', 'familysim', 'nodesim', 'cosScore', 'svmscore'):
                        print f, matchdata.get(f),
                    print
                    person = self.dbIpers.find_one({'refId': idI})
                    matchperson = self.dbIIpers.find_one({'refId': idII})
                    matchtxt = mt_tmp.matchtextPerson(person, self.dbIpers,
                                                      self.config['families'], self.config['relations'])
                    candidates = search(matchtxt, person['sex'], 20) #Lucene search
                    pos = 0
                    for (kid,score) in candidates:
                        pos += 1
                        if kid == matchperson['_id']:
                            print 'Hit in lucene', pos, score, matchdata.get('status')
                            break
                    if pos >= 20 or pos == 0: print 'No lucene hit in first 20 hits'
                    print

    def setMatchStatusfromFacit(self):
        from collections import defaultdict
        import pickle
        antMatch=0
        antFamMatch=0
        antMatchOK=0
        antFamMatchOK=0
        workMap = defaultdict(list)
        matchMap = defaultdict(list)
        for okPair in self.OK['person']:
            (pI, pII) = okPair.split(';')
            match = self.matches.find_one({'pwork.refId': pI, 'pmatch.refId': pII})
            if match: antMatch+=1
            else:
                p = self.persons.find_one({'refId': pI})
                candidate = self.match_persons.find_one({'refId': pII})
                match = matchPers(p, candidate, self.config)
            match['status'] = 'Match'
            self.matches.update_one({'_id': match['_id']}, match, upsert=True)
        #Fam
        map = self.config['originalData'].find_one({'type': 'Fmap'})
        if map:
            workMap['_id'] = map['_id']
            for (k,v) in pickle.loads(map['data']).iteritems(): workMap[k] = v
        map = self.config['match_originalData'].find_one({'type': 'Fmap'})
        if map:
            matchMap['_id'] = map['_id']
            for (k,v) in pickle.loads(map['data']).iteritems(): matchMap[k] = v
        ant=0
        for okPair in self.OK['family']:
            (pI, pII) = okPair.split(';')
            #iterate all comb p* Map(p*) break when found
            match = self.fam_matches.find_one({'workRefId': pI, 'matchRefId': pII})
            if match: antFamMatch+=1
            else:
                pass
            #if match['status'] in self.statOK: antFamMatchOK+=1
        print 'PersMatch=', antMatch, 'FamMatch=', antFamMatch
        print 'OK PersMatch=', antMatchOK, 'FamMatch=', antFamMatchOK

if __name__=="__main__":
    dbName = sys.argv[1]
    mdbName = sys.argv[2]
    config = common.init(dbName, matchDBName=mdbName)
    facit = Facit(config)
    #facit.updateFacit()
    facit.getFacit()
    #facit.verify()
    #print facit.genTrainData()
    facit.listMisses()
