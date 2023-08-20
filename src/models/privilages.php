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
?>