<?php
    function getAllDrives($db) {
        $stmt = $db->prepare("SELECT id, driveName, driveCredentials FROM drives");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function createNewDrive($db, $driveName, $driveCredentials) {
        $stmt = $db->prepare("INSERT INTO drives (driveName, driveCredentials) VALUES (:driveName, :driveCredentials)");
        $stmt->bindParam(':driveName', $driveName);
        $stmt->bindParam(':driveCredentials', $driveCredentials);
        $stmt->execute();
        return $db->lastInsertId();
    }

    function checkIfNameNotUsed($db, $driveName) {
        $stmt = $db->prepare("SELECT driveName FROM drives WHERE driveName = :driveName");
        $stmt->bindParam(':driveName', $driveName);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function checkIfDriveExist($db, $driveId) {
        $stmt = $db->prepare("SELECT id FROM drives WHERE id = :driveId");
        $stmt->bindParam(':driveId', $driveId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
?>