<?php
function IsNullOrEmptyString($question)
{
    return (!isset($question) || strlen($question) == 0);
}

function exception_error_handler($errno, $errstr, $errfile, $errline)
{
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

function setTimezone($default) {
    $timezone = "";
   
    // On many systems (Mac, for instance) "/etc/localtime" is a symlink
    // to the file with the timezone info
    if (is_link("/etc/localtime")) {
       
        // If it is, that file's name is actually the "Olsen" format timezone
        $filename = readlink("/etc/localtime");
       
        $pos = strpos($filename, "zoneinfo");
        if ($pos) {
            // When it is, it's in the "/usr/share/zoneinfo/" folder
            $timezone = substr($filename, $pos + strlen("zoneinfo/"));
        } else {
            // If not, bail
            $timezone = $default;
        }
    }
    else {
        // On other systems, like Ubuntu, there's file with the Olsen time
        // right inside it.
        $timezone = trim(file_get_contents("/etc/timezone"));
        if (!strlen($timezone)) {
            $timezone = $default;
        }
    }
    date_default_timezone_set($timezone);
}
