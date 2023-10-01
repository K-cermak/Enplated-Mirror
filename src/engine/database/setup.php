<?php
    connectToDb("sqlite", __DIR__ . "/db.sqlite");

    function connectToDb($dbConnEnv, $dbPath) {
        $conn = new PDO("sqlite:" . $dbPath);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //enabele foreign keys
        $conn->exec("PRAGMA foreign_keys = ON;");
        if (!$conn) {
            die('<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Database Error</title><meta name="robots" content="index, follow"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous"><link href="https://cdn.jsdelivr.net/gh/K-cermak/Enplated-Framework@enp-v3/enp-data/darkmode.min.css" rel="stylesheet"></head> <body><style>body{height:80vh}.container{height:100%}</style> <div class="container d-flex align-items-center justify-content-center"> <div class="row"> <div class="col-12"> <div class="alert alert-danger text-center" role="alert"> <h4 class="alert-heading mt-4">Database Error</h4> <p>Unfortunately, it was not possible to connect to the database.<br>Please try again later. Sorry for the complications.</p> </div> </div> </div> </div> </body></html>');
        }

        $_ENV["DATABASES"][$dbConnEnv] = $conn;
    }
?>