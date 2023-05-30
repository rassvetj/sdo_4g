<?
require_once("1.php");
require_once('news.lib.php4');

echo show_tb();
?>
    <TABLE width="100%" border=0 cellspacing=0 cellpadding=0 class=skip>
   <tr><td width=1 height=15><img src="images/spacer.gif" width=1 height=1 alt=""></td></tr>

   <tr><td class=skip><table border=0 cellpadding=0 cellspacing=0 align=center width="525">
   <tr><td ><TABLE border=0 cellpadding=0 cellspacing=0 width="100%" class=cHilight><tr>
                        <td valign=top class=shedtitle><?=_("O сервере")?></td>
                        <td width="82%"><img src="images/spacer.gif" width=1 height=1 alt=""></td>
                        <td height=19 valign=bottom class=wingna style="font-size: 14px"><div><b>&#224;</b></div></td>
                  </tr>
                </TABLE>
   </td>
   </tr>
<tr><td width=1 height=15><img src="images/spacer.gif" width=1 height=1 alt=""></td></tr>


<tr><td>
<table width=80% border=0  cellspacing="0" cellpadding="0">
<tr><td class=schedule00>
<p align="left" class=title><img src="images/rule.gif" width="14" height="17">
     <B><FONT size="2" ><?=_("Администрация")?></FONT></B></p>

              <DIV align="left">
   <P><FONT size="2" class=cMain>
<?
  $sql="SELECT * FROM personal WHERE type='admin' ORDER by `PID` ASC";
  $res=sql($sql);
  while ($result=@sqlget($res))
  {
?>

<?
     echo "<b>".$result['FIO']."</b><br>";
     echo "".$result['work']."<br>";
     echo "тел. ".$result['tel']."<br>";
     echo "e-mail: ".$result['email'];

?>
   <br></p>
<?
}
?>
<p align="left" class=title><img src="images/rule.gif" width="14" height="17">
     <B><FONT size="2" ><?=_("Техническая поддержка")?></FONT></B></p>

              <DIV align="left">
   <P><FONT size="2" class=cMain>
<?
  $sql="SELECT * FROM personal WHERE type='tech' ORDER by `PID` ASC";
  $res=sql($sql);
  while ($result=@sqlget($res))
  {
?>

<?
     echo "<b>".$result['FIO']."</b><br>";
     echo "".$result['work']."<br>";
     echo "тел. ".$result['tel']."<br>";
     echo "e-mail: ".$result['email'];

?>
   <br></p>
<?
}

   echo show_info_block( 0, "[ALL-CONTENT]", "-~about~-"  );// выводит информацию блоками
?>
</td></tr>
</table>
</td></tr>


   </table>
   </td>
</tr>


   </table>

<?
echo show_tb();
?>