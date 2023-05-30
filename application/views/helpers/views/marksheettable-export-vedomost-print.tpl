<?php 
		$form_control 		= $this->additional['exam_type_name']; 		# взять из сессии Форма обучения
		$faculty	  		= $this->additional['faculty']; 			# взять из сессии Факультет
		$years 				= $this->additional['years']; 				# взять из сессии Учебный год /
		$marksheet_number 	= $this->additional['marksheet_external_id']; # Номер ведомости.
		$course				= !empty($this->additional['course']) ? $this->additional['course'] : ceil($this->additional['semester']/2); #ceil($this->additional['semester']/2); # курс, число
		$dean 				= $this->additional['dean']; # Декан
		$tutor 				= $this->additional['tutor']; # Тьютор
		$date_issue 		= $this->additional['date_issue']; # Дата формирвания ведомости.
		
		#$tutors 			= array();		
		$allGroups 			= array();
		$isModuleSubject 	= false;
		$group_marks		= array(); # кол-во каждой из оценок для статистики в конце таблицы
		
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




<!DOCTYPE html>
<html>
    <head></head>
    <body>
	
<style>
	.rotateText {
		display: inline-block;
		writing-mode: tb-rl;
		font-size: 14px;
		transform: rotate(180deg);
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
 
 </style>


<table style="text-align: center; width:100%" >
	<tr><td>
		ЧЕРНОВИК<br />
		<?=$faculty?><br />
	</td></tr>
	<tr><td style="font-weight: bold;">ЗАЧЕТНО-ЭКЗАМЕНАЦИОННАЯ  ВЕДОМОСТЬ № <?=$marksheet_number?></td></tr>
	
	<tr><td style="font-size: 14px; text-align: left;">	
		<span style="text-indent: 15px;">Форма обучения	<span style="text-decoration: underline;"><?=$form_control?></span></span>
		<span style="text-indent: 15px;">Группа  <span style="text-decoration: underline;"><?=implode(', ', $allGroups);?></span></span>
		<span style="text-indent: 15px;"><span style="text-decoration: underline;"><?=$this->additional['semester'];?>      семестр (<?=(!empty($course))?$course:'        '?> курс)</span></span>
		<span style="text-indent: 15px;">Учебный год <span style="text-decoration: underline;"><?=$years?></span></span>
		<br>
		<span style="text-indent: 15px;">Дисциплина <span style="text-decoration: underline;"><?=$this->additional['discipline'];?></span></span>
		<br>
		<span style="text-indent: 15px;">Ф.И.О. экзаменаторов <span style="text-decoration: underline;"><?=$tutor;?><?#=implode(', ', $tutors);?></span></span>
	</td></tr>		
</table>

<table style="text-align: center; width:100%; font-size: 14px;" class="tbl-content">
	<thead>
		<tr >
			<th >№<br />п/п</th>
			<th >Ф.И.О. обучающегося</th>
			
			<th nowrap>
				<div class="rotateText" >	
					<span style="transform: rotate(180deg); display: -webkit-inline-box;" >№</span> зачетной книжки				
				</div>
			</th>
			
			<th   nowrap>
				<div class="rotateText" >													
					<?=($isModuleSubject)						
						? ('Интегральный<br />текущий рейтинг') 						
						: ('Итоговый текущий рейтинг<br />обучающегося за семестр<br />(до 80 баллов)')?>
				</div>
			</th>
			 
			<th  nowrap>
				<div class="rotateText" >
					Подпись педагогического<br />работника, проводившего текущий<br />контроль успеваемости<br />обучающихся в семестре
				</div>
			</th>
			
			<th  nowrap>
				<div class="rotateText" >
					Pубежный рейтинг обучающегося<br />на зачете / экзамене<br />(до 20 баллов)
				</div>
			</th>
			<th  nowrap>
				<div class="rotateText" >
					Академический рейтинг<br />обучающегося  по  учебной<br />дисциплине<br />(сумма гр. 4  и  6)
				</div>
			</th>
			<th  nowrap>
				<div class="rotateText" >Аттестационная оценка</div>
			</th>
			<th nowrap>
				<div class="rotateText" >	
					Подписи  экзаменаторов		
				</div>
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
					<?=round($this->scores[$key."_total"]['integrateMediumRating'])?>
				<?php else : ?>					
					<?=(isset($this->additional['dataRatingMedium'][$key])) ? ( round($this->additional['dataRatingMedium'][$key]) ) : ('');?>
				<?php endif; ?>
			</td>
			<td></td>
			<td><?=(isset($this->additional['dataRatingTotal'][$key]) && empty( $this->scores[$key.'_total']['fail_message'] ) ) ? ( round($this->additional['dataRatingTotal'][$key]) ) : ('0');?></td>
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
							echo $totalScore;
						
						} else {
							
							$totalScore = round($this->additional['dataRatingMedium'][$key]);
							
							if(empty( $this->scores[$key.'_total']['fail_message'] )){
								$totalScore += round($this->additional['dataRatingTotal'][$key]);
							}
							
							$mark_5 	= $this->scores[$key.'_total']['mark_5'];							
							#if( $isPassTotalRating){
								echo $totalScore;
							#}
						}							
					#}
				?>				
			</td>
			<td style="padding:0px; margin: 0px; width:20px" cellpadding="0"><?/*удовлетворительно*/?>				
				<?php			
				if(
					empty($this->additional['dataRatingTotal'][$key]) 
					||
					!empty( $this->scores[$key.'_total']['fail_message'] )
				) {
					$mark_5 = 0; # неявка
				}elseif( !$isPassTotalRating ){
					$mark_5 = 2; #Неважно, как много набрано баллов. Если завалил экзамен, в любом случае это неуд.
				}



				
				$mark_5_text = Zend_Registry::get('serviceContainer')->getService('Lesson')->getTextFiveScaleMark($mark_5, $this->additional['exam_type']);
				
				if(!isset($group_marks[$mark_5_text])){
					$group_marks[$mark_5_text] = 1;	
				} else {
					$group_marks[$mark_5_text]++;
				}				
				?>
				<?=$mark_5_text?>				
			</td>
			<td></td>			
		</tr>
		<?php endforeach;?>		
	
			
			
	</tbody>
</table>


<table style="width:100%; padding-top:25px" class="tbl-footer">
	<tr>
		<td style="width:30%; padding-right:20px;">Итого:</td><td></td><td></td>
	</tr>	
	<tr>
		<td>"зачтено" <span style="text-decoration: underline;"><?=(int)$group_marks['зачтено']?></span></td> <td>Декан факультета</td> <td nowrap>_________________________ /<?=($dean)?$dean:'_________________________'?></td>
	</tr>
	<tr>
		<td>"не зачтено" <span style="text-decoration: underline;"><?=(int)$group_marks['не зачтено']?></span></td> <td>Дата <?=$date_issue?></td> <td></td>
	</tr>
	<tr>
		<td>"отлично" <span style="text-decoration: underline;"><?=(int)$group_marks['отлично']?></span></td> <td>Экзаменаторы (п. 9)</td> <td nowrap>_________________________ /<?=($tutor)?$tutor:'_________________________'?></td>
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

<script type="text/javascript">
	<!--
	window.print();
	window.onload = function cleanPage(){ document.getElementById('ZFDebug_debug').innerHTML = '';};
	//-->
</script>
</body>
</html>

