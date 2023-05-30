<?
   /*  create_indexing.php4
    *
    * Edit current module
    *
    * @autor Andy. (c) Hypermethod company.
    */

   // including Site settings
      require_once("dir_set.inc.php4");
      require_once("manage_course.lib.php4");
      require_once("defines.class.php4");

      $GLOBALS['controller']->setView('DocumentPopup');
      
      // require_once("deh.php4");

 //  echo "<pre>";print_r($_POST);echo "</pre>";

   // define
$refer="";

//if (isset($_SERVER["HTTP_REFERER"])) $refer=$_SERVER["HTTP_REFERER"];
//$pos = strpos($refer, $sitepath);
//if ($pos === false)
//   {
//      header("location: ".$sitepath);
//      exit();
//   }
echo "<!-- aoutirized -->";

   $def = new eLdef($_POST);
   $curdir="";

      function show_create_top()
      {         
      ?>

<tr>
<td align="center">
<?
      }
      function show_create_bottom()
      {
      ?>
</td>
</tr>
<?
      }

      function show_create_midle($dir = 0)
      {
          global $def, $val;
          $str = (($def->ModID == $val) && $dir) ? "style='background: #cccccc'" : "";
      ?>
</td>
<td align="left" width="100%" <?=$str?>>
<?
      }

      function show_create_checkbox($name)
      {
         global $use_file,$str_http,$modul;
         $strDir = dirname($name);
         $strDir = str_replace("\\", "", $strDir);
         $strDir = str_replace("/", "", $strDir);
      ?>
<INPUT type="checkbox" name="file[]" id="<?=$strDir?>[]" value="<?=$modul."/".$name?>" <? echo (in_array($name,$use_file)) ? "checked" : ""; ?> >
      <?
      }

      function show_create_filename($name)
      {
         echo $name;
      }



function show_dir($d) {
         global $nbsp;
         if (!isset($d))
              $d=realpath("./")."/";
         if ($d[strlen($d)-1]!="/")
             $d.="/";
         if (isset($files))
             unset($files);
         if (@is_dir($d)){
             $di=@dir($d);
             while ($name=$di->read()) {
                    if ($name=="." || $name=="..")
                        continue;
                    if (@is_dir($d.$name))
                        $files["1 $name"]=$name;
                    else
                        $files["2 $name"]=$name;
                    $ftype[$name]=@filetype($d.$name);
             } //while
             $di->close();
         }
         else {
               create_err_row();
         }
         if (isset($files)) {
             if(count($files)!=0) {
                ksort($files);
                foreach ($files as $k=>$v) {
                         $name=$d.$v;
                         switch($ftype[$v]) {
                                case "file":
                                      create_file_row(($d.$v));
//                                      create_file_row(($d.urlencode($v)));
                                break;
                                case "dir":
                                    $nbsp++;
                                    create_dir_row(($d.$v));
//                                    create_dir_row(($d.urlencode($v)));
                                    show_dir($d.urlencode($v));
                                    if ( $nbsp>0 ) $nbsp--;
                                break;
                         }       // swith --
                }       //foreach --
             }       //if count $files = 0
         } //if set $files
}   // empty_dir($d)

function create_file_row($file)
{
   global $str,$str_http;
   global $sitepath,$nbsp;
   $img="";
   for ($i=0;$i<$nbsp;$i++) $img.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
   $img.="<img src='".$sitepath."images/mod/file.gif'>";
   $name=str_replace($str,"",$file);
   show_create_top();
   show_create_checkbox(str_replace($str,"",$file));
   show_create_midle();
   show_create_filename($img." &nbsp;&nbsp;<a href='".$str_http.$name."' target='_blank'>".$name."</a>");
   show_create_bottom();
}

function create_dir_row($file)
{
   global $str;
   global $sitepath,$nbsp;

   $strDir = str_replace($str, "", $file);
   $strDir = str_replace("\\", "", $strDir);
   $strDir = str_replace("/", "", $strDir);

   echo "<script>arrTriggers['{$strDir}'] = true;</script>";

   $img="";
   for ($i=0;$i<$nbsp;$i++)
        $img.="&nbsp;&nbsp;";
//   $img.="<img src='".$sitepath."images/mod/dir.gif' width='12px' height='15px'>";
   $img .= (strlen($strDir)) ? "<a href=\"javascript:checkBoxes('{$strDir}');\"><img src='".$sitepath."images/mod/dir.gif' border=0></a>" : "<img src='".$sitepath."images/mod/dir.gif' border=0>";
   show_create_top();
   show_create_midle();
   show_create_filename($img."<span class=brdr><b>".str_replace($str,"",$file)."</b></span>");
   show_create_bottom();
}

