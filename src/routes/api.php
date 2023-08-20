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
            if (!isset($_GET["local"])) {
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

            $template = processTemplate("iframes/new-drive", []);

        } while (false);

        finishRender($template);
    }

    checkRoute('POST', '/api/folders/check' , function() {
        redirectNotLogin();
        redirectIfNotAdmin();

        $_POST = json_decode(file_get_contents("php://input"), true); //because of axios

        if (isset($_POST["path"]) && !empty($_POST["path"])) {
            $path = $_POST["path"];
            $splitter = "/";

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

            $folders = scandir($path);

            //check if folder
            $folders = array_filter($folders, function($folder) use ($path, $splitter) {
                return is_dir($path .  $splitter . $folder);
            });
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

        if (isset($_POST["path"]) && !empty($_POST["path"]) && isset($_POST["newName"]) && !empty($_POST["newName"])) {
            $path = $_POST["path"];
            $newName = $_POST["newName"];
            $splitter = "/";

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

        }
    });

    checkRoute('POST', '/api/folders/verification' , function() {
        redirectNotLogin();
        redirectIfNotAdmin();
        
        $_POST = json_decode(file_get_contents("php://input"), true); //because of axios

        if (isset($_POST["path"]) && !empty($_POST["path"]) && isset($_POST["type"]) && !empty($_POST["type"])) {
            $type = $_POST["type"];
            $path = $_POST["path"];
            $splitter = "/";

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
            
            if ($type == "exist") {
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
            } else if ($type == "write") {
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
            } else if ($type == "read") {
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
            } else if ($type == "delete") {
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
            } else if ($type == "htaccess") {
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
?>