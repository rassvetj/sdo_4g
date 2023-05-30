<?php 		
	#$course				= ceil($this->subject->semester/2); # курс, число
	$group_marks		= array(); # кол-во каждой из оценок для статистики в конце таблицы
	$course				= !empty($this->course) ? $this->course : ceil($this->semester/2); # курс, число
	
	$signerCaption		= $this->isProgrammFilial ? 'Директор филиала' : 'Декан факультета';
?>
	
<style>
	.rotateText {
		transform: rotate(-90deg);
		writing-mode: tb-rl;      	 
		vertical-align: bottom;
		font-size: 14px;
		line-height:0.40;
		transform-origin: center top 0;	 
		overflow-x: visible;
		padding: 0px;
		padding-left: 0px;	 
		margin:0px;	
	}
	
	.tbl-content{
		border-collapse: collapse;
	}
  
	.tbl-content td, .tbl-content th {
		border: 1px solid black;
	}
 
	.info-row th{
		background-color: #b3b3b3;
		height:12px;
		line-height:0.40;
		padding-top: 7px;
		margin: 0px;
		vertical-align: middle;
	}
	
	.data-row td{
		height:20px;
	}
	
	td {
		height: 20px;
	}
	
	.tbl-footer td{
		height:12px;
		font-size: 14px;
		line-height:0.40;		
	}
 
 .caption_3:before{ content: '№ зачетной книжки'; }
 
 .caption_4_1:before{ content: 'Итоговый текущий рейтинг'; }
 .caption_4_2:before{ content: 'обучающегося за семестр';  }
 .caption_4_3:before{ content: '(до 80 баллов)'; }
 
 .caption_4_4:before{ content: 'Интегральный'; }
 .caption_4_5:before{ content: 'текущий рейтинг'; }
 
 
 .caption_5_1:before{ content: 'Подпись педагогического'; }
 .caption_5_2:before{ content: 'работника, проводившего текущий'; }
 .caption_5_3:before{ content: 'контроль успеваемости'; }
 .caption_5_4:before{ content: 'обучающихся в семестре'; }
 
 
 
 .caption_6_1:before{ content: 'Pубежный рейтинг обучающегося'; }
 .caption_6_2:before{ content: 'на зачете / экзамене'; }
 .caption_6_3:before{ content: '(до 20 баллов)'; }
 
 .caption_7_1:before{ content: 'Академический рейтинг'; }
 .caption_7_2:before{ content: 'обучающегося  по  учебной'; }
 .caption_7_3:before{ content: 'дисциплине'; }
 .caption_7_4:before{ content: '(сумма гр. 4  и  6)'; }
 
 .caption_8:before{ content: 'Аттестационная оценка'; }
 
 .caption_9:before{ content: 'Подписи  экзаменаторов'; }
 </style>


<table style="text-align: center; width:100%" >
	<tr><td>
		ФЕДЕРАЛЬНОЕ ГОСУДАРСТВЕННОЕ БЮДЖЕТНОЕ ОБРАЗОВАТЕЛЬНОЕ УЧРЕЖДЕНИЕ ВЫСШЕГО ОБРАЗОВАНИЯ<br />
		«РОССИЙСКИЙ ГОСУДАРСТВЕННЫЙ СОЦИАЛЬНЫЙ УНИВЕРСИТЕТ»<br />
		<?=$this->faculty?><br />
	</td></tr>
	<tr><td style="font-weight: bold;">ЗАЧЕТНО-ЭКЗАМЕНАЦИОННАЯ ВЕДОМОСТЬ № <?=$this->marksheet_external_id?></td></tr>
	
	<tr><td style="font-size: 14px; text-align: left;">
		<span style="text-indent: 15px;">Форма обучения	<span style="text-decoration: underline;"><?=$this->form_study?></span></span>
		<span style="text-indent: 15px;">Группа  <span style="text-decoration: underline;"><?=$this->groups;?></span></span>
		<span style="text-indent: 15px;"><span style="text-decoration: underline;"><?=$this->semester?>      семестр (<?=(!empty($course))?$course:'        '?> курс)</span></span>
		<span style="text-indent: 15px;">Учебный год <span style="text-decoration: underline;"><?=$this->years?></span></span>
		<br>
		<span style="text-indent: 15px;">Дисциплина <span style="text-decoration: underline;"><?=$this->subject->name;?> (<?=$this->exam_type_name?>)</span></span>
		<span style="text-indent: 15px;">   Номер попытки <span style="text-decoration: underline;"><?=$this->attempt;?></span></span>
		<br>
		<span style="text-indent: 15px;">Ф.И.О. экзаменаторов <span style="text-decoration: underline;"><?=$this->tutor;?></span></span>
	</td></tr>		
</table>

