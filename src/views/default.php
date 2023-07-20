<?php
    function processTemplate($templateFile, $templateVars) {
        $templatePath = __DIR__ . '/' . $templateFile . '.enp';
        if (file_exists($templatePath)) {
            $buffer = file_get_contents($templatePath);

            //DELETE ALL CONTENT BETWEEN {{-- AND --}}
            $buffer = preg_replace('/\{\{--.*--\}\}/sU', '', $buffer);

            //INCLUDE 
            foreach (explode("\n", $buffer) as $line) {
                //if line contain @include, get file name between ' or "
                if (str_contains($line, '@include')) {
                    //get file name between '
                    if (getStringBetween($line, "'", "'") != null) {
                        $includeFile = getStringBetween($line, "'", "'");
                    } else {
                        //get file name between "
                        $includeFile = getStringBetween($line, '"', '"');
                    }
                    $data = processTemplate($includeFile, $templateVars);
                    $buffer = str_replace($line, $data, $buffer);
                }
            }

            //  /{{\s*env\((.+)\)\s*}}/

            //PATTERNS
            $patterns = [
                '/{{\s*\$(.+?)\s*}}/',
                '/{\{\s*env\((.+?)\)\s*\}\}/',
                '/@php(.+?)@endphp/s',
                '/@if\s*\((.+)\)/',
                '/@elseif\s*\((.+)\)/',
                '/@else/',
                '/@endif/',
                '/@foreach\s*\((.+)\)/',
                '/@endforeach/',
                '/@for\s*\((.+)\)/',
                '/@endfor/',
            ];
            
            //replacement for each pattern
            $replacements = [
                '<?php echo htmlentities($$1$2); ?>',
                '<?php echo htmlentities($_ENV["APP"][$1]); ?>',
                '<?php $1 ?>',
                '<?php if ($1): ?>',
                '<?php elseif ($1): ?>',
                '<?php else: ?>',
                '<?php endif; ?>',
                '<?php foreach ($1): ?>',
                '<?php endforeach; ?>',
                '<?php for ($1): ?>',
                '<?php endfor; ?>',
            ];
            
            //replace patterns with replacements
            $buffer = preg_replace($patterns, $replacements, $buffer);

            //VARIABLS
            foreach ($templateVars as $key => $value) {
                $$key = $value;
            }
            
            if (isset($_ENV['APP']['DEBUG_BUFFER']) && $_ENV['APP']['DEBUG_BUFFER'] == true) {
                //create random file in /debug and save buffer to it
                $debugFile = __DIR__ . '/../debug/' . rand(100000, 999999) . '.php';
                file_put_contents($debugFile, $buffer);
            }

            //eval code to buffer, do not print
            $buffer = eval("ob_start(); ?>$buffer<?php return ob_get_clean();");

            return $buffer;

        } else {
            return false;
        }
    }

    function getStringBetween($string, $start, $end) {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    function resourceView($data, $defaultType) {
        //check if POST dataType include type
        $dataType = "";
        if (isset($_POST['dataType'])) {
            if ($_POST['dataType'] == 'json') {
                $dataType = 'json';
            } else if ($_POST['dataType'] == 'xml') {
                $dataType = 'xml';
            } else if ($_POST['dataType'] == 'csv') {
                $dataType = 'csv';
            } else {
                $dataType = 'json';
            }
        } else {
            $dataType = $defaultType;
        }

        if ($dataType == 'json') {
            header('Content-Type: application/json');
            echo json_encode($data);
        } else if ($dataType == 'xml') {
            header('Content-Type: application/xml');
            echo arrayToXml($data, false);
        } else if ($dataType == 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="data.csv"');
            echo arrayToCsv($data);
        }

        die();
    }
    
    function arrayToXml($array, $xml = false){
        if($xml === false){
            $xml = new SimpleXMLElement('<result/>');
        }
    
        foreach($array as $key => $value){
            if(is_array($value)){
                arrayToXml($value, $xml->addChild($key));
            } else {
                $xml->addChild($key, $value);
            }
        }
    
        return $xml->asXML();
    }

    function arrayToCsv($array) {
        $csv = "";
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $csv .= arrayToCsv($value);
            } else {
                $csv .= $key . ',' . $value . "\n";
            }
        }
        return $csv;
    }

    function finishRender($template) {
        echo $template;
        die();
    }
?>