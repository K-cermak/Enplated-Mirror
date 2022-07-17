<?php
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
?>