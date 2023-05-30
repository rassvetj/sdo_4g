<?php 
		$form_control 		= $this->additional['exam_type_name']; # взять из сессии/ выводится после названия сессии
		$faculty	  		= $this->additional['faculty']; # Факультет
		$years 				= $this->additional['years']; # взять из сессии Учебный год /
		$marksheet_number 	= $this->additional['marksheet_external_id']; # Номер ведомости.
		$course				= !empty($this->additional['course']) ? $this->additional['course'] : ceil($this->additional['semester']/2); #ceil($this->additional['semester']/2); # курс, число
		$dean 				= $this->additional['dean']; # Декан
		$tutor 				= $this->additional['tutor']; # Тьютор
		$date_issue 		= $this->additional['date_issue']; # Дата формирвания ведомости.
		$form_study		= $this->additional['form_study']; #Форма обучения
		
		$tutors 			= array();		
		$allGroups 			= array();
		$isModuleSubject 	= false;
		$group_marks		= array(); # кол-во каждой из оценок для статистики в конце таблицы
		
		$signerCaption		= $this->isProgrammFilial ? 'Директор филиала' : 'Декан факультета';
		
		foreach($this->persons as $key => $person){
			$groups = $person->studyGroups;
			if ($groups) {				
				foreach ($groups as $group) {
					$allGroups[$group['name']] = $group['name'];					
				}				
			}
			if($isModuleSubject === false){
				$isModuleSubject = (isset($this->scores[$key."_total"]['integrateMediumRating'])) ? true : false;
			}
		}
		
		/*
		if(!empty($this->additional['tutors'])){
			foreach($this->additional['tutors'] as $t){
				$tutors[] = $t->LastName.' '.$t->FirstName.' '.$t->Patronymic;
			}			
		}
		*/
		
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
 
  #watermark {
    position: fixed;
    top: 25%;
    width: 100%;
    text-align: center;
    opacity: .15;
    transform: rotate(-35deg);
    transform-origin: 50% 50%;
    z-index: -1000;
	font-size: 13em;
  }
 </style>

