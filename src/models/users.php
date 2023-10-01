<?php
    //LOGIN
    function verifyLogin($db, $username, $password) {
        $stmt = $db->prepare("SELECT id, loginName, privilegeLevel, password FROM users WHERE loginName = :username AND password = :password");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            return false;
        }

        //cut password for first 10 characters
        $result["password"] = substr($result["password"], 0, 10);
        return $result;
    }

    function verifyNothingChanged($db, $username, $privilegeLevel, $passwordCheck) {
        $stmt = $db->prepare("SELECT password FROM users WHERE loginName = :username AND privilegeLevel = :privilegeLevel");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':privilegeLevel', $privilegeLevel);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            return false;
        }

        //cut password for first 10 characters
        $result["password"] = substr($result["password"], 0, 10);
        if ($result["password"] == $passwordCheck) {
            return true;
        } else {
            return false;
        }
    }

    function getUsersInfo($db, $id) {
        $stmt = $db->prepare("SELECT privilegeLevel, registered, lastLogin FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    function logLastLogin($db, $id) {
        $stmt = $db->prepare("UPDATE users SET lastLogin = datetime('now') WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }


    //USERS LISTING
    function getAllUsers($db) {
        $stmt = $db->prepare("SELECT id, loginName, privilegeLevel, registered, lastLogin FROM users");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function getUsersWithPrivilege($db, $privilegeLevel) {
        $stmt = $db->prepare("SELECT id, loginName FROM users WHERE privilegeLevel = :privilegeLevel");
        $stmt->bindParam(':privilegeLevel', $privilegeLevel);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function createUser($db, $name, $password, $privilegeLevel) {
        $stmt = $db->prepare("INSERT INTO users (loginName, password, privilegeLevel, registered, lastLogin) VALUES (:name, :password, :privilegeLevel, datetime('now'), '2000-00-00 00:00:00')");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':privilegeLevel', $privilegeLevel);
        $stmt->execute();
    }


    //USERNAMES
    function checkIfUsernameExist($db, $username) {
        $stmt = $db->prepare("SELECT id FROM users WHERE loginName = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    function changeUsername($db, $id, $newName) {
        $stmt = $db->prepare("UPDATE users SET loginName = :newName WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':newName', $newName);
        $stmt->execute();
    }


    //PASSWORD
    function checkIfPasswordMatch($db, $id, $password) {
        $stmt = $db->prepare("SELECT id FROM users WHERE id = :id AND password = :password");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    function changePassword($db, $id, $newPassword) {
        $stmt = $db->prepare("UPDATE users SET password = :newPassword WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':newPassword', $newPassword);
        $stmt->execute();
    }


    //PRIVILEGE LEVEL
    function changePrivilegeLevel($db, $id, $newPrivilegeLevel) {
        $stmt = $db->prepare("UPDATE users SET privilegeLevel = :newPrivilegeLevel WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':newPrivilegeLevel', $newPrivilegeLevel);
        $stmt->execute();
    }
?>