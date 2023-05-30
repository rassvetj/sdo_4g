<?php
   /*  show_mod_struct.php4
    *
    * Отображает структуру КУРСА
    *
    * @autor DK. (c) Hypermethod company.
    */

      require_once("dir_set.inc.php4");


   $sql="SELECT type,mod_l,conttype FROM ".$mod_cont_table." WHERE ModID='".$_GET['ModID']."' AND McID='".$_GET['McID']."'";

   $sql_result=sql($sql);
   $res=sqlget($sql_result);
   $file=$res['mod_l'];
   $type=$res['conttype'];
   $flag=0;

   echo "<html><head><TITLE>".APPLICATION_TITLE."</TITLE></head><body>";
   echo "<script>location.href='$file'</script>";

   exit();

   if ($ec=implode("",file($file))) $flag=1;

   if ($ec=implode("",file($file))) $flag=1;

   if ($res['type']=="html" && $flag)
      {
//       header("Content-type: ".$res['conttype']);

//       header("Host: ".$sitepath);
         header("Content-Location: ".dirname($file));
         header("Location: ".$file);
//       include($file);

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