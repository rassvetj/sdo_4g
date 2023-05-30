<?php
require_once("1.php");

$bid = $_REQUEST['bid'];
$dir = $_REQUEST['dir'];

//$materials = array();
$files = array();
$dirs = array();

$cid = getField('library','cid','bid',$bid);
if (isset($_GET['cid'])) $cid = (int) $_GET['cid'];
$path = $wwf."/COURSES/course$cid";
/*
$res = sql("SELECT * FROM library WHERE cid='$cid'");
while ($row = sqlget($res)) {
    $materials[$row['bid']] = $row;
}

$res = sql("SELECT * FROM library WHERE is_active_version = '1' AND parent IN ('".implode("', '",array_keys($materials))."')");
while ($row = sqlget($res)) {
    $files[$row['bid']] = $row;
}

foreach ($files as $bid=>$file) {
    $materials[$file['parent']]['filename'] = $file['filename'];
}
*/

//проверим не пытается ли кто-то подняться выше чем положенно
$dir = (count(array_keys(explode('/',$dir), '..'))>1 || $dir=='/..' || $dir=='/../')?'':$dir;
$path .= $dir;

$units = scandir($path);
foreach ($units as $unit) {
    if ($unit == '.') continue;
    if (is_dir($path.'/'.$unit)) {
        $dirs[] = $unit;
    }else {
        $files[] = $unit;
    }
}

$GLOBALS['controller']->setView('DocumentPopup');
$GLOBALS['controller']->setHeader(_("Размещенные материалы"));
$GLOBALS['controller']->captureFromOb(CONTENT);

echo "<script type='text/javascript'>
        function sendRes(obj) {
            window.opener.document.getElementById('book_file_name').value = obj.innerHTML;
            window.opener.document.getElementById('book_file_path').value = obj.title;
            window.close();
        }

        function openDir (obj) {
            crntDir = '$dir';
            document.getElementById('dir').value = crntDir + '/' + obj.innerHTML;
            document.getElementById('dirForm').submit();
        }
      </script>";

echo "<form id='dirForm' action='' method='post'>
          <input name='dir' id='dir' type='hidden' />
          <input name='bid' type='hidden' value='$bid' />
      </form>

      <table width=100% class=main cellspacing=0>
        <tr>
            <!--th>#</th-->
            <!--th>Материал</th-->
            <th>"._("Имя файла")."</th>
        </tr>";
/*
foreach ($materials as $bid=>$material) {
    echo "<tr>
            <td>$bid</td>
            <td>{$material['title']}</td>
            <td><span onClick='sendRes(this);' style='cursor:pointer;' title='{$material['filename']}'>".substr($material['filename'], strrpos($material['filename'],'/')+1)."</span></td>
          </tr>";
}
*/
foreach ($dirs as $file) {
    echo "<tr>
            <td>
                <img src='{$sitepath}images/mod/dir.gif' >&nbsp;<span onClick='openDir(this);' style='cursor:pointer;'>$file</span></td>
          </tr>";
}
foreach ($files as $file) {
    echo "<tr>
            <td>
                <img src='{$sitepath}images/mod/file.gif' >&nbsp;<span onClick='sendRes(this);' style='cursor:pointer;' title='/COURSES/course$cid{$dir}/$file'>$file</span></td>
          </tr>";
}

echo "</table>";

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();







//from 3.2

/*
 $domxml_object = domxml_open_file($path.'/course.xml');
$elements_array = $domxml_object->get_elements_by_tagname("item");
if (is_array($elements_array)) {
    foreach ($elements_array as $element) {
        $attrs = $element->attributes();
        if(is_array($attrs)) {
            $lesson = false;
            $title = "";
            $lesson = "";
            foreach ($attrs as $attr) {
                switch ($attr->name) {
                    case "title":
                        $title = _("Материал:")." ".iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding,$attr->value);
                        break;
                    case "type":
                        if ($attr->value == "lesson") {
                            $lesson = true;
                        }
                        break;
                    case "DB_ID":
                        $link = "/COURSES/course$cid/index.htm?id=" . urlencode($attr->value);
                        break;

                }
                if ($attr->name == "title") {
                }
                if (($attr->name == "type") && ($attr->value == "lesson")) {
                    $lesson = true;
                }
            }
            if ($lesson) {
                //echo "<img src='{$sitepath}images/mod/file.gif' >&nbsp;{$title}<br />";
                $materials[] = array('link'=>$link, 'title'=>$title);
            }
        }
    }
}
 */
?>