function create_err_row()
{
   global $str;
   global $sitepath,$nbsp;

//   $file="Не создана директория. Вы можете зайти в свойства подмодуля для создания необходимой директории.";
   $file=_("не создана директория");
   $img="";
   for ($i=0;$i<$nbsp;$i++) $img.="&nbsp;&nbsp;";
// $img.="<img src='".$sitepath."images/mod/dir.gif' width='12px' height='15px'>";
   show_create_top();
   show_create_midle();
   show_create_filename($img.str_replace($str,"",$file));
   show_create_bottom();
}

function create_mod_row($name,$dir)
{
   global $sitepath,$nbsp;
   $img="";
   for ($i=0;$i<$nbsp;$i++) $img.="&nbsp;&nbsp;";
   $img.="<img src='".$sitepath."images/mod/dir.gif' >";
   show_create_top();
   show_create_midle(1);
   show_create_filename($img."<span id=createindex class='foldername'> <b>".$name."</b> - (".$dir.")</span>");
   show_create_bottom();
}

function read_module_content($modid,$table)
{
   $use_file=array();
   $sql="SELECT mod_l FROM ".$table." WHERE ModID='".$modid."'";
   $sql_result=sql($sql);
   if (sqlrows($sql_result)>0)
      while ($res=sqlget($sql_result)) {
        $arrPath = pathinfo($res['mod_l']);
        $use_file[] = $arrPath['basename'];
      }
      return $use_file;
}
   $nbsp=1;
   $mod_list=$def->return_mod_list();

   if ($def->make=="coolindexing" && (!empty($def->PID) || ($_SESSION['s']['perm'] > 2)) && !empty($def->b_userTeacher))
   {
      // ФАЙЛОВАЯ СТРУКТУРА
      if (isset($_POST['file']))
      {
         $use_file=read_module_content($def->ModID,$mod_cont_table);
            while (list($key,$val)=each($use_file))
            {
               if (!in_array(str_replace($def->return_http_path_allmod(),"",$val),$_POST['file']))
               {
                  $sql="DELETE FROM  ".$mod_cont_table." WHERE ModID='".$def->ModID."' AND mod_l='".$val."'";
                  $sql_result=@sql($sql);
               }
            }
         reset($use_file);
            while (list($key,$val)=each($_POST['file']))
            {
               if (!in_array($def->return_http_path_allmod().$val,$use_file))
               {
                  $sql="INSERT INTO ".$mod_cont_table." (Title,ModID,mod_l,type,conttype) VALUES ('".$def->valid_string($val)."','".$def->ModID."','".$def->get_path_to_mod().$def->valid_string($val)."','html','text/html')";
                  $sql_result=@sql($sql);
               }
            }
      }else{
         $sql="DELETE FROM  ".$mod_cont_table." WHERE ModID='".$def->ModID."'";
         $sql_result=sql($sql);
      }
      
      // XML 
      if (isset($_POST['link_delete'])) {
          foreach ($_POST['link_delete'] as $l_key=>$l_value) {
              $l_value = $adodb->Quote($l_value);
              $sql="DELETE FROM {$mod_cont_table} WHERE ModID='{$def->ModID}' AND mod_l={$l_value}";
              @sql($sql);
          }
      }
      
      if (isset($_POST['link']) && isset($_POST['link_title'])) {
          foreach ($_POST['link'] as $l_key=>$l_value) {
              $title = $adodb->Quote($_POST['link_title'][$l_key]);
              $mod_l = $adodb->Quote($_POST['link'][$l_key]);
              $sql="INSERT INTO ".$mod_cont_table." (Title,ModID,mod_l,type,conttype) VALUES ({$title},'{$def->ModID}',{$mod_l},'html','text/html')";
              $sql_result=@sql($sql);
          }
      }
      
      $GLOBALS['controller']->setMessage(_("Изменения сохранены"), JS_CLOSE_SELF_REFRESH_OPENER);
      echo "<h5>"._("Изменения сохранены")."</h5>";
   }

