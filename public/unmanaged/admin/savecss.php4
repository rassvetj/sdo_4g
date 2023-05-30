<?php

     
     include('setup.inc.php');
     include('defines.inc.php4');

     require_once('param.inc.'.$filesext);

     require_once('desc.inc.'.$filesext);

     function valid($value,$show)
     {
      reset($value);
      
      $ok=true;

      list($key, $val) = each($value);

//      if ($show) {echo "<tr><td>find errors ." ;};

      while (list($param) = each($value[$key]))
        {
         if ($val[$param]!='Ok') 
                {
//                if ($ok && $show) { echo "\n<br>Warning find error !!!<br>\n</td></tr> " ; };
                 if ($show) {showparamname($param,$val[$param]);};
                 $ok=false;
                }
/*                 else
                {
                 if ($ok && $show) { echo " . ";} ;
                };
*/
        };

//      if ($ok && $show) { echo "\n<br>No errors find :)\n <br> Save your settings <br></td></tr>";} ;

      return($ok);
     }

     function savecss($file,$str)
     {
      $fp = @fopen($file,"w"); 
      if ($fp) 
        {
          fputs  ($fp,"$str");
          fclose ($fp);   
        } else {
          echo _("Запись файла невозможна, проверьте есть ли у вас права на запись");
        }
     }

     function createstr($value,$valik)
     {
        reset($value);
        reset($valik);
        $str="<?php \n";
        list($flag, $val2) = each($valik);
        while (list($key, $val) = each($value))
                {
   
                 if ((!empty($val['type'])) && (!empty($val['name'])) && (!empty($val['coment'])))
                        {
                        switch ($val['type']) {
                                  case "class"       : $str.=saveclass($key,$value,$valik,$flag);   break;
                                  case "link"       :  $str.=saveclass($key,$value,$valik,$flag);   break;
                                  case "tag"       :   $str.=saveclass($key,$value,$valik,$flag);   break;
                        }
                        } 
        $str.="\n";     
                }

        $str.="?> \n";     
        return($str);
     }

     function createcss($value,$valik)
     {
        reset($value);
        reset($valik);
   global $DIR_CSS;
// $strcss=myfile($DIR_CSS."def.css");
        $strcss="@font-face { \nfont-family: Micra; \nfont-style: normal; \nfont-weight: normal; \nsrc: url(MICRA0.eot); }\n";
        list($flag, $val2) = each($valik);
        while (list($key, $val) = each($value))
                {

                 if ((!empty($val['type'])) && (!empty($val['name'])) && (!empty($val['coment'])))
                        {
                        switch ($val['type']) {
                                  case "class"       : $strcss.=gencss($key,$value,$valik,$flag,1);   break;
                                  case "link"       : $strcss.=gencss($key,$value,$valik,$flag,0);   break;
                                  case "tag"       : $strcss.=gencss($key,$value,$valik,$flag,0);   break;
                        }
                        }
                }

        return($strcss);
     }
     
     function saveclass($key,$value,$valik,$flag)
     {
        $val = $value[$key];

        $str="";

//        $str.="<?php\n";
        $str.="\$cssval[".$key."][name]='".$val['name']."';\n";
        $str.="\$cssval[".$key."][type]='".$val['type']."';\n";
        $str.="\$cssval[".$key."][coment]='".$val['coment']."';\n";
//        $str.="\n";
        if ($key!=$flag)
        {        
        while (list($param) = each($value[$key]))        
         {
             $strtemp=getstyle($param,$val[$param]);
             if (!empty($strtemp)){
   //                      $str.="<?php\n";
                         $str.="\$cssval";
                         $str.="[".$key."][".$param."]='".$val[$param]."';\n";
    //                     $str.="\n";
                         }
         }
        }else
        {
        $val2= $valik[$key];
         while (list($param) = each($valik[$key]))        
         {
             if (!empty($val2[$param])){
             $strtemp=getstyle($param,$val2[$param]);
             if (!empty($strtemp)){
//                         $str.="<?php\n";
                         $str.="\$cssval";
                         $str.="[".$key."][".$param."]='".$val2[$param]."';\n";
 //                        $str.="\n";
                         }
            }
         }
        }
        return($str);
     }

    function gencss($key,$value,$valik,$flag,$classs)
    {
        $val = $value[$key];

        $str="";

        $str.= ($classs) ? "." : "";
   $str.=$val['name']." {\n";
        if ($key!=$flag)
        {        
        while (list($param) = each($value[$key]))        
         {
             $strtemp=getstyle($param,$val[$param]);
             if (!empty($strtemp)){
                         $str.=$strtemp;
                         }
         }
        }else
        {
        $val2= $valik[$key];
         while (list($param) = each($valik[$key]))        
         {
             if (!empty($val2[$param])){
             $strtemp=getstyle($param,$val2[$param]);
             if (!empty($strtemp)){
                         $str.=$strtemp;
                         }
            }
         }
        }

        $str.="}\n";

        return($str);
    }

    $refresh=1 ;

    $link=HTTP_SITE."admin/setup2.".$filesext."?&element=".$element;

    if (!isset($delete))
     {
      if (!isset($error)) header("Location: setup2.php4");
     if (!valid($error,0)) 
        {
         $refresh=30   ;
        };
     
     if (!isset($save))
      {
        if (!isset($delete))
         {
          $link.="&preview=".$preview;
         }
      }
    }

