<?php 		
	#$course				= ceil($this->subject->semester/2); # курс, число
	$group_marks		= array(); # кол-во каждой из оценок для статистики в конце таблицы
	/*
	Номер попытки - выгрузить
	Рейтинг за семестр - это что?
	Быджет/договор - это что за поле?
	Экзамен/зачет/курсовая - выгрузить
	Дата выдачи - дата кокой выдачи? Дата создания этого документа в СДО или дата создания ИН в 1С?
	[РуководительДолжность] - что сюда записывается и откуда?
	Экзаменаторы (Ф.И.О.)  - это тьюторы? Или декан из ведомости?
	ТемаТекст - что тут заполнять?
	Чем ТемаТекст отличается от Тема ?	
	*/
	# Флаг, который проверяет, что значение переменной есть среди доступных значений. Предотвращает зачеркивание всех значений при отсутсвиии доступного значения в печатной форме.
	$is_found_form_control = in_array($this->form_control, array('Экзамен','Зачет','Дифференцированный зачет','Контрольная работа','Курсовая работа','Практика')) ? true : false;
	$is_found_study_base   = in_array($this->study_base, array('Бюджет','Договор')) ? true : false;
	
?>

<table style="border-collapse: collapse;">
	<tr><td colspan="16" style="text-align: center;"><b>Российский государственный социальный университет</b></td></tr>
	<tr>
		<td colspan="13"></td>	
		<td colspan="1" style="border: 1px solid black; text-align: center;"><?=$this->attempt?></td>
		<td colspan="2"></td>	
		<td rowspan="21"></td>
	</tr>
	<tr>
		<td colspan="12"></td>	
		<td colspan="3" style="text-align: center; font-size:12px;">(Номер попытки сдачи)</td>		
		<td></td>	
	</tr>
	<tr>
		<td colspan="12" style="text-align: center;"><b>ИНДИВИДУАЛЬНОЕ НАПРАВЛЕНИЕ №</b></td>
		<td colspan="4"  style="border-bottom: 1px solid black; text-align: center;"><?=$this->marksheet_external_id?></td>
	</tr>
	<tr><td colspan="16" style="text-align: center;"><b><i>(Действительно 10 дней с момента выдачи)</i></b></td></tr>	
	<tr>
		<td colspan="3" style="white-space: nowrap;"><b>Студент (Ф.И.О.)</b></td>
		<td colspan="13" style="border-bottom: 1px solid black;"><b><?=$this->escape($this->student->LastName.' '.$this->student->FirstName.' '.$this->student->Patronymic);?></b></td>
	</tr>
	<tr>
		<td colspan="2" style="vertical-align:bottom;">Факультет</td>
		<td colspan="8" style="border-bottom: 1px solid black; vertical-align:bottom;"><?=$this->faculty?></td>
		<td colspan="1" style="vertical-align:bottom;">Группа</td>
		<td colspan="5" style="border-bottom: 1px solid black; vertical-align:bottom;"><?=$this->groups;?></td>
	</tr>
	<tr>
		<td colspan="16" style="padding-top:10px;"></td>
	</tr>
	<tr>
		<td colspan="2">Семестр:</td>
		<td colspan="8" style="border-bottom: 1px solid black;"><?=$this->semester?></td>
		<td colspan="2" style="white-space: nowrap;">Учебный год:</td>
		<td colspan="6" style="border-bottom: 1px solid black;"><b><?=$this->years?></b></td>
	</tr>
	<tr>
		<td colspan="7">Основа обучения:</td>
		<td colspan="2" valign="center" style="padding: 0 1em; border: 1px solid black; text-align: center;">
			<?=($this->study_base != 'Бюджет' && $is_found_study_base) ? '<span style="display: inline-block; width: auto; height: 0.85em; margin: 0 auto; border-bottom: 1px solid black; zoom: 1;">Бюджет</span>' : 'Бюджет'?>
		</td>
		<td colspan="2" valign="center" style="padding: 0 1em; border: 1px solid black; text-align: center;">
			<?=($this->study_base != 'Договор' && $is_found_study_base) ? '<span style="display: inline-block; width: auto; height: 0.85em; margin: 0 auto; border-bottom: 1px solid black; zoom: 1;">Договор</span>' : 'Договор'?>		
		</td>
		<td colspan="5"></td>	
	</tr>
	<tr>
		<td colspan="7"></td>		
		<td colspan="4" style="text-align: center; font-size:12px;">(ненужное зачеркнуть)</td>
		<td colspan="5"></td>	
	</tr>
	<tr>
		<td colspan="5" style="vertical-align:bottom;">Наименование дисциплины</td>
		<td colspan="7" style="border-bottom: 1px solid black; vertical-align:bottom;"><b><?=$this->subject->name;?></b></td>
		<td colspan="2" style="border: 1px solid black; "><span style="white-space: nowrap;">Рейтинг за</span> семестр</td>
		<td colspan="2" style="border: 1px solid black; text-align: center;"><b><?=$this->rating;?></b></td>
	</tr>
	<tr>
		<td colspan="16" style="padding-top:10px;"></td>
	</tr>
	<tr>
		<td colspan="3" style="vertical-align:bottom; padding-top: 24px;">Отчетность:</td>		
		<td colspan="2" valign="center" style="padding: 0 1em; border: 1px solid black; text-align: center;">
			<?=($this->form_control != 'Экзамен' && $is_found_form_control) ? '<span style="display: inline-block; width: auto; height: 0.85em; margin: 0 auto; border-bottom: 1px solid black; zoom: 1;">Экзамен</span>' : 'Экзамен'?>			
		</td>
		<td colspan="2" valign="center" style="padding: 0 1em; border: 1px solid black; text-align: center;">
			<?=($this->form_control != 'Зачет' && $is_found_form_control) ? '<span style="display: inline-block; width: auto; height: 0.85em; margin: 0 auto; border-bottom: 1px solid black; zoom: 1;">Зачет</span>' : 'Зачет'?>			
		</td>
		<td colspan="3" valign="center" style="padding: 0 1em; border: 1px solid black; text-align: center;">
			<?=($this->form_control != 'Дифференцированный зачет' && $is_found_form_control) ? '<span style="display: inline-block; width: auto; height: 0.85em; margin: 0 auto; border-bottom: 1px solid black; zoom: 1;">Дифференциро</span>' : 'Дифференциро'?>
			<br />
			<?=($this->form_control != 'Дифференцированный зачет' && $is_found_form_control) ? '<span style="display: inline-block; width: auto; height: 0.85em; margin: 0 auto; border-bottom: 1px solid black; zoom: 1;">ванный зачет</span>' : 'ванный зачет'?>			
		</td>
		<td colspan="2" valign="center" style="padding: 0 1em; border: 1px solid black; text-align: center;"> 			
			<?=($this->form_control != 'Контрольная работа' && $is_found_form_control) ? '<span style="display: inline-block; width: auto; height: 0.85em; margin: 0 auto; border-bottom: 1px solid black; zoom: 1;">Контрольная</span>' : 'Контрольная'?>			
			<br />
			<?=($this->form_control != 'Контрольная работа' && $is_found_form_control) ? '<span style="display: inline-block; width: auto; height: 0.85em; margin: 0 auto; border-bottom: 1px solid black; zoom: 1;">работа</span>' : 'работа'?>			
		</td>
		<td colspan="2" valign="center" style="padding: 0 1em; border: 1px solid black; text-align: center;">
			<?=($this->form_control != 'Курсовая работа' && $is_found_form_control) ? '<span style="display: inline-block; width: auto; height: 0.85em; margin: 0 auto; border-bottom: 1px solid black; zoom: 1;">Курсовая</span>' : 'Курсовая'?>			
			<br />
			<?=($this->form_control != 'Курсовая работа' && $is_found_form_control) ? '<span style="display: inline-block; width: auto; height: 0.85em; margin: 0 auto; border-bottom: 1px solid black; zoom: 1;">работа</span>' : 'работа'?>			
		</td>
		<td colspan="2" valign="center" style="padding: 0 1em; border: 1px solid black; text-align: center;">
			<?=($this->form_control != 'Практика' && $is_found_form_control) ? '<span style="display: inline-block; width: auto; height: 0.85em; margin: 0 auto; border-bottom: 1px solid black; zoom: 1;">Практика</span>' : 'Практика'?>			
		</td>
	</tr>	
	<tr>
		<td colspan="3"></td>		
		<td colspan="13" style="text-align: center; font-size:12px;">(ненужное зачеркнуть)</td>
	</tr>
	<tr>
		<td colspan="3"><b>Дата выдачи</b></td>
		<td colspan="5" style="border-bottom: 1px solid black;"><b><?=$this->date_issue?></b></td>		
		<td colspan="3" style="border-bottom: 1px solid black;"><b>Подпись</b></td>
		<td colspan="5" style="border-bottom: 1px solid black;"><b></b></td>
	</tr>
	<tr>
		<td colspan="5"><b><i>Экзаменаторы (Ф.И.О.)</i></b></td>
		<td colspan="11" style="border-bottom: 1px solid black;"><b><?=$this->tutor?></b></td>		
	</tr>
	<tr>
		<td colspan="5"></td>
		<td colspan="11" style="text-align: center; font-size:12px;">(заполняется заведующим кафедрой)</td>
	</tr>
	<tr>
		<td colspan="16" style="padding-top:10px;"></td>
	</tr>
	<tr style="text-align: center;">
		<td colspan="3" style="border: 1px solid black;"><b>Оценка<br />(цифрой)</b></td>
		<td colspan="4" style="border: 1px solid black; white-space: nowrap;"><b>Оценка (прописью)</b></td>
		<td colspan="4" style="border: 1px solid black;"><b>Рейтинговая<br />оценка ответа<br />(баллы)</b></td>
		<td colspan="5" style="border: 1px solid black;"><b>Подписи экзаменаторов</b></td>
	</tr>
	<tr style="text-align: center;">
		<td colspan="3" style="border: 1px solid black; vertical-align: middle;"><b><?=$this->mark_5;?></b></td>
		<td colspan="4" style="border: 1px solid black; vertical-align: middle;"><b><?=$this->mark_5_text;?></b></td>
		<td colspan="4" style="border: 1px solid black; vertical-align: middle;"><b><?=$this->mark_total;?></b></td>
		<td colspan="5" style="border: 1px solid black;"></td>
	</tr>
	<?php if(!empty($this->theme)):?>
	<tr>
		<td colspan="3" style="border: 1px solid black;"><?=$this->theme_type?></td>		
		<td colspan="13" style="border: 1px solid black;"><?=$this->theme?></td>
	</tr>
	<?php endif;?>
	<tr>
		<td colspan="6"><b><i>Дата сдачи отчетности</i></b></td>
		<td colspan="5" style="border-bottom: 1px solid black;"></td>
		<td colspan="5"></td>
	</tr>	
