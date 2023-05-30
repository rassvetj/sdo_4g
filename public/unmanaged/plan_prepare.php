<?php
//	header("Content-Type: application/force-download; ".$GLOBALS['controller']->lang_controller->lang_current->encoding);
	header("Content-Type: text/xml;" . $GLOBALS['controller']->lang_controller->lang_current->encoding);

	header("Cache-control: private"); 
	header("Content-Disposition: filename=test.xml"); 

	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-cache");
	header("Cache-Control: post-check=0, pre-check=0");
	header("Pragma: no-cache");


	include("cfg.php");
	include("KDb_mysql_DEBUG.php");
	include("KUtil.php");
	include("KTemplate.php");
	$db=new KDb_mysql_DEBUG();
	include("db.php");
	$t=new KTemplate();


	require_once("../1.php");
	require_once("../_def.php");
	require_once("new_def.php");
//	require_once("schedule.lib.php");
		function date_loc2sql($str)
	{
   		 list($day,$month, $year) =split("[/.-]",$str);
	   	 return "$year-$month-$day";
	}

	function date_sql2loc($str)
	{
    	list($year,$month,$day) =split("[/.-]",$str);
	    return "$day.$month.$year";
	}



	$db2=$db->new_instance();
	$db3=$db->new_instance();
	$db4=$db->new_instance();
	// ­  ўе®¤Ґ - ­®¬Ґа  ЈагЇЇ, ¤ в _б Ё ¤ в _Ї® (­Ґ®Ўп§ вҐ«м­® зҐвЄ® Ї® ­Ґ¤Ґ«п¬,
	//  ўв®¬ вЁзҐбЄЁ ЎҐаҐвбп ­ з «® ­Ґ¤Ґ«Ё) 
	$arr_gid=$_GET['arr_gid'];
	$date_b=$_GET['date_b'];
	$date_e=$_GET['date_e'];

	$date_b=date_loc2sql($date_b);
	$date_e=date_loc2sql($date_e);

	$t->file('plan_prepare.htm','w');
	$t->block('w',array('Body','Group','Week','Day','Lesson','About_Group','Cicle'));
	$t->set('commandor_rank',"");
	$t->set('commandor_name',"");
	$t->set("GROUPS");
	

	foreach ($arr_gid as $gid) 
	{
    	$db->q("SELECT name AS title, gid AS group_id
    			FROM groupname WHERE gid='".$gid."'
    			");
        while ($db->nr())
        {
   			$db->assign($t);
	   	 	$t->parse('GROUPS','Group',true);
        }
	} 

	$db->q("SELECT MIN(cBegin) AS DATE_B, MAX(cEnd) AS DATE_E FROM
        		Courses");
    if ($db->nr())
    {
       	$DATE_B = $db->f("DATE_B");
	   	$DATE_E = $db->f("DATE_E");
    };
   
	$t->set("WEEKS");
	$db->q("SELECT DISTINCTROW floor((TO_DAYS(begin)-TO_DAYS('".$DATE_B."')-weekday('".$DATE_B."'))/7)+1 as week_number
            FROM schedule WHERE begin>='$date_b' AND end<'$date_e'
	  	    GROUP BY  floor((TO_DAYS(begin)-TO_DAYS('".$DATE_B."')+weekday('".$DATE_B."'))/7)+1
		   ");

    while ($db->nr())
    {

   		$db->assign($t);
        $t->set('DAYS');

        $db2->q("SELECT weekday(begin) AS day_week, DATE_FORMAT(begin,'%d.%m.%Y') AS day_date,
				TO_DAYS(begin)-TO_DAYS('".$DATE_B."')-2*(floor((TO_DAYS(begin)-TO_DAYS('".$DATE_B."')+weekday('".$DATE_B."'))/7))+1 AS day_number
			    FROM schedule
			    WHERE floor((TO_DAYS(begin)-TO_DAYS('".$DATE_B."')+weekday('".$DATE_B."'))/7)+1='".$db->f('week_number')."'
				GROUP BY weekday(begin) ORDER BY weekday(begin)");
		while($db2->nr())
		{
			$db2->assign($t);

            $t->set("LESSONS");

 			$db3->q("SELECT TIME_TO_SEC(begin)/60 AS ttt,
					SHEID FROM schedule 
					WHERE floor((TO_DAYS(begin)-TO_DAYS('".$DATE_B."')+weekday('".$DATE_B."'))/7)+1='".$db->f('week_number')."'
					AND weekday(begin)='".$db2->f('day_week')."' 
					ORDER BY begin
			");

			while ($db3->nr())
			{
				$db3->assign($t);
				$num=getlessonperiod(getallperiods(), (int)$db3->f('ttt'));
//                $t->set("lesson_id",$num);
                $t->set("lesson_id",(int)$db3->f('ttt'));
                $t->set("ABOUT_GROUP");
   					
               	$count = count($arr_gid);
               	foreach ($arr_gid as $gid) 
				{

				    $db4->q("SELECT departments.name AS d_name,
					groupuser.gid AS group_id,Courses.Title AS course_name
				    FROM (((departments INNER JOIN Courses
	                USING(did)) INNER JOIN schedule ON (schedule.CID=Courses.CID))
       	    	    INNER JOIN scheduleID ON(scheduleID.SHEID=schedule.SHEID))
       	       	    INNER JOIN groupuser ON(groupuser.MID=scheduleID.MID)
           	   		WHERE groupuser.gid = '".$gid."'
           	   		AND scheduleID.SHEID='".$db3->f('SHEID')."' GROUP BY groupuser.gid");
           	   		while($db4->nr())
           	   		{
           	   		    $t->set("CICLE");
   		              	$db4->assign($t);
		                $t->set("cicle_number",$db4->f('course_name')." (".$db4->f('d_name').")");
		                $t->parse('CICLE','Cicle',true);
	   		   			$t->parse('ABOUT_GROUP','About_Group',true);
           	   		}
           	   	
                }
				$t->parse('LESSONS','Lesson',true);

			}
		$t->parse('DAYS','Day',true);
			
		}

	$t->parse('WEEKS','Week',true);

    }


	echo $t->subst('Body');
?>