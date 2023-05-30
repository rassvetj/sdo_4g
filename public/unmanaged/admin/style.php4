<?php

$include=TRUE;

require ("setup.inc.php");
//require ("adm_t.php4");
echo show_tb();
require ("adm_fun.php4");


debug_yes("array",$HTTP_COOKIE_VARS);
debug_yes("array",$HTTP_GET_VARS);
debug_yes("array",$styles['1']);
debug_yes("array",$styles['2']);


$connect=get_mysql_base();

if (isset($HTTP_GET_VARS['change']))
        {
                if (isset($HTTP_GET_VARS['select']))
                {
                 if (@is_file("styles/".$HTTP_GET_VARS['select'].".css"))
                        {
                                $res=sql_query(2,$HTTP_GET_VARS['select']);
                                $result=@sqlget($res);
                                // Comment by DiMA 21.02.2003
                                //$copy=@copy("styles/".$HTTP_GET_VARS['select'].".css","../styles/style.css");
                        }else
                        {
                                $copy=FALSE;
                        }
                if (!$copy) echo "<center><h1>Not copy FILE</h1></center>";
                }
        }



$res=sql_query(1);
$result=@sqlget($res);

debug_yes("array",$result);

?>

        <script language="JavaScript">
         function selectT()
                {
                 document.templ.src='styles/'+document.forms['style'].select.value+'.jpg';
                };
        </script>


<center>
<form action="style.php4" method="get" name="style">
<table align="center" border="0" cellspacing="1" cellpadding="5" width="80%" class=br   style="font-size:13px">
        <tr align=center>
                <td class=questt>
        <b> <?=_("Выберите шаблон для сервера studium:")?> </b>
                </td>
        </tr>
        <tr align=center>
                <td bgcolor="white">
        <select name="select" size="2" onchange="selectT()" style="width:250px">
<?
                while (list($key,$value)=each($styles))
                        {
                        echo "<option value=".$value['sname'];
                        if ($value['sname']==$result['value']) echo " selected";
                        echo ">".$value['coment']."</option>\n";
                        }
?>
<!--      <option value="new">New template..</option> -->
        </select>
                </td>
        </tr>
        <tr align=center>
                <td class=questt>
                        <img src="styles/<?=$result['value']?>.jpg" name="templ">
                </td>
        </tr>
        <tr align=center>
                <td bgcolor="white">
                <input type="submit" name="change" value="<?=_("Изменить")?>" style="width:250px;border-width:1px; color : Black;height : auto;">
                </td>
        </tr>
        <tr align=center>
                <td class=questt>
        <input type=button onclick="location.reload();" value='<?=_("Обновить")?>'>
                </td>
        </tr>
        </table>
        </form>
<!-- <form action="design.php4" method="get" name="back">
<table align="center" border="0" cellspacing="15" cellpadding="5" width="100%">
        <tr align=center>
                <td>
                <input type="submit" name="change" value="back" style="width:250px;border-width:1px ;color : Black;">
                </td>
        </tr>
</table>
</form> -->
</center>


<?php

//require_once("adm_b.php4");
echo show_tb();
?>