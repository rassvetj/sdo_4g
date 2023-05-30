<?php

       require_once('1.php');

       if (!$s[login]) exitmsg("Пожалуйста, авторизуйтесь","/?$sess");

       $smarty = new Smarty_els();

       $sql = "SELECT groupname.name, groupname.gid FROM groupname WHERE cid='-1' ORDER BY name";
       $res = sql($sql);
       while($row = sqlget($res)) {
           $groups[$row['gid']] = $row['name'];
       }
	   
	   $GLOBALS['controller']->setHelpSection('curator-group');
       
       $sql = "
       SELECT 
           People.MID,
           People.LastName,
           People.FirstName,
           People.Login,
           departments.name AS name,
           departments.did AS did,
           departments_groups.gid AS gid,
           departments.not_in AS not_in
       FROM departments
       LEFT JOIN departments_groups ON (departments_groups.did=departments.did)
       LEFT JOIN People ON (People.MID=departments.mid)
       WHERE departments.application = '".DEPARTMENT_APPLICATION."'
       ORDER BY departments.name
       ";
       
       $res = sql($sql);
       while($row = sqlget($res)) {
           $departments[$row['did']]['firstname'] = $row['FirstName'];
           $departments[$row['did']]['lastname']  = $row['LastName'];
           $departments[$row['did']]['login']  = $row['Login'];
           $departments[$row['did']]['name']      = $row['name'];
           $departments[$row['did']]['not_in']      = $row['not_in'];
           $departments[$row['did']]['groups'][] = $row['gid'];
           if ($row['MID']) {
               $mids[] = $row['MID'];
           }
       }
       
       $sql = "
       SELECT DISTINCT
           People.MID,
           People.LastName,
           People.FirstName,
           People.Login
       FROM People 
       INNER JOIN deans ON (deans.MID=People.MID)";
       if (is_array($mids) && count($mids)) {
           $sql .= "WHERE People.MID NOT IN ('".join("','",$mids)."')";
       }
       $sql .="
       ORDER BY People.LastName";
       
       $res = sql($sql);
       while($row = sqlget($res)) {
           $deans[$row['MID']]['firstname'] = $row['FirstName'];
           $deans[$row['MID']]['lastname'] = $row['LastName'];
           $deans[$row['MID']]['login'] = $row['Login'];
/*           if (is_array($groups) && count($groups)) {
               $deans[$row['MID']]['groups'] = array_keys($groups);
           }
*/       }
          
       
       $smarty->assign_by_ref('deans',$deans);
       $smarty->assign_by_ref('departments',$departments);
       $smarty->assign_by_ref('groups',$groups);
       
       $GLOBALS['controller']->setHeader(_('Сводная таблица курирования групп'));
       $GLOBALS['controller']->captureFromReturn(CONTENT,$smarty->fetch('departments_groups.tpl'));
       $GLOBALS['controller']->terminate();       

?>