<?php
     
$setup2=TRUE;
//$not=TRUE;
$include=TRUE;


     include('setup.inc.php');
     require ("create.php4");
     include('defines.inc.php4');
//     require ("adm_t.php4");
echo show_tb();
     require ("adm_fun.php4");

        
//echo "style.css";
 

      if (!isset($preview))
          {
        copy ($DIR_CSS."style.css",$DIR_CSS."style.css"."bak");
        copy ($DIR_CSS."style.css".".php4",$DIR_CSS."style.css".".php4"."bak");
           
        }

      include($DIR_CSS."style.css".".php4"."bak"); 

      
      require_once('fun.inc.'.$filesext);

      require_once('js.inc.'.$filesext);
     
      if (!empty($cssval)) 
        {  
         if (empty($element)) 
          { 
           $element=0; 
          }
      
        showjs($cssval,$element);  
        
?>
   <center>
   <table width='95%' align="center" border="0" cellspacing="1" cellpadding="5" class=br> 
   <tr align='center'>
     <td class=questt>
      <b><?=_("Мастер редактирования цветовой гаммы сервера")?></b>
   </td>
   </tr>
   <tr align='center'>
     <td bgcolor="white">

     <?php

       if ($element!=0) 
          { 
         
       
         $val=$cssval[$element];

         if ((!empty($val['type'])) && (!empty($val['name'])) && (!empty($val['coment'])))
              {
               echo $val['name']." - ".$val['coment'];
              }
          }
        
   $link="setup2.".$filesext;
       
     ?>
   </td>
   </tr>
   <tr align='center'>
     <td class=questt>
    <form name="list" method="POST" action="<?=$link?>">
   <table width='100%'>
     <tr>
        <th>
          <?=_("Выберете стиль для редактирования:")?>
        </th>
     </tr>
     <tr>
       <td>
          <select name="element" size="5" style="width:100%">
           <?php
              showlist($cssval); 
           ?>
          </select>
        </td>
     </tr>
     <tr>
        <td align=center>
       <?php
         if (isset($all))
          {
            echo "<input type='hidden' name='all' value='1'>";
          }
           else
          {
           $all=0;
          }
 
       ?>
      <input type="submit" name="Submit" value="<?=_("Показать этот стиль")?>" style="width:200px">
        </td>
     </tr>
   </table>
   </form>
   </td>
   </tr>

   <tr align='center'>
     <td bgcolor="white">

  <form name="mode" method="POST" action="setup2.<?php echo $filesext;?>">
   <table width='100%'>
       <?php
       editmode($all,$element);
      

   ?>
    </table>
   </form>
   </td>
   </tr>
   <tr align='center'>
     <td class=questt>
  <form name="master" method="POST" action="savecss.<?php echo $filesext;?>">
   <table width='100%'  bgcolor="white">
   <?php
     showtable($cssval,$element); 
     }
   ?>
    </table>
   <?php
           echo "<input type='hidden' name='element' value='$element'>";
   ?>
   <br>

   <input type="submit" name="preview" value="<?=_("Применить")?>">&nbsp;<input type="submit" name="save" value="<?=_("Сохранить")?>">&nbsp;<input type="reset" name="Reset" value="<?=_("Сбросить")?>" onclick="ResetF()">&nbsp;<input type="submit" name="delete" value="<?=_("Отменить")?>">
  </form>
   </td>
   </tr>
   <tr align='center'>
     <td bgcolor="white">
  <?php
     if (empty($cssval)) 
         { 
          echo "<b>"._("Ошибка чтения данных")."</b><br>";
         }

        
  
  ?>
   </td>
   </tr>
</table>
  </center>

<?

//require_once("adm_b.php4");
echo show_tb();
?>