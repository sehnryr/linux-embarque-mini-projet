<?php
$state = $_POST['state'];
$filename = "/sys/class/leds/beaglebone:green:usr2/brightness";
$file = fopen($filename, "w") or die("Unable to open file!");
fwrite($file, $state);
fclose($file);
?>
