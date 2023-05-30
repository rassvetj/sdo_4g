<?php
   require_once("1.php");
   if (!$teach) login_error();


   $ss="test_e1";


   if (!isset($s[$ss][cid]) || !isset($s[tkurs][$s[$ss][cid]])) {
      if ($c!="selectkurs" && $c!="selectkurs_submit") $c="selectkurs";
   }

   if (isset($_GET['CID'])) { $c="selectkurs_submit"; $newcid=$_GET['CID'];}



switch ($c) {

case "":

   echo show_tb();
   echo ph(_("Редактирование свойств курса"));
   echo "<a href=\"$PHP_SELF?".$sess."c=selectkurs\">"._("Выбрать курс")."</a><br><br>";

   echo "<a href=\"formula.php?".$sess."\">"._("Редактирование Формул")."</a><br>";
   echo "<a href=\"groups.php?".$sess."\">"._("Редактирование Груп учащихся")."</a><br>";
   $query = "SELECT TypeDes FROM Courses WHERE CID=".$s[$ss][cid];
   $result = sql($query,"err select TypeDes");
   $row = sqlget($result);
   if($row['TypeDes'] != 2) {
      echo "<a href=\"abitur.php4?CID=".$s[$ss][cid]."\">"._("Редактирование учащихся")."</a>";
   }
   echo show_tb();
   exit();

case "selectkurs":

   echo show_tb();
   echo ph(_("Выбор курса для редактирования формул"));
   echo "
   <form action=$PHP_SELF method=post name=m>$sessf
   <input type=hidden name=c value=\"selectkurs_submit\">
   <span class=\"tests\">"._("Выберите курс:")."</span><br><br>
   <select name=newcid size=14 style=\"width:100%\">";

   $res=sql("SELECT * FROM Courses WHERE cid IN (".implode(",",$s[tkurs]).") ORDER BY Title","errTT243");
   while ($r=sqlget($res)) echo "<option value=$r[CID]>$r[Title]".($s[usermode]?"&nbsp;($r[CID])":"");

   echo "</select><br><br>
   <table cellspacing=\"0\"  cellpadding=\"0\" border=0 width=\"100%\">
   <tr>
   <td align=\"right\" valign=\"top\"><input type=\"image\" name=\"ok\" onmouseover=\"this.src='".$sitepath."images/send_.gif';\" onmouseout=\"this.src='".$sitepath."images/send.gif';\" src=\"".$sitepath."images/send.gif\" align=\"right\" alt=\"ok\" border=\"0\"></td>
   </tr>
   </table>
   </form>";

   echo show_tb();
   exit;


case "selectkurs_submit":

   intvals("newcid");
   if ($newcid==0) exitmsg(_("Ничего не выборано"),"$PHP_SELF?c=selectkurs$sess");
   if (!isset($s[tkurs][$newcid])) exit("HackDetect: "._("нет прав перейти на чужой курс"));
   $s[$ss][cid]=$newcid;
   refresh("$PHP_SELF?$sess");
   exit;


}



?>