<?php

function cron_log($msg) {
    $file = ABSPATH . 'wp-content/log-cron.txt';

    if (!file_exists($file)) {
        $fh = fopen($file, 'w') or die("Can't create file");
        $current = $msg;
    }
    else {
        $current = file_get_contents($file);
        $current .= "\n" . $msg;
    }

    // Write the contents back to the file
    file_put_contents($file, $current);
    //}
}

include_once('classes/plugin.class.php');
include_once('classes/cron.class.php');