<?php
    header('Content-Type: text/html; charset=utf-8');
    session_start();
    require_once "settings.php";

    if (!isset($_SESSION["current_folder"])) {
        $_SESSION["current_folder"] = ROOT_FOLDER;
    }

    if ($_POST && isset($_POST["folder"])) {
        $_SESSION["current_folder"] = $_SESSION["current_folder"] . $_POST["folder"] . "/";
    }

    //GO BACK
    if (isset($_GET["goBack"])) {
        if ($_GET["goBack"] == "root") {
            $_SESSION["current_folder"] = ROOT_FOLDER;
        } else {
            $stepBack = $_GET["goBack"];
            $folders = explode("/", $_SESSION["current_folder"]);
            $folders = array_slice($folders, 0, count($folders) - $stepBack);
            $_SESSION["current_folder"] = implode("/", $folders) . "/";
        }
    }
    $protocol = $_SERVER["REQUEST_SCHEME"];
    $host = $_SERVER["HTTP_HOST"];
    $serverFolder = str_replace(SERVER_ROOT, "", $_SESSION["current_folder"]);
    $webUrl = $protocol . "://" . $host . "/" . $serverFolder;

    echo "<script>const currentFolder = '". $_SESSION["current_folder"] ."'; </script>";
    echo "<script>const webUrl = '" . $webUrl . "'; </script>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>

    <meta name="color-scheme" content="light dark">
    <link rel="icon" type="image/png" href="<?php echo FAVICON_LOCATION; ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-dark-5@1.1.3/dist/css/bootstrap-nightfall.min.css" rel="stylesheet" media="(prefers-color-scheme: dark)">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="partials/js/setup.js"></script>
    <script src="partials/js/fileInfo.js"></script>
    <link rel="stylesheet" href="partials/css/folders.css">
