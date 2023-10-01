<?php
    //GROUP INFO
    function getAllGroups($db) {
        $stmt = $db->prepare("SELECT id, groupName FROM groups");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function getGroupInfo($db, $id) {
        $stmt = $db->prepare("SELECT id, groupName FROM groups WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function checkIfGroupNameExist($db, $groupName) {
        $stmt = $db->prepare("SELECT groupName FROM groups WHERE groupName = :groupName");
        $stmt->bindParam(':groupName', $groupName);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


    //GROUP MANAGMENT
    function createGroup($db, $groupName) {
        $stmt = $db->prepare("INSERT INTO groups (groupName) VALUES (:groupName)");
        $stmt->bindParam(':groupName', $groupName);
        $stmt->execute();
    }

    function renameGroup($db, $groupName, $id) {
        $stmt = $db->prepare("UPDATE groups SET groupName = :groupName WHERE id = :id");
        $stmt->bindParam(':groupName', $groupName);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    function deleteGroup($db, $id) {
        $stmt = $db->prepare("DELETE FROM groups WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }


    //USERS IN GROUPS
    function getUsernamesInGroup($db, $groupId) {
        $stmt = $db->prepare("SELECT users.id, users.loginName FROM userInGroups LEFT JOIN users ON userInGroups.userId = users.id WHERE userInGroups.groupID = :groupId");
        $stmt->bindParam(':groupId', $groupId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function getGroupsByUser($db, $userId) {
        $stmt = $db->prepare("SELECT groups.id, groups.groupName FROM userInGroups LEFT JOIN groups ON userInGroups.groupId = groups.id WHERE userInGroups.userId = :userId");
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function removeAllUsersFromGroup($db, $groupId) {
        $stmt = $db->prepare("DELETE FROM userInGroups WHERE groupId = :groupId");
        $stmt->bindParam(':groupId', $groupId);
        $stmt->execute();
    }

    function addUserToGroup($db, $userId, $groupId) {
        $stmt = $db->prepare("INSERT INTO userInGroups (userId, groupId) VALUES (:userId, :groupId)");
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':groupId', $groupId);
        $stmt->execute();
    }
?>