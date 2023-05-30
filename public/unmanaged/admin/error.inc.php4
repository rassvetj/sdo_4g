<?php

   if (isset($error_inc_php4)) exit();
   
   $error_inc_php4=TRUE;
   
   $err[1]=_("Не указан MID пользователя либо база данных пустая");
   
   function show_error($res)
   {
      return true;
      global $err;
      echo "<b><font align=center color=red> "._("Внимание:")." </font>";
      echo $err[$res];
      echo "</b>";
   }

?>