<?php
    $str   = @file_get_contents('/proc/uptime');
    $num   = floatval($str);
    $secs  = fmod($num, 60); $num = intdiv($num, 60);
    $mins  = $num % 60;      $num = intdiv($num, 60);
    $hours = $num % 24;      $num = intdiv($num, 24);
    $days  = $num;

    echo $days . ' day(s) ' . $hours . ' hour(s) ' . $mins . ' minute(s) ';
?>