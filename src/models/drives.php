<?php
    function getAllDrives($db) {
        $stmt = $db->prepare("SELECT id, driveName, driveCredentials FROM drives");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


/**************************/

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


/**************************/

    function createNewDrive($db, $driveName, $driveCredentials) {
        $stmt = $db->prepare("INSERT INTO drives (driveName, driveCredentials) VALUES (:driveName, :driveCredentials)");
        $stmt->bindParam(':driveName', $driveName);
        $stmt->bindParam(':driveCredentials', $driveCredentials);
        $stmt->execute();
        return $db->lastInsertId();
    }

    function updateDrive($db, $driveId, $driveCredentials) {
        $stmt = $db->prepare("UPDATE drives SET driveCredentials = :driveCredentials WHERE id = :driveId");
        $stmt->bindParam(':driveId', $driveId);
        $stmt->bindParam(':driveCredentials', $driveCredentials);
        $stmt->execute();
    }

    function renameDrive($db, $driveId, $driveName) {
        $stmt = $db->prepare("UPDATE drives SET driveName = :driveName WHERE id = :driveId");
        $stmt->bindParam(':driveId', $driveId);
        $stmt->bindParam(':driveName', $driveName);
        $stmt->execute();
    }

    function deleteDrive($db, $driveId) {
        $stmt = $db->prepare("DELETE FROM drives WHERE id = :driveId");
        $stmt->bindParam(':driveId', $driveId);
        $stmt->execute();
    }


/**************************/

    function getDrivesWithAccess() {
        $drives = modelCall("drives", "getAllDrives", ['db' => getDatabaseEnvConn('sqlite')]);

        if ($_SESSION["privilegeLevel"] == 1) {
            for ($i = 0; $i < count($drives); $i++) {
                $drives[$i]["accessLevel"] = "edit";
            }
        } else if ($_SESSION["privilegeLevel"] == 2) {
            //get in which user is in
            $groups = modelCall("groups", "getGroupsByUser", ['db' => getDatabaseEnvConn('sqlite'), "userId" => $_SESSION["userId"]]);

            foreach ($drives as $i=>$drive) {
                $privilegeEdit = false;
                $privilegeView = false;
                $privilegeLimitToView = false;
                $privilegeDisabled = false;

                foreach ($groups as $group) {
                    $privilege = modelCall("privileges", "getPrivilegesForGroupInDrive", ['db' => getDatabaseEnvConn('sqlite'), "driveId" => $drive["id"], "groupId" => $group["id"]]);

                    if (!isset($privilege["0"]["privilegeLevel"])) {
                        continue;
                    }
                    $privilegeLevel = $privilege["0"]["privilegeLevel"];

                    if ($privilegeLevel == "-2") {
                        $privilegeDisabled = true;
                    } else if ($privilegeLevel == "-1") {
                        $privilegeLimitToView = true;
                    } else if ($privilegeLevel == "1") {
                        $privilegeView = true;
                    } else if ($privilegeLevel == "2") {
                        $privilegeEdit = true;
                    }
                }

                if ($privilegeDisabled) {
                    $drives[$i]["accessLevel"] = "none";
                } else if ($privilegeLimitToView && ($privilegeView || $privilegeEdit)) {
                    $drives[$i]["accessLevel"] = "view";
                } else if ($privilegeEdit) {
                    $drives[$i]["accessLevel"] = "edit";
                } else if ($privilegeView) {
                    $drives[$i]["accessLevel"] = "view";
                } else {
                    $drives[$i]["accessLevel"] = "none";
                }
            }
        }
        
        return $drives;
    }
?>