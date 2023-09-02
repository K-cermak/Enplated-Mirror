<?php
    checkRoute('GET', '/iframes/new-drive' , function() {
        newDrive();
    });

    checkRoute('POST', '/iframes/new-drive' , function() {
        newDrive();
    });

    function newDrive() {
        redirectNotLogin();
        redirectIfNotAdmin();

        do {
            if (!isset($_GET["local"]) && !isset($_GET["ftp"])) {
                $template = processTemplate("iframes/new-drive", []);
                break;
            }

            if (isset($_GET["local"]) && isset($_GET["1"])) {
                $template = processTemplate("iframes/local/step1", []);
                break;
            }

            if (isset($_GET["local"]) && isset($_GET["2"])) {
                $template = processTemplate("iframes/local/step2", []);
                break;
            }

            if (isset($_GET["local"]) && isset($_GET["3"])) {
                $template = processTemplate("iframes/local/step3", []);
                break;
            }

            if (isset($_GET["local"]) && isset($_GET["4"])) {
                if (!isset($_POST["newName"]) || empty($_POST["newName"])) {
                    $template = processTemplate("iframes/local/step3", []);
                    break;
                }

                $name = $_POST["newName"];

                //check if name is between 3 and 20 chars and only contains letters, numbers, - and _
                if (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $name)) {
                    $template = processTemplate("iframes/local/step3", ['error' => 'Name must be between 3 and 20 chars and only contain letters, numbers, - and _']);
                    break;
                }

                $result = modelCall('drives', 'checkIfNameNotUsed', ['db' => getDatabaseEnvConn('sqlite'), "driveName" => $name]);
                if (count($result) > 0) {
                    $template = processTemplate("iframes/local/step3", ['error' => 'Name already used']);
                    break;
                }

                $driveCredentials = json_encode([
                    "type" => "local",
                    "path" => $_SESSION["api-selectedPath"]
                ]);
                //unset($_SESSION["api-selectedPath"]);

                $result = modelCall('drives', 'createNewDrive', ['db' => getDatabaseEnvConn('sqlite'), "driveName" => $name, "driveCredentials" => $driveCredentials]);
                if ($result == -1) {
                    $template = processTemplate("iframes/local/step3", ['error' => 'Error creating drive']);
                    break;
                }

                $template = processTemplate("iframes/local/step4", []);
                break;
            }

            if (isset($_GET["ftp"]) && isset($_GET["1"])) {
                if (isset($_GET["selectOnly"]) && isset($_GET["driveId"])) {
                    //get from db
                    $drive = modelCall('drives', 'getDriveData', ['db' => getDatabaseEnvConn('sqlite'), "driveId" => $_GET["driveId"]]);
                    if ($drive == -1) {
                        $template = processTemplate("iframes/ftp/error", []);
                        break;
                    }

                    $driveCredentials = json_decode($drive[0]["driveCredentials"], true);
                    $_SESSION["api-ftpServerAddress"] = $driveCredentials["serverAddress"];
                    $_SESSION["api-ftpPort"] = $driveCredentials["port"];
                    $_SESSION["api-ftpUsername"] = $driveCredentials["username"];
                }

                $template = processTemplate("iframes/ftp/step1", []);
                break;
            }

            if (isset($_GET["ftp"]) && isset($_GET["2"])) {
                //check credentials
                $serverAddress = "";
                $port = 21;
                $username = "";
                $password = "";

                if (isset($_GET["selectOnly"]) && isset($_GET["driveId"])) {
                    //get from db
                    $drive = modelCall('drives', 'getDriveData', ['db' => getDatabaseEnvConn('sqlite'), "driveId" => $_GET["driveId"]]);
                    if ($drive == -1) {
                        $template = processTemplate("iframes/ftp/error", []);
                        break;
                    }

                    $driveCredentials = json_decode($drive[0]["driveCredentials"], true);
                    $serverAddress = $driveCredentials["serverAddress"];
                    $port = $driveCredentials["port"];
                    $username = $driveCredentials["username"];
                    $password = $driveCredentials["password"];
                    
                } else {
                    if (isset($_POST["serverAddress"]) && !empty($_POST["serverAddress"])) {
                        $serverAddress = $_POST["serverAddress"];
                    } else if (isset($_SESSION["api-ftpServerAddress"]) && !empty($_SESSION["api-ftpServerAddress"])) {
                        $serverAddress = $_SESSION["api-ftpServerAddress"];
                    }
                    if (isset($_POST["port"]) && !empty($_POST["port"])) {
                        $port = $_POST["port"];
                    } else if (isset($_SESSION["api-ftpPort"]) && !empty($_SESSION["api-ftpPort"])) {
                        $port = $_SESSION["api-ftpPort"];
                    }
                    if (isset($_POST["username"]) && !empty($_POST["username"])) {
                        $username = $_POST["username"];
                    } else if (isset($_SESSION["api-ftpUsername"]) && !empty($_SESSION["api-ftpUsername"])) {
                        $username = $_SESSION["api-ftpUsername"];
                    }
                    if (isset($_POST["password"]) && !empty($_POST["password"])) {
                        $password = $_POST["password"];
                    } else if (isset($_SESSION["api-ftpPassword"]) && !empty($_SESSION["api-ftpPassword"])) {
                        $password = $_SESSION["api-ftpPassword"];
                    }
                }

                $port = intval($port);

                //save to session
                $_SESSION["api-ftpServerAddress"] = $serverAddress;
                $_SESSION["api-ftpPort"] = $port;
                $_SESSION["api-ftpUsername"] = $username;
                $_SESSION["api-ftpPassword"] = $password;

                //try to connect
                $ftp_conn = ftp_connect($serverAddress, $port, 10);
                if (!$ftp_conn) {
                    if (isset($_GET["selectOnly"])) {
                        $template = processTemplate("iframes/ftp/error", []);
                    } else {
                        $template = processTemplate("iframes/ftp/step1", ['error' => 'Error connecting to server']);
                    }
                    break;
                }

                //try to login
                if (!ftp_login($ftp_conn, $username, $password)) {
                    if (isset($_GET["selectOnly"])) {
                        $template = processTemplate("iframes/ftp/error", []);
                    } else {
                        $template = processTemplate("iframes/ftp/step1", ['error' => 'Error logging in']);
                    }
                    break;
                }

                //close connection
                ftp_close($ftp_conn);

                $template = processTemplate("iframes/ftp/step2", []);
                break;
            }

            if (isset($_GET["ftp"]) && isset($_GET["3"])) {
                $template = processTemplate("iframes/ftp/step3", []);
                break;
            }

            if (isset($_GET["ftp"]) && isset($_GET["4"])) {
                $template = processTemplate("iframes/ftp/step4", []);
                break;
            }

            if (isset($_GET["ftp"]) && isset($_GET["5"])) {
                if (!isset($_POST["newName"]) || empty($_POST["newName"])) {
                    $template = processTemplate("iframes/ftp/step4", []);
                    break;
                }

                $name = $_POST["newName"];

                //check if name is between 3 and 20 chars and only contains letters, numbers, - and _
                if (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $name)) {
                    $template = processTemplate("iframes/ftp/step4", ['error' => 'Name must be between 3 and 20 chars and only contain letters, numbers, - and _']);
                    break;
                }

                $result = modelCall('drives', 'checkIfNameNotUsed', ['db' => getDatabaseEnvConn('sqlite'), "driveName" => $name]);
                if (count($result) > 0) {
                    $template = processTemplate("iframes/ftp/step4", ['error' => 'Name already used']);
                    break;
                }

                $driveCredentials = json_encode([
                    "type" => "ftp",
                    "path" => $_SESSION["api-selectedPath"],
                    "serverAddress" => $_SESSION["api-ftpServerAddress"],
                    "port" => $_SESSION["api-ftpPort"],
                    "username" => $_SESSION["api-ftpUsername"],
                    "password" => $_SESSION["api-ftpPassword"]
                ]);
                unset($_SESSION["api-selectedPath"]);
                unset($_SESSION["api-ftpServerAddress"]);
                unset($_SESSION["api-ftpPort"]);
                unset($_SESSION["api-ftpUsername"]);
                unset($_SESSION["api-ftpPassword"]);

                $result = modelCall('drives', 'createNewDrive', ['db' => getDatabaseEnvConn('sqlite'), "driveName" => $name, "driveCredentials" => $driveCredentials]);
                if ($result == -1) {
                    $template = processTemplate("iframes/ftp/step4", ['error' => 'Error creating drive']);
                    break;
                }

                $template = processTemplate("iframes/ftp/step5", []);
                break;
            }

            $template = processTemplate("iframes/new-drive", []);

        } while (false);

        finishRender($template);
    }

    checkRoute('POST', '/api/folders/check' , function() {
        redirectNotLogin();
        redirectIfNotAdmin();

        $_POST = json_decode(file_get_contents("php://input"), true); //because of axios

        if (isset($_POST["path"]) && !empty($_POST["path"]) && isset($_POST["type"]) && !empty($_POST["type"])) {
            $path = $_POST["path"];
            $type = $_POST["type"]; //local or ftp
            $splitter = "/";

            if ($type == "local") {
                //if running on windows, replace / with \
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $splitter = "\\";
                    //get first 3 chars of __DIR__
                    $dir = substr(__DIR__, 0, 3);
                    if ($path == "/") {
                        $path = $dir;
                    } else {
                        //remove first char
                        $path = substr($path, 1);
                        $path = $dir . str_replace("/", "\\", $path);
                    }
                }

                //check if folder exists
                if (!file_exists($path)) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Folder does not exist'
                        ]
                    ], 'json');
                }

                $folders = scandir($path);

                //check if folder
                $folders = array_filter($folders, function($folder) use ($path, $splitter) {
                    return is_dir($path .  $splitter . $folder);
                });

            } else if ($type == "ftp") {
                //try to connect
                $ftp_conn = ftp_connect($_SESSION["api-ftpServerAddress"], $_SESSION["api-ftpPort"], 10);
                if (!$ftp_conn) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Error connecting to server'
                        ]
                    ], 'json');
                }

                //try to login
                if (!ftp_login($ftp_conn, $_SESSION["api-ftpUsername"], $_SESSION["api-ftpPassword"])) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Error logging in'
                        ]
                    ], 'json');
                }

                //get folders
                $folders = ftp_nlist($ftp_conn, $path);

                //check if folder
                $folders = array_filter($folders, function($folder) use ($ftp_conn) {
                    return ftp_size($ftp_conn, $folder) == -1;
                });

                //remove path from folder name
                $folders = array_map(function($folder) use ($path) {
                    return str_replace($path, "", $folder);
                }, $folders);

                //close connection
                ftp_close($ftp_conn);
            }

            //remove . and ..
            $folders = array_filter($folders, function($folder) {
                return $folder != "." && $folder != "..";
            });

            //reset index of array
            $folders = array_values($folders);

            resourceView([
                'apiResponse' => [
                    'status' => 'success',
                    'data' => $folders
                ]
            ], 'json');

        } else {
            require_once "engine/errors/403.php";
            die();
        }
    });

    checkRoute('POST', '/api/folders/create' , function() {
        redirectNotLogin();
        redirectIfNotAdmin();

        $_POST = json_decode(file_get_contents("php://input"), true); //because of axios

        if (isset($_POST["path"]) && !empty($_POST["path"]) && isset($_POST["newName"]) && !empty($_POST["newName"]) && isset($_POST["type"]) && !empty($_POST["type"])) {
            $path = $_POST["path"];
            $newName = $_POST["newName"];
            $type = $_POST["type"]; //local or ftp
            $splitter = "/";

            if ($type == "local") {
                //if running on windows, replace / with \
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $splitter = "\\";
                    //get first 3 chars of __DIR__
                    $dir = substr(__DIR__, 0, 3);
                    if ($path == "/") {
                        $path = $dir;
                    } else {
                        //remove first char
                        $path = substr($path, 1);
                        $path = $dir . str_replace("/", "\\", $path);
                    }
                }

                $newPath = $path . $splitter . $newName;

                if (!file_exists($newPath)) {
                    mkdir($newPath);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'success',
                            'message' => 'Folder created successfully'
                        ]
                    ], 'json');
                } else {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Folder already exists'
                        ]
                    ], 'json');
                }
            } else if ($type == "ftp") {
                //try to connect
                $ftp_conn = ftp_connect($_SESSION["api-ftpServerAddress"], $_SESSION["api-ftpPort"], 10);
                if (!$ftp_conn) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Error connecting to server'
                        ]
                    ], 'json');
                }

                //try to login
                if (!ftp_login($ftp_conn, $_SESSION["api-ftpUsername"], $_SESSION["api-ftpPassword"])) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Error logging in'
                        ]
                    ], 'json');
                }

                //create folder
                if (!ftp_mkdir($ftp_conn, $path . "/" . $newName)) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Error creating folder'
                        ]
                    ], 'json');
                }

                //close connection
                ftp_close($ftp_conn);

                resourceView([
                    'apiResponse' => [
                        'status' => 'success',
                        'message' => 'Folder created successfully'
                    ]
                ], 'json');
            }

        }
    });

    checkRoute('POST', '/api/folders/verification' , function() {
        redirectNotLogin();
        redirectIfNotAdmin();
        
        $_POST = json_decode(file_get_contents("php://input"), true); //because of axios

        if (isset($_POST["path"]) && !empty($_POST["path"]) && isset($_POST["requestType"]) && !empty($_POST["requestType"]) && isset($_POST["type"]) && !empty($_POST["type"])) {
            $requestType = $_POST["requestType"];
            $path = $_POST["path"];
            $type = $_POST["type"]; //local or ftp
            $splitter = "/";

            if ($type == "local") {
                //if running on windows, replace / with \
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $splitter = "\\";
                    //get first 3 chars of __DIR__
                    $dir = substr(__DIR__, 0, 3);
                    if ($path == "/") {
                        $path = $dir;
                    } else {
                        //remove first char
                        $path = substr($path, 1);
                        $path = $dir . str_replace("/", "\\", $path);
                    }
                }
                
                if ($requestType == "exist") {
                    if (file_exists($path)) {
                        resourceView([
                            'apiResponse' => [
                                'status' => 'success',
                                'message' => 'Folder exists'
                            ]
                        ], 'json');
                    } else {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'message' => 'Folder does not exist'
                            ]
                        ], 'json');
                    }
                } else if ($requestType == "write") {
                    //make verify.enp file in folder
                    $newPath = $path . $splitter . "verify.enp";
                    $file = fopen($newPath, "w");
                    fwrite($file, "enp-verify");
                    fclose($file);

                    //check if file exists
                    if (file_exists($newPath)) {
                        resourceView([
                            'apiResponse' => [
                                'status' => 'success',
                                'message' => 'Folder is writable'
                            ]
                        ], 'json');
                    } else {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'message' => 'Folder is not writable'
                            ]
                        ], 'json');
                    }
                } else if ($requestType == "read") {
                    //check if verify.enp file exists and its content is enp-verify
                    $newPath = $path . $splitter . "verify.enp";
                    if (file_exists($newPath)) {
                        $file = fopen($newPath, "r");
                        $content = fread($file, filesize($newPath));
                        fclose($file);

                        if ($content == "enp-verify") {
                            resourceView([
                                'apiResponse' => [
                                    'status' => 'success',
                                    'message' => 'Folder is readable'
                                ]
                            ], 'json');
                        } else {
                            http_response_code(400);
                            resourceView([
                                'apiResponse' => [
                                    'status' => 'error',
                                    'message' => 'Folder is not readable'
                                ]
                            ], 'json');
                        }
                    } else {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'message' => 'Folder is not readable'
                            ]
                        ], 'json');
                    }
                } else if ($requestType == "delete") {
                    //delete verify.enp file in folder
                    $newPath = $path . $splitter . "verify.enp";
                    if (file_exists($newPath)) {
                        unlink($newPath);
                    }

                    //check if file exists
                    if (!file_exists($newPath)) {
                        resourceView([
                            'apiResponse' => [
                                'status' => 'success',
                                'message' => 'Folder is deletable'
                            ]
                        ], 'json');
                    } else {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'message' => 'Folder is not deletable'
                            ]
                        ], 'json');
                    }
                } else if ($requestType == "htaccess") {
                    //check if .htaccess is not in folder
                    $newPath = $path . $splitter . ".htaccess";
                    if (!file_exists($newPath)) {
                        resourceView([
                            'apiResponse' => [
                                'status' => 'success',
                                'message' => 'Folder is not protected'
                            ]
                        ], 'json');
                    } else {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'message' => 'Folder is protected'
                            ]
                        ], 'json');
                    }
                }
            } else if ($type == "ftp") {
                //try to connect
                $ftp_conn = ftp_connect($_SESSION["api-ftpServerAddress"], $_SESSION["api-ftpPort"], 10);
                if (!$ftp_conn) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Error connecting to server'
                        ]
                    ], 'json');
                }

                //try to login
                if (!ftp_login($ftp_conn, $_SESSION["api-ftpUsername"], $_SESSION["api-ftpPassword"])) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Error logging in'
                        ]
                    ], 'json');
                }

                if ($requestType == "exist") {
                    if (ftp_nlist($ftp_conn, $path) !== false) {
                        resourceView([
                            'apiResponse' => [
                                'status' => 'success',
                                'message' => 'Folder exists'
                            ]
                        ], 'json');
                    } else {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'message' => 'Folder does not exist'
                            ]
                        ], 'json');
                    }
                } else if ($requestType == "write") {
                    //make verify.enp file in folder
                    $newPath = $path . "/" . "verify.enp";
                    $file = fopen('php://temp', "w");
                    fwrite($file, "enp-verify");
                    rewind($file);
                    ftp_fput($ftp_conn, $newPath, $file, FTP_ASCII);

                    //check if file exists
                    if (ftp_nlist($ftp_conn, $newPath) !== false) {
                        resourceView([
                            'apiResponse' => [
                                'status' => 'success',
                                'message' => 'Folder is writable'
                            ]
                        ], 'json');
                    } else {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'message' => 'Folder is not writable'
                            ]
                        ], 'json');
                    }
                } else if ($requestType == "read") {
                    //check if verify.enp file exists and its content is enp-verify
                    $newPath = $path . "/" . "verify.enp";
                    
                    if (ftp_nlist($ftp_conn, $newPath) !== false) {
                        $file = fopen('php://temp', "w");
                        ftp_fget($ftp_conn, $file, $newPath, FTP_ASCII);
                        rewind($file);
                        $content = fread($file, ftp_size($ftp_conn, $newPath));
                        fclose($file);
                        
                        if ($content == "enp-verify") {
                            resourceView([
                                'apiResponse' => [
                                    'status' => 'success',
                                    'message' => 'Folder is readable'
                                ]
                            ], 'json');
                        } else {
                            http_response_code(400);
                            resourceView([
                                'apiResponse' => [
                                    'status' => 'error',
                                    'message' => 'Folder is not readable'
                                ]
                            ], 'json');
                        }
                    } else {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'message' => 'Folder is not readable'
                            ]
                        ], 'json');
                    }
                } else if ($requestType == "delete") {
                    //delete verify.enp file in folder
                    $newPath = $path . "/" . "verify.enp";
                    if (ftp_nlist($ftp_conn, $newPath) !== false) {
                        ftp_delete($ftp_conn, $newPath);
                    }

                    //check if file exists
                    if (ftp_nlist($ftp_conn, $newPath) == false) {
                        resourceView([
                            'apiResponse' => [
                                'status' => 'success',
                                'message' => 'Folder is deletable'
                            ]
                        ], 'json');
                    } else {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'message' => 'Folder is not deletable'
                            ]
                        ], 'json');
                    }
                }
            }
        }
    });

    checkRoute('POST', '/api/folders/change', function() {
        redirectNotLogin();
        redirectIfNotAdmin();
        
        $_POST = json_decode(file_get_contents("php://input"), true); //because of axios

        if (isset($_POST["driveId"]) && !empty($_POST["driveId"]) && isset($_POST["type"]) && !empty($_POST["type"])) {
            $driveId = $_POST["driveId"];
            $type = $_POST["type"]; //local or ftp

            //check if drive exists
            $driveExist = modelCall('drives', 'checkIfDriveExist', ['db' => getDatabaseEnvConn('sqlite'), "driveId" => $driveId]);
            if (!$driveExist) {
                http_response_code(400);
                resourceView([
                    'apiResponse' => [
                        'status' => 'error',
                        'message' => 'Drive does not exist'
                    ]
                ], 'json');
            }

            if ($type == "local") {
                $driveCredentials = json_encode([
                    "type" => "local",
                    "path" => $_SESSION["api-selectedPath"]
                ]);
            } else if ($type == "ftp") {
                $driveCredentials = json_encode([
                    "type" => "ftp",
                    "path" => $_SESSION["api-selectedPath"],
                    "serverAddress" => $_SESSION["api-ftpServerAddress"],
                    "port" => $_SESSION["api-ftpPort"],
                    "username" => $_SESSION["api-ftpUsername"],
                    "password" => $_SESSION["api-ftpPassword"]
                ]);
            }

            modelCall('drives', 'updateDrive', ['db' => getDatabaseEnvConn('sqlite'), "driveId" => $driveId, "driveCredentials" => $driveCredentials]);
            resourceView([
                'apiResponse' => [
                    'status' => 'success',
                    'message' => 'Folder changed'
                ]
            ], 'json');
            
        }
    });

    checkRoute('POST', '/api/privileges/getInfo', function() {
        redirectNotLogin();
        redirectIfNotAdmin();
        
        $_POST = json_decode(file_get_contents("php://input"), true); //because of axios

        if (isset($_POST["driveId"]) && !empty($_POST["driveId"])) {
            $driveId = $_POST["driveId"];

            $driveExist = modelCall('drives', 'checkIfDriveExist', ['db' => getDatabaseEnvConn('sqlite'), "driveId" => $driveId]);
            if (!$driveExist) {
                http_response_code(400);
                resourceView([
                    'apiResponse' => [
                        'status' => 'error',
                        'message' => 'Drive does not exist'
                    ]
                ], 'json');
            }

            $privileges = modelCall('privileges', 'getPrivilegesForDrive', ['db' => getDatabaseEnvConn('sqlite'), "driveId" => $driveId]);
            $allGroups = modelCall('groups', 'getAllGroups', ['db' => getDatabaseEnvConn('sqlite')]);

            resourceView([
                'apiResponse' => [
                    'status' => 'success',
                    'message' => 'Privileges fetched successfully',
                    'privileges' => json_encode($privileges),
                    'allGroups' => json_encode($allGroups)
                ]
            ], 'json');
        }
    });

    checkRoute('POST', '/api/fileViewer/getContent', function() {
        redirectNotLogin();

        $_POST = json_decode(file_get_contents("php://input"), true); //because of axios

        if (isset($_POST["path"]) && !empty($_POST["path"]) && isset($_POST["drive"]) && !empty($_POST["drive"])) {
            $path = $_POST["path"];
            $drive = $_POST["drive"];

            if ($path == "#drives#") {
                $drives = modelCall("drives", "getDrivesWithAccess", []);

                for ($i = 0; $i < count($drives); $i++) {
                    $type = json_decode($drives[$i]["driveCredentials"], true)["type"];
                    $drives[$i]["type"] = $type;
                    unset($drives[$i]["driveCredentials"]);
                    if (isset($drives[$i]["accessLevel"]) && $drives[$i]["accessLevel"] == "none") {
                        unset($drives[$i]);
                    }
                }

                //fix index
                $drives = array_values($drives);

                resourceView([
                    'apiResponse' => [
                        'status' => 'success',
                        'type' => 'drives',
                        'data' => $drives
                    ]
                ], 'json');
            } else {
                $drives = modelCall("drives", "getDrivesWithAccess", []);
                $drivesCredential = "";

                //check if has access
                $hasAccess = false;
                for ($i = 0; $i < count($drives); $i++) {
                    if ($drives[$i]["id"] == $drive) {
                        if ($drives[$i]["accessLevel"] == "edit" || $drives[$i]["accessLevel"] == "view") {
                            $hasAccess = true;
                            $drivesCredential = $drives[$i]["driveCredentials"];
                        }
                        break;
                    }
                }

                if (!$hasAccess) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'You do not have access to this drive'
                        ]
                    ], 'json');
                }

                //check if path doesnt contain .. or /../
                if (strpos($path, "..") !== false || strpos($path, "/../") !== false) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Invalid path'
                        ]
                    ], 'json');
                }

                //get drive credentials and create path
                $driveCredentials = json_decode($drivesCredential, true);
                $driveType = $driveCredentials["type"];

                if ($driveType == "local") {
                    $drivePath = $driveCredentials["path"];
                    $path = $drivePath . $path;
    
                    //if running on windows, replace / with \
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        //get first 3 chars of __DIR__
                        $dir = substr(__DIR__, 0, 3);
                        if ($path == "/") {
                            $path = $dir;
                        } else {
                            //remove first char
                            $path = substr($path, 1);
                            $path = $dir . str_replace("/", "\\", $path);
                        }
                    }

                    //check if folder exists
                    if (!file_exists($path)) {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'message' => 'Folder does not exist'
                            ]
                        ], 'json');
                    }

                    $folders = scandir($path);

                    //remove . and ..
                    $folders = array_filter($folders, function($folder) {
                        return $folder != "." && $folder != "..";
                    });
                } else if ($driveType == "ftp") {
                    $drivePath = $driveCredentials["path"];
                    $serverAddress = $driveCredentials["serverAddress"];
                    $port = $driveCredentials["port"];
                    $username = $driveCredentials["username"];
                    $password = $driveCredentials["password"];

                    //try to connect
                    $ftp_conn = ftp_connect($serverAddress, $port, 10);
                    if (!$ftp_conn) {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'type' => 'error-connecting-to-server',
                                'message' => 'Error connecting to server'
                            ]
                        ], 'json');
                    }

                    //try to login
                    if (!ftp_login($ftp_conn, $username, $password)) {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'type' => 'error-logging-in',
                                'message' => 'Error logging in'
                            ]
                        ], 'json');
                    }

                    //get folders
                    $folders = ftp_nlist($ftp_conn, $path);

                    //close connection
                    ftp_close($ftp_conn);

                    //remove path from folder name
                    $folders = array_map(function($folder) use ($path) {
                        return str_replace($path, "", $folder);
                    }, $folders);
                }

                //reset index of array
                $folders = array_values($folders);

                $files = [];
                //if has extension, get file
                for ($i = 0; $i < count($folders); $i++) {
                    $extension = pathinfo($folders[$i], PATHINFO_EXTENSION);
                    if (!empty($extension)) {
                        array_push($files, $folders[$i]);
                        unset($folders[$i]);
                    }
                }

                //sort files
                sort($files);

                //reset index of array
                $folders = array_values($folders);

                //add files to folders
                for ($i = 0; $i < count($files); $i++) {
                    array_push($folders, $files[$i]);
                }

                resourceView([
                    'apiResponse' => [
                        'status' => 'success',
                        'data' => $folders
                    ]
                ], 'json');
            }
        } else {
            require_once "engine/errors/403.php";
            die();
        }
    });

    checkRoute('POST', '/api/fileViewer/createFolder', function() {
        redirectNotLogin();

        $_POST = json_decode(file_get_contents("php://input"), true); //because of axios
        if (isset($_POST["path"]) && !empty($_POST["path"]) && isset($_POST["drive"]) && !empty($_POST["drive"]) && isset($_POST["newName"]) && !empty($_POST["newName"])) {
            $path = $_POST["path"];
            $drive = $_POST["drive"];
            $newName = $_POST["newName"];


            $drives = modelCall("drives", "getDrivesWithAccess", []);
            $drivesCredential = "";

            //check if has access
            $hasAccess = false;
            for ($i = 0; $i < count($drives); $i++) {
                if ($drives[$i]["id"] == $drive) {
                    if ($drives[$i]["accessLevel"] == "edit") {
                        $hasAccess = true;
                        $drivesCredential = $drives[$i]["driveCredentials"];
                    }
                    break;
                }
            }

            if (!$hasAccess) {
                http_response_code(400);
                resourceView([
                    'apiResponse' => [
                        'status' => 'error',
                        'message' => 'You do not have access to this drive'
                    ]
                ], 'json');
            }

            //check if path doesnt contain .. or /../
            if (strpos($path, "..") !== false || strpos($path, "/../") !== false) {
                http_response_code(400);
                resourceView([
                    'apiResponse' => [
                        'status' => 'error',
                        'message' => 'Invalid path'
                    ]
                ], 'json');
            }

            //get drive credentials and create path
            $driveCredentials = json_decode($drivesCredential, true);
            $driveType = $driveCredentials["type"];
            if ($driveType == "local") {
                $drivePath = $driveCredentials["path"];
                $path = $drivePath . $path . "/" . $newName;

                //if running on windows, replace / with \
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    //get first 3 chars of __DIR__
                    $dir = substr(__DIR__, 0, 3);
                    if ($path == "/") {
                        $path = $dir;
                    } else {
                        //remove first char
                        $path = substr($path, 1);
                        $path = $dir . str_replace("/", "\\", $path);
                    }
                }

                if (!file_exists($path)) {
                    mkdir($path);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'success',
                            'message' => 'Folder created successfully'
                        ]
                    ], 'json');
                } else {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Folder already exists'
                        ]
                    ], 'json');
                }
            } else if ($driveType == "ftp") {
                $drivePath = $driveCredentials["path"];
                $serverAddress = $driveCredentials["serverAddress"];
                $port = $driveCredentials["port"];
                $username = $driveCredentials["username"];
                $password = $driveCredentials["password"];

                //try to connect
                $ftp_conn = ftp_connect($serverAddress, $port, 10);
                if (!$ftp_conn) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'type' => 'error-connecting-to-server',
                            'message' => 'Error connecting to server'
                        ]
                    ], 'json');
                }

                //try to login
                if (!ftp_login($ftp_conn, $username, $password)) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'type' => 'error-logging-in',
                            'message' => 'Error logging in'
                        ]
                    ], 'json');
                }

                //create folder
                if (!ftp_mkdir($ftp_conn, $path . "/" . $newName)) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Error creating folder'
                        ]
                    ], 'json');
                }

                //close connection
                ftp_close($ftp_conn);

                resourceView([
                    'apiResponse' => [
                        'status' => 'success',
                        'message' => 'Folder created successfully'
                    ]
                ], 'json');
            }
        }
    });

    checkRoute('POST', '/api/fileViewer/getFileInfo', function() {
        redirectNotLogin();

        $_POST = json_decode(file_get_contents("php://input"), true); //because of axios
        
        if (isset($_POST["drive"]) && !empty($_POST["drive"]) && isset($_POST["type"]) && !empty($_POST["type"])) {
            $drive = $_POST["drive"];
            $type = $_POST["type"]; //drive or folder or file

            if ($type == "folder" || $type == "file") {
                if (!isset($_POST["path"]) || empty($_POST["path"])) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Path not specified'
                        ]
                    ], 'json');
                } else {
                    $path = $_POST["path"];
                }

                if (strpos($path, "..") !== false || strpos($path, "/../") !== false) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Invalid path'
                        ]
                    ], 'json');
                }
            }

            if ($type == "file") {
                if (!isset($_POST["file"]) || empty($_POST["file"])) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'File not specified'
                        ]
                    ], 'json');
                } else {
                    $file = $_POST["file"];
                }

                if (strpos($file, "..") !== false || strpos($file, "/../") !== false) {
                    http_response_code(400);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Invalid file'
                        ]
                    ], 'json');
                }
            }

            //check if has access
            $drives = modelCall("drives", "getDrivesWithAccess", []);
            $drivesCredential = "";
            $hasAccess = false;
            for ($i = 0; $i < count($drives); $i++) {
                if ($drives[$i]["id"] == $drive) {
                    if ($drives[$i]["accessLevel"] == "edit") {
                        $hasAccess = "edit";
                        $drivesCredential = $drives[$i]["driveCredentials"];
                    } else if ($drives[$i]["accessLevel"] == "view") {
                        $hasAccess = "view";
                        $drivesCredential = $drives[$i]["driveCredentials"];
                    }
                    break;
                }
            }

            if (!$hasAccess) {
                http_response_code(400);
                resourceView([
                    'apiResponse' => [
                        'status' => 'error',
                        'message' => 'You do not have access to this drive'
                    ]
                ], 'json');
            }

            $driveCredentials = json_decode($drivesCredential, true);
            $driveType = $driveCredentials["type"];

            if ($type == "drive") {
                if ($driveType == "local") {
                    resourceView([
                        'apiResponse' => [
                            'status' => 'success',
                            'type' => 'drive',
                            'driveType' => $driveType,
                            'accessLevel' => $hasAccess
                        ]
                    ], 'json');

                } else if ($driveType == "ftp") {
                    //get host and port
                    $serverAddress = $driveCredentials["serverAddress"];
                    $port = $driveCredentials["port"];

                    //get ping
                    $ping = pingServer($serverAddress, $port);
                    resourceView([
                        'apiResponse' => [
                            'status' => 'success',
                            'type' => 'drive',
                            'driveType' => $driveType,
                            'ping' => $ping,
                            'accessLevel' => $hasAccess
                        ]
                    ], 'json');
                }
            } else if ($type == "folder") {
                //size, number of files, dates

                if ($driveType == "local") {
                    $drivePath = $driveCredentials["path"];
                    $path = $drivePath . $path;

                    //if running on windows, replace / with \
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        //get first 3 chars of __DIR__
                        $dir = substr(__DIR__, 0, 3);
                        if ($path == "/") {
                            $path = $dir;
                        } else {
                            //remove first char
                            $path = substr($path, 1);
                            $path = $dir . str_replace("/", "\\", $path);
                        }
                    }

                    //check if folder exists
                    if (!file_exists($path)) {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'message' => 'Folder does not exist'
                            ]
                        ], 'json');
                    }

                    //get size and number of files
                    $size = 0;
                    $numberOfFiles = 0;
                    $overflow = false;
                    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
                        if ($file->getFilename() == "." || $file->getFilename() == "..") {
                            continue;
                        }
                        if ($numberOfFiles >= getAppEnvVar("COUNT_FILE_MAX")) {
                            $overflow = true;
                            break;
                        }
                        $size += $file->getSize();
                        $numberOfFiles++;
                    }

                    //get dates
                    $creationDate = date("Y-m-d H:i:s", filectime($path));
                    $lastModifiedDate = date("Y-m-d H:i:s", filemtime($path));

                    resourceView([
                        'apiResponse' => [
                            'status' => 'success',
                            'type' => 'folder',
                            'size' => $size,
                            'numberOfFiles' => $numberOfFiles,
                            'creationDate' => $creationDate,
                            'lastModifiedDate' => $lastModifiedDate,
                            'overflow' => $overflow,
                            'driveType' => "local",
                        ]
                    ], 'json');

                } else if ($driveType == "ftp") {
                    $drivePath = $driveCredentials["path"];
                    $serverAddress = $driveCredentials["serverAddress"];
                    $port = $driveCredentials["port"];
                    $username = $driveCredentials["username"];
                    $password = $driveCredentials["password"];

                    //try to connect
                    $ftp_conn = ftp_connect($serverAddress, $port, 10);
                    if (!$ftp_conn) {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'type' => 'error-connecting-to-server',
                                'message' => 'Error connecting to server'
                            ]
                        ], 'json');
                    }

                    //try to login
                    if (!ftp_login($ftp_conn, $username, $password)) {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'type' => 'error-logging-in',
                                'message' => 'Error logging in'
                            ]
                        ], 'json');
                    }

                    //get size and number of files
                    $size = 0;
                    $numberOfFiles = 0;
                    $overflow = false;
                    $files = ftp_nlist($ftp_conn, $path);
                    foreach ($files as $file) {
                        if ($file == "." || $file == "..") {
                            continue;
                        }
                        if ($numberOfFiles >= getAppEnvVar("COUNT_FILE_MAX")) {
                            $overflow = true;
                            break;
                        }
                        $size += ftp_size($ftp_conn, $file);
                        $numberOfFiles++;
                    }
                    

                    //close connection
                    ftp_close($ftp_conn);

                    resourceView([
                        'apiResponse' => [
                            'status' => 'success',
                            'type' => 'folder',
                            'size' => $size,
                            'numberOfFiles' => $numberOfFiles,
                            'overflow' => $overflow,
                            'driveType' => "ftp",
                        ]
                    ], 'json');
                }
            } else if ($type == "file") {
                //size, dates

                if ($driveType == "local") {
                    $drivePath = $driveCredentials["path"];
                    $path = $drivePath . $path . "/" . $file;

                    //if running on windows, replace / with \
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        //get first 3 chars of __DIR__
                        $dir = substr(__DIR__, 0, 3);
                        if ($path == "/") {
                            $path = $dir;
                        } else {
                            //remove first char
                            $path = substr($path, 1);
                            $path = $dir . str_replace("/", "\\", $path);
                        }
                    }

                    //check if file exists
                    if (!file_exists($path)) {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'message' => 'File does not exist'
                            ]
                        ], 'json');
                    }

                    //get size
                    $size = filesize($path);

                    //get dates
                    $creationDate = date("Y-m-d H:i:s", filectime($path));
                    $lastModifiedDate = date("Y-m-d H:i:s", filemtime($path));

                    resourceView([
                        'apiResponse' => [
                            'status' => 'success',
                            'type' => 'file',
                            'size' => $size,
                            'creationDate' => $creationDate,
                            'lastModifiedDate' => $lastModifiedDate,
                            'driveType' => "local",
                        ]
                    ], 'json');

                } else if ($driveType == "ftp") {
                    $drivePath = $driveCredentials["path"];
                    $serverAddress = $driveCredentials["serverAddress"];
                    $port = $driveCredentials["port"];
                    $username = $driveCredentials["username"];
                    $password = $driveCredentials["password"];

                    //try to connect
                    $ftp_conn = ftp_connect($serverAddress, $port, 10);
                    if (!$ftp_conn) {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'type' => 'error-connecting-to-server',
                                'message' => 'Error connecting to server'
                            ]
                        ], 'json');
                    }

                    //try to login
                    if (!ftp_login($ftp_conn, $username, $password)) {
                        http_response_code(400);
                        resourceView([
                            'apiResponse' => [
                                'status' => 'error',
                                'type' => 'error-logging-in',
                                'message' => 'Error logging in'
                            ]
                        ], 'json');
                    }

                    //get size
                    $size = ftp_size($ftp_conn, $path . "/" . $file);

                    //close connection
                    ftp_close($ftp_conn);

                    resourceView([
                        'apiResponse' => [
                            'status' => 'success',
                            'type' => 'file',
                            'size' => $size,
                            'driveType' => "ftp",
                        ]
                    ], 'json');
                }

            }
        }
    });

    function pingServer($serverAddress, $port) {
        $start = microtime(true);
        $file = @fsockopen($serverAddress, $port, $errno, $errstr, 10);
        $stop = microtime(true);
        $status = 0;

        if (!$file) {
            $status = -1;  // Site is down
        } else {
            fclose($file);
            $status = ($stop - $start) * 1000;
            $status = floor($status);
        }

        return $status;
    }
?>