<?php

       require_once('1.php');

       if (!$s[login]) exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");

       $smarty = new Smarty_els();

       $CHILDREN=array();
       get_orgunits_children_array($CHILDREN);
       
       $sql = "
       SELECT 
           People.MID,
           People.LastName,
           People.FirstName,
           People.Login,
           departments.name AS name,
           departments.did AS did,
           departments.not_in AS not_in,
           departments_soids.soid AS soid
       FROM departments
       LEFT JOIN departments_soids ON (departments_soids.did=departments.did)
       LEFT JOIN People ON (People.MID=departments.mid)
       WHERE departments.application = '".DEPARTMENT_APPLICATION."'
       ORDER BY departments.name
       ";
       
       $res = sql($sql);
       while($row=sqlget($res)) {
           
           $departments[$row['did']]['firstname'] = $row['FirstName'];
           $departments[$row['did']]['lastname']  = $row['FirstName'];
           $departments[$row['did']]['login']     = $row['Login'];
           $departments[$row['did']]['name']      = $row['name'];
           $departments[$row['did']]['not_in']      = $row['not_in'];
           $departments[$row['did']]['orgunits'][] = $row['soid'];
           $departments[$row['did']]['orgunits'] = array_merge($departments[$row['did']]['orgunits'],get_orgunits_children($row['soid']));
           if ($row['MID']>0) {
               $mids[] = $row['MID'];
           }
           if ($row['soid']>0) {
               $soids[] = $row['soid'];
           }
           
       }
              
       $sql = "
       SELECT DISTINCT
           People.MID,
           People.LastName,
           People.FirstName,
           People.Login
       FROM People 
       INNER JOIN deans ON (deans.MID=People.MID)
       LEFT JOIN structure_of_organ ON (structure_of_organ.mid=deans.MID)
       WHERE structure_of_organ.mid IS NULL ";
       if (is_array($mids) && count($mids)) {
           $sql .= "AND People.MID NOT IN ('".join("','",$mids)."')";
       }
       $sql .="
       ORDER BY People.LastName";
       
       $res = sql($sql);
       unset($deans); $get_all_orgunits = false;
       while($row=sqlget($res)) {           
           $get_all_orgunits = true;
           
           $deans[$row['MID']]['firstname'] = $row['FirstName'];
           $deans[$row['MID']]['lastname'] = $row['LastName'];
           $deans[$row['MID']]['login'] = $row['Login'];
           
       }
       
       if ($get_all_orgunits) {
           $sql = "SELECT soid, name FROM structure_of_organ WHERE type=2 ORDER BY name";
       } else {
           if (is_array($soids) && count($soids)) {
               $soids = array_unique($soids);
               $sql = "SELECT soid, name FROM structure_of_organ WHERE type=2 AND soid IN ('".join("','",$soids)."')";
           }
       }
       
       unset($orgunits);
       if ($get_all_orgunits || (is_array($soids) && count($soids))) {
           $res = sql($sql);
           while($row=sqlget($res)) {
               $orgunits[$row['soid']] = $row['name'];
           }           
       }
       
       $smarty->assign_by_ref('deans',$deans);
       $smarty->assign_by_ref('departments',$departments);
       $smarty->assign_by_ref('orgunits',$orgunits);
       
       $GLOBALS['controller']->setHeader(_('Сводная таблица курирования оргединиц'));
       $GLOBALS['controller']->captureFromReturn(CONTENT,$smarty->fetch('departments_soids.tpl'));  
       $GLOBALS['controller']->terminate();
       
       function get_orgunits_children_array(&$children) {
           $sql = "SELECT DISTINCT soid, owner_soid FROM structure_of_organ WHERE type=2";
           $res = sql($sql);
           while($row=sqlget($res)) {
               $children[$row['owner_soid']][] = $row['soid'];
           }
       }
       
       function get_orgunits_children($orgunit) {
           global $CHILDREN;
           
           $ret = array();
           
           if ($orgunit>0) {
               if (isset($CHILDREN[$orgunit])) {
                   if (is_array($CHILDREN[$orgunit]) && count($CHILDREN[$orgunit])) {
                       foreach($CHILDREN[$orgunit] as $v) {
                           $ret[] = $v;
                           $ret = array_merge($ret,get_orgunits_children($v));
                       }
                   }
               }
           }
           return $ret;
       }
       
?>