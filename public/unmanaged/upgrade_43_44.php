<?php
include("1.php");
define("HARDCODE_WITHOUT_SESSION", true);
include "../../application/cmd/cmdBootstraping.php";


$config   = Zend_Registry::get('config');


$PATH = $config->path->upload->files;


$count = 0;
$errors = 0;

$res = sql("select f.* from list l 
inner join file f on f.kod=l.kod
inner join interview i on i.question_id=l.kod
/*where f.kod='424-3'*/
group by f.kod");

if(sqlrows($res)==0) die('Nothing to do!');

while ($row = sqlget($res)) {
$count++;

$fext = substr($row['fname'], strrpos($row['fname'], '.'));

echo "<hr>";
    $sql = "INSERT INTO files (name, path, file_size) VALUES ('{$row['fname']}', 'none', ".strlen($row['fdata']).");";
echo "{$sql}<br>";
    $res1 = sql($sql);
    $file_id = sqllast();
    if(!$file_id || $file_id==-1)
    {   echo "<h2>Can't insert into table 'files'<br>{$sql}</h2>";
        $errors++;
        continue;
    }

    $filePathName = $PATH.$file_id.$fext;
    if(file_exists($filePathName))
    {   echo "<h1>File already exist: {$filePathName}</h1>";
        $errors++;
        continue;
    }

    $fd = fopen($filePathName, "w");
    if(!$fd)
    {   echo "<h2>Can't write file: {$filePathName}</h2>";
        $errors++;
        continue;
    }
echo "Write file: {$filePathName}<br>";
    fwrite($fd, $row['fdata']);
    fclose($fd);

    $sql2 = "INSERT INTO list_files (file_id, kod) VALUES ('{$file_id}', '{$row['kod']}');";
echo "{$sql2}<br>";
    $res2 = sql($sql2);
/*
    $last_id = sqllast();
pr($last_id);
    if(!$last_id || $last_id==-1)
    {   echo "Can't insert into table 'list_files': {$sql2}<br>";
        $errors++;
        continue;
    }
*/

    $sql = "DELETE FROM file WHERE kod='{$row['kod']}' AND fdate='{$row['fdate']}' AND fname='{$row['fname']}'";
echo "{$sql}<br>";
    $res3 = sql($sql);
    if(!$res3)
    {   echo "<h2>Can't delete from table 'file'<br>{$sql}</h2>";
        $errors++;
        continue;
    }

}
die("FINISH OK. Total: {$count}; Errors: {$errors}");