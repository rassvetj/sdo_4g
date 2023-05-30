<?
$filename = $_GET['file'];
if (preg_match('/^[0-9a-zA-Z_-]+\\.tpl$/',$filename)){
    if (!file_exists($filename)){
        $filename = "../not_available.inc.tpl";
    }
    $f = fopen($filename,'r');
    echo fread($f, filesize($filename));    
}
?>