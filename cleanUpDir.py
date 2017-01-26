#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
"""
Cleaning directories for non-existing databases
"""
import os, shutil, time, datetime, sys

from pymongo import MongoClient
client = MongoClient()
dbs = client.database_names()
#print dbs
#print len(dbs)
#sys.exit()

def cleanUp(activeUser, dir, db):
    #only called for the user 'guest*'
    if not (activeUser.startswith('guest') or activeUser.startswith('GUEST')):
        database = activeUser + '_' + dbname
        print 'doing', activeUser, 'directory', dir, 'db', db, 'database', database
        #remove directory if no database
        if database not in dbs:
#        print 'ls -ld', dir
                print 'cleanUpDir removing', dir, 'no database', database
#ACTIVE!!
                shutil.rmtree(dir)

for filename in os.listdir('./files/'):
    #print filename, '  mtime',os.stat('./files/'+filename).st_mtime, time.time() 
    if (os.path.isdir(os.path.join('./files', filename))):
        if not filename.startswith('GUEST0'):
            for dbname in os.listdir(os.path.join('./files', filename)):
                if os.path.isdir(os.path.join('./files', filename, dbname)):
                    cleanUp(filename, os.path.join('./files', filename, dbname), dbname)
