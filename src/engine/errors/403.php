<?php
    //set response code to 403
    http_response_code(403);
?>

<!DOCTYPE html>
<html>
    <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <title>403 Error</title>
      <meta name="robots" content="index, follow">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
      <link href="https://cdn.jsdelivr.net/gh/K-cermak/Enplated-Framework@enp-v3/enp-data/darkmode.min.css" rel="stylesheet">
    </head>

    <body>
        <div class='d-flex align-items-center justify-content-center vh-100' style='background-color: #222222;'>
            <div class='text-center'>
            <h1 class='display-1 fw-bold mb-4'>Error | <span class="text-info">403</span></h1>
                <p class='lead'>You have been denied access to this page or resource.</p>
            </div>
        </div>
    </body>
</html>