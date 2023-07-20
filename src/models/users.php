<?php
    function verifyLogin($db, $username, $password) {
        //hash password
        $password = hash("sha256", $password);

        $stmt = $db->prepare("SELECT id, loginName, privilageLevel FROM users WHERE loginName = :username AND password = :password");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
?>