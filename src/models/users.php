<?php
    //LOGIN
    function verifyLogin($db, $username, $password) {
        $stmt = $db->prepare("SELECT id, loginName, privilageLevel FROM users WHERE loginName = :username AND password = :password");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    function getUsersInfo($db, $id) {
        $stmt = $db->prepare("SELECT registered, lastLogin FROM users WHERE id = :id");
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
        $stmt = $db->prepare("SELECT id, loginName, privilageLevel, registered, lastLogin FROM users");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function createUser($db, $name, $password, $privilageLevel) {
        $stmt = $db->prepare("INSERT INTO users (loginName, password, privilageLevel, registered, lastLogin) VALUES (:name, :password, :privilageLevel, datetime('now'), '2000-00-00 00:00:00')");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':privilageLevel', $privilageLevel);
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


    //PRIVILAGE LEVEL
    function changePrivilageLevel($db, $id, $newPrivilageLevel) {
        $stmt = $db->prepare("UPDATE users SET privilageLevel = :newPrivilageLevel WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':newPrivilageLevel', $newPrivilageLevel);
        $stmt->execute();
    }
?>