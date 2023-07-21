<?php
    checkRoute('GET', '/' . getAppEnvVar("LOGIN_URL") , function() {
        if (isset($_SESSION["userId"])) {
            header('Location: ' . getAppEnvVar("BASE_URL") . "/dashboard");
            die();
        }

        $template = processTemplate("login", []);
        finishRender($template);
    });


    checkRoute('POST', '/' . getAppEnvVar("LOGIN_URL") , function() {
        if (isset($_SESSION["userId"])) {
            header('Location: ' . getAppEnvVar("BASE_URL") . "/dashboard");
            die();
        }

        $error = "";
        do {
            if (isset($_SESSION["loginAttempts"]) && $_SESSION["loginAttempts"] >= 5) {
                if (isset($_SESSION["lastAttempt"]) && $_SESSION["lastAttempt"] + 300 > time()) {
                    $error = "You have exceeded the maximum number of login attempts. Please try again later.";
                    break;
                } else {
                    $_SESSION["loginAttempts"] = 0;
                }
            }

            if (!isset($_POST["username"]) || !isset($_POST["password"]) || empty($_POST["username"]) || empty($_POST["password"])) {
                $error = "Please fill all the fields";
                break;
            }

            $username = $_POST["username"];
            $password = $_POST["password"];
            $result = modelCall('users', 'verifyLogin', ['db' => getDatabaseEnvConn('sqlite'), "username" => $username, "password" => hash("sha256", $password)]);

            if ($result === false) {
                $error = "Wrong username or password";
                if (!isset($_SESSION["loginAttempts"])) {
                    $_SESSION["loginAttempts"] = 0;
                }
                $_SESSION["loginAttempts"]++;

                if ($_SESSION["loginAttempts"] >= 5) {
                    $_SESSION["lastAttempt"] = time();
                    $error = "You have exceeded the maximum number of login attempts. Please try again later.";
                }
                break;
            }

            if ($result["privilageLevel"] == 0) {
                $error = "You are not allowed to login, because your account has been disabled. Please contact the administrator.";
                break;
            }

            modelCall('users', 'logLastLogin', ['db' => getDatabaseEnvConn('sqlite'), "id" => $result["id"]]);
            $_SESSION["userId"] = $result["id"];
            $_SESSION["username"] = $result["loginName"];
            $_SESSION["privilageLevel"] = $result["privilageLevel"];
            $_SESSION["passwordCheck"] = $result["password"];
            $_SESSION["loginAttempts"] = 0;
            $_SESSION["lastAttempt"] = 0;
            header('Location: ' . getAppEnvVar("BASE_URL") . "/dashboard?logged");
            
        } while (false);

        if ($error !== "") {
            $template = processTemplate("login", ["error" => $error]);
            finishRender($template);
        }
    });
?>