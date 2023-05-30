<?php
   require_once("1.php");
   require_once("metadata.lib.php");
   if ($s['perm']<=1) login_error();

   if ($c!='edit') {
       $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
   }

switch ($c) {

case "":

    echo show_tb();
    $GLOBALS['controller']->captureFromOb(CONTENT);
    
    $sql = "
                SELECT
                 COUNT(DISTINCT People.`MID`) AS num_stud
                FROM
                  People
                  INNER JOIN Students ON (People.`MID` = Students.`MID`)
                  LEFT OUTER JOIN groupuser ON (People.`MID` = groupuser.`mid`)
                GROUP BY
                  `groupuser`.cid
    ";

    $res = sql($sql);
    $row = sqlget($res);
    $freestud = $row['num_stud'];
    $row = sqlget($res);
    $busystud = $row['num_stud'];
    $allstud = $busystud + $freestud;

    echo "
    <div style='padding-bottom: 5px;'>
        <div style='float: left;'><img src='{$GLOBALS['sitepath']}images/icons/small_star.gif'>&nbsp;</div>
        <div><a href='{$GLOBALS['sitepath']}addgroup.php4?c=edit&gid=0' style='text-decoration: none;'>"._("создать учебную группу")."</a></div>
    </div>";
        
    echo "
    <table width=100% class=main cellspacing=0>
    <tr><th>"._("Название")."</th><th>"._("Количество слушателей")."</th><th width='100px' align='center'>"._("Действия")."</th></tr>
    ";
    
    /*
    $res=sql("SELECT * FROM cgname ORDER BY cgid","errGR73");
    while ($r=sqlget($res)) {
      echo "<tr>
            <td><a target=_blank href=$PHP_SELF?c=editgr&cgid=$r[cgid]&hide_ghosts=1$sess><B>$r[name]</B></a> (".getStCol($r['cgid']).")</td>
            <td  width='100px' align='center'><a href=$PHP_SELF?c=delete&cgid=$r[cgid]$sess onclick=\"if (!confirm('"._("Удалить группу?")."')) return false;\" >".getIcon("delete")."</a></tr>";
    }
    
    if (sqlrows($res)==0) echo "<tr><td colspan=2>"._("На этом курсе не создано ни одной группы")."</td></tr>";
    
    echo "<tr><th></th><th></th></tr>";
    */

    //вытаскиваем куррируемые группы
    $sql = "SELECT departments_groups.gid 
           FROM `departments` 
           LEFT JOIN `departments_groups` ON (departments.did = departments_groups.did)
           WHERE departments.mid = '{$GLOBALS['s']['mid']}'";
    $myGroups = array();
    $res = sql($sql);
    while ($row = sqlget($res)) {
       $myGroups[] = $row['gid'];
    }   
      
    $sql="SELECT * FROM groupname WHERE cid=-1 ".(count($myGroups)?"AND gid IN ('".implode("','",$myGroups)."')":'')." ORDER BY name";
    $res=sql($sql,"errGR73");
    while ($r=sqlget($res)) {
      echo "<tr>
            <td><!-a target=_blank href=$PHP_SELF?c=editgr&autogroups=1&gid=$r[gid]&hide_ghosts=1$sess-->$r[name]<!--/a--></td>
            <td>".getStCol($r['gid'], "groupuser")."</td>
            <td  width='100px' align='center'>
                <a href=\"$PHP_SELF?c=edit&gid=$r[gid]$sess\">".getIcon("edit", _('Редактировать группу'))."</a>
                <a href=$PHP_SELF?c=delete&gid=$r[gid]$sess onclick=\"if (!confirm('"._("Удалить группу?")."')) return false;\"  >".getIcon("delete", _('Удалить группу'))."</a>
            </td>
            </tr>";
    }
    
    if (sqlrows($res) == 0) {
       echo "<tr><td colspan=3 align=center>"._("нет данных для отображения")."</td></tr>";
    }
    echo "</table>";
    
    $GLOBALS['controller']->captureStop(CONTENT);
    echo show_tb();
   break;

case "new_gr":

        //fn $strValidTable = (isset($_POST['autogroup'])) ? "groupname" : "cgname";
        //fn $strExtraInsert = (isset($_POST['autogroup'])) ? ", cid='-1'" : "";
        global $adodb;

        if (!empty($name)) {
           if(isset($_POST['autogroup']))
              $res=sql("INSERT INTO groupname (name, cid)
                        values
                       (".$adodb->Quote($name).", -1)","errFM185");
           else
              $res=sql("INSERT INTO cgname (name)
                        values
                       (".$adodb->Quote($name).")","errFM185");
       }

        $qu = "SELECT did FROM departments WHERE mid = '{$s['mid']}' AND application = '".DEPARTMENT_APPLICATION."'";
        $re = sql($qu);
        if(sqlrows($re)) {
        	$ro = sqlget($re);
        	sql("INSERT INTO departments_groups (did, gid) VALUES ('{$ro['did']}', '".sqllast()."')");
        }

        refresh("$PHP_SELF?$sess");
        sqlfree($res);
        break;

case "delete":
   intvals("cgid");
   if (!empty($cgid))
   {
   $rq="UPDATE `Students` SET cgid='' WHERE cgid=$cgid";
   $res=sql($rq,"errGR138");
   sqlfree($res);

   $res=sql("DELETE FROM cgname WHERE cgid='$cgid'","errFM185");
   sqlfree($res);
   }

   if (!empty($gid)) {
            $sql = "DELETE FROM groupname WHERE gid = '{$gid}'";
            $res=sql($sql);
            sqlfree($res);

            $sql = "DELETE FROM groupuser WHERE gid = '{$gid}'";
            $res=sql($sql);
            sqlfree($res);

            $sql = "DELETE FROM departments_groups WHERE gid = '{$gid}'";
            $res=sql($sql);
            sqlfree($res);
   }

   refresh("$PHP_SELF?$sess");

   break;

case "editgr":

        echo show_tb();
        $GLOBALS['controller']->setView('DocumentPopup');
        $GLOBALS['controller']->captureFromOb(CONTENT);

   if (isset($_GET['gid'])) {
//                echo writeGroupList( -1, $gid );
                echo writeGroupListWide( -1, $gid );
   } else {

           intvals("cgid");
           echo ph(_("Редактирование группы"));

           $gr=sqlval("SELECT * FROM cgname WHERE cgid=$cgid","errGR87");
           if (!is_array($gr)) exit(_("Такой группы не существует."));
        //   if ($gr[CID]!=$cid) exit("HackDetect: доступ к чужому курсу");


           $GLOBALS['controller']->captureFromOb(TRASH);
           echo "
           &lt;&lt; <a href=$PHP_SELF?$sess>"._("вернуться к списку групп")."</a>";
           $GLOBALS['controller']->captureStop(TRASH);
           echo "<P>"._("Редактируемая группа:")." <b>$gr[name]</b><P>";

          $boolEditable = ($s['perm'] > 2);

           if ($boolEditable) {
            $strCheckedGhosts = (isset($_GET['hide_ghosts']) ? "checked" : "");
            $tmp.= "
                   <form id='form_ghosts' name='form_ghosts' method='GET' action='{$_SERVER['PHP_SELF']}'>
                   <input type=hidden name=c value=\"editgr\">
                   <input type=hidden name=cgid value=\"$cgid\">
                   <input type='checkbox' name='hide_ghosts' value='1' $strCheckedGhosts onClick=\"document.getElementById('form_ghosts').submit()\">"._("отображать только входящих в группу")."
                        </form>
            ";
           }
        echo $tmp;
                   echo "<form action=$PHP_SELF method=post>
           <input type=hidden name=c value=\"post_editgr\">
           <input type=hidden name=cgid value=\"$cgid\">";

/*           echo "
           <table width=100% class=main cellspacing=0>
           <tr><th>ФИО</th><th>логин</th><th>email</th></tr>";
*/

   $boolOrderDir = (!isset($_GET['dir'])) ? ORDER_DESC : !$_GET['dir'];
   $sqlOrderDir = ($boolOrderDir) ? "DESC" : "";
   $strImageArrowDir = ($boolOrderDir) ? "up" : "down";

           switch ($_GET['assort']) {
                   case ORDER_BY_LNAME:
                           $sqlOrder = " People.LastName";
                           $strImageArrowLname = "<img src='[PATH]images/sort_{$strImageArrowDir}.gif' border=0>";
                           break;
                   case ORDER_BY_POSITION:
                           $sqlOrder = " People.Position";
                           $strImageArrowPosition = "<img src='[PATH]images/sort_{$strImageArrowDir}.gif' border=0>";
                           break;
                   case ORDER_BY_RANK:
                           $sqlOrder = " People.rnid";
                           $strImageArrowRank = "<img src='[PATH]images/sort_{$strImageArrowDir}.gif' border=0>";
                           break;
                   default:
                           $sqlOrder = " People.MID";
                           $strImageArrowLname = "";
                           $strImageArrowPosition = "";
                           $strImageArrowRank = "";
                           break;
           }
        $strHideGhosts = (isset($_GET['hide_ghosts'])) ? "&hide_ghosts={$_GET['hide_ghosts']}" : "";

           echo "
                <table width=100% class=main cellspacing=0>
                <tr>
                <th nowrap><a class=cpass href={$_SERVER['PHP_SELF']}?c={$_GET['c']}&cgid={$_GET['cgid']}{$strHideGhosts}&assort=".ORDER_BY_LNAME."&dir={$boolOrderDir}>"._("ФИО")."</a>{$strImageArrowLname}</th>
                <th nowrap><a class=cpass href=#>"._("Логин")."</a></th>";
                   if (defined("LOCAL_REGINFO_CIVIL") && !LOCAL_REGINFO_CIVIL) {
                    echo "
                     <th nowrap><a class=cpass href={$_SERVER['PHP_SELF']}?c={$_GET['c']}&cgid={$_GET['cgid']}{$strHideGhosts}&assort=".ORDER_BY_RANK."&dir={$boolOrderDir}>"._("Звание")."</a>{$strImageArrowRank}</th>
                    <th nowrap><a class=cpass href={$_SERVER['PHP_SELF']}?c={$_GET['c']}&cgid={$_GET['cgid']}{$strHideGhosts}&assort=".ORDER_BY_POSITION."&dir={$boolOrderDir}>"._("Должность")."</a>{$strImageArrowPosition}</th>";
                   } else {
                    echo "
                     <th nowrap><a class=cpass href='#'>E-mail</a></th>";
                   }
                echo "</tr>";

           $res=sql("SELECT Students.MID as mid FROM Students, cgname WHERE cgname.cgid=$cgid AND Students.cgid=cgname.cgid","errGR159");
           $check=array();
           while ($r=sqlget($res)) $check[$r[mid]]=1;

        /*   $sql = "SELECT People.FirstName, People.LastName, People.Login, People.email,
                            People.mid as mid
                     FROM Students, cgname
                     LEFT JOIN People ON Students.MID=People.MID
                     WHERE NOT ISNULL(People.MID)
              AND (Students.cgid='' OR Students.cgid='0' OR Students.cgid=cgname.cgid)
              AND cgname.cgid=$cgid
                     GROUP BY Students.MID ORDER BY People.mid";
        */
            if (!$_GET['hide_ghosts']) {
                    $sqlWhere = " OR
                          (Students.cgid = 0) OR
                          (Students.cgid IS NULL)";
            }
                /*$sql = "
                        SELECT DISTINCT
                          People.`MID` as mid,
                          People.LastName,
                          People.FirstName,
                          People.email,
                          People.Login
                        FROM
                          People
                          INNER JOIN Students ON (People.`MID` = Students.`MID`)
                        WHERE
                          ((Students.cgid = {$cgid}) {$sqlWhere})
                        ORDER BY mid
                ";
*/
        $sql = "
                SELECT DISTINCT
                  People.`MID` as distinct_mid,
                  People.Login,
                  People.LastName,
                  People.FirstName,
                  People.Patronymic,
                  People.Position,
                  People.EMail,
                  rank.Title as rank
                FROM
                  Students
                  INNER JOIN People ON (People.`MID` = Students.`mid`)
                  LEFT JOIN rank ON (rank.rnid = People.rnid)
                  WHERE
                    ((Students.cgid = {$cgid}) {$sqlWhere})
                   ORDER BY
                     {$sqlOrder} {$sqlOrderDir}
   ";
                $res=sql($sql,"errGR105");

                echo student_alias_parse(_("Отметьте")." [sTUDENT_ALIAS-ROD-MORE], "._("входящих в эту группу")." ("._("всего")." ").sqlrows($res).")<P>";

           while ($r=sqlget($res)) {
                    if (!$GLOBALS['peopleFilter']->is_filtered($r['distinct_mid'])) continue;
                    $strChecked = ($check[$r['distinct_mid']]) ? "checked" : "";
                    $strEditAbility = ($boolEditable) ? "<input type=checkbox name='che[]' value='{$r['distinct_mid']}' {$strChecked}>" : "";
                    if (!$_GET['hide_ghosts'] || $check[$r['distinct_mid']]) {
                                   echo "<tr>
                                   <td nowrap>".$strEditAbility."$r[LastName] $r[FirstName] $r[Patronymic]</td>
                                     <td>{$r['Login']}</td>";
                                   if (defined("LOCAL_REGINFO_CIVIL") && !LOCAL_REGINFO_CIVIL) {
                                           echo "
                                             <td>{$r['rank']}</td>
                                             <td>{$r['Position']}</td>";
                                   } else {
                                           echo "
                                             <td>{$r['EMail']}</td>";
                                   }
                    }

/*              echo "<tr><td><input type=checkbox name='che[]' value=$r[mid] ".($check[$r[mid]]?"checked":"").
              ">$r[FirstName] $r[LastName]</td><td>$r[Login]</td>".
              "<td>$r[email]</td></tr>";
*/           }
           echo "
           <P>
           <tr>
              <td colspan=100 align=\"right\" valign=\"top\">";
              if ($GLOBALS['controller']->enabled) echo okbutton();
              else
              echo "<input type=\"image\" name=\"ok\"
              onmouseover=\"this.src='".$sitepath."images/send_.gif';\"
              onmouseout=\"this.src='".$sitepath."images/send.gif';\"
              src=\"".$sitepath."images/send.gif\" align=\"right\" alt=\"ok\" border=\"0\">";
              echo "</td>
           </tr>
           </table>
           </form>";
   }
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
   return;

case "post_editgr":

   intvals("cid cgid");

   if (isset($_GET['autogroups']) && isset($_GET['gid'])) {

              $gr=sqlval("SELECT * FROM groupname WHERE gid='{$_GET['gid']}'","errGR87");
           if (!is_array($gr)) exit(_("Такой группы не существует."));
        //   if ($gr[CID]!=$cid) exit("HackDetect: доступ к чужому курсу");

              $rq="DELETE FROM `groupuser` WHERE gid='{$_GET['gid']}'";
              sql($rq,"errGR138");


           if (is_array($che) && count($che)) {
           	/*
           	// Не подходит для MSSQL
                     foreach ($che as $c) {
                               $strListValues .= "({$c}, -1, {$_GET['gid']}), ";
                     }
                     $strListValues = substr($strListValues, 0, -2);
              $rq="INSERT INTO `groupuser` (`mid`, `cid`, `gid`),  VALUES {$strListValues}";
              sql($rq,"errGR139");
            */
                     foreach ($che as $c) {
              			$rq="INSERT INTO `groupuser` (`mid`, `cid`, `gid`) VALUES ({$c}, -1, {$_GET['gid']})";
              			sql($rq,"errGR139");
                     }
           }

   } else {

           $gr=sqlval("SELECT * FROM cgname WHERE cgid=$cgid","errGR87");
           if (!is_array($gr)) exit(_("Такой группы не существует."));
        //   if ($gr[CID]!=$cid) exit("HackDetect: доступ к чужому курсу");

              $rq="UPDATE `Students` SET cgid='' WHERE cgid=$cgid";
              $res=sql($rq,"errGR138");
              sqlfree($res);


           if (is_array($che) && count($che)) {
              $rq="UPDATE `Students` SET cgid=$cgid WHERE MID IN (".implode(", ",$che).")";
              $res=sql($rq,"errGR139");
              sqlfree($res);
           }
   }
   $GLOBALS['controller']->setView('DocumentBlank');
   $GLOBALS['controller']->setMessage(_("Группа успешно отредактирована"), JS_CLOSE_SELF_REFRESH_OPENER);
   $GLOBALS['controller']->terminate();
   if (!$GLOBALS['controller']->enabled)
   refresh("$PHP_SELF?$sess");
   return;



case 'edit':
    // SAJAX BEGIN
    require_once($wwf.'/lib/sajax/Sajax.php');

    sajax_init();
    sajax_export("edit_group_unused_people","edit_group_used_people");
    sajax_handle_client_request();
    $sajax_javascript = sajax_get_javascript();

    $GLOBALS['controller']->setHelpSection('edit');
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
    $smarty->assign('list1_options',edit_group_unused_people('',$gid));
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
                x_edit_group_unused_people(str, '".(int) $gid."', show_list_options);
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
                
            function showHideString(obj, str) {                
                str = str ? str : '"._("введите часть имени или логина")."';
                if (obj.value == str) {
                    obj.value = '';
                    obj.style.fontStyle = 'normal';
                    obj.style.color = 'black';
                }else {
                    if (!obj.value) {
                        obj.style.fontStyle = 'italic';
                        obj.style.color = 'grey';
                        obj.value = str;                
                    }
                }
            }
            $(function() {
                showHideString($('#editbox_search').get(0))
            });
    ");
    // ===============================================================================

    $_smarty->assign('users', $smarty->fetch('control_list2list.tpl'));
    $_smarty->assign('okbutton',okbutton());
    echo $_smarty->fetch('group_edit.tpl');
    $GLOBALS['controller']->captureStop(CONTENT);
    $GLOBALS['controller']->terminate();
break;

case 'edit_post':
    $name = trim(strip_tags($_POST['name']));
    $gid = (int) $_POST['gid'];
    $persons = $_POST['list2'];

    if ($gid>0) {
        $sql = "UPDATE groupname SET name=".$GLOBALS['adodb']->Quote($name)." WHERE gid='$gid'";
        sql($sql);

        sql("DELETE FROM groupuser WHERE gid='$gid'");
    }
    else {
        $sql = "INSERT INTO groupname (name, cid) VALUES (".$GLOBALS['adodb']->Quote($name).", -1)";
        sql($sql);        
        $gid = sqllast();
    }
    
    if (is_array($persons) && count($persons)) {
        foreach($persons as $mid) {
            if ($mid>0) {
                sql("INSERT INTO groupuser (gid,mid,cid) VALUES ('".(int) $gid."','".(int) $mid."','-1')");
            }
        }
    }

    refresh($sitepath.'addgroup.php4');
break;

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
           .(int) $mid."\" > "
           .$name."</option>";
        }
    }

    return $html;
}

function edit_group_unused_people($search='', $gid=0) {
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
        groupuser.mid IS NULL
        AND (People.LastName LIKE '%".addslashes($search)."%' OR People.FirstName LIKE '%".addslashes($search)."%'
        OR People.Login LIKE '%".addslashes($search)."%')
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
                .(int) $mid."\" > "
                .$name."</option>";
            }
        }

    }
    return $html;
}

?>