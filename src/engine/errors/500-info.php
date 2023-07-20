<?php
    //set response code to 500
    http_response_code(500);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Error Debug</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/gh/K-cermak/Enplated-Framework@enp-v3/enp-data/darkmode.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mt-4">500 Error Debug</h1>
                <div class="row">
                    <div class="col-12">
                        <hr>
                        <h2>Debug info:</h2>
                        <hr>
                        <div class="row ms-3">
                            <div class="col-12">
                                <h3>Error code:</h3>
                                <p class="ms-3"><?php echo $_ENV['ERROR']['code']; ?></p>
                                
                                <div class="accordion ms-3" id="accordionFlushExample">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="flush-headingOne">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne" aria-expanded="false" aria-controls="flush-collapseOne">Error Codes Table:</button>
                                        </h2>
                                        <div id="flush-collapseOne" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body">
                                                <table class="table table-striped table-dark">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">Code</th>
                                                            <th scope="col">Description</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <th scope="row">1</th>
                                                            <td>E_ERROR</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">2</th>
                                                            <td>E_WARNING</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">4</th>
                                                            <td>E_PARSE</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">8</th>
                                                            <td>E_NOTICE</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">16</th>
                                                            <td>E_CORE_ERROR</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">32</th>
                                                            <td>E_CORE_WARNING</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">64</th>
                                                            <td>E_COMPILE_ERROR</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">128</th>
                                                            <td>E_COMPILE_WARNING</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">256</th>
                                                            <td>E_USER_ERROR</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">512</th>
                                                            <td>E_USER_WARNING</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">1024</th>
                                                            <td>E_USER_NOTICE</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">2048</th>
                                                            <td>E_STRICT</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">4096</th>
                                                            <td>E_RECOVERABLE_ERROR</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">8192</th>
                                                            <td>E_DEPRECATED</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">16384</th>
                                                            <td>E_USER_DEPRECATED</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">30719</th>
                                                            <td>E_ALL</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <h3>Error Message:</h3>
                                <p class="ms-3"><?php echo $_ENV['ERROR']['msg']; ?></p>
                            </div>
                            <div class="col-12">
                                <h3>File:</h3>
                                <?php
                                    $file = str_replace('\\', '/', $_ENV['ERROR']['file']);
                                ?>
                                <p class="ms-3"><?php echo $file ?></p>
                            </div>
                            <div class="col-12">
                                <h3>Line:</h3>
                                <p class="ms-3"><?php echo $_ENV['ERROR']['line']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <hr>
                        <h2>Code snippet:</h2>
                        <hr>

                        <p class="lead"><strong>File name:</strong> 
                        <?php
                            if (!isset($_ENV['APP']['BASE_DIRECTORY'])) {
                                echo $file;
                            } else {
                                //change \ to / if windows
                                echo str_replace($_ENV['APP']['BASE_DIRECTORY'], '', $file);
                            }
                        ?>
                        </p>
                        <div class="row ms-3">
                            <div class="col-12">
                                <pre class="bg-dark text-light p-3 codeBlock"><?php //php tag must be here for correct code allignment
                                        
                                        //check if file exists
                                        if (file_exists($file)) {
                                            $file = fopen($_ENV['ERROR']['file'], "r");

                                            $line = 0;
                                            while(!feof($file)) {
                                                $line++;
                                                $lineContent = fgets($file);
                                                //replace with html chars
                                                $lineContent = htmlspecialchars($lineContent);
                                                if($line == $_ENV['ERROR']['line']) {
                                                    echo "<span class='text-danger'>" . $line . " " . $lineContent . "</span>";
                                                } else {
                                                    echo $line . " " . $lineContent;
                                                }
                                            }
                                            fclose($file);
                                        } else {
                                            echo "File not found";
                                        }
                                    ?>
                                </pre>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row my-5">
                    <div class="col-12">
                        <hr>
                        <h2>All Configured Variables:</h2>
                        <hr>
                        <div class="row ms-3">
                            <div class="col-12 mt-3">
                                <h3>$_ENV</h3>
                                <pre class="ms-5 envVars"><?php print_r(isset($_ENV) ? $_ENV : "No _ENV data"); ?></pre>
                            </div>
                            <div class="col-12 mt-3">
                                <h3>$_SESSION</h3>
                                <pre class="ms-5"><?php print_r(isset($_SESSION) ? $_SESSION : "No _SESSION data"); ?></pre>
                            </div>
                            <div class="col-12 mt-3">
                                <h3>$_GET</h3>
                                <pre class="ms-5"><?php print_r(isset($_GET) ? $_GET : "No _GET data"); ?></pre>
                            </div>
                            <div class="col-12 mt-3">
                                <h3>$_POST</h3>
                                <pre class="ms-5"><?php print_r(isset($_POST) ? $_POST : "No _POST data"); ?></pre>
                            </div>
                            <div class="col-12 mt-3">
                                <h3>$_COOKIE</h3>
                                <pre class="ms-5"><?php print_r(isset($_COOKIE) ? $_COOKIE : "No _COOKIE data"); ?></pre>
                            </div>

                            <div class="col-12 mt-3">
                                <h3>$_REQUEST</h3>
                                <pre class="ms-5"><?php print_r(isset($_REQUEST) ? $_REQUEST : "No _REQUEST data"); ?></pre>
                            </div>

                            <div class="col-12 mt-3">
                                <h3>$_FILES</h3>
                                <pre class="ms-5"><?php print_r(isset($_FILES) ? $_FILES : "No _FILES data"); ?></pre>
                            </div>

                            <div class="col-12 mt-3">
                                <h3>$_SERVER</h3>
                                <pre class="ms-5"><?php print_r(isset($_SERVER) ? $_SERVER : "No _SERVER data"); ?></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        //text
        var text = document.querySelector(".envVars").innerHTML;
        //find all needle positions that contains PASS
        var needle = "PASS";
        var needlePositions = [];
        for (var i = 0; i < text.length; i++) {
            if (text.substr(i, needle.length) == needle) {
                needlePositions.push(i);
            }
        }

        //move position to the end of line
        var needlePositionsEnd = [];
        for (var i = 0; i < needlePositions.length; i++) {
            var position = needlePositions[i];
            var endOfLine = text.indexOf("=", position);
            needlePositionsEnd.push(endOfLine);
        }

        //replace all needle positions with *
        for (var i = 0; i < needlePositionsEnd.length; i++) {
            var position = needlePositionsEnd[i];
            var endOfLine = + text.indexOf("\n", position);
            text = text.substring(0, position) + "*".repeat(endOfLine - position) + text.substring(endOfLine);
        }

        //replace text
        document.querySelector(".envVars").innerHTML = text;
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html>