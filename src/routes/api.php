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

                $result = modelCall('drives', 'createNewDrive', ['db' => getDatabaseEnvConn('sqlite'), "driveName" => $name, "driveCredentials" => $driveCredentials]);
                if ($result == -1) {
                    $template = processTemplate("iframes/local/step3", ['error' => 'Error creating drive']);
                    break;
                }

                $template = processTemplate("iframes/local/step4", []);
                break;
            }

            if (isset($_GET["ftp"]) && isset($_GET["1"])) {
                $template = processTemplate("iframes/ftp/step1", []);
                break;
            }

            if (isset($_GET["ftp"]) && isset($_GET["2"])) {
                //check credentials
                $serverAddress = "";
                $port = 21;
                $username = "";
                $password = "";

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

                $port = intval($port);

                //save to session
                $_SESSION["api-ftpServerAddress"] = $serverAddress;
                $_SESSION["api-ftpPort"] = $port;
                $_SESSION["api-ftpUsername"] = $username;
                $_SESSION["api-ftpPassword"] = $password;

                //try to connect
                $ftp_conn = ftp_connect($serverAddress, $port, 10);
                if (!$ftp_conn) {
                    $template = processTemplate("iframes/ftp/step1", ['error' => 'Error connecting to server']);
                    break;
                }

                //try to login
                if (!ftp_login($ftp_conn, $username, $password)) {
                    $template = processTemplate("iframes/ftp/step1", ['error' => 'Error logging in']);
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
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Error connecting to server'
                        ]
                    ], 'json');
                }

                //try to login
                if (!ftp_login($ftp_conn, $_SESSION["api-ftpUsername"], $_SESSION["api-ftpPassword"])) {
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
                $folders = array_filter($folders, function($folder) use ($ftp_conn, $path) {
                    return ftp_size($ftp_conn, $path . "/" . $folder) == -1;
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
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Error connecting to server'
                        ]
                    ], 'json');
                }

                //try to login
                if (!ftp_login($ftp_conn, $_SESSION["api-ftpUsername"], $_SESSION["api-ftpPassword"])) {
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Error logging in'
                        ]
                    ], 'json');
                }

                //create folder
                if (!ftp_mkdir($ftp_conn, $path . "/" . $newName)) {
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
                            resourceView([
                                'apiResponse' => [
                                    'status' => 'error',
                                    'message' => 'Folder is not readable'
                                ]
                            ], 'json');
                        }
                    } else {
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
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Error connecting to server'
                        ]
                    ], 'json');
                }

                //try to login
                if (!ftp_login($ftp_conn, $_SESSION["api-ftpUsername"], $_SESSION["api-ftpPassword"])) {
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'Error logging in'
                        ]
                    ], 'json');
                }

                if ($requestType == "exist") {
                    if (ftp_nlist($ftp_conn, $path) != false) {
                        resourceView([
                            'apiResponse' => [
                                'status' => 'success',
                                'message' => 'Folder exists'
                            ]
                        ], 'json');
                    } else {
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
                    if (ftp_nlist($ftp_conn, $newPath) != false) {
                        resourceView([
                            'apiResponse' => [
                                'status' => 'success',
                                'message' => 'Folder is writable'
                            ]
                        ], 'json');
                    } else {
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
                    
                    if (ftp_nlist($ftp_conn, $newPath) != -1) {
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
                            resourceView([
                                'apiResponse' => [
                                    'status' => 'error',
                                    'message' => 'Folder is not readable'
                                ]
                            ], 'json');
                        }
                    } else {
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
                    if (ftp_nlist($ftp_conn, $newPath) != false) {
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

        if (isset($_POST["driveId"]) && !empty($_POST["driveId"])) {
            //check if drive exists
            $driveExist = modelCall('drives', 'checkIfDriveExist', ['db' => getDatabaseEnvConn('sqlite'), "driveId" => $_POST["driveId"]]);
            if (!$driveExist) {
                resourceView([
                    'apiResponse' => [
                        'status' => 'error',
                        'message' => 'Drive does not exist'
                    ]
                ], 'json');
            }

            $driveCredentials = json_encode([
                "type" => "local",
                "path" => $_SESSION["api-selectedPath"]
            ]);

            modelCall('drives', 'updateDrive', ['db' => getDatabaseEnvConn('sqlite'), "driveId" => $_POST["driveId"], "driveCredentials" => $driveCredentials]);
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
                    resourceView([
                        'apiResponse' => [
                            'status' => 'error',
                            'message' => 'You do not have access to this drive'
                        ]
                    ], 'json');
                }

                //check if path doesnt contain .. or /../
                if (strpos($path, "..") !== false || strpos($path, "/../") !== false) {
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
                }
    
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

                //reset index of array
                $folders = array_values($folders);

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
                resourceView([
                    'apiResponse' => [
                        'status' => 'error',
                        'message' => 'You do not have access to this drive'
                    ]
                ], 'json');
            }

            //check if path doesnt contain .. or /../
            if (strpos($path, "..") !== false || strpos($path, "/../") !== false) {
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
            }

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
                resourceView([
                    'apiResponse' => [
                        'status' => 'error',
                        'message' => 'Folder already exists'
                    ]
                ], 'json');
            }
        }
    });
?>