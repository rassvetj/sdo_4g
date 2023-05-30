<?php
$out = '
<div id="schedule-daily">
<div id="schedule-daily-wrapper-1">
';
$formTitles =  HM_Interview_InterviewModel::getTypes(); //Набор статусов задания (определён в HM_Interview_InterviewModel) 

if ($this->empty){
	$out .= _('Отсутствуют данные для отображения');
}
else{

	$out .= '
		<table width=100% class=main cellspacing=0>
			<tr>
				<th>'._('Название').'</th>
				<th>'.$formTitles[HM_Interview_InterviewModel::MESSAGE_TYPE_TASK].'</th>
				<th>'.$formTitles[HM_Interview_InterviewModel::MESSAGE_TYPE_QUESTION].'</th>
				<th>'.$formTitles[HM_Interview_InterviewModel::MESSAGE_TYPE_ANSWER].'</th>
				<th>'.$formTitles[HM_Interview_InterviewModel::MESSAGE_TYPE_TEST].'</th>
				<th>'.$formTitles[HM_Interview_InterviewModel::MESSAGE_TYPE_CONDITION].'</th>
				<th>'.$formTitles[HM_Interview_InterviewModel::MESSAGE_TYPE_BALL].'</th>
				<th>'._('Всего').'</th>
			</tr>
		';
	foreach ($this->subjects as $s){
			// временные переменные для подсчета количества вариантов в данном статусе
			$task = 0;
			$question = 0;
			$answer = 0;
			$test = 0;
			$condition = 0;
			$ball = 0;
			$totalforcourse = 0; //сумма  по строкам для каждого учебного курса
			$totalforlesson = 0; //сумма  по строкам для каждого занятия
			$lessons = '';
			
		foreach ($s['lessons'] as $l){
			$totalforlesson = array_sum(array($l['task'],$l['question'],$l['answer'],$l['test'],$l['condition'],$l['ball']));
			$totalforcourse = $totalforcourse + $totalforlesson; //
			$task = $task + $l['task'];
			$question = $question + $l['question'];
			$answer = $answer + $l['answer'];
			$test = $test + $l['test'];
			$condition = $condition + $l['condition'];
			$ball = $ball + $l['ball'];
			
			
			$lessons .= '
				<tr>
					<td class=task_lesson>'.$l['schetitle'].'</td>
					<td>'.$l['task'].'</td>
					<td>'.$l['question'].'</td>
					<td>'.$l['answer'].'</td>
					<td>'.$l['test'].'</td>
					<td>'.$l['condition'].'</td>
					<td>'.$l['ball'].'</td>
					<td><strong><a href="'.$l['url'].'">'.$totalforlesson.'</a></strong></td>
				</tr>
				';
			
			
		}
		$courses = '
			<tr class=task_course>
				<td class=task_course>'.$s['subname'].'</td>
				<td>'.$task.'</td>
				<td>'.$question.'</td>
				<td>'.$answer.'</td>
				<td>'.$test.'</td>
				<td>'.$condition.'</td>
				<td>'.$ball.'</td>
				<td>'.$totalforcourse.'</td>
			</tr>';
			$tasks = $tasks + $task;
			$questions = $questions + $question;
			$answers = $answers + $answer;
			$tests = $tests + $test;
			$conditions = $conditions + $condition;
			$balls = $balls + $ball;
			$totalforall = $totalforall + $totalforcourse; // сумма по столбцам.
			$out .=$courses.$lessons;
	}
	
	
	$out .= '
			<tr>
				<td><strong>'._('Всего').'</strong></td>
				<td><strong>'.$tasks.'</strong></td>
				<td><strong>'.$questions.'</strong></td>
				<td><strong>'.$answers.'</strong></td>
				<td><strong>'.$tests.'</strong></td>
				<td><strong>'.$conditions.'</strong></td>
				<td><strong>'.$balls.'</strong></td>
				<td><strong>'.$totalforall.'</strong></td>
			</tr>';
	$out .= '</table>';

}
$out .= '
</div>
</div>';
echo $out;
?>