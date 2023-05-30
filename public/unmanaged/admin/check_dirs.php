<?
   include("../1.php");

   if (!$s[login] || $s[perm]!=4) exitmsg("Access denied","/?$sess");

$GLOBALS['controller']->captureFromOb(CONTENT);

   switch ($c) {

      case "":
         echo show_tb();
         echo ph(_("Администрирование"));
         echo "<a href=$PHP_SELF?c=go$sess>"._("Запустить процесс проверки прав...")." &gt;&gt;&gt;</a> ("._("может занять несколько секунд").")";
         echo show_tb();
         exit;


      case "go":

         $p="COURSES";
         chdir("../");
         echo "<table width=100% class=main cellspacing=0><tr><td><ul>";

         $res=sql("SELECT * FROM Courses","errCD23");
         $tmpfn="check_dirs_".md5(microtime()).".tmp";
         while ($r=sqlget($res)) {
            $cid=$r[CID];
            $dirs=array("$p/course$cid","$p/course$cid/mods","$p/course$cid/TESTS",
                        "$p/course$cid/TESTS_ANW","$p/course$cid/webcam_room_$cid");
            foreach ($dirs as $v) {
               @mkdir($v,0777);
               @chmod($v,0777);
               $tmp="$v/$tmpfn";
               if (!is_dir($v)) {
                  echo "<li>"._("Каталог")." <b>$v</b> "._("не удалось создать (проверьте права на каталог)");
                  //exit;
                  continue;
               }
               @touch($tmp);
               if (!file_exists($tmp)) {
                  echo "<li>"._("Каталог")." <b>$v</b> "._("не доступен на запись, не удалось создать временный файл")." <b>$tmp</b> "._("(проверьте права на каталог)");
                  //exit;
                  continue;
               }
               @unlink($tmp);
               if (file_exists($tmp)) {
                  echo "<li>"._("Не удается стереть временный файл")." <b>$tmp</b> "._("(проверьте права на каталог и файлы)");
                  //exit;
                  continue;
               }
            }
            echo "<li>"._("Каталоги курса")." N$cid "._("проверены")." ($r[Title])";
            flush();

            //exit;
         }
         // Проверка необходимых каталогов на чтение и запись
         $dirs = array('smarty/templates_c', 'laws', 'library', 'media', 'options', 'temp');
         if (is_array($dirs) && count($dirs)) {
             foreach($dirs as $dir) {
                 $tmp = $dir.'/'.$tmpfn;
                 $ok = true;
                 if (check_exists($dir)) {
                     if (check_write($tmp)) {
                         if (check_read($tmp)) {
                             if (!check_delete($tmp)) {
                                 // ошибка удаления файла в каталоге
                                 echo "<li><font color=red>Каталог <b>[$dir]</b>: не удалось удалить временной файл $tmp (проверьте права на каталог и файлы)</font>";
                                 $ok = false;
                             }
                         } else {
                             // ошибка чтения
                             echo "<li><font color=red>Каталог <b>[$dir]</b>: не удалось открыть временной файл $tmp, каталог не доступен на чтение (проверьте права на каталог и файлы)</font>";
                             $ok = false;
                         }
                     } else {
                         // ошибка записи
                         echo "<li><font color=red>Каталог <b>[$dir]</b>: не удалось создать временной файл $tmp, каталог не доступен на запись (проверьте права на каталог и файлы)</font>";
                         $ok = false;
                     }
                 } else {
                     // отсутствует каталог
                     echo "<li><font color=red>Каталог <b>$dir</b>: не удаётся создать каталог (проверьте права на каталог и файлы)</font>";
                     $ok = false;
                 }
                // if ($ok) echo "<li>" . printf(_("Каталог %s проверен"), $dir);
		 if ($ok) echo "<li>"._("Каталог "). $dir ._(" проверен");
                 echo '<br>';
                 flush();
             }
         }

         // проверка файлов на запись
         $files = array('zlog/php-error.log', 'template/all-mail.html');
         if (is_array($files) && count($files)) {
             foreach($files as $file) {
                 $fp = @fopen($file,'a+');
                // $message = "<li>".printf(_("Файл %s проверен"),$file) ;
		 $message = "<li>"._("Файл "). $file. _(" проверен");
                 if (!$fp) {
                     $message = "<li><font color=red>Файл <b>$file</b>: не удаётся открыть файл для записи или создать файл (проверьте права на каталог или файл)</font>";
                 }
                 @fclose($fp);
                 echo $message.'<br>';
             }
         }
		echo "</ul></td></tr></table>";

   }

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

function check_exists($dir) {
    @mkdir($dir,0777);
    @chmod($dir,0777);
    if (!is_dir($dir)) {
        return false;
    }
    return true;
}

function check_write($dir) {
    $tmp="$dir";
    @touch($tmp);
    if (!file_exists($tmp)) {
        return false;
    }

    return true;
}

function check_read($dir) {
    $tmp="$dir";
    $fp = @fopen($tmp,'r');
    if (!$fp) {
        return false;
    }
    @fclose($fp);
    return true;
}

function check_delete($dir) {
    $tmp="$dir";
    @unlink($tmp);
    if (file_exists($tmp)) {
        return false;
    }
    return true;
}

?>