?>
<html>
 <head>
   <title>HyperMethod Studium Design SetupSave</title>
   <meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
   <meta http-equiv="refresh" content="<?php echo $refresh ?>;URL=<?php echo $link ?>">
   <link rel="stylesheet" href="setup.css" type="text/css" >
 </head>
 <?php
   echo "<style>";

//      require_once("style.css"); 

      include($DIR_CSS."style.css"."bak"); 
      include($DIR_CSS."style.css".".php4"."bak"); 


   echo "</style>";
?> 
 <body>
     
  <?php

  if (!isset($delete))
    { 
      if (valid($error,0))
        {
         if (!isset($save))
                {
                 echo "<center><h1>"._("Применение Стиля")."</h1><br>";
                }
               else
                {
                 echo "<center><h1>"._("Запись Стиля")."</h1><br>";
                }
        }
        else
         {   
         echo "<center><h1>"._("Найдены ошибки, запись невозможна!")."</h1><br>";
         }
   }
   else
         {
          echo "<center><h1>"._("Восстановление данных")."</h1><br>";
         }
  
  ?>
   in progresss ....
  <table width='75%'>
   <?php
    
  if (!isset($delete))
   {
   if (!empty($cssval) && !empty($valik) && !empty($error)) {

        if (valid($error,1))
         {
           $str=createstr($cssval,$valik); 
           $strcss=createcss($cssval,$valik); 
          if (!isset($save))
           { 
           savecss($DIR_CSS."style.css".".php4"."bak",$str);
//           "style.css".="bak";
           savecss($DIR_CSS."style.cssbak",$strcss);
           }
           else
           {
                savecss($DIR_CSS."style.css".".php4",$str);
                savecss($DIR_CSS."style.css",$strcss);
           }
          if (!isset($save))
           {
               require_once('js.inc.'.$filesext); 
                ?>
                <script language=JavaScript>
                        function Open() {
                                
     //                           ww=window.open('<?=HTTP_SITE?>/test.html','PreviewWin','toolbar=1,resizable=1,directories=1,status=1,menubar=1,scrollbars=1');
     //                           ww.close();
     //                           ww=window.open('<?=HTTP_SITE?>/test.html','PreviewWin','toolbar=1,resizable=1,directories=1,status=1,menubar=1,scrollbars=1');
     //                           ww.focus();
                                }
                        Open();
                </script>
                <?php
           };
         
         };

        }
         else
        {
         echo "<tr><td>"._("Ошибка данных")."</td></tr>";
         echo $DIR_CSS."style.css".".php4"."bak";
         echo $cssval;
         echo $valik;
         echo $error;
        }
   }
   ?>



   </table>
  </center>
 </body>
</html>