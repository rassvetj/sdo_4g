<style>
	.tbl-result th, .tbl-result td {
		border: 1px solid black;
	}
	.tbl-result {
		border-collapse: collapse;
	}
	.tbl-result td {
		padding: 3px;
	}
</style>
<? if(empty($this->data)) : ?>
	Нет данных
<? else : ?>
	<table class="tbl-result">
		<tr>
			<th rowspan="1">Текущая/прошедшая</th>
			<th rowspan="1">Дата начала</th>
			<th rowspan="1">Дата окончания</th>
			<th rowspan="1">Дата продления</th>			
			<th rowspan="1">ФИО тьютора</th>
			<th rowspan="1">Сессия доступна тьютору</th>
			<th rowspan="1">Последний тьютор</th>
			<th rowspan="1">Факультет</th>
			<th rowspan="1">Кафедры</th>
			<th rowspan="1">ID сессии (1С)</th>
			<th rowspan="1">Сессия</th>
			<th rowspan="1">ЗЕТ</th>									
			<th rowspan="1">Нет реакции преподавателя</th>
			<th rowspan="1">Группы студентов</th>
			<th rowspan="1">Программы обучения</th>			
			<th rowspan="1">Тьютор с заблокированной записью</th>			
			<th rowspan="1">Детально</th>			
		</tr>		
		<? foreach($this->data as $i) : ?>
			<?php 
				$unchecked_works_data = $i['unchecked_works_data'];
				$links 			 	  = array();
				foreach($unchecked_works_data as $r){ 
					$links[] = '<a target="_blank" href="http://'.$_SERVER['SERVER_NAME'].'/interview/index/index/lesson_id/'.$r['lesson_id'].'/subject_id/'.$r['subject_id'].'/user_id/'.$r['student_id'].'">'.$r['student_name'].'</a>';
				}
			?>
		
			<tr>
				<td><?=$i['isCurrent'];?></td>
				<td><?=$i['begin'];?></td>
				<td><?=$i['end'];?></td>
				<td><?=$i['time_ended_debt'];?></td>
				<td><?=$i['tutor_name'];?></td>
				<td><?=$i['isAvailableSubject'];?></td>
				<td><?=$i['isLastTutor'];?></td>
				<td><?=$i['faculty'];?></td>				
				<td><?=$i['department'];?></td>
				<td><?=$i['subject_external_id'];?></td>
				<td><a target="_blank" href="<?=$this->baseUrl($this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $i['subject_id'])));?>"><?=$i['subject_name'];?></a></td>
				<td><?=$i['zet'];?></td>								
				<td><?=$i['proveCount'];?></td>
				<td><?=(!empty($i['groups']))?(implode(', ', $i['groups'])):('Нет');?></td>
				<td><?=(!empty($i['programms']))?(implode(', ', $i['programms'])):('Нет');?></td>	
				<td><?=$i['blocked_tutor_name'];?></td>		
				<td><?=implode(', ', $links);?></td>
			</tr>
		<? endforeach; ?>
	</table>
	<a href="<?=$this->baseUrl($this->url(array('module' => 'report', 'controller' => 'unchecked-works-new', 'action' => 'get-csv')));?>" target="_blank"><button>Выгрузить в csv</button></a>
<? endif; ?>