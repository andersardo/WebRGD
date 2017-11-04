#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
"""
"""
import os
import common
#from pymongo import MongoClient

class openRGDmongo:
    """
    """
    def __init__(self, workDB, matchDB = '', gedcomPath = '', user = 'aatest'):
        self.gedcomPath = gedcomPath
        if matchDB:
            self.config = common.init(user+'_'+workDB, matchDBName = user+'_'+matchDB)
            self.dbI = workDB
            self.dbII = matchDB
        else:
            self.config = common.init(user+'_'+workDB)
            self.dbI = workDB
            self.dbII = None

    def runCommand(self, cmd):
        print 'OScmd:', cmd
        os.system(cmd)

    def importGedcom(self, name, user='aatest'):
        print 'import', name
        filesUserDir = './files/'+user
        filesDir = './files/'+user+'/'+name+'/'
        self.runCommand('rm -rf '+filesDir)
        self.runCommand('mkdir -p '+filesDir)
        if self.gedcomPath:
            self.runCommand('cp '+self.gedcomPath+' '+filesDir+name.replace('_', '')+'.ged')
        else:
            self.runCommand('cp '+name+'.ged '+filesDir)
        self.runCommand('python indataValidering.py '+filesUserDir+' '+name+'.ged')
        self.runCommand('python importGedcom.py '+user+' '+filesDir+name+'.ged_UTF8')

    def match(self, options = {}):
        args = ''
        for k in options.keys():
            args += k + ' ' + options[k]
        self.runCommand('python match.py '+ args + ' ' + self.dbI+' '+self.dbII)

    def setupDBs(self):
        self.importGedcom(self.dbI)
        if self.dbII:
            self.importGedcom(self.dbII)
            self.match()

    def loadDB(self, path, name):
        self.runCommand('mongorestore --drop --db '+name+' '+path)

