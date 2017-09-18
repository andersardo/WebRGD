import shutil
from pymongo import MongoClient

config = None
statOK = frozenset(['Match', 'OK', 'rOK'])
statEjOK = frozenset(['EjMatch', 'EjOK', 'rEjOK'])
statManuell = frozenset(['Manuell', 'rManuell'])
statAll =  frozenset(['EjTestad']).union(statOK,statManuell,statEjOK)
statCol = {'EjTestad': 'white'}
for (s,c) in ((statOK,'green'), (statEjOK,'red'), (statManuell,'yellow')):
   for st in s: statCol[st]=c
#Which status transitions are allowed
#More transitions to Match? i updateFam...
statUpdates = [('Match','rOK'), ('Match','OK'), ('Match','rEjOK'), ('Match','EjOK'),
               ('rOK','OK'), ('rOK','EjOK'),
               ('OK', 'EjOK'),
               ('EjMatch','rEjOK'), ('EjMatch','EjOK'),('EjMatch','rOK'), ('EjMatch','OK'),
               ('rEjOK','EjOK'), ('rEjOK','OK'),
               ('EjOK', 'OK'),
               ('Manuell','Match'),('Manuell','EjMatch'),
               ('Manuell','rManuell'), ('Manuell','rOK'), ('Manuell','OK'),
               ('Manuell','rEjOK'), ('Manuell','EjOK'),
               ('rManuell','rOK'), ('rManuell','OK'), ('rManuell','rEjOK'), ('rManuell','EjOK'),
               ('EjTestad','Manuell'), ('EjTestad','rManuell'), ('EjTestad','rOK'),
               ('EjTestad','OK'), ('EjTestad','rEjOK'), ('EjTestad','EjOK')]
import pprint, inspect
pp = pprint.PrettyPrinter(indent=4)

admClient = MongoClient()
RGDadm = admClient['RGDadm']

"""
Indexes and which conditions used in queries
Collection   find/update conditions
------------------------------------
persons

famlilies    'children', 'husb', 'wife', 'husb'+'wife'
  index:     'children', 'husb', 'wife',

originalData 'recordId', 'data.contributionId', 'type',
	     'type'+'data.record._id',
  index:     'recordId', 'type'

matches      'workid'+'matchid', 'children', 'status'+'workid'+?,
	     'nodesim', 'status', 
  index:      'workid', 'matchid', 'children', 'status'

fam_matches  'workid'+'matchid', 'workid', 'matchid', 'children.workid'
	     'children.pwork._id'+'children.pmatch._id',
	     'pwork._id',
             'husb.pwork._id'+'husb.pmatch._id',
             'wife.pwork._id'+'wife.pmatch._id',
  index:     'workid', 'matchid', 'status', 'children.workid'

flags
"""

def init(workDBName, dropWorkDB=False, matchDBName = None, dropMatchDB=False,
         indexes=False):
#Indexes only used from standalone, long-running programs
#If called from UI.py it interferes with database deletion
    cmn = {}
    #cmn['RGDid'] = RGDadm.seq
    cmn['contributor'] = RGDadm.contributor
    cmn['contribution'] = RGDadm.contribution
    client = MongoClient()
    if dropWorkDB: 
       client.drop_database(workDBName)
       for mdb in client.database_names():
          for coll in client[mdb].collection_names():
             if coll.endswith(workDBName):
                print 'Dropping', mdb, coll
                client[mdb][coll].drop()
    db = client[workDBName]
    cmn['workDB'] = workDBName
    cmn['persons'] = db.persons
    cmn['families'] = db.families
    cmn['relations'] = db.relations
    cmn['originalData'] = db.originalData
    if indexes:
       cmn['relations'].ensure_index('relTyp')
       cmn['relations'].ensure_index('famId')
       cmn['relations'].ensure_index('persId')
       cmn['originalData'].ensure_index('recordId')
       cmn['originalData'].ensure_index('type')
    if matchDBName:
        cmn['matchDB'] = matchDBName
        cmn['matches'] = db['matches_' + matchDBName]
        cmn['fam_matches'] = db['fam_matches_' + matchDBName]
        cmn['flags'] = db['flags_' + matchDBName]
        matchClient = MongoClient()
        if dropMatchDB:
           matchClient.drop_database(matchDBName)
           for mdb in matchClient.database_names():
              for coll in matchClient[mdb].collection_names():
                 if coll.endswith(matchDBName):
                    print 'Dropping', mdb, coll
                    matchClient[mdb][coll].drop()
        matchDB = matchClient[matchDBName]
        cmn['match_persons'] = matchDB.persons
        cmn['match_families'] = matchDB.families
        cmn['match_relations'] = matchDB.relations
        cmn['match_originalData'] = matchDB.originalData
        if indexes:
           cmn['matches'].ensure_index('workid')
           cmn['matches'].ensure_index('matchid')
           cmn['matches'].ensure_index('status')
           cmn['fam_matches'].ensure_index('workid')
           cmn['fam_matches'].ensure_index('matchid')
           cmn['fam_matches'].ensure_index('status')
           cmn['match_relations'].ensure_index('relTyp')
           cmn['match_relations'].ensure_index('famId')
           cmn['match_relations'].ensure_index('persId')
           cmn['match_originalData'].ensure_index('recordId')
           cmn['match_originalData'].ensure_index('type')
    return cmn

def checkStatusUpdate(fromStat, toStat):
   if (fromStat,toStat) in statUpdates: return True
   else: return False

def get_id(what):
   r = RGDadm.seq.find_and_modify(query={'type': what},
                                  update={'$inc' : {'seqNo': 1} }, 
                                  safe=True, new=True, upsert=True)
   return what+'_'+str(r['seqNo'])

def deleteDB(dbname):
   admClient.drop_database(dbname)
   try:
      shutil.rmtree('searchDB/'+dbname)
   except: pass
   return dbname + ' deleted'

def rmMatchData(dbname):
   db = admClient[dbname]
   dbs = set()
   for coll in db.collection_names():
      if 'matches_' in coll:
         dbs.add(coll.replace('fam_matches_','').replace('matches_',''))
         db[coll].drop()
   if dbs:
      return 'Matchdata collections for databases ' + ', '.join(dbs) + ' removed from ' + dbname
   else:
      return 'No collections found'

def infoDB(dbname):
   db = admClient[dbname]
   dbs = set()
   antOK = {}
   antMan = {}
   res = '<h4>' + dbname + ':</h4>'
   res += 'has ' + str(db.persons.find().count()) + ' person records<br>'
   res += 'and ' + str(db.families.find().count()) + ' family records<br>'
   for coll in db.collection_names():
      if 'matches_' in coll:
         antOK[coll] = db[coll].find({'status': {'$in': list(statOK)}}).count()
         antMan[coll] = db[coll].find({'status': {'$in': list(statManuell)}}).count()
         dbs.add(coll.replace('fam_matches_','').replace('matches_',''))
   if dbs: res += 'is matched with database(s): ' + ', '.join(dbs) + '<br>'
   for mdb in dbs:
      try:
         res += 'in match with ' + mdb + ': person-matches OK=' + str(antOK['matches_'+mdb])
         res += ' Manuell=' + str(antMan['matches_'+mdb])
         res += ' and family-matches OK=' + str(antOK['fam_matches_'+mdb])
         res += ' Manuell=' + str(antMan['fam_matches_'+mdb])
         res += '<br>'
      except: pass
   #Merged with??
   return res
