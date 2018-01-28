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
luceneVM.attachCurrentThread('LuceneDB', True)

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
        self.analyzer = StandardAnalyzer()
        self.indexDir = None
        self.searcher = None
        (user,db) = dbName.split('_', 1)
        directory = "./files/"+user+'/'+db+'/LuceneIndex'
        if not os.path.exists(directory):
            os.mkdir(directory)
        elif dropDB:
            shutil.rmtree(directory)
            os.mkdir(directory)
        self.indexDir = SimpleFSDirectory(Paths.get(directory))
        try:
            self.searcher = IndexSearcher(DirectoryReader.open(self.indexDir))
        except Exception, e: #if directory empty
            pass

    def index(self, personDB, familyDB, relationDB):
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
            doc.add(Field("text", matchtxt, TextField.TYPE_NOT_STORED))
            writer.addDocument(doc)

        #Family matchtext
        for f in familyDB.find():
            matchtxt = mt.matchtextFamily(f, familyDB, personDB, relationDB)
            doc = Document()
            doc.add(Field('uid',str(f['_id']), StringField.TYPE_STORED))
            doc.add(Field('sex','FAM', StringField.TYPE_STORED))
            doc.add(Field("text", matchtxt, TextField.TYPE_NOT_STORED))
            writer.addDocument(doc)

        writer.commit()
        writer.close()
        return

    def deleteRec(self, pid):
        config = IndexWriterConfig(self.analyzer)
        #config.setOpenMode(IndexWriterConfig.OpenMode.APPEND)
        writer = IndexWriter(self.indexDir, config)
        res = writer.deleteDocuments(Term('uid', pid))
        res = writer.commit()
        writer.close()
        self.searcher = IndexSearcher(DirectoryReader.open(self.indexDir))
        return

    def search(self, q, sex, ant=5, config = None):
        query = QueryParser("text", self.analyzer).parse(q.replace('/', '\/'))
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
        ['P41', 'M', 'jonas erik b1939 d1971']
    ]
    config = IndexWriterConfig(searchDB.analyzer)
    config.setOpenMode(IndexWriterConfig.OpenMode.CREATE)
    writer = IndexWriter(searchDB.indexDir, config)
    for d in docs:
        doc = Document()
        doc.add(Field('uid', d[0], StringField.TYPE_STORED))
        doc.add(Field('sex', d[1], StringField.TYPE_STORED))
        doc.add(Field("text", d[2], TextField.TYPE_NOT_STORED))
        writer.addDocument(doc)
    writer.commit()
    writer.close()

    #test deletion
    h = searchDB.search('b1939 jonas', 'M')
    print h
    print 'Deleting P39'
    searchDB.deleteRec('P39')
    h = searchDB.search('b1939 jonas', 'M')
    print h
