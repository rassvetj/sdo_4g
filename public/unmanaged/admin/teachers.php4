<?php

//defines
$include=TRUE ;
require ("setup.inc.php");
echo show_tb();
require ("adm_fun.php4");
require ("error.inc.php4");
$connect=get_mysql_base();
?>
<center>
<br><br><br>
<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" class=br  style="font-size:13px">
   <tr align="center">
      <td class=questt>
        <b><a href="teachers.php4"><?=_("Преподаватели")?></a></b>
          </td>
   </tr>
  </table>
</center>
<?
//create table



if (isset($ok) && isset($MID)) {
      $work->complete=1;
      $work->ok=1;
      $result=array("MID"=>htmlspecialchars($MID),
                              "LastName"=>htmlspecialchars($LastName),
                              "FirstName"=>htmlspecialchars($FirstName),
                              "EMail"=>htmlspecialchars($EMail),
                              "Patronymic"=>htmlspecialchars($Patronymic),
                              "Password"=>htmlspecialchars($Password),
                              "Login"=>htmlspecialchars($Login),
                               );                   
		$reg_form_items = explode(";", REGISTRATION_FORM);	                               
		$meta_information = "";
		foreach($reg_form_items as $key => $value) {
		  $meta_information .= "block=".$value."~";
		  $meta_information .= trim(set_metadata($_POST, get_posted_names($_POST), $value),"~");
		  $meta_information .= "[~~]";
		}
		$result['Information'] = trim($meta_information, "[~~]");

		if(count($_FILES) > 0) {
                      $fn="$tmpdir/asd";
                           move_uploaded_file($_FILES['photo'][tmp_name],$fn);
                           if (!file_exists($fn))
                              exit(_("Не удалось скопировать файл, нет прав записи в")." $tmpdir");
                           $buf=gf($fn);
                           $imsize=@getimagesize($fn);
                           if (!is_array($imsize) || count($imsize)<4 || $imsize[0]==0 && $imsize[1]==0)
                              exit(_("Загруженный файл не является картинкой GIF, JPG или PNG."));
                           @unlink($fn);
                             $data_buf = unpack("H*hex", $buf);
                             $res3 = sql("SELECT mid FROM filefoto WHERE mid=$MID","errFF001");
                           if($counter=sqlrows($res3)){                         
                                                      
                              if(dbdriver == "mssql")     
                                 $res_img="
                                        UPDATE filefoto SET
                                        last=".time().",
                                        fx='$imsize[0]',
                                        fy='$imsize[1]',
                                        foto=0x".$data_buf['hex']." 
                                        WHERE mid='$MID'";
                                                                
                              else
                            	$res_img="
                            	       UPDATE filefoto SET
                                        last=".time().",
                                        fx='$imsize[0]',
                                        fy='$imsize[1]',
                                        foto='0' 
                                        WHERE mid='$MID'";                            
                                             }     
                           else{
                              if(dbdriver == "mssql")     
                           	$res_img="
                           	INSERT INTO filefoto (mid, last, fx, fy, foto)
                            	VALUES (
                            	'$MID', 
                            	'".time()."', 
                            	'$imsize[0]', 
                            	'$imsize[1]', 
                            	0x".$data_buf['hex'].")";
                                                                 
                             else
                            	$res_img="
                            	INSERT INTO filefoto (mid, last, fx, fy, foto)
                            	VALUES (
                            	'$MID', 
                            	'".time()."', 
                            	'$imsize[0]', 
                            	'$imsize[1]', 
                            	'0')";                       
                                     }
                                     
         $res = sql($res_img,"errFF002");
         sqlfree($res);
         $table_f =(dbdriver == "mssql") ? "'filefoto'" : "filefoto";
         $buf =(dbdriver == "mssql") ? "0x".$data_buf['hex'] : $buf;
         $adodb->UpdateBlob($table_f, 'foto',$buf,"mid=".$GLOBALS['adodb']->Quote($MID)."");                                 
      }
      
      
      $res=sql_query(23,$result);

/*      $reg_form_items = explode(";", REGISTRATION_FORM);
      $meta_information = "";
      foreach($reg_form_items as $key => $value) {
              $meta_information .= trim(set_metadata($_POST, get_posted_names($_POST), $value),"~");
      }
      $meta_information = mysql_escape_string($meta_information);
      $query = "UPDATE People SET Information = '$meta_information' WHERE MID = $MID";
      $result = sql($query, "ssdfsdf");*/

}
if (isset($edit) && isset($HTTP_GET_VARS["MID"])) {
      $work->complete=0;
      $work->edit=1;
      $work->complete=edit_table("teachers.php4",$HTTP_GET_VARS["MID"]);
}
if (isset($del) && isset($HTTP_GET_VARS["MID"])) {
      $work->complete=0;
      $work->del=1;
      $work->complete=delete_from_teachers($HTTP_GET_VARS["MID"]);
      debug_yes("Num Rows 1",$work->complete);
}
if ($work->del && $work->complete) echo "<h1>"._("Удалено")."</h1>";
if (!isset($edit)) {
      $work->complete=0;
      $work->show=1;
      if (isset($order)){
              $res=sql_query(15,$order);
      }
      else {
              $res=sql_query(15);
      }

      $work->complete=generate_table("teachers.php4",$res);
   }
if (!$work->complete) show_error(1);
?>
</center>
<?php
echo show_tb();
?>