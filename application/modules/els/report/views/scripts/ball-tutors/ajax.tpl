<style>
	.tbl-result th, .tbl-result td {
		border: 1px solid black;
	}
	.tbl-result {
		border-collapse: collapse;
	}
	.tbl-result td, .tbl-result th  {
		text-align: center;
		vertical-align: middle;
		padding: 5px;
	}
</style>
<? if(empty($this->data)) : ?>
	Нет данных
<? else : ?>
	<table class="tbl-result">
		<tr>
			<th rowspan="2"><?=_('ФИО тьютора')?></th>
			<th rowspan="2"><?=_('Факультет')?></th>
			<th rowspan="2"><?=_('Кафедры')?></th>
			<th rowspan="2"><?=_('Сессия')?></th>
			<th rowspan="2"><?=_('ЗЕТ')?></th>			
			<th rowspan="2"><?=_('Семестр')?></th>			
			<th rowspan="2"><?=_('Количество студентов')?></th>
			<th rowspan="2"><?=_('Необходимо проверить работ всего')?></th>			
			<th rowspan="2"><?=_('Группы студентов')?></th>
			<th rowspan="2"><?=_('Программы обучения')?></th>			
			<th colspan="3"><?=_('Академическая активность')?></th>
			<th colspan="3"><?=_('Задание к разделу')?></th>
			<th colspan="3"><?=_('Рубежный контроль')?></th>			
			<th colspan="3"><?=_('Итоговый контроль')?></th>	
			<th rowspan="2"><?=_('Курс завершен')?></th>						
		</tr>
		<tr>
			<th><?=_('выставил')?></th>					
			<th><?=_('осталось')?></th>				
			<th><?=_('выставил детально')?></th>
			
			<th><?=_('выставил')?></th>					
			<th><?=_('осталось')?></th>				
			<th><?=_('выставил детально')?></th>
			
			<th><?=_('выставил')?></th>					
			<th><?=_('осталось')?></th>			
			<th><?=_('выставил детально')?></th>			
			
			<th><?=_('выставил')?></th>					
			<th><?=_('осталось')?></th>
			<th><?=_('выставил детально')?></th>
		</tr>
		<? foreach($this->data as $i) : ?>
			<tr>
				<td><?=$i['tutor_name'];?></td>
				<td><?=$i['faculty'];?></td>
				<td><?=$i['department'];?></td>
				<td><a target="_blank" href="<?=$this->baseUrl($this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $i['subject_id'])));?>"><?=$i['subject_name'];?></a></td>
				<td><?=$i['zet'];?></td>
				<td><?=$i['semester'];?></td>
				<td><?=$i['countStudents'];?></td>
				<td><?=$i['totalNeedBallStudentd'];?></td>				
				<td><?=(!empty($i['groups']))?(implode(', ', $i['groups'])):('Нет');?></td>
				<td><?=(!empty($i['programms']))?(implode(', ', $i['programms'])):('Нет');?></td>				
				<td><?=$i['ballCount'][1];?></td>
				<td>
					<? $need = ($i['totalNeedBallStudentd'] - $i['ballCount'][1]); ?>
					<?=($need < 0)?(0):($need);?>					
				</td>								
				<td><?=(!empty($i['ballCountDetail'][1])) ? implode(', ', $i['ballCountDetail'][1]) : ('');?></td>
				
				<td><?=$i['ballCount'][2];?></td>
				<td>
					<? $need = ($i['totalNeedBallStudentd'] - $i['ballCount'][2]); ?>
					<?=($need < 0)?(0):($need);?>					
				</td>								
				<td><?=(!empty($i['ballCountDetail'][2])) ? implode(', ', $i['ballCountDetail'][2]) : ('');?></td>
				
				<td><?=$i['ballCount'][3];?></td>
				<td>
					<? $need = ($i['totalNeedBallStudentd'] - $i['ballCount'][3]); ?>
					<?=($need < 0)?(0):($need);?>					
				</td>								
				<td><?=(!empty($i['ballCountDetail'][3])) ? implode(', ', $i['ballCountDetail'][3]) : ('');?></td>
				
				<td><?=$i['ballCount'][4];?></td>
				<td>
					<? $need = ($i['totalNeedBallStudentd'] - $i['ballCount'][4]); ?>
					<?=($need < 0)?(0):($need);?>					
				</td>				
				<td><?=(!empty($i['ballCountDetail'][4])) ? implode(', ', $i['ballCountDetail'][4]) : ('');?></td>	
				<td><?=($i['assignStudents'] > 0 ? 'Нет' : 'Да')?></td>
			</tr>
		<? endforeach; ?>
	</table>
	<p><?=_('Курс завершен - да, если нет ни одного доступного тьютору студента, назначенного на сессию.')?></p>
	<a href="<?=$this->baseUrl($this->url(array('module' => 'report', 'controller' => 'ball-tutors', 'action' => 'get-csv')));?>" target="_blank"><button><?=_('Выгрузить в csv')?></button></a>
<? endif; ?>