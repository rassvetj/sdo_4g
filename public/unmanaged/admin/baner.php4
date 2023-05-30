<?php
   include_once("setup.inc.php");

   $html=show_tb(1);

   $str=loadtmpl("adm-images.html");
   $allcont=loadtmpl("adm-mail.html");
   $result="";
   $submit=(isset($_POST['submit'])) ? $_POST['submit'] : "";

   if ($submit) {
   for($i=1;$i<5;$i++) {
      if (is_uploaded_file($HTTP_POST_FILES['img'.$i]['tmp_name'])) 
      if (move_uploaded_file($HTTP_POST_FILES['img'.$i]['tmp_name'],$wwf."/images/index2/ind_pict".$i.".jpg")) $result[$i]=1;

      preg_match_all("~[a-z0-9._:/?&%\~`!@#\$^*()=+-]{2,100}~i",$_POST['link'.$i],$ok);
      $lnk="";
      $lnk=$ok[0][0];
      $lnk=trim($lnk);
      $res=sql("UPDATE OPTIONS SET value='".$lnk."' WHERE name='link".$i."'","err"); 
      }
       refresh("$PHP_SELF?$sess&result=".$result);



   }        
   
   

   $html=str_replace('[ALL-CONTENT]',$allcont,$html);
   $html=str_replace('[HEADER]',ph(_("Настройка центральных картинок")),$html);
   $html=str_replace('[ALL]',$str,$html);
   $html=str_replace('[RESULT]',"",$html);

   for($i=1;$i<5;$i++) {
      $lnk=getField("OPTIONS","value","name","link$i");
      $html=str_replace('[LINK'.$i.']',$lnk,$html);
   }


   printtmpl($html);
?>