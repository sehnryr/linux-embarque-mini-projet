<?php
$filename = "/sys/bus/iio/devices/iio:device0/in_voltage3_raw";
$file = fopen($filename, "r") or die("Unable to open file!");
echo fread($file, filesize($filename));
fclose($file);
?>
