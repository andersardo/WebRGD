#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import os
import shutil

import lucene
#from java.io import File
from java.nio.file import Paths
from org.apache.lucene.analysis.core import WhitespaceAnalyzer
from org.apache.lucene.analysis.standard import StandardAnalyzer
from org.apache.lucene.analysis.miscellaneous import LimitTokenCountAnalyzer
from org.apache.lucene.document import Document, Field, TextField, StringField
from org.apache.lucene.index import \
  FieldInfo, IndexWriter, IndexWriterConfig, DirectoryReader, Term
from org.apache.lucene.store import SimpleFSDirectory
from org.apache.lucene.queryparser.classic import QueryParser
from org.apache.lucene.search import IndexSearcher
from org.apache.lucene.util import Version

from matchtext import matchtext
luceneVM = lucene.initVM() #default 2048?
#luceneVM.attachCurrentThread('LuceneDB', True)

class luceneDB:
    """
    Handles all details for the free-text database Lucene
    """
    def __init__(self, dbName, dropDB=False):
        #self.initObject = lucene.initVM() #default 2048? #vmargs=['-Djava.awt.headless=true']
        """
        attachCurrentThread(name, asDaemon)
        Before a thread created in Python or elsewhere but not in the Java VM
        can be used with the Java VM, this method needs to be invoked.
        The two arguments it takes are optional and self-explanatory.
        """
        #self.initObject.attachCurrentThread('LuceneDB', True)
        luceneVM.attachCurrentThread('LuceneDB')
        self.analyzer = StandardAnalyzer() #split on whitespace, no trunkation or stemming
        self.indexDir = None
        self.searcher = None
        (user,db) = dbName.split('_', 1)
        directory = "./files/"+user+'/'+db+'/LuceneIndex'
        if dropDB: shutil.rmtree(directory)
        self.indexDir = SimpleFSDirectory(Paths.get(directory)) #creates directory if not exists

    def personText(self, person):
        txt = []
        txt.append(person['name'])
        for field, value in person.iteritems():
            if field in ('birth', 'death'):
                for key, val in value.iteritems():
                    if key == 'place': txt.append(val)
                    elif key == 'date':
                        txt.append(val)
                        if len(val) > 4: txt.append(val[0:4])
            elif field in ('_id', 'refId'): txt.append(value)
        return ' '.join(txt).lower()

    def index(self, personDB, familyDB, relationDB):
        """
        indexes a database
        Field match includes information about parents and is used to find matches
        Field text has Ids, names, places, and dates and is used to find a person/family
        """
        config = IndexWriterConfig(self.analyzer)
        config.setOpenMode(IndexWriterConfig.OpenMode.CREATE)
        writer = IndexWriter(self.indexDir, config)
        #indexWriter.setRAMBufferSizeMB(256)  #?

        mt = matchtext()

        for p in personDB.find({}, no_cursor_timeout=True):
            matchtxt = mt.matchtextPerson(p, personDB, familyDB, relationDB)
            doc = Document()
            doc.add(Field('uid',str(p['_id']), StringField.TYPE_STORED))
            doc.add(Field('sex',str(p['sex']), StringField.TYPE_STORED))
            doc.add(Field("match", matchtxt, TextField.TYPE_NOT_STORED))
            doc.add(Field("text", mt.luceneFix(self.personText(p)), TextField.TYPE_NOT_STORED))
            writer.addDocument(doc)

        #Family matchtext
        for f in familyDB.find():
            #matchtxt = mt.matchtextFamily(f, familyDB, personDB, relationDB)
            doc = Document()
            doc.add(Field('uid',str(f['_id']), StringField.TYPE_STORED))
            #doc.add(Field('sex','FAM', StringField.TYPE_STORED))
            #doc.add(Field("match", matchtxt, TextField.TYPE_NOT_STORED))
            txt = f['_id']
            if 'refId' in f: txt += ' ' + f['refId']
            doc.add(Field("text", txt, TextField.TYPE_NOT_STORED))
            writer.addDocument(doc)

        writer.commit()
        writer.close()
        return

    def updateDeleteRec(self, pid1, pid2, personDB, familyDB, relationDB):
        config = IndexWriterConfig(self.analyzer)
        config.setOpenMode(IndexWriterConfig.OpenMode.APPEND)
        writer = IndexWriter(self.indexDir, config)
        mt = matchtext()
        writer.deleteDocuments(Term('uid', pid1))
        writer.deleteDocuments(Term('uid', pid2))
        p = personDB.find_one({'_id': pid1})
        matchtxt = mt.matchtextPerson(p, personDB, familyDB, relationDB)
        doc = Document()
        doc.add(Field('uid',str(pid1), StringField.TYPE_STORED))
        doc.add(Field('sex',str(p['sex']), StringField.TYPE_STORED))
        doc.add(Field("match", matchtxt, TextField.TYPE_NOT_STORED))
        doc.add(Field("text", mt.luceneFix(self.personText(p)), TextField.TYPE_NOT_STORED))
        writer.addDocument(doc)
        writer.commit()
        writer.close()
        self.searcher = IndexSearcher(DirectoryReader.open(self.indexDir))
        return

    def deleteRec(self, pid):
        config = IndexWriterConfig(self.analyzer)
        config.setOpenMode(IndexWriterConfig.OpenMode.APPEND)
        writer = IndexWriter(self.indexDir, config)
        writer.deleteDocuments(Term('uid', pid))
        writer.commit()
        writer.close()
        self.searcher = IndexSearcher(DirectoryReader.open(self.indexDir))
        return

    def query(self, txt, ant=10):
        """Searches for a person or family by id, name, place, or date"""
        q = QueryParser("text", self.analyzer).parse(txt.replace('/', '\/').lower())
        if not self.searcher:
            self.searcher = IndexSearcher(DirectoryReader.open(self.indexDir))
        scoreDocs = self.searcher.search(q, ant).scoreDocs
        hits = []
        for scoreDoc in scoreDocs:
            doc = self.searcher.doc(scoreDoc.doc)
            hits.append([doc.get("uid"), scoreDoc.score])
        return hits

    def search(self, q, sex, ant=5, config = None):
        """Searches for a match"""
        query = QueryParser("match", self.analyzer).parse(q.replace('/', '\/'))
        #Hur lägga till sex?
        if not self.searcher:
            self.searcher = IndexSearcher(DirectoryReader.open(self.indexDir))
        scoreDocs = self.searcher.search(query, ant).scoreDocs
        hits = []
        for scoreDoc in scoreDocs:
            doc = self.searcher.doc(scoreDoc.doc)
            if sex == doc.get("sex"):
                hits.append([doc.get("uid"), scoreDoc.score])
        return hits

