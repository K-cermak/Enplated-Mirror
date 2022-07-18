<?php
    header('Content-Type: text/html; charset=utf-8');
    session_start();
    require_once "settings.php";

    if (!isset($_SESSION["accessCode"]) || $_SESSION["accessCode"] != ACCESS_CODE) {
        header("Location: login.php");
        die();
    }

    if ($_POST && isset($_POST["fileInfo"])) {
        //return json with file info
        $fileName = $_POST["fileInfo"];
        $file = fopen($fileName, "r");

        //get size
        $size = filesize($fileName);

        //get type
        $type = mime_content_type($fileName);

        //get name
        $name = basename($fileName);

        //get extension
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        //get date
        $date = date("Y-m-d H:i:s", filemtime($fileName));

        //get permissions
        $permissions = substr(sprintf('%o', fileperms($fileName)), -4);
        
        //return json
        echo json_encode(array(
            "name" => $name,
            "size" => $size,
            "type" => $type,
            "extension" => $extension,
            "date" => $date,
            "permissions" => $permissions,
        ));

        die();
    }

    if ($_POST && isset($_POST["folderInfo"])) {
        //return json with folder info
        $folderName = $_POST["folderInfo"];

        //get size
        $size = dirsize($folderName);
               
        //get name
        $name = basename($folderName);
        
        //get date
        $date = date("Y-m-d H:i:s", filemtime($folderName));
        
        //get permissions
        $permissions = substr(sprintf('%o', fileperms($folderName)), -4);

        //number of files in folder
        $files = calcNumberOfFiles($folderName);
        
        
        //return json
        echo json_encode(array(
            "name" => $name,
            "size" => $size,
            "date" => $date,
            "permissions" => $permissions,
            "files" => $files,
        ));
        
        die();
    }

    if ($_POST && isset($_POST["newFolderName"])) {
        if ($_POST["newFolderName"] != "") {
            $newFolderName = $_POST["newFolderName"];

            $newFolderName = cleanName($newFolderName);
            if (validateName($newFolderName) == false) {
                header("Location: index.php?folderCreate=prohibitedChars");
                die();
            }

            //check if folder exist
            if (file_exists($_SESSION["current_folder"] . $newFolderName)) {
                header("Location: index.php?folderCreate=exist");
                die();
            }

            //create folder
            if (mkdir($_SESSION["current_folder"] . $newFolderName)) {
                header("Location: index.php?folderCreate=ok");
                die();
            } else {
                header("Location: index.php?folderCreate=error");
                die();
            }
        
        }
        header("Location: index.php?folderCreate=error");
        die();
    }

    if ($_POST && isset($_POST["renameOldName"]) && $_POST["renameNewFolderName"]) {
        $oldName = $_POST["renameOldName"];
        $newName = $_POST["renameNewFolderName"];

        $newName = cleanName($newName);
        if (validateName($newName) == false) {
            header("Location: index.php?folderRename=prohibitedChars");
            die();
        }

        if (file_exists($_SESSION["current_folder"] . $newName)) {
            header("Location: index.php?folderRename=exist");
            die();
        }

        if (recurse_copy($_SESSION["current_folder"] . $oldName, $_SESSION["current_folder"] . $newName)) {
            if (deleteDataFolder($_SESSION["current_folder"] . $oldName, false)) {
                header("Location: index.php?folderRename=ok");
                die();
            } else {
                header("Location: index.php?folderRename=error");
                die();
            }
        } else {
            header("Location: index.php?folderRename=error");
            die();
        }


    }

    if ($_POST && isset($_POST["deleteFolderName"])) {
        $folderName = $_POST["deleteFolderName"];
        echo $_SESSION["current_folder"] . $folderName;

        deleteDataFolder($_SESSION["current_folder"] . $folderName);

        header("Location: index.php?folderDelete=error");
        die();
    }

    if ($_POST && isset($_POST["renameOldName"]) && $_POST["renameNewFileName"]) {
        $oldName = $_POST["renameOldName"];
        $newName = $_POST["renameNewFileName"];

        $newName = cleanName($newName);
        if (validateName($newName, false) == false) {
            header("Location: index.php?fileRename=prohibitedChars");
            die();
        }

        if (file_exists($_SESSION["current_folder"] . $newName)) {
            header("Location: index.php?fileRename=exist");
            die();
        }

        if (rename($_SESSION["current_folder"] . $oldName, $_SESSION["current_folder"] . $newName)) {
            header("Location: index.php?fileRename=ok");
            die();
        } else {
            header("Location: index.php?fileRename=error");
            die();
        }
    }

    if ($_POST && isset($_POST["deleteFileName"])) {
        $fileName = $_POST["deleteFileName"];
        echo $_SESSION["current_folder"] . $fileName;

        if (unlink($_SESSION["current_folder"] . $fileName)) {
            header("Location: index.php?fileDelete=ok");
            die();
        } else {
            header("Location: index.php?fileDelete=error");
            die();
        }
    }
   
    if ($_FILES) {
        $success = 0;
        $error = 0;
        $exist = 0;
        $prohibitedChars = 0;

        for ($i = 0; $i < count($_FILES["uploadFile"]["name"]); $i++) {
            $fileName = $_FILES["uploadFile"]["name"][$i];
            $fileTmpName = $_FILES["uploadFile"]["tmp_name"][$i];
            $fileError = $_FILES["uploadFile"]["error"][$i];
            $filePath = $_SESSION["current_folder"] . $fileName;

            if ($fileError == 0) {
                $fileName = cleanName($fileName);
                if (validateName($fileName, false) == false) {
                    $prohibitedChars++;
                }
                if (file_exists($filePath)) {
                    if (isset($_POST["uploadFileOverwrite"])) {
                        if (unlink($filePath) && move_uploaded_file($fileTmpName, $filePath)) {
                            $success++;
                            continue;
                        } else {
                            $error++;
                            continue;
                        }
                    } else {
                        $exist++;
                        continue;
                    }
                }
                
                if (move_uploaded_file($fileTmpName, $filePath)) {
                    $success++;
                    continue;
                } else {
                    $error++;
                    continue;
                }
            } else {
                $error++;
                continue;
            }
        }

        header("Location: index.php?fileUpload=completed&success=" . $success . "&error=" . $error . "&exist=" . $exist . "&prohibitedChars=" . $prohibitedChars);
        die();
    }

    //if no action is set, return to login
    header("Location: login.php");
    die();


    //***********************************************************
    //                    FUNCTIONS
    //***********************************************************
    function validateName($newName, $isFolder = true) {
        if (strpos($newName, "..") !== false) {
            return false;
        }

        if ($isFolder == true) {
            if (strpos($newName, ".") !== false) {
                return false;
            }
        } else {
            if ($newName == ".") {
                return false;
            }
        }

        if ($newName == "") {
            return false;
        }
        if (preg_match('/^\s*$/', $newName)) {
            return false;
        }
        //if contain / or \ or : or * or ? or " or < or > or |
        if (strpos($newName, "/") !== false || strpos($newName, "\\") !== false || strpos($newName, ":") !== false || strpos($newName, "*") !== false || strpos($newName, "?") !== false || strpos($newName, "\"") !== false || strpos($newName, "<") !== false || strpos($newName, ">") !== false || strpos($newName, "|") !== false) {
            return false;
        }
        return true;
    }

    function cleanName($name) {
        //delete spaces at the beginning and at the end of the name
        $name = trim($name);
        return $name;
    }

    function calcNumberOfFiles($folderName) {
        $files = 0;
        $dir = opendir($folderName);
        while(($file = readdir($dir)) !== false) {
            if ($file != "." && $file != "..") {
                if (is_file($folderName . "/" . $file)) {
                    $files++;
                } else if (is_dir($folderName . "/" . $file)) {
                    $files += calcNumberOfFiles($folderName . "/" . $file);
                }
            }
        }
        return $files;
    }

    function dirsize($folderName) {
        $size = 0;
        $dir = opendir($folderName);
        while(($file = readdir($dir)) !== false) {
            if ($file != "." && $file != "..") {
                if (is_file($folderName . "/" . $file)) {
                    $size += filesize($folderName . "/" . $file);
                } else if (is_dir($folderName . "/" . $file)) {
                    $size += dirsize($folderName . "/" . $file);
                }
            }
        }
        return $size;
    }

    function deleteDataFolder($folderLocation, $dieAfterDelete = true) {
        $dir = opendir($folderLocation);
        while(($file = readdir($dir)) !== false) {
            if ($file != "." && $file != "..") {
                if (is_file($folderLocation . "/" . $file)) {
                    unlink($folderLocation . "/" . $file);
                } else if (is_dir($folderLocation . "/" . $file)) {
                    deleteDataFolder($folderLocation . "/" . $file, false);
                }
            }
        }
        rmdir($folderLocation);

        if ($dieAfterDelete == true) {
            echo "stop";
            header("Location: index.php?folderDelete=ok");
            die();
        }
        return true;
    }

    function recurse_copy($src, $dst) { 
        $dir = opendir($src); 
        @mkdir($dst); 
        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) { 
                    recurse_copy($src . '/' . $file,$dst . '/' . $file); 
                } 
                else { 
                    copy($src . '/' . $file,$dst . '/' . $file); 
                } 
            } 
        } 
        closedir($dir); 
        return true;
    }
?>