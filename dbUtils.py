# -*- coding: utf-8 -*-
# This Python file uses the following encoding: utf-8
"""
> db.persons.findOne()
{
	"_id" : "P_55",
	"death" : {
		"date" : "17640827",
		"source" : "*1 Klinte CI:2, Sidan 195",
        "quality": 1
		"place" : "Klinte (I)",
		"normPlaceUid" : "1675"
	},
	"name" : "Anna Margaretha* /Falkengren/",
	"sex" : "F",
	"grpNameLast" : "4419",
	"birth" : {
		"date" : "17050615",
		"source" : "*4 Fardhem AI:2, Sidan 37"
        "quality": 4
	},
	"grpNameGiven" : "704 6942",
	"type" : "person",
	"refId" : "gedcom_I1223"
}
> db.families.findOne()
{
	"_id" : "F_19",
	"marriage" : {
		"source" : "Visby Stifts Herdaminne"
        "quality": 10
	},
	"type" : "family",
	"refId" : "gedcom_F01"
}
> db.relations.findOne()
{
	"_id" : ObjectId("59bf643db75a5412169767d6"),
	"famId" : "F_47",
	"persId" : "P_123",
	"relTyp" : "husb"     #can be child, husb, wife
}
> db.originalData.findOne({'type': 'person'})
{
	"_id" : ObjectId("59d47b1fb75a541d97959d63"),
	"recordId" : "P_270",
	"type" : "person",
	"contributionId" : "A_31",
	"record" : <person-record>,
	"data" : [ ],
	"gedcom" : "0 @I1223@ INDI\n1 SEX F\n1 NAME Anna Margaretha* /Falkengren/\n1 BIRT\n2 DATE 15 JUN 1705\n2 SOUR Fardhem AI:2 (1751-1769) Bild 430 / sid 37\n1 DEAT\n2 DATE 27 AUG 1764\n2 PLAC Klinte (I)\n2 SOUR Klinte CI:2 (1758-1828) Bild 1020 / sid 195 (AID: v61999.b1020.s195, NAD: SE/ViLA/23050)\n1 FAMS @F01@\n1 CHAN\n2 DATE 29 AUG 2017\n3 TIME 11:59:00"
}

> db.originalData.findOne({'type': 'family'})
{
	"_id" : ObjectId("59d47b1fb75a541d97959d74"),
	"recordId" : "F_117",
	"type" : "family",
	"contributionId" : "A_31",
	"record" : <family-record>,
	"relation" : [
		<relation-record with "famId": "F_117">,
		<relation-record with "famId": "F_117">
	],
	"data" : [ ],
	"gedcom" : "0 @F01@ FAM\n1 MARR\n2 SOUR Visby Stifts Herdaminne\n1 HUSB @A_31-I5115@\n1 WIFE @A_31-I1223@\n1 CHIL @I78017@\n1 CHIL @I1705@\n1 CHIL @I78016@\n1 CHIL @I1798@\n1 CHIL @I2137@\n1 CHAN\n2 DATE 29 AUG 2017\n3 TIME 11:28:00"
}

> db.matches_admin_P.findOne()
{
	"_id" : ObjectId("59bec3ccb75a540d58761efa"),
	"status" : "rOK",
	"matchid" : "P_68",
	"workid" : "P_55",
	"score" : 10.606552124023438,
	"familysim" : 0,
	"nodesim" : 0.8333333333333334,
	"cosScore" : 0.6689936080056726,
	"svmscore" : 0.9999973174618935
}
> db.fam_matches_admin_P.findOne()
{
	"_id" : ObjectId("59bec3ccb75a540d58761f0c"),
	"status" : "FamEjOK",
	"matchRefId" : "gedcom_F01",
	"workRefId" : "gedcom_F04",
	"wife" : <match-record>,
	"matchid" : "F_23",
	"workid" : "F_22",
	"summary" : {
		"status" : "Manuell",
		"husb" : "Match",
		"children" : [ ],
		"wife" : "EjMatch"
	},
	"husb" : <match-record>,
	"children" : [
         {
			<match-record>,
			"sort" : "0",     !!KOLLA!!
		},
		{
			"status" : "",
			"sort" : "17340721",
			"pwork" : <person-record>,   #or pmatch
			"workid" : "P_64"
		},

 ]
}

"""
#rename to getFamily
def getFamilyFromChild(childId, familyDB, relationsDB):
    """
    Build a full family record (husb,wife,marriage,children)
    from a person record where person is child in the family
    """
    family = relationsDB.find_one({'relTyp': 'child', 'persId': childId})
    if family:
        return getFamilyFromId(family['famId'], familyDB, relationsDB)
    else:
        return None
    """
    if family:
        famRecord = familyDB.find_one({'_id': family['famId']})
        famRecord['children'] = []
        for member in relationsDB.find({'famId': family['famId']}):
            if member['relTyp'] == 'child': famRecord['children'].append(member['persId'])
            else: famRecord[member['relTyp']] = member['persId']
            #if 'husb' in member: famRecord['husb'] = member['husb']
            #elif 'wife' in member: famRecord['wife'] = member['wife']
            #elif 'child' in member: famRecord['children'].append(member['child'])
        return famRecord
    else: return None
    """

def getFamilyFromId(famId, familyDB, relationsDB):
    """
    Build a full family record (husb,wife,marriage,children)
    from a familyId
    """
    famRecord = familyDB.find_one({'_id': famId})
    if famRecord is None: return None
    famRecord['husb'] = None
    famRecord['wife'] = None
    famRecord['children'] = []
    for member in relationsDB.find({'famId': famRecord['_id']}):
        if member['relTyp'] == 'child': famRecord['children'].append(member['persId'])
        else: famRecord[member['relTyp']] = member['persId']
        #if 'husb' in member: famRecord['husb'] = member['husb']
        #elif 'wife' in member: famRecord['wife'] = member['wife']
        #elif 'child' in member: famRecord['children'].append(member['child'])
    return famRecord
