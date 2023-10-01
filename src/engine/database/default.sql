CREATE TABLE 'users' (
    'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    'loginName' TEXT NOT NULL,
    'password' TEXT NOT NULL,
    'privilegeLevel' INTEGER NOT NULL,
    'registered' DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    'lastLogin' DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE 'groups' (
    'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    'groupName' TEXT NOT NULL
);

CREATE TABLE 'userInGroups' (
    'userId' INTEGER NOT NULL,
    'groupId' INTEGER NOT NULL,
    PRIMARY KEY ('userId', 'groupId')
    FOREIGN KEY ('userId') REFERENCES 'users' ('id'),
    FOREIGN KEY ('groupId') REFERENCES 'groups' ('id')
);

CREATE TABLE 'drives' (
    'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    'driveName' TEXT, 'driveCredentials' TEXT NOT NULL
);

CREATE TABLE 'drivesPrivileges' (
    'groupId' INTEGER NOT NULL,
    'driveId' INTEGER NOT NULL,
    'privilegeLevel' INTEGER NOT NULL,
    PRIMARY KEY ('groupId', 'driveId'),
    FOREIGN KEY ('groupId') REFERENCES 'groups' ('id'),
    FOREIGN KEY ('driveId') REFERENCES 'drives' ('id')
);