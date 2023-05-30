<?php
	if(empty($this->content)){
		echo _('Нет данных по заданным параметрам');
	} else {	
		echo "\xEF\xBB\xBF"; #BOM
		echo implode(';', $this->fields)."\r\n";
		
		foreach($this->content as $i){
			$groups 	= empty($i['groups']) 		? '' : implode(', ', $i['groups']);
			$programms	= empty($i['programms']) 	? '' : implode(', ', $i['programms']);
			echo $i['tutor_name'].';';
			echo $i['roles'].';';
			echo $i['roles_lessons'].';';
			echo $i['subject_faculty'].';';
			echo $i['subject_chair'].';';
			echo $i['subject_name'].';';
			echo $i['subject_external_id'].'.;'; # . - fix против преобазования MS Excel-ем строки в число с обрезанием последних символов
			echo date('d.m.Y', strtotime($i['subject_begin'])).';';
			echo date('d.m.Y', strtotime($i['subject_end'])).';';
			echo $i['zet'].';';
			echo $i['semester'].';';
			echo $i['countStudents'].';';
			echo $groups.';';
			echo $programms.';';
			echo str_replace('.', ',', $i['percent_lecture']).';';
			echo str_replace('.', ',', $i['percent_practice']).';';
			echo str_replace('.', ',', $i['percent_lab']).';';
			echo str_replace('.', ',', $i['percent_boundary_control']).';';
			echo $i['boundary_control_detail'].';';
			echo str_replace('.', ',', $i['percent_ipz']).';';
			echo str_replace('.', ',', $i['percent_plan_ready']).';';
			echo $i['subject_isDO'].';';
			echo $this->baseUrl($this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $i['subject_id']))).';';
			echo $i['subject_lection'].';';
			echo $i['subject_practice'].';';
			echo $i['subject_lab'].';';
			echo "\r\n";		
		}
	}
?>