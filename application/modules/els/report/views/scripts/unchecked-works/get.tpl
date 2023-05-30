<style>
	.tbl-result th, .tbl-result td {
		border: 1px solid black;
	}
	.tbl-result {
		border-collapse: collapse;
	}
</style>
<? if(empty($this->data)) : ?>
	<p><? echo _('Нет данных'); ?></p>
<? else : ?>
	<table class="tbl-result">
		<tr>
			<th rowspan="1"><?=_('Текущая/прошедшая')?></th>
			<th rowspan="1"><?=_('Дата начала')?></th>
			<th rowspan="1"><?=_('Дата окончания')?></th>
			<th rowspan="1"><?=_('Дата продления')?></th>			
			<th rowspan="1"><?=_('ФИО тьютора')?></th>
			<th rowspan="1"><?=_('Сессия доступна тьютору')?></th>
			<th rowspan="1"><?=_('Последний тьютор')?></th>
			<th rowspan="1"><?=_('Факультет')?></th>
			<th rowspan="1"><?=_('Кафедры')?></th>
			<th rowspan="1"><?=_('ID сессии (1С)')?></th>
			<th rowspan="1"><?=_('Сессия')?></th>
			<th rowspan="1"><?=_('ЗЕТ')?></th>									
			<th rowspan="1"><?=_('Нет реакции преподавателя')?></th>
			<th rowspan="1"><?=_('Группы студентов')?></th>
			<th rowspan="1"><?=_('Программы обучения')?></th>			
			<th rowspan="1"><?=_('Тьютор с заблокированной записью')?></th>			
		</tr>		
		<? foreach($this->data as $i) : ?>
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
			</tr>
		<? endforeach; ?>
	</table>
	<a href="<?=$this->baseUrl($this->url(array('module' => 'report', 'controller' => 'unchecked-works', 'action' => 'get-csv')));?>" target="_blank"><button><?=_('Выгрузить в csv')?></button></a>
<? endif; ?>