<?php

namespace Pianissimo\Component\Framework\PianoTuner;

class PianoTuner
{
    /**
     * All code below this line is temporary
     */
    private function pianoTuner(Response $response): string
    {
        $controllerInfo = (new ReflectionClass($response->getControllerClass()))->getShortName() . '::' . $response->getControllerFunction();
        $originInfo = $response->getRoute() ? $response->getRoute()->getName() : $controllerInfo;

        // Generate a hash to prevent outside manipulation/conflicts of/with the PianoTuner elements
        $hashToolbar = '_' . base_convert(md5(random_int(0,999)), 10, 36);
        $hashFunction = '_' . base_convert(md5(random_int(0,999)), 10, 36);

        $codeColor = '#6ab04c';
        if ($response->getStatusCode() !== 200) {
            $codeColor = '#eb4d4b';
        }

        $executionTime = round(((microtime(true) - $this->getStartTime()) * 1000), 0);

        return '
            <div id="' . $hashToolbar . '" style="font-family: Verdana; font-size: 14px; background: black; position: fixed; 
                bottom: 0; left: 0; right: 0; height: 40px; color: white; padding-right: 40px;">
                <div style="background: ' . $codeColor . '; float: left; height: 100%; padding: 10px;">' . $response->getStatusCode() . '</div>
                <div style="background: #1e272e; float: left; height: 100%; padding: 10px;">PianoTuner @' . $originInfo . '</div>
                <div style="background: #22a6b3; float: left; height: 100%; padding: 10px;">' . $executionTime . ' ms</div>
                <div style="background: #535c68; float: right; height: 100%; padding: 10px;">' . $_SERVER['REQUEST_METHOD'] . '</div>
            </div>
            <div style="background: #22a6b3; font-family: Verdana; width: 40px; height: 40px; vertical-align: middle;
                font-size: 20px; position: fixed; bottom: 0; right: 0; line-height: 35px; padding: 0px; cursor: pointer;
                color: white; text-align: center;" onclick="' . $hashFunction . '()">&#119070;</div>
            <script>
                if (typeof(Storage) !== "undefined" && localStorage.pianoTuner === "false") {
                    document.getElementById("' . $hashToolbar . '").style.display = "none";
                }
                    
                function ' . $hashFunction . '() {
                  const x = document.getElementById("' . $hashToolbar . '");
                  if (x.style.display === "none") {
                    x.style.display = "block";
                    
                    if (typeof(Storage) !== "undefined") {
                      localStorage.pianoTuner = true;
                    }
                  } else {
                    x.style.display = "none";
                    
                    if (typeof(Storage) !== "undefined") {
                      localStorage.pianoTuner = false;
                    }
                  }
                }
            </script>
        ';
    }
}
