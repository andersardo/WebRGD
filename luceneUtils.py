#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import os

import lucene
#from java.io import File
from java.nio.file import Paths
from org.apache.lucene.analysis.core import WhitespaceAnalyzer
from org.apache.lucene.analysis.standard import StandardAnalyzer
from org.apache.lucene.analysis.miscellaneous import LimitTokenCountAnalyzer
from org.apache.lucene.document import Document, Field, TextField, StringField
from org.apache.lucene.index import \
  FieldInfo, IndexWriter, IndexWriterConfig, DirectoryReader
from org.apache.lucene.store import SimpleFSDirectory
from org.apache.lucene.queryparser.classic import QueryParser
from org.apache.lucene.search import IndexSearcher
from org.apache.lucene.util import Version

from matchtext import matchtext

#INIT
lucene.initVM() #default 2048?
#lucene.initVM(lucene.CLASSPATH, maxheap='4096m') # does not increase indexing speed
#analyzer = StandardAnalyzer(Version.LUCENE_CURRENT)
analyzer = StandardAnalyzer()
indexDir = None
searcher = None

def setupDir(dbName):
    global indexDir, searcher
    (user,db) = dbName.split('_', 1)
    #directory = "./searchDB/"+dbName
    directory = "./files/"+user+'/'+db+'/LuceneIndex'
    if not os.path.exists(directory):
        os.mkdir(directory)
    #indexDir = SimpleFSDirectory(File(directory))
    indexDir = SimpleFSDirectory(Paths.get(directory))
    try:
        searcher = IndexSearcher(DirectoryReader.open(indexDir))
    except Exception, e:
        pass

def index(personDB, familyDB, relationDB):
    #config = IndexWriterConfig(Version.LUCENE_CURRENT, analyzer)
    config = IndexWriterConfig(analyzer)
    config.setOpenMode(IndexWriterConfig.OpenMode.CREATE)
    writer = IndexWriter(indexDir, config)
#?#indexWriter.setRAMBufferSizeMB(50);  KOLLA

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

def search(q, sex, ant=5, config = None):
#    if not searcher:
#        print 'Setting up Lucene', config['matchDB']
#        #lucene.initVM()
#        lucene.attachCurrentThread()
#        setupDir(config['matchDB'])
    #query = QueryParser(Version.LUCENE_CURRENT, "text", analyzer).parse(q.replace('/', '\/'))
    query = QueryParser("text", analyzer).parse(q.replace('/', '\/'))
    #Hur lägga till sex?
    scoreDocs = searcher.search(query, ant).scoreDocs
    #print "%s total matching documents." % len(scoreDocs)
    ##hits = {} #NOT ORDERD
    hits = []
    for scoreDoc in scoreDocs:
        doc = searcher.doc(scoreDoc.doc)
        if sex == doc.get("sex"):
#?#        if doc.get("sex") in (sex, 'U', 'O'):
#Increases match-time with 20%
            #print 'uid:', doc.get("uid"), 'sex:', doc.get("sex"), 'score:', scoreDoc.score
            hits.append([doc.get("uid"), scoreDoc.score])
    #del searcher
    return hits
