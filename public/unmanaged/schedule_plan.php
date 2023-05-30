<?
require("1.php");
require_once('formula_calc.php');
require("schedule.lib.php");

switch($_GET['mode']) {
	case "plan":
		if( isset($_GET['sheid']) ) {
			$schedule = new Schedule;
			$schedule->init($_GET['sheid']);

			$schedule_for_smarty['type'] = $schedule->get_type();
			$schedule_for_smarty['subject'] = $schedule->get_subject();
			$schedule_for_smarty['targets'] = $schedule->get_targets();
			$schedule_for_smarty['time'] = $schedule->get_time();
			$schedule_for_smarty['date'] = $schedule->get_date();
			$schedule_for_smarty['period'] = $schedule->get_period();
			$schedule_for_smarty['studiedproblems'] = $schedule->get_studiedproblems();
			$schedule_for_smarty['room']  = $schedule->get_room();
	
			$current_year = date("Y", time());
	
			$smarty_tpl = new Smarty_els;
			$smarty_tpl->assign("schedule", $schedule_for_smarty);
			$smarty_tpl->assign("current_year", $current_year);
			$smarty_tpl->display("schedule_plan.tpl");
		}
	break;
	case "week_schedule":
		if( isset($_GET['begin_day']) ) {
            $GLOBALS['controller']->setView('DocumentPrint');
            $GLOBALS['controller']->captureFromOb(CONTENT);
			$week_schedule = new WeekSchedule;
			$week_schedule->init_by_begin_week($_GET['begin_day']);
            
			if($s['perm'] == 2)
            {
              	if(isset($s["tkurs"]) && is_array($s["tkurs"]))
                	$week_schedule->set_cids($s["tkurs"]);        
            }
            elseif($s['perm'] == 1)
            {
               	if(isset($s["skurs"]) && is_array($s["skurs"]))
               		$week_schedule->set_cids($s["skurs"]);        
            }   			
            
			$week_schedule_for_smarty = $week_schedule->get_as_array();
			$smarty_tpl = new Smarty_els;
            $smarty_tpl->assign('sitepath',$sitepath);
			$smarty_tpl->assign("begin_day", $week_schedule->begin_week);
			$smarty_tpl->assign("end_day", $week_schedule->end_week); 
			$smarty_tpl->assign("week_schedule", $week_schedule_for_smarty);
			$smarty_tpl->display("week_schedule_print.tpl");
            $GLOBALS['controller']->captureStop(CONTENT);
            $GLOBALS['controller']->terminate();
		}			
	break;
}
?>