</table>

<?/*
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
	<tr><td style="text-align: right;">ИНДИВИДУАЛЬНОЕ НАПРАВЛЕНИЕ</td></tr>
	<tr><td>
		ФЕДЕРАЛЬНОЕ ГОСУДАРСТВЕННОЕ БЮДЖЕТНОЕ ОБРАЗОВАТЕЛЬНОЕ УЧРЕЖДЕНИЕ ВЫСШЕГО ОБРАЗОВАНИЯ<br />
		«РОССИЙСКИЙ ГОСУДАРСТВЕННЫЙ СОЦИАЛЬНЫЙ УНИВЕРСИТЕТ»<br />
		<?=$this->faculty?><br />
	</td></tr>
	<tr><td style="font-weight: bold;">ЗАЧЕТНО-ЭКЗАМЕНАЦИОННАЯ ВЕДОМОСТЬ № <?=$this->marksheet_external_id?></td></tr>
	
	<tr><td style="font-size: 14px; text-align: left;">	
		<span style="text-indent: 15px;">Форма обучения	<span style="text-decoration: underline;"><?=$this->exam_type_name?></span></span>
		<span style="text-indent: 15px;">Группа  <span style="text-decoration: underline;"><?=$this->groups;?></span></span>
		<span style="text-indent: 15px;"><span style="text-decoration: underline;"><?=$this->semester?>      семестр (<?=(!empty($this->course))?$this->course:'        '?> курс)</span></span>
		<span style="text-indent: 15px;">Учебный год <span style="text-decoration: underline;"><?=$this->years?></span></span>
		<br>
		<span style="text-indent: 15px;">Дисциплина <span style="text-decoration: underline;"><?=$this->subject->name;?></span></span>
		<br>
		<span style="text-indent: 15px;">Ф.И.О. экзаменаторов <span style="text-decoration: underline;"><?=$this->tutors;?></span></span>
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
		<tr >
			<td>1.</td>
			<td style="text-align:left"><?=$this->escape($this->student->LastName.' '.$this->student->FirstName.' '.$this->student->Patronymic);?></td>
			<td><?=$this->recordBookNumber;?></td>
			<td><?=$this->mark_current;?></td> 
			<td></td>
			<td><?=$this->mark_landmark;?></td>
			<td><?=$this->mark_total;?></td>
			<td style="padding:0px; margin: 0px; width:20px" cellpadding="0">
				<?php			
				if(!isset($group_marks[$this->mark_5_text])){
					$group_marks[$this->mark_5_text] = 1;	
				} else {
					$group_marks[$this->mark_5_text]++;
				}				
				?>
				<?=$this->mark_5_text;?>				
			</td>
			<td></td>			
		</tr>
	</tbody>
</table>


<table style="width:100%; padding-top:25px" class="tbl-footer">
	<tr>
		<td style="width:30%; padding-right:20px;">Итого:</td><td></td><td></td>
	</tr>	
	<tr>
		<td>"зачтено" <span style="text-decoration: underline;"><?=(int)$group_marks['зачтено']?></span></td> <td>Декан факультета</td> <td nowrap><?=($this->dean)?$this->dean:'_________________________'?> /_________________________</td>
	</tr>
	<tr>
		<td>"не зачтено" <span style="text-decoration: underline;"><?=(int)$group_marks['не зачтено']?></span></td> <td>Дата <?=date('d.m.Y')?></td> <td></td>
	</tr>
	<tr>
		<td>"отлично" <span style="text-decoration: underline;"><?=(int)$group_marks['отлично']?></span></td> <td>Экзаменаторы (п. 9)</td> <td nowrap>_________________________ /_________________________</td>
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

*/?>