?>
<HTML>
<head>
<META content="text/html; charset=<?=$GLOBALS['controller']->lang_controller->lang_current->encoding?>" http-equiv="Content-Type">
<TITLE>eLearning Server 3000</TITLE>
<SCRIPT src="<?=$sitepath?>js/FormCheck.js" language="JScript" type="text/javascript"></script>
<SCRIPT src="<?=$sitepath?>js/img.js" language="JScript" type="text/javascript"></script>
<SCRIPT src="<?=$sitepath?>js/hide.js" language="JScript" type="text/javascript"></script>
<?php
$GLOBALS['controller']->captureFromOb(CONTENT);
?>
<script language="JavaScript">
arrTriggers = new Array();
function checkBoxes(name)
{
//        blabla
        for (i = 0; i < document.forms[0].elements.length; i++) {
                if (document.forms[0].elements[i].id.indexOf(name) == 0) {
                        document.forms[0].elements[i].checked = arrTriggers[name];
                }
        }
        arrTriggers[name] = !arrTriggers[name];
}
</script>
<title>eLearn Server 3000</title>
<link rel="stylesheet" href="<?=$sitepath?>styles/style.css" type="text/css">
</head>
<body>
<!--
<?php //echo $def->show_all_vars(); ?>
-->
<form action="<?=$sitepath?>teachers/create_index.php4" method="POST" target="_self">
    <input type="hidden" name="ModID" value="<?=$def->ModID?>">
    <input type="hidden" name="CID" value="<?=$def->CID?>">
    <input type="hidden" name="PID" value="<?=$def->PID?>">
    <input type="hidden" name="showfull" value="<?=$def->showfull?>">
    <input type="hidden" name="make" value="coolindexing">
<table width=100% class=main cellspacing=0>
         <tr>
         <th style="width: 50px;"><?=_("Вкл.")?></th>
         <th width="100%"><?=_("Загруженные файлы")?></th>
         </tr>
     <?
     if (!empty($def->b_userTeacher)) {
          $use_file=read_module_content($def->ModID,$mod_cont_table);
          while (list($key,$val)=each($mod_list)) {
                 $modul=$val;
                 create_mod_row($def->return_mod_name($val),$val);
                 $str=$def->return_path_to_mod($val);
                 $str_http=$def->return_http_path_mod($val);
                 if (!empty($str)) show_dir($str);
          }
     }
     ?>
</TABLE>
<br />
<table width=100% class=main cellspacing=0>
     <?
            $xml_filename = "{$_SERVER['DOCUMENT_ROOT']}/COURSES/course{$_POST['CID']}/course.xml";
            if (!file_exists($xml_filename)) {
                //echo "<tr><td>"._("Нет файла структуры курса")." {$xml_filename}.</td></tr>";
            }
            else {
                ?>
         <tr>
         <th style="width: 50px;"><?=_("Вкл.")?></th>
         <th width="100%"><?=_("Материалы курса")?></th>
         </tr>                
                <?
                $domxml_object = domxml_open_file($xml_filename);
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
    			                         $link = "/COURSES/course{$_POST['CID']}/index.htm?id=" . urlencode($attr->value);
    			                     break;
    			                         
    			                 }
    			                if ($attr->name == "title") {
    			                }
    				            if (($attr->name == "type") && ($attr->value == "lesson")) {
                                    $lesson = true;
    				            }
    			             }
    			             if ($lesson) {
    			                 $_sql = "SELECT * FROM mod_content WHERE ModID = '{$def->ModID}' AND mod_l LIKE '{$link}'";
    			                 $_res = sql($_sql);
    			                 $checked = sqlrows($_res) ? "checked" : "";
    			                 echo "<tr>
    			                       <td align=center>
    			                       <input type=checkbox name=link[] value=\"{$link}\" {$checked}/>
    			                       <input type=hidden name=link_delete[] value=\"{$link}\" >
    			                       <input type=hidden name=link_title[] value=\"{$title}\" >
    			                       </td>
    			                       <td valign=middle><img src='{$sitepath}images/mod/file.gif' >&nbsp;{$title}</td></tr>";
    			             }
                		}
    				}
    			}
    		}
                   
     ?>
</table>
<br />
<?=okbutton()?>
</form>
<?php
$GLOBALS['controller']->setHeader(_("Размещенные материалы"));
$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();
?>
</body>
</HTML>