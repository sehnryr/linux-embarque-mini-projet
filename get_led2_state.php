<?php
$filename = "/sys/class/leds/beaglebone:green:usr2/brightness";
$file = fopen($filename, "r") or die("Unable to open file!");
echo fread($file, filesize($filename));
fclose($file);
?>
