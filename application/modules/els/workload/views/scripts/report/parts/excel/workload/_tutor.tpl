<h3><?=$this->caption;?></h3>
Период: <?=$this->period;?>
<?php if(count($this->content)) : ?>
<table style="margin: 0 auto; border-collapse: collapse; text-align: center;">
	<tr>
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Дисциплина</th>
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Номер группы</th>
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Количество студентов</th>		
		<th colspan="3" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Выполнено педагогической нагрузки, <?=($this->report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_ALL) ? ('проверенных работ') : ('часов');?></th>				
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Дата начала</th>
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Дата окончания</th>
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Дата продления</th>	
	</tr>
	<?php if($this->report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_EXTENDED) : ?>
		<th colspan="3" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Всего</tr>
	<?php else : ?>
		<tr>
			<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">осенний семестр</th>
			<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">весенний семестр</th>
			<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Всего</th>		
		</tr>
	<?php endif; ?>
		
	<?php foreach($this->content as $i): ?>
	<tr>					
		<td style="border: 1px solid black; padding: 3px;"><a target="_blank" href="<?=$this->serverUrl();?>/<?=$this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'list-switcher' => 'current', 'subject_id' => $i['subject_id']), 'default', true);?>"><?=$i['name'];?></a></td>													
		<td style="border: 1px solid black; padding: 3px;"><?=$i['groups'];?></td>																											
		<td style="border: 1px solid black; padding: 3px;"><?=$i['students'];?></td>
		<?php if($this->report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_EXTENDED) : ?>
			<td colspan="3" style="border: 1px solid black; padding: 3px;"><?=str_replace('.', ',', $i['workloadTotal']);?></td>	
		<?php else : ?>		
			<td style="border: 1px solid black; padding: 3px;"><?=str_replace('.', ',', $i['workloadAutumn'] );?></td>													
			<td style="border: 1px solid black; padding: 3px;"><?=str_replace('.', ',', $i['workloadSpring'] );?></td>													
			<td style="border: 1px solid black; padding: 3px;"><?=str_replace('.', ',', ($i['workloadSpring'] + $i['workloadAutumn']) );?></td>	
		<?php endif; ?>
		<td style="border: 1px solid black; padding: 3px;"><?=$i['subject_begin'];?></td>				
		<td style="border: 1px solid black; padding: 3px;"><?=$i['subject_end'];?></td>				
		<td style="border: 1px solid black; padding: 3px;"><?=$i['subject_debt'];?></td>		
	</tr>
	<?php endforeach; ?>
</table>
<?php else : ?>
	нет данных
<?php endif; ?>

