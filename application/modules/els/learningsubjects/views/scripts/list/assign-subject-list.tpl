<?php if (!$this->gridAjaxRequest):?>	
	<p><b>Назначение курса для сделедующих предметов:</b></p><br>
	<table>
		<tr style="text-align: left;">
			<th style="padding: 0 5px 0 5px;">Название</th>
			<th style="padding: 0 5px 0 5px;">Направление подготовки</th>
			<th style="padding: 0 5px 0 5px;">Специализация</th>
			<th style="padding: 0 5px 0 5px;">ЗЕТ</th>
			<th style="padding: 0 5px 0 5px;">Год</th>
			<th style="padding: 0 5px 0 5px;">Контроль</th>
			<th style="padding: 0 5px 0 5px;">Сем.</th>
		</tr>
		<?php foreach($this->learningSubjects as $ls) : ?>
			<tr>
				<td style="padding: 0 5px 0 5px;"><?=$ls['name'];?></td>
				<td style="padding: 0 5px 0 5px;"><?=$ls['direction'];?></td>
				<td style="padding: 0 5px 0 5px;"><?=$ls['specialisation'];?></td>
				<td style="padding: 0 5px 0 5px;"><?=$ls['zet'];?></td>
				<td style="padding: 0 5px 0 5px;"><?=$ls['year'];?></td>
				<td style="padding: 0 5px 0 5px;"><?=$ls['control'];?></td>
				<td style="padding: 0 5px 0 5px;"><?=$ls['semester'];?></td>
			</tr>					
		<?php endforeach; ?>
	</table>
	<br>
<?php endif;?>
<?=$this->grid?>