</head>
<body>
    <style>
        .row {
            margin: 0;
        }
        header {
            padding: 10px 0px 30px 0px;
        }
    </style>

    <header>
        <div class="row">
            <div class="col-md-12">
                <h1><?php echo APP_NAME; ?></h1>
            </div>
        </div>
    </header>

    <main>
        <div class="row">
            <div class="col-sm-9">
                <?php
                    echo messageChecker();
                ?>
                <div class="card">
                    <div class="card-body">
                        <div class="row card-title">
                            <div class="col-sm-9">
                            <?php
                                echo generatePath();
                            ?>
                            </div>
                            <div class="col-sm-3 text-end mt-1">
                                <i class="bi bi-upload mt-2 mx-2 uploadButton"></i>
                                <i class="bi bi-folder-plus mt-2 mx-2 addFolderButton"></i>
                                <i class="bi bi-arrow-clockwise mt-2 mx-2 refreshButton"></i>
                            </div>
                        </div>
                        <div>
                            <div class="row mt-3">
                                <?php
                                    echo getFolders();
                                    echo getFiles();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-sm-3 data">
                <div class="card">
                    <div class="card-body selectedInfo">
                        <h4 class="card-title">Selected file info:</h4>
                        <div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>


    <div class="modal fade" id="addFolderModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Create Folder in this directory</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form action="api.php" method="post">
                        <div class="form-group">
                            <label for="newFolderName">Folder name:</label>
                            <input type="text" class="form-control mt-2" id="newFolderName" name="newFolderName" placeholder="Folder name" required>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary mt-3">Create Folder</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="renameFolder">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Rename Folder</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form action="api.php" method="post">
                        <input type="hidden" class="form-control" id="renameOldName" name="renameOldName" placeholder="Folder name" required>

                        <div class="form-group">
                            <label for="renameNewFolderName">New Folder name:</label>
                            <input type="text" class="form-control mt-2" id="renameNewFolderName" name="renameNewFolderName" placeholder="Folder name" required>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary mt-3">Rename Folder</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteFolder">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Delete Folder</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p>Do you really want to delete folder <strong></strong>?</p>

                    <form action="api.php" method="post">
                        <input type="hidden" class="form-control" id="deleteFolderName" name="deleteFolderName" placeholder="Folder name" required>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-danger mt-3">Delete Folder</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="renameFile">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Rename File</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form action="api.php" method="post">
                        <input type="hidden" class="form-control" id="renameOldName" name="renameOldName" placeholder="File name" required>

                        <div class="form-group">
                            <label for="renameNewFileName">New File name:</label>
                            <input type="text" class="form-control mt-2" id="renameNewFileName" name="renameNewFileName" placeholder="File name" required>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary mt-3">Rename Folder</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteFile">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Delete File</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p>Do you really want to delete file <strong></strong>?</p>

                    <form action="api.php" method="post">
                        <input type="hidden" class="form-control" id="deleteFileName" name="deleteFileName" placeholder="File name" required>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-danger mt-3">Delete File</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="uploadFile">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Upload File</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <form action="api.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="uploadFile">Select file(s) to upload (max: <?php echo ini_get('upload_max_filesize') ?>B):</label>
                            <input type="file" class="form-control mt-2" id="uploadFile" name="uploadFile[]" multiple="multiple" required>
                        </div>

                        <div class="form-check mt-3">
                            <label class="form-check-label" for="uploadFileOverwrite">Overwrite file(s) if exists</label>
                            <input class="form-check-input" type="checkbox" id="uploadFileOverwrite" name="uploadFileOverwrite">
                        </div>
                        
                        
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary mt-3">Upload File(s)</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <?php
        function generatePath() {
            //count / in root folder
            $count = substr_count(ROOT_FOLDER, "/");

            //split $_SESSION["current_folder"] into array
            $path_array = explode("/", $_SESSION["current_folder"]);
            $path_array_length = count($path_array);
            $path_string = '<a href="?goBack=root"><button type="button" class="btn btn-secondary">' . DISK_NAME . '</button></a> / ';
            for ($i = $count; $i < $path_array_length; $i++) {
                if ($path_array[$i] != "") {
                    if ($i == $path_array_length - 2) {
                        $path_string .= '<button type="button" class="btn btn-secondary" disabled>' . $path_array[$i] . '</button> /';
                    } else {
                        $path_string .= '<a href="?goBack=' . ($path_array_length - $i - 1) . '"><button type="button" class="btn btn-secondary">' . $path_array[$i] . '</button></a> / ';
                    }
                }
            }
            return $path_string;
        }

        function getFolders() {
            $folders = array();
            $files = scandir($_SESSION["current_folder"]);
            foreach ($files as $file) {
                if ($file != "." && $file != ".." && is_dir($_SESSION["current_folder"]."/".$file)) {
                    array_push($folders, $file);
                }
            }

            //convert array to string
            $foldersString = "";
            foreach ($folders as $folder) {
                if ($folder != "." && $folder != "..") {
                    $foldersString .= '
                        <div class="card text-center folderDataFolder m-1" style="width: 8rem;">
                            <img class="card-img-top mx-auto" src="partials/icons/folder.svg" alt="Folder icon" style="max-width:4rem;">
                            <div class="card-body">
                                <h6>'. $folder . '</h6>
                            </div>

                            <form method="POST">
                                <input type="hidden" name="folder" value="'.$folder.'">
                                <button type="submit" class="btn btn-secondary submitButton" style="display:none;">Submit</button>
                            </form>
                        </div>
                    ';
                }
            }
            return $foldersString;
        }

        function getFiles() {
            $files = array();
            $files = scandir($_SESSION["current_folder"]);
            $filesString = "";
            foreach ($files as $file) {
                if ($file != "." && $file != ".." && !is_dir($_SESSION["current_folder"]."/".$file)) {
                    $filesString .= '
                        <div class="card text-center folderDataFile m-1" style="width: 8rem;">
                            <img class="card-img-top mx-auto" src="' . generateFileIcon($file) . '" alt="File icon" style="max-width:4rem;">
                            <div class="card-body">
                                <h6>'. $file . '</h6>
                            </div>
                        </div>
                    ';
                }
            }
            return $filesString;            
        }

        function generateFileIcon($file) {
            $folder = "partials/icons/";
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            do {
                if ($extension == "") {
                    $extension = "folder";
                    break;
                }
                $extension = strtolower($extension);
                if ($extension == "jpg" || $extension == "jpeg" || $extension == "png" || $extension == "gif") {
                    $folder .= "image.svg";
                    break;
                }
                if ($extension == "svg") {
                    $folder .= "svg.svg";
                    break;
                }
                if ($extension == "mp3" || $extension == "wav" || $extension == "flac") {
                    $folder .= "audio.svg";
                    break;
                }
                if ($extension == "mp4" || $extension == "avi" || $extension == "mkv") {
                    $folder .= "video.svg";
                    break;
                }
                if ($extension == "pdf") {
                    $folder .= "pdf.svg";
                    break;
                }
                if ($extension == "doc" || $extension == "docx") {
                    $folder .= "word.svg";
                    break;
                }
                if ($extension == "xls" || $extension == "xlsx") {
                    $folder .= "excel.svg";
                    break;
                }
                if ($extension == "ppt" || $extension == "pptx") {
                    $folder .= "powerpoint.svg";
                    break;
                }
                if ($extension == "zip" || $extension == "rar") {
                    $folder .= "zip.svg";
                    break;
                }
                if ($extension == "txt") {
                    $folder .= "txt.svg";
                    break;
                }
                if ($extension == "html") {
                    $folder .= "html.svg";
                    break;
                }
                if ($extension == "js") {
                    $folder .= "js.svg";
                    break;
                }
                if ($extension == "css") {
                    $folder .= "css.svg";
                    break;
                }
                if ($extension == "xml") {
                    $folder .= "xml.svg";
                    break;
                }
                if ($extension == "sql") {
                    $folder .= "sql.svg";
                    break;
                }
                if ($extension == "php") {
                    $folder .= "php.svg";
                    break;
                }
                if ($extension == "py") {
                    $folder .= "python.svg";
                    break;
                }
                if ($extension == "rb") {
                    $folder .= "ruby.svg";
                    break;
                }
                if ($extension == "java") {
                    $folder .= "java.svg";
                    break;
                }
                if ($extension == "c") {
                    $folder .= "c.svg";
                    break;
                }
                if ($extension == "cpp") {
                    $folder .= "cpp.svg";
                    break;
                }
                if ($extension == "cs") {
                    $folder .= "cs.svg";
                    break;
                }
                if ($extension == "h") {
                    $folder .= "h.svg";
                    break;
                }
                if ($extension == "hpp") {
                    $folder .= "hpp.svg";
                    break;
                }
                if ($extension == "json") {
                    $folder .= "json.svg";
                    break;
                }

                $folder .= "file.svg";
            } while (false);

            return $folder;
        }

        function messageChecker() {
            $message = "";
            if (isset($_GET["folderCreate"])) {
                if ($_GET["folderCreate"] == "ok") {
                    $message .= generateMessages("success", "Folder created successfully.");
                }
                if ($_GET["folderCreate"] == "error") {
                    $message .= generateMessages("warning", "Unknown error when creating folder.");
                }
                if ($_GET["folderCreate"] == "exist") {
                    $message .= generateMessages("warning", "Error: Folder already exists.");
                }
                if ($_GET["folderCreate"] == "prohibitedChars") {
                    $message .= generateMessages("warning", "Error: Prohibited characters in folder name or probibited name.");
                }
            }

            if (isset($_GET["folderRename"])) {
                if ($_GET["folderRename"] == "ok") {
                    $message .= generateMessages("success", "Folder renamed successfully.");
                }
                if ($_GET["folderRename"] == "error") {
                    $message .= generateMessages("warning", "Unknown error when renaming folder.");
                }
                if ($_GET["folderRename"] == "exist") {
                    $message .= generateMessages("warning", "Error: Folder already exists.");
                }
                if ($_GET["folderRename"] == "prohibitedChars") {
                    $message .= generateMessages("warning", "Error: Prohibited characters in folder name or probibited name.");
                }
            }

            if (isset($_GET["folderDelete"])) {
                if ($_GET["folderDelete"] == "ok") {
                    $message .= generateMessages("success", "Folder deleted successfully.");
                }
                if ($_GET["folderDelete"] == "error") {
                    $message .= generateMessages("warning", "Unknown error when deleting folder.");
                }
            }

            if (isset($_GET["fileRename"])) {
                if ($_GET["fileRename"] == "ok") {
                    $message .= generateMessages("success", "File renamed successfully.");
                }
                if ($_GET["fileRename"] == "error") {
                    $message .= generateMessages("warning", "Unknown error when renaming file.");
                }
                if ($_GET["fileRename"] == "exist") {
                    $message .= generateMessages("warning", "Error: File already exists.");
                }
                if ($_GET["fileRename"] == "prohibitedChars") {
                    $message .= generateMessages("warning", "Error: Prohibited characters in file name or probibited name.");
                }
            }

            if (isset($_GET["fileDelete"])) {
                if ($_GET["fileDelete"] == "ok") {
                    $message .= generateMessages("success", "File deleted successfully.");
                }
                if ($_GET["fileDelete"] == "error") {
                    $message .= generateMessages("warning", "Unknown error when deleting file.");
                }
            }

            if (isset($_GET["fileUpload"])) {
                $success = $_GET["success"];
                $error = $_GET["error"];
                $exist = $_GET["exist"];
                $prohibitedChars = $_GET["prohibitedChars"];

                if ($error == 0 && $exist == 0 && $prohibitedChars == 0) {
                    if ($success == 1) {
                        $message .= generateMessages("success", "1 File uploaded successfully.");
                    } else {
                        $message .= generateMessages("success", $success . " Files uploaded successfully.");
                    }
                } else {
                    $subMessage = "Error when uploading files. Number of successfully uploaded files: " . $success . " / ". $success + $error + $exist + $prohibitedChars .".<br><strong>Reason:</strong><br>";

                    if ($error > 0) {
                        $subMessage .= $error . " file(s) failed to upload.<br>";
                    }
                    if ($exist > 0) {
                        $subMessage .= $exist . " file(s) already exist.<br>";
                    }
                    if ($prohibitedChars > 0) {
                        $subMessage .= $prohibitedChars . " file(s) contain prohibited characters.<br>";
                    }

                    $message .= generateMessages("warning", $subMessage);
                }
            }

            return $message;
        }

        function generateMessages($type, $message) {
            return '<div class="alert alert-'.$type.' alert-dismissible fade show" role="alert">
                    '.$message.'
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
        }
    ?>

    <!-- DELETE ALL PARAMS-->
    <script>window.history.replaceState('', '', window.location.pathname);</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>