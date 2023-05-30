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

function show_create_top() {?>
         <tr>
          <td>
           <table width=100% class=br  cellspacing="0" cellpadding="0">
            <tr>
             <td width="20px" bgcolor="White">
<?
}

function show_create_bottom() {?>
         </td>
        </tr>
       </table>
      </td>
     </tr>
<?
}

function show_create_midle() {?>
         </td>
         <td align="left"  bgcolor="White" class="shedaddform">
<?
}

function show_create_checkbox($name) {
         global $use_file,$str_http,$modul;
         $strDir = dirname($name);
         $strDir = str_replace("\\", "", $strDir);
         $strDir = str_replace("/", "", $strDir);?>
         <INPUT type="checkbox" name="file[]" id="<?=$strDir?>[]" value="<?=$modul."/".$name?>" <? echo (in_array($str_http.$name,$use_file)) ? "checked" : ""; ?> >
<?
}

function show_create_filename($name) {
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
         if (@is_dir($d)) {
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
                                      create_file_row(($d.urlencode($v)));
                                break;
                                case "dir":
                                    $nbsp++;
                                    create_dir_row(($d.urlencode($v)));
                                    show_dir($d.urlencode($v));
                                    if ( $nbsp>0 ) $nbsp--;
                                break;
                         }
                }
             }       //if count $files = 0
         } //if set $files
}   // empty_dir($d)

function create_file_row($file) {
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

function create_dir_row($file) {
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

   $file=_("Не создана директория. Вы можете зайти в свойства подмодуля для создания необходимой директории.");
   $img="";
   for ($i=0;$i<$nbsp;$i++) $img.="&nbsp;&nbsp;";
// $img.="<img src='".$sitepath."images/mod/dir.gif' width='12px' height='15px'>";
   show_create_top();
   show_create_midle();
   show_create_filename($img.str_replace($str,"",$file));
   show_create_bottom();
}

function create_mod_row($name,$dir) {
   global $sitepath,$nbsp;
   $img="";
   for ($i=0;$i<$nbsp;$i++) $img.="&nbsp;&nbsp;";
   $img.="<img src='".$sitepath."images/mod/dir.gif' >";
   show_create_top();
   show_create_midle();
   show_create_filename($img."<span id=createindex class='foldername'> <b>".$name."</b> - (".$dir.")</span>");
   show_create_bottom();
}

function read_module_content($modid,$table) {
   $use_file=array();
   $sql="SELECT mod_l FROM ".$table." WHERE ModID='".$modid."'";
   $sql_result=sql($sql);
   if (sqlrows($sql_result)>0)
      while ($res=sqlget($sql_result))
         $use_file[]=$res->mod_l;
      return $use_file;
}
   $nbsp=1;
   $mod_list=$def->return_mod_list();

   if ($def->make=="coolindexing" && !empty($def->PID) && !empty($def->b_userTeacher))
   {
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
                  $sql="INSERT INTO ".$mod_cont_table." (Title,ModID,mod_l,type,conttype) VALUES ('".$def->valid_string($val)."','".$def->ModID."','".$def->return_http_path_allmod().$def->valid_string($val)."','html','text/html')";
                  $sql_result=@sql($sql);
               }
            }
      }else{
         $sql="DELETE FROM  ".$mod_cont_table." WHERE ModID='".$def->ModID."'";
         $sql_result=sql($sql);
      }
      echo "<h5>"._("Ссылки на модули были изменены")."</h5>";
   }

?>
<HTML>
<head>
<META content="text/html; charset=<?=$GLOBALS['controller']->lang_controller->lang_current->encoding?>" http-equiv="Content-Type">
<TITLE>eLearning Server 3000</TITLE>
<SCRIPT src="<?=$sitepath?>js/FormCheck.js" language="JScript" type="text/javascript"></script>
<SCRIPT src="<?=$sitepath?>js/img.js" language="JScript" type="text/javascript"></script>
<SCRIPT src="<?=$sitepath?>js/hide.js" language="JScript" type="text/javascript"></script>
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
<?= //$def->show_all_vars(); ?>
-->
<table width=95% border=0 cellspacing="1" cellpadding="0" align="center">
   <tr>
         <td>
            <table width=100% class=brdr  cellspacing="0" cellpadding="0">
                     <tr>
                  <th><span id=createindex class=brdr> &nbsp;&nbsp;<?=_("Выберите необходимые ресурсы")?></span></th>
                  </tr>
                 </table>
               </td>
     </tr>
     <form action="<?=$sitepath?>teachers/create_import_index.php4" method="POST" target="_self">
         <input type="hidden" name="ModID" value="<?=$def->ModID?>">
         <input type="hidden" name="CID" value="<?=$def->CID?>">
         <input type="hidden" name="PID" value="<?=$def->PID?>">
         <input type="hidden" name="showfull" value="<?=$def->showfull?>">
         <input type="hidden" name="make" value="import_indexing">
     <?
     $query = "SELECT mod_content.title as title, mod_content.mod_l as mod_l FROM mod_content INNER JOIN mod_list ON mod_list.ModID = mod_content.ModID
               WHERE mod_list.CID=".$def->CID." AND mod_l LIKE '%index.htm?id=%'";
     echo $query;
     $result = sql($query, "err select mod_content");
     while($row = sqlget($result)) {
           echo "<pre>";
           print_r($row);
           echo "</pre>";
     }

     /*if (!empty($def->b_userTeacher)) {
          $use_file=read_module_content($def->ModID,$mod_cont_table);
          while (list($key,$val)=each($mod_list)) {
                 $modul=$val;
                 create_mod_row($def->return_mod_name($val),$val);
                 $str=$def->return_path_to_mod($val);
                 $str_http=$def->return_http_path_mod($val);
                 if (!empty($str)) show_dir($str);
          }
     }*/
     ?>
               <tr>
               <td align="right" class="shedaddform">
               <input type="image" name="submit" onMouseOver="this.src='<?=$sitepath?>images/send_.gif';" onMouseOut="this.src='<?=$sitepath?>images/send.gif';" src="<?=$sitepath?>images/send.gif" align="right" alt="ok" border=0>
               </td>
            </tr>
      </form>
</TABLE>
</body>
</HTML>