<?php
$filename = "/sys/class/gpio/gpio50/value";
$file = fopen($filename, "r") or die("Unable to open file!");
echo fread($file, filesize($filename));
fclose($file);
?>
