<?php
$state = $_POST['state'];
$filename = "/sys/class/gpio/gpio3/value";
$file = fopen($filename, "w") or die("Unable to open file!");
fwrite($file, $state);
fclose($file);
?>
