<?php
    function modelCall($modelName, $modelFunction, $modelParams = []) {
        $modelPath = __DIR__ . '/'. $modelName . '.php';
        if (file_exists($modelPath)) {
            require_once $modelPath;
            if (function_exists($modelFunction)) {
                //create vars for each param
                foreach ($modelParams as $key => $value) {
                    $$key = $value;
                }
                return $modelFunction(...$modelParams);

            } else {
                return false;
            }
        } else {
            return false;
        }
    }
?>