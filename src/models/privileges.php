<?php
    function getPrivilegesForDrive($db, $driveId) {
        $stmt = $db->prepare("SELECT groupId, privilegeLevel FROM drivesPrivileges WHERE driveId = :driveId");
        $stmt->bindParam(':driveId', $driveId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function removeAllPrivilegesWithGroup($db, $groupId) {
        $stmt = $db->prepare("DELETE FROM drivesPrivileges WHERE groupId = :groupId");
        $stmt->bindParam(':groupId', $groupId);
        $stmt->execute();
    }

    function setPrivilege($db, $driveId, $groupId, $privilegeLevel) {
        $stmt = $db->prepare("INSERT OR IGNORE INTO drivesPrivileges (driveId, groupId, privilegeLevel) VALUES (:driveId, :groupId, :privilegeLevel) ON CONFLICT(driveId, groupId) DO UPDATE SET privilegeLevel = :privilegeLevel");
        $stmt->bindParam(':driveId', $driveId);
        $stmt->bindParam(':groupId', $groupId);
        $stmt->bindParam(':privilegeLevel', $privilegeLevel);
        $stmt->execute();
    }

    function removePrivilege($db, $driveId, $groupId) {
        $stmt = $db->prepare("DELETE FROM drivesPrivileges WHERE driveId = :driveId AND groupId = :groupId");
        $stmt->bindParam(':driveId', $driveId);
        $stmt->bindParam(':groupId', $groupId);
        $stmt->execute();
    }
?>