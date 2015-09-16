db.ipinfodb.ensureIndex({ip:1},{unique: true});
db.ipinfodb.ensureIndex({ip:1,created:1});
db.ipinfodb.ensureIndex({city:1,region:1,country:1});