<?


   include("1.php");
   include("metadata.lib.php");




/*   if (sqlrows($res)==0) echo "<tr><td colspan='2'>На этом курсе не создано ни одной группы.</td></tr>";

   $res=sql("SELECT * FROM cgname ORDER BY cgid","errGR73");
   while ($r=sqlget($res)) {
      echo "<tr><td colspan=2><b><a href=$PHP_SELF?c=showdgr&cgid=$r[cgid]$sess>$r[name]</a></b> (".getStCol($r[cgid]).") </td>
                  <!--td align='center'>деканат</td-->
            </tr>";
   }

*/
   if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
   if ($s[perm]<2) exitmsg(_("К этой странице могут обратится только преподаватели"),"/?$sess");
   if (count($s[tkurs])==0) exitmsg(_("Вы зарегистрированы в статусе преподавателя, но на данный момент вы не преподаете ни на одном из курсов."),"/?$sess");

   $ss="test_e1";


   $cid=(isset($_GET['CID'])) ? intval($_GET['CID']) : intval($s[$ss][cid]);
   $s[$ss][cid]=$cid;


   if (!isset($s[$ss][cid]) || !isset($s[tkurs][$s[$ss][cid]])) {
      $s[$ss][cid]=reset($s[tkurs]);
   }




   $s[$ss][cid]=$cid;

