<?php
    checkRoute('GET', '/' , function() {
        if (isset($_SESSION["userId"])) {
            header('Location: ' . getAppEnvVar("BASE_URL") . "/dashboard");
        } else {
            redirectNotLogin();
        }
    });
?>