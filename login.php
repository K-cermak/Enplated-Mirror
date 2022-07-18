<?php
    header('Content-Type: text/html; charset=utf-8');
    session_start();
    require_once "settings.php";

    //logout
    if (isset($_GET["logout"]) && $_GET["logout"] != "completed" && $_GET["logout"] != "accessChanged") {
        session_destroy();
        header("Location: login.php?logout=completed");
        die();
    }

    //if logged
    if (isset($_SESSION["accessCode"])) {
        if ($_SESSION["accessCode"] == ACCESS_CODE) {
            header("Location: index.php");
            die();
        } else {
            session_destroy();
            header("Location: login.php?logout=accessChanged");
            die();
        }
    }

    //login
    if ($_POST && isset($_POST["accessCode"])) {
        if ($_POST["accessCode"] == ACCESS_CODE) {
            $_SESSION["accessCode"] = ACCESS_CODE;
            header("Location: index.php");
            die();
        } else {
            header("Location: login.php?error=wrongCode");
            die();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>

    <meta name="color-scheme" content="light dark">
    <meta name="robots" content="noindex" />
    <link rel="icon" type="image/png" href="<?php echo FAVICON_LOCATION; ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-dark-5@1.1.3/dist/css/bootstrap-nightfall.min.css" rel="stylesheet" media="(prefers-color-scheme: dark)">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="partials/js/setup.js"></script>
    <script src="partials/js/fileInfo.js"></script>
    <link rel="stylesheet" href="partials/css/folders.css">
</head>
    <body class="d-flex flex-column min-vh-100">
        <?php
            if (isset($_GET["error"])) {
                if ($_GET["error"] == "wrongCode") {
                    echo generateMessages("danger", "Wrong Access Code.");
                }
            }
            if (isset($_GET["logout"])) {
                if ($_GET["logout"] == "completed") {
                    echo generateMessages("success", "Logout succesfull.");
                }
            }      
            if (isset($_GET["logout"])) {
                if ($_GET["logout"] == "accessChanged") {
                    echo generateMessages("warning", "Access Code changed. Please login again.");
                }
            }
        ?>
        
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3 mt-5">
                    <h1><?php echo APP_NAME; ?></h1>
                    <form action="?login" method="post">
                        <div class="form-group">
                            <label for="accessCode">Insert your Access Code:</label>
                            <input type="password" class="form-control" id="accessCode" name="accessCode" placeholder="Access Code">
                        </div>
                        
                        <button type="submit" class="btn btn-primary mt-3">Login</button>
                    </form>
                </div>
            </div>
        </div>

        <footer class="bg-dark text-lg-start mt-auto pt-2 pb-3">
            <div class="row">
                <div class="col text-start pt-3 customMessage">
                    <h4><?php echo FOOTER_MESSAGE; ?></h4>
                </div>
                <div class="col text-end">
                    <h4>This site is powered by <a href="https://github.com/K-cermak/Enplated-Mirror" target="_blank">Enplated Mirror</a> v1.0.</h4>
                    <h4>Developed by <a href="https://k-cermak.com" target="_blank">Karlosoft Group</a>.</h4>
                </div>
            </div>
        </footer>

        <?php
            function generateMessages($type, $message) {
                return '<div class="container mt-5"><div class="alert alert-'.$type.' alert-dismissible fade show" role="alert">
                        '.$message.'
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div></div>';
            }
        ?>

        <!-- DELETE ALL PARAMS-->
        <script>window.history.replaceState('', '', window.location.pathname);</script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>