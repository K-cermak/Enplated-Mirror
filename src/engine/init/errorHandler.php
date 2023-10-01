<?php
    ini_set('display_errors', '0');

    function handle_fatal_error() {
        $error = error_get_last();
        if (is_array($error)) {
            $errorCode = $error['type'] ?? 0;
            $errorMsg = $error['message'] ?? '';
            $file = $error['file'] ?? '';
            $line = $error['line'] ?? null;

            if ($errorCode > 0) {
                handle_error($errorCode, $errorMsg, $file, $line);
            }
        }
    }

    function handle_error($code, $msg, $file, $line) {
        //if warning, notice or deprecated error, ignore it
        if ($code == E_WARNING) { //JUST BECAUSE PHP FTP LIBRARY IS CRAP
            return;
        }
        //delete all output buffers
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (isset($_ENV['APP']['PRODUCTION']) && $_ENV['APP']['PRODUCTION'] == false) {
            $_ENV['ERROR'] = [
                'code' => $code,
                'msg' => $msg,
                'file' => $file,
                'line' => $line
            ];
            require_once __DIR__ . '/../errors/500-info.php';
        } else {
            //show error page
            require_once __DIR__ . '/../errors/500.php';
        }
        die();
    }

    set_error_handler('handle_error');
    register_shutdown_function('handle_fatal_error');
?>