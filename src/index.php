<?php
    date_default_timezone_set('Europe/Prague');
    session_start();

    /*BASIC*/
        require_once __DIR__ . '/engine/init/errorHandler.php';
        require_once __DIR__ . '/engine/init/envParser.php';
        require_once __DIR__ . '/engine/init/helper.php';

    /*DATABASE*/
        require_once __DIR__ . '/engine/database/setup.php';

    /*MODELS*/
        require_once __DIR__ . '/models/default.php';

    /*VIEWS*/
        require_once __DIR__ . '/views/default.php';

    /*ROUTES*/
        require_once __DIR__ . '/engine/init/request.php';
        require_once __DIR__ . '/routes/default.php';
        require_once __DIR__ . '/engine/errors/404.php';
?>