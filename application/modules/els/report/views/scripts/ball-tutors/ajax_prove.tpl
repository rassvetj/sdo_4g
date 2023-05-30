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
			<th rowspan="1"><?=_('ФИО тьютора')?></th>
			<th rowspan="1"><?=_('Факультет')?></th>
			<th rowspan="1"><?=_('Кафедры')?></th>
			<th rowspan="1"><?=_('Сессия')?></th>
			<th rowspan="1"><?=_('ЗЕТ')?></th>			
			<th rowspan="1"><?=_('Количество студентов')?></th>			
			<th rowspan="1"><?=_('Работа прикреплена, но не проверена')?></th>
			<th rowspan="1"><?=_('Группы студентов')?></th>
			<th rowspan="1"><?=_('Программы обучения')?></th>			
		</tr>		
		<? foreach($this->data as $i) : ?>
			<tr>
				<td><?=$i['tutor_name'];?></td>
				<td><?=$i['faculty'];?></td>
				<td><?=$i['department'];?></td>
				<td><a target="_blank" href="<?=$this->baseUrl($this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $i['subject_id'])));?>"><?=$i['subject_name'];?></a></td>
				<td><?=$i['zet'];?></td>
				<td><?=$i['countStudents'];?></td>				
				<td><?=$i['proveCount'];?></td>
				<td><?=(!empty($i['groups']))?(implode(', ', $i['groups'])):('Нет');?></td>
				<td><?=(!empty($i['programms']))?(implode(', ', $i['programms'])):('Нет');?></td>											
			</tr>
		<? endforeach; ?>
	</table>
	<a href="<?=$this->baseUrl($this->url(array('module' => 'report', 'controller' => 'ball-tutors', 'action' => 'get-csv-prove')));?>" target="_blank"><button>Выгрузить в csv</button></a>
<? endif; ?>