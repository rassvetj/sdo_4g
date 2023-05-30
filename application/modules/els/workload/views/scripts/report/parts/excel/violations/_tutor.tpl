<h3><?=$this->caption;?></h3>
Период: <?=$this->period;?>
<?php if(count($this->content)) : ?>
<table style="margin: 0 auto; border-collapse: collapse; text-align: center;">
	<tr>
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Дисциплина</th>					
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Номер группы</th>					
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Количество студентов</th>							
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Заданий без оценки</th>
		<th colspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Направление приветственного письма</th>
		<th colspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Проверка практических заданий</th>
		<th colspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Консультирование на предметном форуме</th>
		<th colspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Направление мотивированного заключения</th>
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Всего нарушений</th>
		<th rowspan="2" style="border: 1px solid black; font-weight: bold; vertical-align: middle;">Кол-во непроверенных  работ студентов</th>		
	</tr>
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">всего нарушений</th>
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">средняя продолжительность нарушения сроков, дней</th>
		
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">всего нарушений</th>
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">средняя продолжительность нарушения сроков, дней</th>
		
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">всего нарушений</th>
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">средняя продолжительность нарушения сроков, дней</th>
		
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">всего нарушений</th>
		<th style="border: 1px solid black; font-weight: bold; vertical-align: middle;">средняя продолжительность нарушения сроков, дней</th>
	</tr>
	<tr></tr>
	<?php foreach($this->content as $i): ?>
	<tr>
		<td style="border: 1px solid black; padding: 3px;"><?=$i['name'];?></td>													
		<td style="border: 1px solid black; padding: 3px;"><?=$i['groups'];?></td>															
		<td style="border: 1px solid black; padding: 3px;"><?=$i['students'];?></td>												
		<td style="border: 1px solid black; padding: 3px;"><?=$i['notBall'];?></td>		
		<td style="border: 1px solid black; padding: 3px;"><?=$i['vi_MessageCount'];?></td>
		<td style="border: 1px solid black; padding: 3px;"><?=$i['T_message'];?></td>
		<td style="border: 1px solid black; padding: 3px;"><?=$i['vi_SubjectCount'];?></td>
		<td style="border: 1px solid black; padding: 3px;"><?=$i['T_subject_avg'];?></td>
		<td style="border: 1px solid black; padding: 3px;"><?=$i['vi_ForumCount'];?></td>
		<td style="border: 1px solid black; padding: 3px;"><?=$i['T_forum_avg'];?></td>
		<td style="border: 1px solid black; padding: 3px;"><?=$i['vi_VedomostCount'];?></td>
		<td style="border: 1px solid black; padding: 3px;"><?=$i['T_vedomost'];?></td>
		<td style="border: 1px solid black; padding: 3px;"><?=$i['count_violations'];?></td>		
		<td style="border: 1px solid black; padding: 3px;"><?=$i['newWorks'];?></td>		
	</tr>
	<?php endforeach; ?>
</table>
<?php else : ?>
	нет данных
<?php endif; ?>