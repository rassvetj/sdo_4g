<?php if(count($this->content)) : ?>
<table class = "workload">
	<tr>
		<th rowspan="2">ФИО преподавателя</th>
		<th rowspan="2">Кафедра</th>
		<th rowspan="2">Факультет</th>
		<th rowspan="2">Дисциплина</th>
		<th rowspan="2">Номер группы</th>
		<th rowspan="2">Количество студентов</th>				
		<th colspan="3">Выполнено педагогической нагрузки, <?=($this->report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_ALL) ? ('проверенных работ') : ('часов');?></th>
	</tr>
	<?php if($this->report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_EXTENDED) : ?>
		<tr colspan="3">&nbsp;</tr>
	<?php else : ?>
		<tr>		
			<th>осенний семестр</th>		
			<th>весенний семестр</th>	
			<th>Всего</th>	
		<tr>		
	<?php endif; ?>
	<?php foreach($this->content as $i): ?>
	<tr>					
		<td><?=$i['fio'];?></td>														
		<td><?=$i['department'];?></td>														
		<td><?=$i['faculty'];?></td>																
		<td><a target="_blank" href="<?=$this->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'list-switcher' => 'current', 'subject_id' => $i['subject_id']), 'default', true);?>"><?=$i['name'];?></a></td>		
		<td><?=$i['groups'];?></td>														
		<td><?=$i['students'];?></td>														
		<?php if($this->report_type_id == HM_Workload_WorkloadModel::REPORT_TYPE_EXTENDED) : ?>
			<td colspan="3"><?=$i['workloadTotal'];?></td>	
		<?php else : ?>
			<td><?=$i['workloadAutumn'];?></td>	
			<td><?=$i['workloadSpring'];?></td>		
			<td><?=($i['workloadSpring'] + $i['workloadAutumn']);?></td>	
		<?php endif; ?>			
	</tr>
	<?php endforeach; ?>
</table>
<?php 
$params = array();
if(count($this->urlParams)){
	$params = $this->urlParams;
}
$params['controller'] = 'workload';
$params['action'] = 'get-workload-report';
$params['export'] = 'excel';

?>
<a href="<?=$this->baseUrl($this->url($params));?>"><button>Выгрузить в Excel</button></a>
<?php else : ?>
	нет данных
<?php endif; ?>