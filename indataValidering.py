#!/usr/bin/env python
# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
import argparse, sys, os, shutil, tempfile, subprocess, codecs, locale
import conf.config
locale.setlocale(locale.LC_ALL, 'en_US.UTF-8') 
sys.stdout = codecs.getwriter(locale.getpreferredencoding())(sys.stdout)

"""
useremail = {
  'anders'   : 'anders.ardo@gmail.com',
  'kalle'    : 'carl-johan.gustafsson@telia.com',
  'rolf'     : 'hjalmo114@hotmail.com',
  'christer' : 'christer@dis.se',
  'kjell'    : 'kjell.crone@home.se',
  'hans'     : 'hansnyman@telia.com',
  'annelie'  : 'petared@gmail.com',
  'annika'   : 'annika.nygren@telia.com',
  'arne'     : 'arne@sorlov.com',
  'bernth'   : 'bernth.lindfors@telia.com',
  'lars'     : 'ekblom5@telia.com',
  'runar'    : 'runar.hortlund@telia.com',
  'ulf'      : 'ulf.456@hotmail.com',
  'BSF'      : 'hjalmo114@hotmail.com'
}
"""

parser = argparse.ArgumentParser()
parser.add_argument("workdir", help="Working directory" )
parser.add_argument("fn", help="Gedcom filnamn" )
parser.add_argument("--email", help="User email (for result-lists)" )
parser.add_argument("--namn", help="Kontrollista namn", action='store_true' )
parser.add_argument("--ort", help="Testa orter", action='store_true' )
parser.add_argument("--dubl", help="Testa dubletter", action='store_true' )
args = parser.parse_args()
workdir = args.workdir
fn = args.fn
email = args.email
namn = args.namn
ort = args.ort
dubl = args.dubl

#In first approx email is username
#if email in useremail: email = useremail[email]
#else: email = None

# strip leading path from file name to avoid directory traversal attacks
#fn = os.path.basename(fileitem.filename)
##rootdir = tempfile.mkdtemp(dir = workdir)
rootdir = workdir + '/' + os.path.basename(fn).split('.')[0]
#remove old files from catalogue
for file in os.listdir(rootdir):
    if file != os.path.basename(fn):
        os.unlink(os.path.join(rootdir, file))
os.chdir(rootdir)
os.rename(os.path.basename(fn), '_'+os.path.basename(fn))
shutil.copy2('_'+os.path.basename(fn), 'RGD1.GED')
os.system('ln ../../../PHP/fsndata.txt')

print 'Resultat'
if not email: print 'Ingen email - endast resultat via denna sida'
print u'Resultatl&auml;nkar:</pre>'
print '<b><a href="/getFile?fil='+rootdir+'/Log" target="_blank">Log av indatavalidering</a></b><br>'
#print rootdir+'/Log<br>'

#Alltid:konvutf8x och xprsstrt.
os.system('php ../../../PHP/konvutf8z.php >> Log')
os.system('php ../../../PHP/xprsstrtz.php >> Log')
#skapa RGD9 och RGDN
os.system('php ../../../PHP/dubbprepz.php >> Log')
filLista = []
filLista.append('RGDN.txt')
#För RGDO listan:även listrgdox
if ort:
    os.system('php ../../../PHP/listrgdox.php >> Log')
    filLista.append('RGDO.txt')
#För RGDD listan. även dubbtest
if dubl:
    import subprocess
    try:
        retcode = subprocess.call("php ../../../PHP/dubbtest.php >> Log", shell=True)
        os.system('echo "dubbtst retcode= '+str(retcode)+'\n" >> Log') 
        filLista.append('RGDD.txt')
    except OSError as e:
        os.system('echo "dubbtst OSError= '+str(e)+'\n" >> Log')
#Temporarily disabled - takes long time
#    try:
#        retcode = subprocess.call("php ../../../PHP/dubbtestx.php >> Log", shell=True)
#        os.system('echo "dubbtstx retcode= '+str(retcode)+'\n" >> Log') 
#    except OSError as e:
#        os.system('echo "dubbtstx OSError= '+str(e)+'\n" >> Log')
if os.path.isfile('./Info.txt'):
    print '<b><a href="/getFile?fil='+rootdir+'/Info.txt" target="_blank">Info.txt</a> - </b>Informationslista med saknade relationskopplingar <br>'
if namn and os.path.isfile('./RGDN.txt'):
    print '<b><a href="/getFile?fil='+rootdir+u'/RGDN.txt" target="_blank">RGDN.txt</a> - Namnfel eller namn som saknas i namndatabasen, men finns med avvikande kön</b><br>'
if ort and os.path.isfile('./RGDO.txt'):
    print '<b><a href="/getFile?fil='+rootdir+u'/RGDO.txt" target="_blank">RGDO.txt</a> - Ortnamn / Platser som ej kunnat identifieras som församlingar i GEDCOM filen</b><br>'
if dubl and os.path.isfile('./RGDD.txt'):
    print '<b><a href="/getFile?fil='+rootdir+u'/RGDD.txt" target="_blank">RGDD.txt</a> - Dubblett sökning</b><br>'

os.system('echo "ALLT KLART\n" >> Log')
try:
#    larm = codecs.open('LARM_lista.txt', "r", "utf-8")
#    print '<b><a href="/getFile?fil='+rootdir+'/LARM_lista.txt" target="_blank">Larmlista</a></b><br>'
    larm = codecs.open('Check_lista.txt', "r", "utf-8")
    print '<b><a href="/getFile?fil='+rootdir+'/Check_lista.txt" target="_blank">Checklista</a></b><br>'
    print "<h1>Checklista</h1>\n"
    print '<pre>' + larm.read() + '</pre>'
    larm.close()
except:
    pass
os.system('mv RGD1.GED '+os.path.basename(fn)+'_UTF8')
if not email: sys.exit()
os.system('echo "Email= '+email+'\n" >> Log')
os.system('echo "Sending email\n" >> Log')

# Import smtplib for the actual sending function
import smtplib
from email.mime.text import MIMEText

# Import the email modules we'll need
me = 'RGD.NoReply@dis.se'
you = email

for textfile in (filLista):
# Open a plain text file for reading.  For this example, assume that
# the text file contains only ASCII characters.
    fp = open(textfile, 'rb')
# Create a text/plain message
    msg = MIMEText(fp.read(), 'plain', 'utf-8')
    fp.close()

# me == the sender's email address
# you == the recipient's email address
    msg['Subject'] = 'The contents of %s from %s' % (textfile, os.path.basename(fn))
    msg['From'] = me
    msg['To'] = you

# Send the message via our own SMTP server, but don't include the
# envelope header.
    s = smtplib.SMTP(conf.config.mailserver)
    s.sendmail(me, [you], msg.as_string())
    s.quit()

os.system('echo "Sent email\n" >> Log')
