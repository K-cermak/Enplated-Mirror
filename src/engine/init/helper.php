<?php
    function checkIfDebugMode() {
        if (isset($_ENV['APP']['PRODUCTION']) && $_ENV['APP']['PRODUCTION'] == false) {
            return true;
        } else {
            return false;
        }
    }

    function logToJsConsole($msg) {
        if (checkIfDebugMode() && isset($_ENV['APP']['CONSOLE_LOG']) && $_ENV['APP']['CONSOLE_LOG'] == true) {
            echo '<script>console.log("' . $msg . '")</script>';
        }
    }

    function getAppEnvVar($key) {
        if (isset($_ENV['APP'][$key])) {
            return $_ENV['APP'][$key];
        } else {
            return null;
        }
    }

    function getDatabaseEnvConn($key) {
        if (isset($_ENV['DATABASES'][$key])) {
            return $_ENV['DATABASES'][$key];
        } else {
            return null;
        }
    }

    function getRequestParam($key) {
        if (isset($_ENV['REQUEST']['PARAMS'][$key])) {
            return $_ENV['REQUEST']['PARAMS'][$key];
        } else {
            return null;
        }
    }
?>