<div id="watermark">ЧЕРНОВИК</div>
<table style="text-align: center; width:100%" >
	<tr><td>
		ФЕДЕРАЛЬНОЕ ГОСУДАРСТВЕННОЕ БЮДЖЕТНОЕ ОБРАЗОВАТЕЛЬНОЕ УЧРЕЖДЕНИЕ ВЫСШЕГО ОБРАЗОВАНИЯ<br />
		«РОССИЙСКИЙ ГОСУДАРСТВЕННЫЙ СОЦИАЛЬНЫЙ УНИВЕРСИТЕТ»<br />
		<?=$faculty?><br />
	</td></tr>
	<tr><td style="font-weight: bold;">ЗАЧЕТНО-ЭКЗАМЕНАЦИОННАЯ  ВЕДОМОСТЬ № <?=$marksheet_number?></td></tr>
	
	<tr><td style="font-size: 14px; text-align: left;">	
		<span style="text-indent: 15px;">Форма обучения	<span style="text-decoration: underline;"><?=$form_study?></span></span>
		<span style="text-indent: 15px;">Группа  <span style="text-decoration: underline;"><?=implode(', ', $allGroups);?></span></span>
		<span style="text-indent: 15px;"><span style="text-decoration: underline;"><?=$this->additional['semester'];?>      семестр ( <?=(!empty($course))?$course:' <span style="text-decoration: underline;"> </span> '?> курс)</span></span>
		<span style="text-indent: 15px;">Учебный год <span style="text-decoration: underline;"><?=$years?></span></span>
		<br>
		<span style="text-indent: 15px;">Дисциплина <span style="text-decoration: underline;"><?=$this->additional['discipline'];?> (<?=$form_control?>)</span></span>
		<span style="text-indent: 15px;">   Номер попытки <span style="text-decoration: underline;"><?=$this->additional['attempt'];?></span></span>
		<br>
		<span style="text-indent: 15px;">Ф.И.О. экзаменаторов <span style="text-decoration: underline;"><?=$tutor;?><?#=implode(', ', $tutors);?></span></span>
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
					<?=($isModuleSubject)						
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
		<?php foreach($this->persons as $key => $person):?>
			<?php
			$isPassTotalRating = Zend_Registry::get('serviceContainer')->getService('Lesson')->isPassTotalRating($this->additional['maxBallTotalRating'], $this->additional['dataRatingTotal'][$key], $this->additional['isDO'], $this->additional['is_practice']);            
			
			$isPassTotalRating = empty( $this->scores[$key.'_total']['fail_message'] ) ? $isPassTotalRating : false;
			
			$row_number++;
            ?>
		<tr >
			<td><?=$row_number;?>.</td>
			<td style="text-align:left"><?=$this->escape($person->getName());?></td>
			<td><?=$this->additional['recordBookNumbers'][$key];?></td>
			<td>
				<?php if($isModuleSubject): ?>
					<?= (!empty($this->scores[$key."_total"]['integrateMediumRating'])) ? round($this->scores[$key."_total"]['integrateMediumRating']) : ''; ?>
				<?php else : ?>					
					<?=(isset($this->additional['dataRatingMedium'][$key]) && !empty($this->additional['dataRatingMedium'][$key])) ? ( round($this->additional['dataRatingMedium'][$key]) ) : ('');?>
				<?php endif; ?>
			</td>
			<td></td>
			<td><?=(isset($this->additional['dataRatingTotal'][$key]) && empty( $this->scores[$key.'_total']['fail_message'] ) && !empty($this->additional['dataRatingTotal'][$key]) ) ? ( round($this->additional['dataRatingTotal'][$key]) ) : ('');?></td>
			<td>				
				<?php				
					$totalScore = -1;
					#if($this->scores[$key."_total"]['mark'] > -1) {
						if($isModuleSubject){						
							$totalScore = round($this->scores[$key."_total"]['integrateMediumRating']);
							
							if(empty( $this->scores[$key.'_total']['fail_message'] )){
								$totalScore += round($this->additional['dataRatingTotal'][$key]);
							}
							
							$mark_5 	= Zend_Registry::get('serviceContainer')->getService('Lesson')->getFiveScaleMark($totalScore); # TODO перенести в сервисный слов Lesson							
							if(!empty($totalScore) && $totalScore >= 65){
								echo $totalScore;
							}
						
						} else {
							
							$totalScore = round($this->additional['dataRatingMedium'][$key]);
							
							if(empty( $this->scores[$key.'_total']['fail_message'] )){
								$totalScore += round($this->additional['dataRatingTotal'][$key]);
							}
							
							$mark_5 	= $this->scores[$key.'_total']['mark_5'];							
							#if( $isPassTotalRating){
								#if(!empty($totalScore) && $totalScore >= 65){
								if(!empty($totalScore)){
									echo $totalScore;
								}
							#}
						}							
					#}
				?>				
			</td>
			<td style="padding:0px; margin: 0px; width:20px" cellpadding="0"><?/*удовлетворительно*/?>				
				<?php
				$isHasFailInModule = false; # есть ли причины недопуска.
				
				if($isModuleSubject){	
					foreach($this->additional['moduleData']['subjects'] as $subject_id => $subject_name) {
						if($isHasFailInModule === false && $this->additional['moduleData']['additional'][$subject_id]['is_fail_module'][$key]){
							$isHasFailInModule = true;
						}
					}
				}

				
				if(
					empty($this->additional['dataRatingTotal'][$key]) 
					||
					!empty( $this->scores[$key.'_total']['fail_message'] )
				) {
					$mark_5 = 0; # неявка
				#}elseif( !$isPassTotalRating ){
				}elseif( !$isPassTotalRating || $isHasFailInModule ){
					$mark_5 = 2; #Неважно, как много набрано баллов. Если завалил экзамен, в любом случае это неуд.
				}



				$mark_5_text = Zend_Registry::get('serviceContainer')->getService('Lesson')->getTextFiveScaleMark($mark_5, $this->additional['exam_type']);
				
				if(!isset($group_marks[$mark_5_text])){
					$group_marks[$mark_5_text] = 1;	
				} else {
					$group_marks[$mark_5_text]++;
				}				
				?>
				<?#= ($mark_5 >= 2) ? $mark_5_text : ''; ?>
				<?=$mark_5_text; ?>
			</td>
			<td></td>			
		</tr>
		<?php endforeach;?>
	</tbody>
</table>


<?php 
	# для реальной ведомости, не черновика:
	#$rowr_per_first_page	= 10;
	#$rowr_per_other_page	= 17;
	#$skip_rows				= array(8,9); # явно заданное кол-во строк для принудительного разрыва страницы
	#$skip_rows_other		= array(0, 15, 16); # кол-во строк для НЕпервой страницы, при которых нужно делать разрыв страницы
	
	# для черновика
	$rowr_per_first_page	= 12;
	$rowr_per_other_page	= 19;
	$skip_rows				= array(9,10); # явно заданное кол-во строк для принудительного разрыва страницы
	$skip_rows_other		= array(16, 17); # кол-во строк для НЕпервой страницы, при которых нужно делать разрыв страницы
	
	$ostatok 			 	= ($row_number - $rowr_per_first_page) % $rowr_per_other_page; 
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
        <td>"зачтено" <span style="text-decoration: underline;"><?=(int)$group_marks['зачтено']?></span></td> <td><?=$signerCaption?></td> <td nowrap>_________________________ / <?=($dean)?$dean:'_________________________'?> /</td>
	</tr>
	<tr>
		<td>"не зачтено" <span style="text-decoration: underline;"><?=(int)$group_marks['не зачтено']?></span></td> <td>Дата <?=$date_issue?></td> <td></td>
	</tr>
	<tr>
		<td>"отлично" <span style="text-decoration: underline;"><?=(int)$group_marks['отлично']?></span></td> <td>Экзаменаторы (п. 9)</td> <td nowrap>_________________________ / <?=($tutor)?$tutor:'_________________________'?> /</td>
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



