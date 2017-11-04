#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
"""
"""
import os
import sys
import common
from pymongo import MongoClient
from matchtext import matchtext
from featureSet import personDefault, famExtended
from matchUtils import nodeSim, familySim, cos
from dbUtils import getFamilyFromId

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

    def updateFacit(self):
        """
        Removes all entries in Facit for matches with matchDB
        Add all OK person- and family-matches to Facit
        Updates self.OK
        Saves current status to ./files/Facit/
        """
        self.Facit[self.facitDB].remove({'matchDb': self.dbII})
        self.OK = {'person': {}, 'family': {}}
        for match in self.matches.find({'status': {'$in': self.statOK}},
                            {'pwork.refId': 1, 'pmatch.refId': 1}):
            self.Facit[self.facitDB].insert_one({'type': 'person', 'matchDb': self.dbII,
                                  'dbIgedcomID': match['pwork']['refId'],
                                  'dbIIgedcomID': match['pmatch']['refId']})
            self.OK['person'][match['pwork']['refId']+';'+match['pmatch']['refId']] = 1
        for match in self.fam_matches.find({'status': {'$in': self.statOK}},
                                      {'workRefId': 1, 'matchRefId': 1}):
            self.Facit[self.facitDB].insert_one({'type': 'family', 'matchDb': self.dbII,
                                  'dbIgedcomID': match['workRefId'],
                                  'dbIIgedcomID': match['matchRefId']})
            self.OK['family'][match['workRefId']+';'+match['matchRefId']] = 1
        print 'Facit: persons=', len(self.OK['person']), 'families=', len(self.OK['family'])
        self.runCommand('mkdir -p files/Facit/anders/')
        self.runCommand('cp -rf files/'+self.dbI.replace('_', '/')+' files/Facit/anders/')
        self.runCommand('mongodump --db='+self.dbI+' --out=files/Facit/'+self.dbI.replace('_', '/'))
        self.runCommand('cp -rf files/'+self.dbII.replace('_', '/')+' files/Facit/anders/')
        self.runCommand('mongodump --db='+self.dbII+' --out=files/Facit/'+self.dbII.replace('_', '/'))
        return

    def getFacit(self):
        """
        get golden truth from Facit to self.OK
        """
        for f in self.Facit[self.facitDB].find({'matchDb': self.dbII}):
            #self.OK[f['type']][f['dbIgedcomID']+';'+f['dbIIgedcomID']] = 1
            #Vallon
            self.OK[f['type']][f['dbIgedcomID'].replace('gedcom_', '')+';'+f['dbIIgedcomID'].replace('gedcom_', '')] = 1
        print 'Facit: persons=', len(self.OK['person']), 'families=', len(self.OK['family'])
        return

    def verify(self, doMatch=True, famFeature=''):
        #NEEDS reload of gedcom???
        print '  Doing', self.dbI, self.dbII
        #self.runCommand('python match.py --featureset ' + model + ' '+db1+' '+db2)
        #self.runCommand('python match.py --famfeatureset ' + model + ' '+db1+' '+db2)
        if doMatch:
            if famFeature:
                self.runCommand('python match.py --famfeatureset '+ famFeature + ' '+self.dbI+' '+self.dbII)
            else:
                self.runCommand('python match.py '+self.dbI+' '+self.dbII)

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
        try:
            recall = float(antOKinFacit)/len(self.OK['person'])
            precision =  float(antOKinFacit)/antOK
            fscore = 2.0 * precision * recall / (precision + recall)
            print '  Precision=', precision, 'Recall=', recall, 'F-score=', fscore
        except:
            pass
        return

    def SVMvect(self, features, label):
        selected = label
        n = 0
        for f in features:
            selected += ' ' + str(n+1) +':' + str(f)
            n += 1
        return selected

    def genTrainDataFacit(self):
        """
        Generate default feature vectors for pairs from dbI and dbII
        Facit determines which are OK, positive examples
        """
        for okPair in self.OK['person']:
            (pI, pII) = okPair.split(';')
            p1 = self.config['persons'].find_one({'refId': pI})
            rgdP = self.config['match_persons'].find_one({'refId': pII})
            nodeScore = nodeSim(p1, rgdP)
            pFam = self.config['families'].find_one({ 'children': p1['_id']})
            rgdFam = self.config['match_families'].find_one({ 'children': rgdP['_id']})
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
            #print self.SVMvect(feat, '+1')
            #trainData += self.SVMvect(feat, '+1') + "\n"
            #self.Facit['traindata'].insert_one( USE UPDATE M UPSERT
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
        ant = 0
        for match in self.matches.find({'status': {'$in': list(common.statOK.union(common.statManuell))}}):
            if match['pwork']['refId']+';'+match['pmatch']['refId'] not in self.OK['person']:
                pI = match['pwork']['refId']
                pII = match['pmatch']['refId']
                p1 = match['pwork']
                rgdP = match['pmatch']
                nodeScore = nodeSim(p1, rgdP)
                pFam = self.config['families'].find_one({ 'children': p1['_id']})
                rgdFam = self.config['match_families'].find_one({ 'children': rgdP['_id']})
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
                pFam = self.config['families'].find_one({ 'children': p1['_id']})
                rgdFam = self.config['match_families'].find_one({ 'children': rgdP['_id']})
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
                tot+=1
                self.Facit['traindata'].update(
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII},
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII,
                     'type': 'person', 'status': 'Random', 'SVMvector': self.SVMvect(feat, '-1')
                    }, upsert=True)
        return tot

    def genFamTrainDataFacit(self):
        """
        Generate default (famExtended) feature vectors for pairs from dbI and dbII
        Facit determines which are OK, positive examples
        """
        from collections import defaultdict
        import pickle
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
        for okPair in self.OK['family']:
            (pI, pII) = okPair.split(';')
            fix = False
            p1 = self.config['families'].find_one({'refId': pI})
            if not p1:
                fix = True
                orig = self.config['originalData'].find_one({'type': 'family', 'record.refId': pI}, {"recordId" : 1})
                p1 = self.config['families'].find_one({'_id': workMap[orig['recordId']][0]})
                if not p1:
                    print 'Not found', pI
                    continue
            fI = getFamilyFromId(p1['_id'], self.config['families'],
                                     self.config['relations'])
            rgdP = self.config['match_families'].find_one({'refId': pII})
            if not rgdP:
                fix = True
                orig = self.config['match_originalData'].find_one({'type': 'family', 'record.refId': pII}, {"recordId" : 1})
                rgdP = self.config['match_families'].find_one({'_id': matchMap[orig['recordId']][0]})
                if not rgdP:
                    print 'Not Found', pII
                    continue
            fII = getFamilyFromId(rgdP['_id'], self.config['match_families'],
                                     self.config['match_relations'])
            feat = famExtended(fI, fII, self.config)
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
        Generate default (famExtended) feature vectors for pairs from dbI and dbII
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
                if not feat: continue
                ant += 1
                self.Facit['famtraindata'].update(
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII},
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII,
                     'type': 'family', 'status': 'Miss', 'SVMvector': self.SVMvect(feat, '-1')
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
                if not feat: continue
                #print 'Feat OK'
                tot+=1
                self.Facit['famtraindata'].update(
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII},
                    {'dbI': self.dbI, 'dbII': self.dbII, 'dbIrefId': pI, 'dbIIrefId': pII,
                     'type': 'family', 'status': 'Random', 'SVMvector': self.SVMvect(feat, '-1')
                    }, upsert=True)
        return tot

    def getFamTraindata(self, query = {}):
        traindata = ''
        for ex in self.Facit['famtraindata'].find(query, {'SVMvector': True}):
            traindata += ex['SVMvector']+"\n"
        return traindata

    def getTraindata(self, query = {}):
        traindata = ''
        for ex in self.Facit['traindata'].find(query, {'SVMvector': True}):
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
