<?php
    registerRoutesFile(__DIR__ . '/dashboard.php');
    registerRoutesFile(__DIR__ . '/login.php');
    registerRoutesFile(__DIR__ . '/main.php');

    function registerRoutesFile($file) {
        require_once $file;
    }

    function checkRoute($method, $path, $callback) {
        logToJsConsole("Checking route: $method $path");
        logToJsConsole('Request method: ' . $_ENV['REQUEST']['METHOD']);
        logToJsConsole('Request path: ' . $_ENV['REQUEST']['URI']);
        logToJsConsole('Request path without base url: ' . str_replace(getAppEnvVar('BASE_URL'), '', $_ENV['REQUEST']['URI']));

        if (isset($_ENV['REQUEST']['FOUND'])) {
            logToJsConsole('Route already found, skipping');
            return;
        }

        //check method
        if ($_ENV['REQUEST']['METHOD'] != $method) {
            logToJsConsole('Method not match, skipping');
            return;
        }

        //if path include variables
        if (strpos($path, '{') !== false) {
            variableRouteCheck($path, $callback);
        } else {
            simpleRouteCheck($path, $callback);
        }
    }

    function simpleRouteCheck($path, $callback) {
        logToJsConsole('Checking SIMPLE route');

        //if part contain ?, remove text after ?
        if (strpos($_ENV['REQUEST']['URI'], '?') !== false) {
            $requestPath = explode('?', $_ENV['REQUEST']['URI'])[0];
        } else {
            $requestPath = $_ENV['REQUEST']['URI'];
        }

        if ($requestPath == $path || $requestPath . "/" == $path || $requestPath == substr($path, 1)) {
            finishRoute($callback);
        }
    }

    function variableRouteCheck($path, $callback) {
        logToJsConsole('Checking VARIABLE route');

        //split path and request by /
        $pathParts = explode('/', $path);
        $requestParts = explode('/', $_ENV['REQUEST']['URI']);

        //remove empty parts and parts that starts with ?
        $pathParts = array_filter($pathParts);
        $requestParts = array_filter($requestParts, function ($part) {
            return $part != '' && substr($part, 0, 1) != '?';
        });

        //if part contain ?, remove text after ?
        $requestParts = array_map(function ($part) {
            if (strpos($part, '?') !== false) {
                return substr($part, 0, strpos($part, '?'));
            } else {
                return $part;
            }
        }, $requestParts);

        //reset indexes
        $pathParts = array_values($pathParts);
        $requestParts = array_values($requestParts);

        //check if path and request match
        if (count($pathParts) != count($requestParts)) {
            logToJsConsole('Path not match, skipping (different length) - (' . count($pathParts) . ' vs ' . count($requestParts) . ')');
            return;
        }

        for ($i = 0; $i < count($pathParts); $i++) {
            //if path part is variable
            if (strpos($pathParts[$i], '{') !== false) {
                //get variable name
                $variableName = substr($pathParts[$i], 1, -1);
                //add variable to request params
                $_ENV['REQUEST']['PARAMS'][$variableName] = $requestParts[$i];
            } else {
                //if path part is not variable
                if ($pathParts[$i] != $requestParts[$i]) {
                    logToJsConsole('Path not match, skipping (different parts)');
                    return;
                }
            }
        }

        finishRoute($callback);
    }

    function finishRoute($callback) {
        logToJsConsole('Route found!');
        $_ENV['REQUEST']['FOUND'] = true;
        ob_start(); //start output buffering
        $callback();
    }
?>