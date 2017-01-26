#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
"""
Cleaning old GUEST-accounts
ACTIVE
"""
import os, shutil, time, datetime

from pymongo import MongoClient
client = MongoClient()

def cleanUp(activeUser, dir):
    #only called for the user 'guest*'
    if activeUser.startswith('guest') or activeUser.startswith('GUEST'):
        print 'doing', activeUser, 'directory', dir
        print 'ls -ld', dir
        #remove directory and delete databases
        for db in client.database_names():
            if db.startswith(activeUser):
                print 'cleanUp dropping', db
#ACTIVE!!
                client.drop_database(db)
        shutil.rmtree(dir)

for filename in os.listdir('./files/'):
    #print filename, '  mtime',os.stat('./files/'+filename).st_mtime, time.time() 
    if (os.path.isdir(os.path.join('./files', filename)) and 
        os.stat('./files/'+filename).st_mtime < time.time() - 5 * 86400):
        if filename.startswith('GUEST0'):
            tmp = filename.split('_')
            cleanUp(tmp[0], os.path.join('./files', filename))
