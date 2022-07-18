<?php
    session_start();
    //Verify login!

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

        if (validateName($newName) == false) {
            header("Location: index.php?folderRename=prohibitedChars");
            die();
        }

        if (file_exists($_SESSION["current_folder"] . $newName)) {
            header("Location: index.php?folderRename=exist");
            die();
        }

        if (rename($_SESSION["current_folder"] . $oldName, $_SESSION["current_folder"] . $newName)) {
            header("Location: index.php?folderRename=ok");
            die();
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

    function deleteDataFolder($folderLocation) {
        $dir = opendir($folderLocation);
        while(($file = readdir($dir)) !== false) {
            if ($file != "." && $file != "..") {
                if (is_file($folderLocation . "/" . $file)) {
                    unlink($folderLocation . "/" . $file);
                } else if (is_dir($folderLocation . "/" . $file)) {
                    deleteDataFolder($folderLocation . "/" . $file);
                }
            }
        }
        rmdir($folderLocation);
        header("Location: index.php?folderDelete=ok");
        die();
    }
?>