<table style="text-align: center; width:100%; font-size: 14px;" class="tbl-content">
	<thead>
		<tr >
			<th style="width:10px; height:240px;">№<br />п/п</th>
			<th style="width:170px;">Ф.И.О. обучающегося</th>
			
			<th  style="width:15px; padding-left: 0px; padding-right: 45%;" nowrap>
				<div class="rotateText caption_3" ></div>
			</th>
			
			<th  style="width:20px; padding-left: 10px; padding-right: 20%; padding-bottom: 80px; " nowrap>
				<div class="rotateText" style="padding-bottom: 0px;">													
					<?=($this->isModuleSubject)
						? ('<span class="caption_4_4"></span>
							<br />
							<span class="caption_4_5">') 
						
						: ('<span class="caption_4_1"></span>
							<br />
							<span class="caption_4_2"></span>
							<br />
							<span class="caption_4_3"></span>')?>
				</div>
			</th>
			 
			<th style="width:35px; padding-left: 5px;  padding-right: 20%;  padding-bottom: 110px;" nowrap>
				<div class="rotateText " >
					<span class="caption_5_1"></span>
					<br />
					<span class="caption_5_2"></span>
					<br />
					<span class="caption_5_3"></span>
					<br />
					<span class="caption_5_4"></span>
				</div>
			</th>
			
			<th style="width:30px; padding-left: 0px;  padding-right: 20%;  padding-bottom: 100px;" nowrap>
				<div class="rotateText">
					<span class="caption_6_1"></span>
					<br />
					<span class="caption_6_2"></span>
					<br />
					<span class="caption_6_3"></span>			
				</div>
			</th>
			<th style="width:30px; padding-left: 5px;  padding-right: 16%;  padding-bottom: 90px;" nowrap>
				<div class="rotateText">
					<span class="caption_7_1"></span>
					<br />
					<span class="caption_7_2"></span>
					<br />
					<span class="caption_7_3"></span>
					<br />
					<span class="caption_7_4"></span>
				</div>
			</th>
			<th style="width:115px; padding-left: 0px; padding-right: 48%; padding-bottom: 120px;" nowrap>
				<div class="rotateText caption_8" ></div>			
			</th>
			<th style="width:60px; padding-left: 0px;  padding-right: 48%;  padding-bottom: 60px;" nowrap>
				<div class="rotateText caption_9" ></div>			
			</th>			
		</tr>
		<tr class="info-row">
			<th>1</th>
			<th>2</th>
			<th>3</th>
			<th>4</th>
			<th>5</th>
			<th>6</th>
			<th>7</th>
			<th>8</th>
			<th>9</th>
		</tr>
	</thead>
	<tbody>
		<?php $row_number = 0; ?>
		<?php foreach($this->persons as $student): ?>
			<?php $row_number++;?>
			<tr >
				<td><?=$row_number;?>.</td>
				<td style="text-align:left"><?=$this->escape($student->LastName.' '.$student->FirstName.' '.$student->Patronymic);?></td>
				<td><?=$student->recordBookNumber;?></td>
				<td><?=$student->mark_current;?></td> 
				<td></td>
				<td><?=$student->mark_landmark;?></td>
				<td><?=$student->mark_total;?></td>
				<td style="padding:0px; margin: 0px; width:20px" cellpadding="0">
					<?php			
					if(!isset($group_marks[$student->mark_5_text])){
						$group_marks[$student->mark_5_text] = 1;	
					} else {
						$group_marks[$student->mark_5_text]++;
					}				
					?>
					<?=$student->mark_5_text;?>				
				</td>
				<td></td>			
			</tr>			
		<?php endforeach;?>
		
	</tbody>
</table>

<?php 
	# для реальной ведомости, не черновика:
	$rowr_per_first_page	= 10;
	$rowr_per_other_page	= 17;
	$skip_rows				= array(7,8,9); # явно заданное кол-во строк для принудительного разрыва страницы
	$skip_rows_other		= array(14, 15, 16); # кол-во строк для НЕпервой страницы, при которых нужно делать разрыв страницы
	
	# для черновика
	#$rowr_per_first_page	= 12;
	#$rowr_per_other_page	= 19;
	#$skip_rows				=  array(9,10); # явно заданное кол-во строк для принудительного разрыва страницы
	#$skip_rows_other		= array(16, 17); # кол-во строк для НЕпервой страницы, при которых нужно делать разрыв страницы
	
	$ostatok				= ($row_number - $rowr_per_first_page) % $rowr_per_other_page; 
?>
<?php if(
		in_array($row_number, $skip_rows)
		||
		($row_number > $rowr_per_first_page &&  in_array($ostatok, $skip_rows_other))		
	):?>
	<? #принудительный разрыв страницы ?>
	<div style="page-break-before: always;"></div>
<?php endif;?>


<table style="width:100%; padding-top:25px" class="tbl-footer">
	<tr>
		<td style="width:30%; padding-right:20px;">Итого:</td><td></td><td></td>
	</tr>	
	<tr>
        <td>"зачтено" <span style="text-decoration: underline;"><?=(int)$group_marks['зачтено']?></span></td> <td><?=$signerCaption?></td> <td nowrap>_________________________ / <?=($this->dean)?$this->dean:'_________________________'?> /</td>
	</tr>
	<tr>
		<td>"не зачтено" <span style="text-decoration: underline;"><?=(int)$group_marks['не зачтено']?></span></td> <td>Дата <?=$this->date_issue?></td> <td></td>
	</tr>
	<tr>
		<td>"отлично" <span style="text-decoration: underline;"><?=(int)$group_marks['отлично']?></span></td> <td>Экзаменаторы (п. 9)</td> <td nowrap>_________________________ / <?=($this->tutor)?$this->tutor:'_________________________'?> /</td>
	</tr>
	<tr>
		<td>"хорошо" <span style="text-decoration: underline;"><?=(int)$group_marks['хорошо']?></span></td> <td></td> <td></td>
	</tr>
	<tr>
		<td>"удовлетворительно" <span style="text-decoration: underline;"><?=(int)$group_marks['удовлетворительно']?></span></td> <td></td> <td></td>
	</tr>
	<tr>
		<td>"неудовлетворительно" <span style="text-decoration: underline;"><?=(int)$group_marks['неуд.']?></span></td> <td></td> <td></td>
	</tr>
	<tr>
		<td>"не явились" <span style="text-decoration: underline;"><?=(int)$group_marks['неявка']?></span></td> <td></td> <td></td>
	</tr>
</table>
<br>
<span style="font-size: 12px; font-style: italic; font-weight:bold;">Примечания:</span>
<span style="font-size: 12px; text-indent:12px line-height:0.40;">
	<p>
		Не допускается: Внесение исправлений и дополнений «от руки» в список обучающихся; исправление оценки с помощью штриха. Ошибочно проставленная оценка зачеркивается и рядом делается запись:   «Исправленному с (указать неправильную оценку)  на  (указать правильную оценку)  верить», скрепляемая подписями экзаменаторов. 
	</p>
	<p>
		В случае неявки обучающегося  на зачет (экзамен) в графе №8 слева делается запись «н/я», в графе №9 ставиться подпись экзаменатора.
	</p>
	<p>
		Ведомость является недействительной без подписи декана факультета, экзаменаторов и педагогического работника, проводившего контроль текущей успеваемости обучающихся в семестре. Аттестационная оценка (о сдаче зачета/ экзамена) проставляется в перерасчете на систему: «неудовлетворительно», «удовлетворительно», «хорошо», «отлично», «зачтено», «не зачтено»  по шкале:
	</p>
</span>



<?php 
	# для реальной ведомости, не черновика:
	$rowr_per_first_page	= 10;
	$rowr_per_other_page	= 17;
	$skip_rows				= array(10, 11); # явно заданное кол-во строк для принудительного разрыва страницы
	$skip_rows_other		= array(0, 1); # кол-во строк для НЕпервой страницы, при которых нужно делать разрыв страницы
	
	# для черновика
	#$rowr_per_first_page	= 12;
	#$rowr_per_other_page	= 19;
	#$skip_rows				=  array(9,10); # явно заданное кол-во строк для принудительного разрыва страницы
	#$skip_rows_other		= array(16, 17); # кол-во строк для НЕпервой страницы, при которых нужно делать разрыв страницы
	
	$ostatok				= ($row_number - $rowr_per_first_page) % $rowr_per_other_page; 
?>
<?php if(
		in_array($row_number, $skip_rows)
		||
		($row_number > $rowr_per_first_page &&  in_array($ostatok, $skip_rows_other))		
	):?>
	<? #принудительный разрыв страницы ?>
	<div style="page-break-before: always;"></div>
<?php endif;?>




<table style="width:60%; margin: 0 auto; font-size: 13px; vertical-align:top; border-collapse: collapse; font-weight:bold;">
	<tr>
		<td rowspan="4" style="border: 1px solid black; padding-left:5px;">
			0-64  баллов       –     не зачтено
			<br />
			65-100 баллов    –     зачтено
		</td>
		<td style="border: 1px solid black; padding-left:5px;">0-64  баллов       –   неудовлетворительно</td>
	</tr>
	<tr>
		<td style="border: 1px solid black; padding-left:5px;">65-74  баллов     –   удовлетворительно</td>
	</tr>
	<tr>
		<td style="border: 1px solid black; padding-left:5px;">75-84 баллов      –   хорошо</td>
	</tr>
	<tr>
		<td style="border: 1px solid black; padding-left:5px;">85-100  баллов   –   отлично</td>
	</tr>
</table>