if __name__=="__main__":
    searchDB = luceneDB('anders_testLucene', dropDB=True)
    #index some documents
    docs = [
        ['P36', 'M', 'hans anders b1936 d1966'],
        ['P37', 'F', 'maria beata b1937 d1967'],
        ['P38', 'U', 'oscar marina b1939 d1968'],
        ['P39', 'M', 'jonas peter b1939 d1969'],
        ['P40', 'F', 'katarina margareta b1940 d1970'],
        ['P41', 'M', 'Jonas erik b1939 d1971']
    ]
    #print searchDB.analyzer
    #print searchDB.indexDir
    config = IndexWriterConfig(searchDB.analyzer)
    config.setOpenMode(IndexWriterConfig.OpenMode.CREATE)
    writer = IndexWriter(searchDB.indexDir, config)
    for d in docs:
        doc = Document()
        doc.add(Field('uid', d[0], StringField.TYPE_STORED))
        doc.add(Field('sex', d[1], StringField.TYPE_STORED))
        doc.add(Field("match", d[2], TextField.TYPE_NOT_STORED))
        res = writer.addDocument(doc)
        #print res
    res = writer.commit()
    #print res
    writer.close()

    #test deletion
    h = searchDB.search('b1939 jonas', 'M')
    print h
    print 'Deleting P39'
    searchDB.deleteRec('P39')
    h = searchDB.search('jonas', 'M')
    print h
