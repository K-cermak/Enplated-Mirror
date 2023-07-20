<?php
    //get request uri with host and ssl
    $request = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    if (strtolower($request) == strtolower(getAppEnvVar("BASE_URL"))) {
        $request = "/";
    } else {
        //remove BASE_URL
        $request = str_replace(strtolower(getAppEnvVar("BASE_URL")), '', $request);
        $request = str_replace(getAppEnvVar("BASE_URL"), '', $request);
    }

    $_ENV['REQUEST'] = [
        'URI' => $request,
        'METHOD' => $_SERVER['REQUEST_METHOD'],
    ];

    if (substr($_ENV['REQUEST']['URI'], 0, 7) == 'public/' || str_starts_with(strtolower($_ENV['REQUEST']['URI']), strtolower(getAppEnvVar("BASE_URL") .  'public/'))) {
        if (substr($_ENV['REQUEST']['URI'], 0, 7) == 'public/') {
            $file = getAppEnvVar("BASE_DIRECTORY") . $_ENV['REQUEST']['URI'];
        } else {
            $file = getAppEnvVar("BASE_DIRECTORY") . substr($_ENV['REQUEST']['URI'], strlen(getAppEnvVar("BASE_URL")));
        }

        if (file_exists($file)) {
            $fileInfo = pathinfo($file);
            $fileType = $fileInfo['extension'];
            $mimeType = mime_content_type($file);

            //for js and css files
            if ($fileType == 'js' || $fileType == 'css') {
                $mimeType = 'text/' . $fileType;
            }

            //for security reasons we don't want to user to download php files
            if ($fileType == 'php') {
                require_once __DIR__ . '/../errors/404.php';
                exit;
            }

            header("Content-Type: $mimeType");
            readfile($file);
            exit;
        } else {
            require_once __DIR__ . '/../errors/404.php';
            exit;
        }
    }
    
?>