<?php
   /*  show_mod.php4
    *
    * Edit current module
    *
    * @autor Andy. (c) Hypermethod company.
    */

//  error_reporting(2047);

   // including Site settings
require_once("dir_set.inc.php4");
/*
      while (list($key,$val)=each($_POST))
         echo $key." - ".$val."<br>";
*/
function excludeDomainName( $url ){
  // исключает доменное имя из адреса
  $u = parse_url( $url );
/*Array
(
    [scheme] => http
    [host] => hostname
    [user] => username
    [pass] => password
    [path] => /path
    [query] => arg=value
    [fragment] => anchor
)*/
  return( $u[path].$u[query].$u[fragment]);

}

function prepare_offline_course_path($str) {
	return str_replace(array(" ", "\\"), array("%20", "/"), $str);
}

   $sql="SELECT type, mod_l, conttype FROM ".$mod_cont_table." WHERE ModID='".$_GET['ModID']."' AND McID='".$_GET['McID']."'";
// echo $sql;
// die();

   $sql_result=sql($sql);
   $res=sqlget($sql_result);
   
   $file = $res['mod_l'];
   $modType = $res['type'];
   $type = $res['conttype'];
   
   $flag=0;

   global $sitepath;

   echo "<html><head><TITLE>".APPLICATION_TITLE."</TITLE></head><body>";
   //<h3>Загрузка файла: <a href='$file'>$file</a><br><br>
   //<a href=javascript:window.close()>[закрыть окно]</a>";
   //if (strpos($type,'type') || strpos
   //   echo "<iframe src='$file'></iframe>
   //   <script>setTimeout('window.close()',3000);</script>";
   //else

//   $file = excludeDomainName( $file );
//   if( strstr($file, "http://"))
//   echo "<H1>".$sitepath.$file."</H1>";
   $arrTmp = parse_url($file);
   $file = $arrTmp['path'];
   $query = $arrTmp['query'];
   $strSlash = (!(strpos($file, '/')===0)) ? '/' : '';
   $strFullPath = $_SERVER['HTTP_HOST'].$strSlash.$file."?".$query;

   // offline browsing
   $sql = "SELECT CID FROM mod_list WHERE ModID = '".(int) $_GET['ModID']."'";
   $res = sql($sql);
   if (sqlrows($res)==1) {
   	
   		$row = sqlget($res);
   		   		
   		$CID = $row['CID'];
        
        /**
        * Сохранение статистики
        */
        $sql = "INSERT INTO mod_attempts (ModID,mid,start) 
                VALUES ('".(int) $_GET['ModID']."','".$GLOBALS['s']['mid']."',".$GLOBALS['adodb']->DBDate(time()).")";
        sql($sql);
   	
   }
   
   if ($s['offline_course_path'][$CID]) {
       $strFullPath .= '&basedir=file:///'.prepare_offline_course_path($s['offline_course_path'][$CID]);
   }

   if ((stristr($modType,'SCORM')!==false) || (stristr($modType,'AICC')!==false)) {
   
   $strFullPath .= '&aicc_sid='.(int) $_GET['McID'].'&aicc_url='.AICC_URL;
             
?>       
<html>
    <head>
    <TITLE><?=APPLICATION_TITLE?></TITLE>
    <script language="Javascript" type="text/javascript" src="<?=$sitepath.'scorm_api.php?ModID='.(int) $_GET['ModID'].'&McID='.(int) $_GET['McID']?>"></script>
    <script language="Javascript" type="text/javascript" src="<?=$sitepath.'lib/scorm/request.js'?>"></script>
    </head>
    <body style="margin=0px; padding: 0px;">
    <iframe name="main" width=100% height=100% src="<?=$GLOBALS['protocol']?>://<?=$strFullPath?>"></iframe>
    </body>
</html>
<?php
       
   } else {
       echo "<script>document.location.href='{$GLOBALS['protocol']}://{$strFullPath}'</script>";
       exit();       
   }
   
   exit();

// =======================================================================================================================

   if ($ec=implode("",file($file))) $flag=1;

//   if ($ec=implode("",file($file))) $flag=1;

   if ($res['type']=="html" && $flag)
      {
//       header("Content-type: ".$res['conttype']);

//       header("Host: ".$sitepath);
         header("Content-Location: ".dirname($file));
         header("Location: ".$file);
//       include($file);
        echo $file;
      }elseif ($res['type']=="file" && $flag)
      {
         header("Location: ".$file);
         //header("Content-type: ".$res['conttype']);
         //header("Content-Disposition: attachment; filename=".basename($res['mod_l']));
         //include($file);

      ?>
         <html>
         <head>
         <TITLE><?=APPLICATION_TITLE?></TITLE>
         </head>
         <body>
         <script>window.close()</script>
         </body>
         </html>
      <?

      }else
      {
      ?>
         <html>
         <body>
         <script>window.close()</script>
         </body>
         </html>
      <?
      //echo $file;
      }
// header("Content-Disposition: attachment; filename=".basename($res['mod_l']));

// echo $ec;

?>