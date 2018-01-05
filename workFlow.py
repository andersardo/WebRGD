#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import os, glob, time
import shutil
from pymongo import MongoClient
client = MongoClient()

def doUpload(dir, fileitem):
    # Test if the file was uploaded
    if fileitem.filename:
       message = ''
       # strip leading path from file name to avoid directory traversal attacks
       fn = os.path.basename(fileitem.filename)
       rootdir = dir + '/'
       fdir = rootdir + fn.split('.')[0]
       if os.path.isdir(fdir): shutil.rmtree(fdir)
       try:
           os.mkdir(fdir)
       except Exception, e:
           message += str(e)
       open(fdir+'/'+fn, 'wb').write(fileitem.file.read())
       message += 'The file "' + fn + '" was uploaded successfully'
       return (message, fdir, fn)

def workFlowUI(user, workingDir):
    files = []
    if not workingDir:
        rootdir = "./files/" 
        if user == 'guest':
            #get temporary directory (do NOT use '_' in directorynames)
            workingDir = ''
            tempdir = rootdir + 'GUEST'
            for i in xrange(1000):
                workingDir = '%s%04d' % (tempdir, i)
                if not os.path.isdir(workingDir):
                    try:
                        os.mkdir(workingDir)
                        user = '%s%04d' % (user, i)
                        break
                    except OSError:
                        continue
        else:
            workingDir = rootdir + user
            if not os.path.isdir(workingDir):
                os.mkdir(workingDir)
    for file in glob.glob(workingDir + "/*.GED_*"):
        files.append(os.path.basename(file))
    for file in glob.glob(workingDir + "/*.ged_*"):
        files.append(os.path.basename(file))
    dbs = []
    #8 = len rootdir FIX!
    activeUser = workingDir[8:]
    for db in client.database_names():
        if db.startswith(activeUser+'_') or (user == 'admin'):
            dbs.append(db)
    return (files, dbs, workingDir, activeUser)

def getDBselect(what, db1, activeUser, workingDir):
    #if db.startswith(activeUser) or (user == 'admin'):  #Sanity check/security FIX
    if what == 'manualMatch':
        submitValue = 'do Manual Matching'
    elif what == 'sanity':
        submitValue = 'do Sanity Check'
    elif what == 'listSkillnad':
        submitValue = 'do Visa Skillnad'
    elif what == 'famMatches':
        submitValue = 'ladda ner matchade familjer'
    elif what == 'listDubl':
        submitValue = 'do Visa Duplicates'
    elif what == 'merge':
        submitValue = 'do Merge'
    else:
        submitValue = 'do ????'
    db = client[db1]
    selOptions = ''
    for coll in db.collection_names():
        #Manual Match => check that collection xxx_match finns
        if coll.startswith('matches_'):
            #listDubl uses match against itself - not valid for others
            if what != 'listDubl' and coll.endswith(db1): continue
            elif what == 'listDubl' and coll[8:] != db1: continue
            selOptions += '<option>'+coll[8:]+'</option>'  #Remove 'matches_' to get databasename
    if selOptions:
        noDefault = u'<option>Välj databas II</option>'
        return '<select name="matchDB">' + noDefault + selOptions + '</select><p><input type="submit" value="'+submitValue+'" />'
    else: return 'No valid choices'

def cleanUp(activeUser, dir):
    #only called for the user 'guest_****'
    if activeUser.startswith('guest') or activeUser.startswith('GUEST'):
        #remove directory and delete databases
        for db in client.database_names():
            if db.startswith(activeUser):
                #mongodump --db 'db' > dir/'db'  #FIX
                print 'cleanUp dropping', db
                client.drop_database(db)
        os.rename(dir, dir + '_' + str(time.time()))


def listOldLogs( user, database ):
    import zipfile
    mess = 'Logfiler ' + database + "<br>\n"
    (tmp, db) = database.split('_', 1)
    utf8 = db + '.ged_UTF8'
    utf8U = db + '.GED_UTF8'
    zipf = zipfile.ZipFile('./files/'+user+'/'+db+'/Logs.zip', 'w')
    for fil in ('Log', 'Info.txt', 'Note.txt', 'Check_lista.txt', 'RGDN.txt', 'RGDO.txt',
                'RGDD.txt', 'RGDK.CSV', 'RGDXL.txt', utf8, utf8U):
        filepath = './files/'+user+'/'+db+'/'+fil
        if os.path.isfile(filepath):
            mess += '<a href="/getFile?fil='+filepath+'">'+fil+'</a><br>'
            zipf.write(filepath)
    for (dirpath, dirnames, filenames) in os.walk('./files/'+user+'/'+db):
        for fil in filenames:
            if fil.endswith('.log') or fil.endswith('.err'):
                mess += '<a href="/getFile?fil='+os.path.join(dirpath, fil)+'">'+fil+'</a><br>'
                zipf.write(os.path.join(dirpath, fil))
        break
    zipf.close()
    mess += '<a href="/getFile?fil='+'./files/'+user+'/'+db+'/Logs.zip'+'">All logs as zip-archive</a><br>'
    mess += '<a href="/getFile?fil='+'./files/'+user+'/'+db+'/DgDub.txt'+'">Dubblettfil till DISGEN</a><br>'
    return mess
