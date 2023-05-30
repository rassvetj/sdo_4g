<?php

   require_once('1.php');
   require_once('lib/classes/CCourseAdaptor.class.php');

   if (!$s[login]) exitmsg("Пожалуйста, авторизуйтесь","/?$sess");

   $smarty = new Smarty_els();
   
   
   $GLOBALS['controller']->setHelpSection('curator-courses');


   $courses = CCoursesAdaptor::get_as_array();
      
   $sql = "
   SELECT 
       People.MID,
       People.LastName,
       People.FirstName,
       People.Login,
       departments.name AS name,
       departments.did AS did,
       departments_courses.cid AS cid
   FROM departments
   LEFT JOIN departments_courses ON (departments_courses.did=departments.did)
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
       $departments[$row['did']]['courses'][] = $row['cid'];
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
   unset($deans);
   while($row = sqlget($res)) {
       $deans[$row['MID']]['firstname'] = $row['FirstName'];
       $deans[$row['MID']]['lastname'] = $row['LastName'];
       $deans[$row['MID']]['login'] = $row['Login'];
//       if (is_array($courses) && count($courses)) {
//           $deans[$row['MID']]['courses'] = array_keys($courses);
//       }
   }
      
   
   $smarty->assign_by_ref('deans',$deans);
   $smarty->assign_by_ref('departments',$departments);
   $smarty->assign_by_ref('courses',$courses);
   
   $GLOBALS['controller']->setHeader(_('Сводная таблица курирования курсов'));
   
   $GLOBALS['controller']->captureFromReturn(CONTENT,$smarty->fetch('departments_courses.tpl'));
   $GLOBALS['controller']->terminate();
        

?>