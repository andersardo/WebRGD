#!/usr/bin/python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import sys, codecs, locale
locale.setlocale(locale.LC_ALL, 'en_US.UTF-8') #sorting??
sys.stdout = codecs.getwriter(locale.getpreferredencoding())(sys.stdout)
import argparse, time, sys, os
import subprocess

parser = argparse.ArgumentParser()
parser.add_argument("workDB", help="Working database name" )
parser.add_argument("workdir", help="Working directory" )

args = parser.parse_args()
workDB = args.workDB
workdir = args.workdir

dbName  = os.path.basename(workDB).split('.')[0]  #No '.' or '/' in databasenames

t0 = time.time()
print 'Utvidgad dubblettlista', dbName
os.chdir(workdir+'/'+dbName.split('_', 1)[1])
try:
    retcode = subprocess.call("php ../../../PHP/dubbtestx.php >> Log", shell=True)
    os.system('echo "dubbtstx retcode= '+str(retcode)+'\n" > UtvidgadDubblett.txt') 
except OSError as e:
    os.system('echo "dubbtstx OSError= '+str(e)+'\n" > UtvidgadDubblett.txt')
print 'Time:',time.time() - t0
print 'Resultat i <a href="/getFile?fil='+workdir+'/'+dbName.split('_', 1)[1]+u'/RGDXL.txt" target="_blank">RGDXL.txt</a>'
if os.path.isfile('./DgDub.txt'):
    print '<b><a href="/getFile?fil='+workdir+'/'+dbName.split('_', 1)[1]+u'/DgDub.txt" target="_blank">DgDub.txt</a> - Dubblett s&ouml;kning</b> Fil till DISGEN<br>'
