<?php
    checkRoute('GET', '/' , function() {
        if (getAppEnvVar("REDIRECT_IF_NOT_LOGGED") == true) {
            header('Location: ' . getAppEnvVar("BASE_URL") . "/" . getAppEnvVar("LOGIN_URL"));
            die();
        } else {
            require_once "engine/errors/401.php";
            die();
        }
    });

    //LOGIN
    checkRoute('GET', '/' . getAppEnvVar("LOGIN_URL") , function() {
        $template = processTemplate("login", []);
        finishRender($template);
    });

    checkRoute('POST', '/' . getAppEnvVar("LOGIN_URL") , function() {
        $error = "";
        do {
            if (!isset($_POST["username"]) || !isset($_POST["password"]) || empty($_POST["username"]) || empty($_POST["password"])) {
                $error = "Please fill all the fields";
                break;
            }
            $username = $_POST["username"];
            $password = $_POST["password"];

            $result = modelCall('users', 'verifyLogin', ['db' => getDatabaseEnvConn('sqlite'), "username" => $username, "password" => $password]);

            print_r($result);
            
        } while (false);

        if ($error !== "") {
            $template = processTemplate("login", ["error" => $error]);
            finishRender($template);
        }
    });
?>