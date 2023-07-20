<?php
    //set response code to 403
    http_response_code(401);
?>

<!DOCTYPE html>
<html>
    <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <title>401 Error</title>
      <meta name="robots" content="index, follow">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
      <link href="https://cdn.jsdelivr.net/gh/K-cermak/Enplated-Framework@enp-v3/enp-data/darkmode.min.css" rel="stylesheet">
    </head>

    <body>
        <div class='d-flex align-items-center justify-content-center vh-100' style='background-color: #222222;'>
            <div class='text-center'>
                <h1 class='display-1 fw-bold mb-4'>Error | <span class="text-danger">401</span></h1>
                <p class='lead'>You are not logged in.</p>
                
                <p class='lead mt-5'>Sorry, but you must use a secret URL to log in. This URL should be provided to you by your administrator or IT manager.</p>
                <p class='text-small'>If you are an administrator yourself and you don't know the path, go to the <strong>.env</strong> file in the root directory and find it in the <strong>LOGIN_URL</strong> variable.</p>
            </div>
        </div>
    </body>
</html>