switch ($c) {

case "":


   echo show_tb();
   echo ph(_("Редактирование групп курса"));
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(_("Группы на курсах"));
   $filter_kurses = selCourses($_SESSION['s']['tkurs'],$CID,true);
   $GLOBALS['controller']->addFilter(_("Курс"),'CID',$filter_kurses,$CID,true);

   $smarty = new Smarty_els();
   $smarty->assign('caption', _("создать группу"));
   $smarty->assign('url', $GLOBALS['sitepath']."groups.php?c=editgr&cid=$CID");
   $smarty->assign('style', "");
   if ($CID) {
   $smarty->display('common/add_link.tpl');
   echo writeGroups( $CID, 0 );
   }
/*
   echo "
<br>
   <form action='$PHP_SELF' name='newgr' method='POST'>
   <input type='hidden' name='c' value='new_gr'>
   <input type='hidden' name='CID' value='{$CID}'>
   <table width=100% class=main cellspacing=0>\n
         <tr>
            <th colspan='2'>"._("Добавить новую группу")."
            </th>
         </tr>
         <tr>
            <td>"._("Название")."</td>
            <td> <input type='text' name='ng' size='40' style='width:100%'>
            </td>
         </table><br>";
   		echo okbutton();
echo "</form>
         ";*/

   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   return;

/*
case "new_gr":
   $ng=(isset($_POST['ng'])) ? $_POST['ng'] : "";
   if (!empty($ng) && $cid)
   $res=sql("INSERT INTO groupname (cid, name) values (
                '$cid',
                ".$GLOBALS['adodb']->Quote($ng).")","errFM185");
   refresh("$PHP_SELF?$sess");
   sqlfree($res);
   break;
*/
case "delete":
   intvals("gid");
   if (!empty($gid))
   deleteGroup( $gid );
//   $res=sql("DELETE FROM groupname WHERE gid='$gid'","errFM185");
   refresh("$PHP_SELF?$sess");
   break;


case "editgr":

    // SAJAX BEGIN
    require_once($wwf.'/lib/sajax/Sajax.php');

    sajax_init();
    sajax_export("edit_group_unused_people","edit_group_used_people");
    sajax_handle_client_request();
    $sajax_javascript = sajax_get_javascript();

    $GLOBALS['controller']->setHelpSection('edit01');
    $GLOBALS['controller']->setHeader(_("Редактирование группы"));
    $GLOBALS['controller']->captureFromOb(CONTENT);
    $_smarty = new Smarty_els();

    $gid = (int) $_GET['gid'];
    if ($gid) {
        $sql = "SELECT * FROM groupname WHERE gid='".(int) $gid."'";
        $res = sql($sql);

        if ($row = sqlget($res)) {
            $_smarty->assign('group',$row);
        }
    }


    // ===============================================================================
    $smarty = new Smarty_els();
    $smarty->assign('list1_options',edit_group_unused_people('',$gid,$cid));
    $smarty->assign('list2_options',edit_group_used_people($gid));
    $smarty->assign('list1_name','list1');
    $smarty->assign('list2_name','list2');
    $smarty->assign('list1_title',_('Все'));
    $smarty->assign('list2_title',_('Группа'));
    $smarty->assign('button_all_click',"if (elm = document.getElementById('editbox_search')) elm.value='*'; get_list_options('*');");
    $smarty->assign('editbox_search_name','editbox_search');
    $smarty->assign('editbox_search_text','');
    $smarty->assign('editbox_search_keyup',"if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_list_options(\''+this.value+'\');',1000);");
    $smarty->assign('list1_container_id','list1_container');
    $smarty->assign('list2_container_id','list2_container');
    $smarty->assign('list3_name','list3');
    $smarty->assign('list3_options', false);
    $smarty->assign('list3_change',"if (elm = document.getElementById('editbox_search')) get_list_options(elm.value);");
    $smarty->assign('list1_list2_click','');
    $smarty->assign('list2_list1_click','');
    $smarty->assign('javascript', $sajax_javascript."
            function show_list_options(html) {
                var elm = document.getElementById('list1_container');
                if (elm) elm.innerHTML = '<select size=10 id=\"list1\" name=\"list1[]\" multiple style=\"width:100%\">'+html+'</select>';
                prepare_options('list1', dropped, assigned);
            }

            function get_list_options(str) {
                var current = 0;

                var select = document.getElementById('editbox_search');
                if (select) current = select.value;


                var elm = document.getElementById('list1_container');
                if (elm) elm.innerHTML = '<select size=10 id=\"list1\" name=\"list1[]\" multiple style=\"width:100%\"><option>"._("Загружаю данные...")."</option></select>';

                get_list2_options('');
                x_edit_group_unused_people(str, '".(int) $gid."', '".(int) $cid."', show_list_options);
            }

            function show_list2_options(html) {
                var elm = document.getElementById('list2_container');
                if (elm) elm.innerHTML = '<select size=10 id=\"list2\" name=\"list2[]\" multiple style=\"width: 100%\">'+html+'</select>';
                prepare_options('list2', assigned, dropped);
            }

            function get_list2_options(str) {
                var elm = document.getElementById('list2_container');
                if (elm) elm.ennerHTML = '<select size=10 id=\"list2\" name=\"list2[]\" multiple style=\"width: 100%\"><option>"._("Загружаю данные...")."</option></select>';
                x_edit_group_used_people('".(int) $gid."', show_list2_options);
            }
    ");
    // ===============================================================================

    $_smarty->assign('users', $smarty->fetch('control_list2list.tpl'));
    $_smarty->assign('cid',(int) $_GET['cid']);
    $_smarty->assign('okbutton',okbutton());
    $_smarty->assign('cancelbutton',button(_("Отмена"), '', 'cancel', '', $GLOBALS['sitepath'].'groups.php'));
    echo $_smarty->fetch('course_group_edit.tpl');
    $GLOBALS['controller']->captureStop(CONTENT);
    $GLOBALS['controller']->terminate();
    return;
/*

   echo show_tb();
   echo ph(_("Редактирование состава группы"));
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(_("Редактирование состава группы"));
   echo writeGroupList( $cid, $gid );
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   return;
*/


case "post_editgr":

   intvals("cid gid");   
   $name = trim(strip_tags($_POST['name']));
   $gid = (int) $_POST['gid'];
   $cid = (int) $_POST['cid'];
   $persons = $_POST['list2'];

   if ($cid) {
       if ($gid>0) {
           $gr=sqlval("SELECT * FROM groupname WHERE gid=$gid","errGR87");
           if (!is_array($gr)) exit(_("Такой группы не существует."));
           if ($gr[cid]!=$cid) exit("HackDetect: "._("доступ к чужому курсу"));
           
           $sql = "UPDATE groupname SET name=".$GLOBALS['adodb']->Quote($name)." WHERE gid='$gid' AND cid='$cid'";
           sql($sql);
       }else {
           $sql = "INSERT INTO groupname (name, cid) VALUES (".$GLOBALS['adodb']->Quote($name).", '$cid')";
           sql($sql);
           $gid = sqllast();
       }

       sql("DELETE FROM groupuser WHERE gid='$gid' AND cid='$cid'");

       if (is_array($persons) && count($persons)) {
           foreach($persons as $mid) {
               if ($mid>0) {
                   sql("INSERT INTO groupuser (gid,mid,cid) VALUES ('".(int) $gid."','".(int) $mid."','$cid')");
               }
           }
       }
   }
       

   refresh($sitepath.'groups.php?CID='.$cid);
   exit();

/*   $meta=set_metadata( $_GET, get_posted_names( $_GET ), "group" );
*/
//   $g=get_posted_names( $_GET );
//   foreach( $g  as $p )
//     echo "$p<BR>";

//    echo "<H1>".count($_GET).": $meta</H1>";

   //$gr=sqlval("UPDATE groupname SET info='$meta' WHERE gid=$gid","errGR87");


/*   $res=sql("DELETE FROM groupuser WHERE gid=$gid AND cid=$cid","errGR136");
   sqlfree($res);
   if (is_array($che) && count($che)) {
      foreach ($che as $k=>$v){
      $rq="INSERT INTO groupuser (gid,cid,mid) VALUES ";
      $rq.="($gid,$cid,".intval($v)."),";
      $rq=substr($rq,0,-1);
      $res=sql($rq,"errGR139");
      sqlfree($res);
      }
   }
   refresh("$PHP_SELF?$sess");
   return;
*/

case "showdgr":

   intvals("cgid");
   echo show_tb();
   echo ph(_("Просмотр группы"));
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHeader(_("Просмотр группы"));
   $gr=sqlval("SELECT * FROM cgname WHERE cgid=$cgid","errGR87");
   if (!is_array($gr)) exit(_("Такой группы не существует."));

   $res=sql("SELECT DISTINCT People.FirstName, People.LastName, People.Login, People.email,
                    People.mid as mid
             FROM Students
             LEFT JOIN People ON Students.MID=People.MID
             WHERE Students.cgid=$cgid AND People.MID IS NOT NULL
             ORDER BY People.mid ","errGR105");


   echo "
   &lt;&lt; <a href=$PHP_SELF?$sess>"._("вернуться к списку групп")."</a><P>
  "._("Просматриваемая группа:")." <b>$gr[name]</b><P>";

   echo "
   <table width=100% class=main cellspacing=0>
   <tr><th>"._("ФИО")."</th><th>"._("логин")."</th></tr>
   ";

   while ($r=sqlget($res)) {
      echo "<tr><td> $r[FirstName] $r[LastName]</td><td>$r[Login]</td>".
      "</tr>";
   }

   echo "</table>";
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();

   return;


}

function edit_group_used_people($gid) {
    $html = '';

    $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

    $sql = "
    SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login, People.EMail
    FROM People
    INNER JOIN Students ON (Students.MID = People.MID)
    INNER JOIN groupuser ON (groupuser.mid = People.MID)
    WHERE groupuser.gid = '$gid'
    ORDER BY People.LastName, People.FirstName, People.Login
    ";

    $res = sql($sql);

    while($row = sqlget($res)) {
        if (!$peopleFilter->is_filtered($row['MID'])) continue;
        if (isset($people[$row['MID']])) continue;

        $people[$row['MID']] = htmlspecialchars($row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].($row['Login'] ? ' ('.$row['Login'].')' : ''),ENT_QUOTES);

    }

    if (is_array($people) && count($people)) {
       foreach($people as $mid => $name) {
           $html .= "<option value=\""
           .(int) $mid."\" title='$name'> "
           .$name."</option>";
        }
    }

    return $html;
}

function edit_group_unused_people($search='', $gid=0, $cid) {
    $html = '';
    if (!empty($search)) {
        $search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
        $search = trim($search);
        $search = str_replace('*','%',$search);
        $people = array();

        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);

        $sql = "
        SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login, People.EMail
        FROM People
        INNER JOIN Students ON (Students.MID = People.MID)
        LEFT JOIN groupuser ON (groupuser.mid = People.MID AND groupuser.gid='$gid')
        WHERE
            groupuser.mid IS NULL AND
            (
             People.LastName LIKE '%".addslashes($search)."%' OR
             People.FirstName LIKE '%".addslashes($search)."%' OR
             People.Login LIKE '%".addslashes($search)."%'
            ) AND
            Students.CID = '".(int) $cid."'
        ORDER BY People.LastName, People.FirstName, People.Login
        ";

        $res = sql($sql);

        while($row = sqlget($res)) {
            if (!$peopleFilter->is_filtered($row['MID'])) continue;
            if (isset($people[$row['MID']])) continue;

            $people[$row['MID']] = htmlspecialchars($row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].($row['Login'] ? ' ('.$row['Login'].')' : ''),ENT_QUOTES);

        }

        if (is_array($people) && count($people)) {
            foreach($people as $mid => $name) {
                $html .= "<option value=\""
                .(int) $mid."\" title='$name'> "
                .$name."</option>";
            }
        }

    }
    return $html;
}


?>