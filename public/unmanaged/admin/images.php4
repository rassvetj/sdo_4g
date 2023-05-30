<?php

$include=TRUE;
        error_reporting(2039);

if (isset($_GET['ok'])) 
        {
                $image=substr($HTTP_GET_VARS['image'],3,48);
                $comp=@copy($HTTP_GET_VARS['imagenew'],$image);
                header("Location: images.php4?comp=".$comp."&image=".$HTTP_GET_VARS['image']);
                exit();
        } //if
                
                
require ("setup.inc.php");
//require ("adm_t.php4");

   echo show_tb();

require ("adm_fun.php4");


debug_yes("array",$HTTP_COOKIE_VARS);
debug_yes("array",$HTTP_GET_VARS);
debug_yes("array",$HTTP_POST_VARS);
if (isset($HTTP_POST_FILES['userfile'])) debug_yes("array",$HTTP_POST_FILES['userfile']);

if (isset($userfile)) debug_yes("file",$userfile);
if (isset($comp)) debug_yes("complete ",$comp) ;
?>
<center><br><br><br>
<?php 
        if (isset($comp))
                {
                        echo ($comp) ?  "<h3>"._("Файл успешно скопирован")."</h3><br>"._("Нажмите \"Обновить\"") : "<h3><font color=\"red\">"._("Ошибка: файл не скопирован")."</font></h3>";
                } //if isset $comp      
        
                                        
                                
?>      
        <form action="images.php4" target="_self" name="act" method="get">
                <table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" class=br style="font-size:13px">
                        <tr align="center">
                                <td  class=questt>
                                        <b>           </b>
                                </td>
                        </tr>
                                <tr align="center">
                                <td  bgcolor="white">
                                        <input type="submit" name="action" value="<?=_("Главная страница")?>" style="width:250px" onclick="get_image(1)">
                                </td>
                        </tr>
                        <tr align="center">
                                <td  class=questt>
                                        <input type="submit" name="action" value="<?=_("Верхнее меню")?>" style="width:250px" onclick="get_image(2)">
                                </td>
                        </tr>
                        <tr align="center">
                                <td  bgcolor="white">
                                        <input type="submit" name="action" value="<?=_("Все изображения")?>" style="width:250px"  onclick="get_image(3)">
                                </td>
                        </tr>
                        <tr align="center">
                                <td  class=questt>
        <input type=button onclick="location.reload();" value='<?=_("Обновить")?>' style="width:250px"> 
                <?php           
                if(!isset($image)) $image=""; 
                echo "<input type=\"hidden\" name=\"image\" value=\"".$image."\">"; 
                ?>              
                                </td>
                        </tr>
                </table>
                </form>
                <?php 


                if (!empty($image))
                                         { 
                                                $image=substr($image,3,48);
                                                if (@is_file($image))
                                                        {
                                
                ?>
<br><br>
                <table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" class=br style="font-size:13px">
                        <tr align="center">
                                <td  class=questt>
                                                <b>             </b>
                                </td>
                        </tr>
                        <tr align="center">
                                <td  bgcolor="white">
                                        <?=$image?>
                                </td>
                        </tr>
                        <tr align="center">
                                <td  class=questt>
                                        <?php echo "<img src=\"".$image."\">";?>
                                </td>
                        </tr>
                        <tr align="center">
                                <td  bgcolor="white">
                                                        .
                                </td>
                        </tr>
                        <tr align="center">
                                <td  class=questt valign='middle'><br>
                                <form enctype="multipart/form-data" action="images.php4" method="post">
                                <?php echo "<input type=\"hidden\" name=\"image\" value=\"../".$image."\">"; ?>
                                <input type="hidden" name="MAX_FILE_SIZE" value="1000000">
                                <?=_("Отправить файл:")?> <input name="userfile" type="file">
                                <input type="submit" value="<?=_("Отправить")?>" name="upload">
                                </form>
                                </td>
                        </tr>
                        <?php 
                        if(isset($upload)) 
                        {
                        if (is_uploaded_file($HTTP_POST_FILES['userfile']['tmp_name'])) 
                {
                        ?>
                        <tr align="center">
                                <td  bgcolor="white">
                                        <b>          </b>
                                </td>
                        </tr>           
                        <tr align="center">
                                <td  class=questt>
                                
                <?php 
                        clear_temp_dir();
                        if (($HTTP_POST_FILES['userfile']['type']=="image/gif") 
                        || ($HTTP_POST_FILES['userfile']['type']=="image/jpg") 
                        || ($HTTP_POST_FILES['userfile']['type']=="image/jpeg")
                        || ($HTTP_POST_FILES['userfile']['type']=="image/pjpeg") 
                                )
                        {
                        $in="temp/".$HTTP_POST_FILES['userfile']['name'];
                        @move_uploaded_file($HTTP_POST_FILES['userfile']['tmp_name'],$in);
                        echo "<img src=".$in.">";
                        ?>      </td>
                        </tr>
                        <tr align='center'>
                                <td  bgcolor='white'>
                                        <br>
                                        <form action="images.php4" method="get" target="_self" name="complete">
                                        <?php 
                                        echo "<input type=\"hidden\" name=\"image\" value=\"../".$image."\">"; 
                                        echo "<input type=\"hidden\" name=\"imagenew\" value=\"".$in."\">";                                     
                                        ?>
                                        <input type="submit" name="ok" value="<?=_("Загрузить сейчас")?>" style="width:250px">
                                        </form>
                                </td>
                        </tr>
                        <?php
                        }else
                        {
                        echo "<font color=red><b>Warning: Posible file attack</b></font></td></tr>";
                        } //end if
                        } // is uloaded files
                        } //isset upload
                        echo "</table>";
                                } //if file
                                        } // is empry dir
                ?>      


        
</center>

<?php

//require_once("adm_b.php4");
   echo show_tb();
?>