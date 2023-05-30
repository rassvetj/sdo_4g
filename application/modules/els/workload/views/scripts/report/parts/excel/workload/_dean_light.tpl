<h3><?=$this->caption;?></h3>
Период: <?=$this->period;?>
<?php if(count($this->content)) : ?>
<table class = "workload" style="margin: 0 auto; border-collapse: collapse; text-align: center;">
	<tr>
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">ФИО преподавателя</th>
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Кафедра</th>
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Факультет</th>
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Дисциплина</th>
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Номер группы</th>
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Количество студентов</th>		
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Выполнено педагогической нагрузки, <?=($this->report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_ALL) ? ('проверенных работ') : ('часов');?></th>		
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Дата начала</th>
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Дата окончания</th>
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Дата продления</th>		
	</tr>	
	
	<?php foreach($this->content as $i): ?>
	<tr>					
		<td style="border: 1px solid black; padding: 3px;"><?=$i['fio'];?></td>														
		<td style="border: 1px solid black; padding: 3px;"><?=$i['department'];?></td>														
		<td style="border: 1px solid black; padding: 3px;"><?=$i['faculty'];?></td>														
		<td style="border: 1px solid black; padding: 3px;"><a target="_blank" href="<?=$this->serverUrl();?>/<?=$this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'list-switcher' => 'current', 'subject_id' => $i['subject_id']), 'default', true);?>"><?=$i['name'];?></a></td>														
		<td style="border: 1px solid black; padding: 3px;"><?=$i['groups'];?></td>														
		<td style="border: 1px solid black; padding: 3px;"><?=$i['students'];?></td>		
		<td style="border: 1px solid black; padding: 3px;"><?=str_replace('.', ',', $i['workloadTotal']);?></td>			
		<td style="border: 1px solid black; padding: 3px;"><?=$i['subject_begin'];?></td>				
		<td style="border: 1px solid black; padding: 3px;"><?=$i['subject_end'];?></td>				
		<td style="border: 1px solid black; padding: 3px;"><?=$i['subject_debt'];?></td>		
	</tr>
	<?php endforeach; ?>
</table>
<?php else : ?>
	нет данных
<?php endif; ?>