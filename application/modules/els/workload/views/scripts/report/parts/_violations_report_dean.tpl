<?php if(count($this->content)) : ?>
<table class = "workload">
	<tr>
		<th rowspan="2">ФИО преподавателя</th>					
		<th rowspan="2">Кафедра</th>					
		<th rowspan="2">Факультет</th>					
		<th colspan="2">Нарушения сроков реагирования</th>	
		<th rowspan="2">Заданий без оценки</th>
		<th rowspan="2">Ссылки на нарушения</th>
		<th rowspan="2">Кол-во непроверенных  работ студентов</th>		
	</tr>
	<tr>
		<th>всего</th>					
		<th>средняя продолжительность нарушения сроков, дней</th>	
	</tr>	
	<?php foreach($this->content as $i): ?>
	<tr>	
		<td><?=$i['fio'];?></td>																													
		<td><?=$i['department'];?></td>																													
		<td><?=$i['faculty'];?></td>																													
		<td><?=$i['count_violations'];?></td>																													
		<td><?=$i['avg'];?></td>
		<td><?=$i['notBall'];?></td>	
		<td><?php 
			if(count($i['urls'])){
				foreach($i['urls'] as $l){
					echo '<a href="'.$l['url'].'" target="_blank">'.$l['name'].'</a>&nbsp;';
				}
			}
		 ?></td>
		<td><?=$i['newWorks'];?></td>		 
	</tr>
	<?php endforeach; ?>
</table>
<?php 
	$params = array();
	if(count($this->urlParams)){
		$params = $this->urlParams;
	}
	$params['controller'] = 'violations';
	$params['action'] = 'get-violations-report';
	$params['export'] = 'excel';
?>
<a href="<?=$this->baseUrl($this->url($params));?>"><button>Выгрузить в Excel</button></a>
<?php else : ?>
	нет данных
<?php endif; ?>