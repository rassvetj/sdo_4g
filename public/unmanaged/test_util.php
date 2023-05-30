<?

   $REQUEST_C = $_REQUEST['c'];
   include("1.php");
   include("test.inc.php");

   $ss="test_e1";

   if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
   if ($s[perm]<2) exitmsg(_("К этой странице могут обратится только:
      преподаватель,  представитель учебной администрации, администратор"),"/?$sess");
   if (count($s[tkurs])==0) exitmsg(_("Вы зарегистрированы в статусе преподавателя, но на данный момент вы не преподаете ни на одном из курсов."),"/?$sess");
   //if (!isset($cid)) $cid=reset($s[tkurs]);
   if (!isset($cid)) $cid=0;

   intvals("cid tid mid");

   if (isset($_REQUEST['c'])) $c = $REQUEST_C;

switch ($c) {

case "limitclean":

    $filter_kurses = selCourses($s['tkurs'],$cid,true);
    $GLOBALS['controller']->addFilter('c','c','hidden','limitclean');
    $GLOBALS['controller']->addFilter(_("Курс"),'cid',$filter_kurses,$cid,false);

   if ($cid && !isset($s[tkurs][$cid])) exitmsg(_("Вы не преподаватель данного курса."),"results.php4?$sess");

   echo show_tb();

   echo ph(_("Очистка/управление кол-вом попыток тестирований"),"");
   $GLOBALS['controller']->captureFromOb(CONTENT);
//   $GLOBALS['controller']->setHeader("Управление кол-вом попыток тестирований");
   echo "
   <table width=100% class=main cellspacing=0><tr>
   <th>"._("ФИО").",<br>"._("Логин")."</th>
   <th>"._("Кол-во")."<br>"._("попыток")."</th>
   <th>"._("Осталось")."<br>"._("попыток")."</th>
   <th>"._("Задание")."</th>
   <th>"._("Последнее")."<br>"._("тестирование")."</th>
   <th>"._("Действия")."</th>
   </tr>";
   
   $resources = array();
   $sql = "SELECT CID FROM Courses WHERE type = '1'";
   $res = sql($sql);
   while($row = sqlget($res)) {
       $resources[$row['CID']] = $row['CID'];
   }

   $where = 'WHERE testcount.cid=0';
   if (count($GLOBALS['s']['tkurs']))
       $where = "WHERE testcount.cid IN ('".join("','",$GLOBALS['s']['tkurs'])."')";
   if ($cid>0)
       $where = "WHERE testcount.cid=".(int) $cid;
   $where .= " AND test.startlimit!='0'";
   $res=sql("SELECT testcount.qty, testcount.last, testcount.mid, testcount.tid, testcount.cid as testcount_cid,
                    People.LastName, People.FirstName, People.Patronymic, People.Login,
                    test.title, test.startlimit, test.tid as ttid, test.limitclean, test.cid
             FROM testcount
             INNER JOIN People ON testcount.mid=People.MID
             INNER JOIN test ON testcount.tid=test.tid
             INNER JOIN Students ON (Students.MID=People.MID AND Students.CID = testcount.cid)
             {$where}
             ORDER BY People.LastName, testcount.tid","errTU43");
   if (!sqlrows($res)){
   		echo "<tr><td colspan='6'>"._("нет данных для отображения")."</td></tr>";
   }
   $toolTip = new ToolTip();
   while ($r=sqlget($res)) {
      $remain = max($r['startlimit'] - $r['qty'], 0);

      if (($r['limitclean']>0) && (($r['last'] + ($r['limitclean']*24*60*60))<time())) {
          $remain = $r['startlimit'];
      }
      echo "<tr><TD>$r[LastName] $r[FirstName] $r[Patronymic] ($r[Login])</TD>";
      echo "
      <td>$r[startlimit]&nbsp;</td>
      <TD>{$remain}</TD>";
      if ($r[ttid]===null) echo "<td>"._("это задание было удалено")."</td>";
      else {
      	  if (in_array($r['cid'], $resources)) {
              echo "<TD>$r[title] ".$toolTip->display('is_cms_test')."</TD>";      	  	
      	  } else {
      	      echo "<TD><a onClick=\"wopen('test_test.php?c=edit2&cid={$r[testcount_cid]}&tid={$r[tid]}','tedit')\" href=\"javascript:void(0);\" title=\""._("Редактировать вопросы")."\">$r[title]</a></TD>";
      	  }
      }
	  echo "
      <TD>".date("d/m/Y H:i",$r[last])."</TD>
      <TD><a href=$PHP_SELF?c=limitclean_delete&mid=$r[mid]&cid={$r[testcount_cid]}&tid=$r[tid]$sess>"._("снять")."</a>
      <a href=$PHP_SELF?c=limitclean_add&mid=$r[mid]&cid={$r[testcount_cid]}&tid=$r[tid]$sess>"._("добавить")."</a></TD>
      </tr>";
   }

   echo "</table><P>";

   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();

   return;


case "limitclean_delete":


   if (!isset($s[tkurs][$cid])) exitmsg("Вы не преподаватель данного курса.","results.php4?$sess");
   $query = "SELECT startlimit FROM test WHERE tid='{$tid}' AND startlimit>0";
   $res = sql($query);
   if ($row = sqlget($res)) {
       //$_sql = "IF(qty<{$row['startlimit']},qty+1,{$row['startlimit']})";
       //if (dbdriver == 'mssql') {
       $_sql = "CASE WHEN qty<".(int) $row['startlimit']." THEN qty+1 ELSE ".(int) $row['startlimit']." END";
       //}
	   $res=sql("UPDATE testcount SET qty=$_sql WHERE mid={$_GET['mid']} AND tid=$tid","errTU79a");
   } else {
	   $res=sql("UPDATE testcount SET qty=qty+1 WHERE mid={$_GET['mid']} AND tid=$tid","errTU79a");
   }
   sqlfree($res);
   refresh("$PHP_SELF?c=limitclean&cid=$cid");
   return;


case "limitclean_add":

   //$_sql = "IF(qty>0,qty-1,0)";
   //if (dbdriver == 'mssql') {
   $_sql = "CASE WHEN qty>0 THEN qty-1 ELSE 0 END";
   //}

   if (!isset($s[tkurs][$cid])) exitmsg(_("Вы не преподаватель данного курса."),"results.php4?$sess");
   $res=sql("UPDATE testcount SET qty=$_sql WHERE mid={$_GET['mid']} AND tid=$tid","errTU79b");
   sqlfree($res);
   refresh("$PHP_SELF?c=limitclean&cid=$cid");
   return;


}


?>