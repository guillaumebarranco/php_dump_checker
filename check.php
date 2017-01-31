<?php

$path = __DIR__.'/../../';
$files = getAllFiles($path);

$currentFile = $files[0];
$currentLine = 1;

foreach ($files as $file) {

    $currentFile = $file;

    $handle = fopen($path.$file, "r");

    $currentLine = 1;

    while (($line = fgets($handle)) !== false) {
        ++$currentLine;
        checkForVarDump($line);
    }

    fclose($handle);
}

echo "SUCCESS";

/*******************/
/*    FUNCTIONS    */
/*******************/

function getAllFiles($path) {

    $files = array();

    $appPath = "app/controllers";
    $ctrlPath = $path.'/'.$appPath;

    $folders = scandir($ctrlPath);

    foreach ($folders as $folder) {

        if($folder !== "." && $folder !== "..") {

            if(substr($folder, -4) === '.php') {

                $files[] = $appPath.'/'.$folder;

            } else {

                $flFiles = scandir($ctrlPath.'/'.$folder);

                foreach ($flFiles as $file) {

                    if($file !== "." && $file !== "..") {
                        $files[] = $appPath.'/'.$folder.'/'.$file;
                    }
                }
            }
        }
    }

    return $files;
}

function errorFound($line, $dump) {
    global $currentFile;
    global $currentLine;

    echo "Error in ".$currentFile." at line ".$currentLine."\n";
    echo "Found ".$dump;
    die;
}

function checkForDeb($line) {

    $dump = strpos($line, 'deb(');

    if(is_int($dump) && !dumpIsCommented($dump, $line)) { // Then we check if it is commented) {
        errorFound($dump, $line);
    }
}

function checkForVarDump($line) {

    $dump = strpos($line, 'var_dump(');

    if(is_int($dump)) { // If var_dump has been found

        if(!dumpIsCommented($dump, $line)) { // Then we check if it is commented

            errorFound($dump, $line);

        } else {
            checkForDeb($line);
        }

    } else {
        checkForDeb($line);        
    }        
}

function dumpIsCommented($dump, $line) {

    /*
        There are three case where a dump is excusable.
        Case 1 : When it is commented in code (by finding // before the dump)
        Case 2 : When there is a function statement before (example for function deb() in functions.php)
        Case 3 : When the dump is flagged as authorized (in function deb, the var_dump is normal so flagged //dump_authorized)
    */

    if($dump === 0) { // If the dump is first in line, we assert it is not commented
        return false;
    }

    $searchCommentInLine = strpos($line, "//");

    if(is_int($searchCommentInLine)) {

        if($searchCommentInLine < $dump) {
            return true;
        }
    }

    $searchIsFunction = strpos($line, "function");

    if(is_int($searchIsFunction)) {

        if($searchIsFunction < $dump) {
            return true;
        }
    }

    $searchAuthorizedDump = strpos($line, "//dump_authorized");

    if(is_int($searchAuthorizedDump)) {

        if($searchAuthorizedDump > $dump) {
            return true;
        }
    }

    return false;
}
