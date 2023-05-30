<?php
header("Content-Type: text/xml; charset=utf-8");
$fname = 'data.xml';
echo file_get_contents($fname);
?>