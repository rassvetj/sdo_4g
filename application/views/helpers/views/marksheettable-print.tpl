<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="<?php echo $this->baseUrl('css/content-modules/marksheet.css')?>" type="text/css">
        <link rel="stylesheet" href="<?php echo $this->baseUrl('css/common.css')?>" type="text/css">
        <!--<link rel="stylesheet" href="<?php echo $this->baseUrl('themes/redmond/css/theme.css')?>" type="text/css">
    --></head>
    <body>
	
	
	<?php if (!count($this->persons)):?>
	    <!-- TODO: Это вынести в отдельный файл при стайлинге -->
	    <div style="padding:10px;text-align:center;color:brown;font-size:14px;"><?php echo _('Отсутствуют данные для отображения')?></div>
	<?php else:?>
	<?php $totalSchedules = count($this->schedules); ?>
	<?php $totalPersons = count($this->persons); ?>
	
	<table id="marksheet" class="main-grid" cellspacing="0" data-schedules="<?php echo $totalSchedules;?>" data-persons="<?php echo $totalPersons;?>">
	    <colgroup><col><col><col span="<?php echo $totalSchedules + 1;?>"></colgroup>
	    <thead>
	        <tr class="marksheet-labels">
	            <!-- TODO: Убрать в нужное место кнопулю комментирования -->
	            <td class="first-cell" ></td>
	            <?php foreach($this->schedules as $key => $schedule):?>
	            <td class="lesson-cell score-cell">
	                <?php echo $this->escape($schedule->title)?>
	            </td>
	            <?php endforeach;?>
				<td class="lesson-cell score-cell">Итоговый текущий рейтинг</td>
				<td class="lesson-cell score-cell">Рубежный рейтинг</td>
				<td class="lesson-cell score-cell">Оценка</td>
	            <td class="score-cell total-cell last-cell"><span class="total-score-label">Итог</span></td>
				<td><div style="margin:5px">Причина недопуска</div></td>
	        </tr>
	        <tr class="marksheet-head">
	            <?/*<th class="marksheet-rowcheckbox first-cell"></th>*/?>
	            <th class="fio-cell"><?php echo _('ФИО');?></th>
	            <?php foreach($this->schedules as $key => $schedule):?>
	            <td class="lesson-cell score-cell"><?=$schedule->max_ball?></td>
	            <?php endforeach;?>
				<td class="lesson-cell score-cell"></td>
				<td class="lesson-cell score-cell"></td>
				<td class="lesson-cell score-cell"></td>
	            <td class="total-cell last-cell score-cell"></td>
				<td class="lesson-cell score-cell"></td>
	        </tr>
	    </thead>
	    <tbody>
	        <?php
	        $temp1 = 1;
	        foreach($this->persons as $key => $person):?>
			<?php
				$show_total_practic = !$this->scores[$key.'_failTotalPractic'];
				
				$isPassTotalRating  = Zend_Registry::get('serviceContainer')->getService('Lesson')->isPassTotalRating($this->additional['maxBallTotalRating'],   $this->additional['dataRatingTotal'][$key],  $this->additional['isDO'], $this->additional['is_practice']);
				$isPassMediumRating = Zend_Registry::get('serviceContainer')->getService('Lesson')->isPassMediumRating($this->additional['maxBallMediumRating'], $this->additional['dataRatingMedium'][$key], $this->additional['isDO']);
				
				# Не допущен к экзамену или сдал его ниже проходного балла.
				#$isFailSubject = empty( $this->scores[$key.'_total']['fail_message'] ) ? false : true; 
				$isPassTotalRating = empty( $this->scores[$key.'_total']['fail_message'] ) ? $isPassTotalRating : false; 
			?>
	        <tr class="<?php echo ($temp1 % 2 == 0) ? "even" : "odd"; if ($temp1 == 1) { echo " first-row"; } else if ($temp1 == $totalPersons) { echo " last-row"; } ?>">
	            <?/*<td class="marksheet-rowcheckbox first-cell"></td>*/?>
	            <td class="fio-cell"><?php echo $this->escape($person->getName());?></td>
	            <?php
	            $temp = 1;
	            foreach($this->schedules as $schedule):?>
	            <td class="score-cell lesson-cell<?php if(!isset($this->scores[$key.'_'.$schedule->SHEID])):?> no-score<?endif;?>"><div>
	                <?php
					if(isset($this->scores[$key.'_'.$schedule->SHEID]) && $this->scores[$key.'_'.$schedule->SHEID]->V_STATUS > -1){
						$score = $this->scores[$key.'_'.$schedule->SHEID]->V_STATUS;					
						$score = (stripos($schedule->title, 'итоговый контроль') !== false) ? round($score) : $score;
						echo str_replace('.', ',', $score);
					}					
					?>					
	            </div></td>
	            <?php $temp++;
	            endforeach;?>
				<td class="score-cell last-cell"><div><?= (isset($this->additional['dataRatingMedium'][$key])) ? (str_replace('.', ',', round($this->additional['dataRatingMedium'][$key]))) : ('');?></td>
				<td class="score-cell last-cell"><div><?= (isset($this->additional['dataRatingTotal'][$key])) ? (str_replace('.', ',', round($this->additional['dataRatingTotal'][$key]))) : ('');?></td>
				<td><div style="margin:5px">
					<?php 						
						if(
							!empty( $this->scores[$key.'_total']['fail_message'] ) 
							||
							$this->additional['dataRatingTotal'][$key] <= 0 )
						{
							echo 'н/я';
							
							
						}elseif( !$isPassTotalRating ){
							$mark_5 = 2; #Неважно, как много набрано баллов. Если завалил экзамен, в любом случае это неуд.
							echo Zend_Registry::get('serviceContainer')->getService('Lesson')->getTextFiveScaleMark($mark_5, $this->additional['exam_type']);	
							
						} else{ 
							echo Zend_Registry::get('serviceContainer')->getService('Lesson')->getTextFiveScaleMark($this->scores[$key.'_total']['mark_5'], $this->additional['exam_type']);						
						}
					?>
				</td>
	            <td class="score-cell total-cell last-cell"><div>
					<?php #if($this->scores[$key."_total"]['mark'] > -1) echo $this->scores[$key."_total"]['mark'];?>
					<?php						
						$totalScore = '';
						if(!empty( $this->scores[$key.'_total']['fail_message'] )){
							$totalScore = round($this->additional['dataRatingMedium'][$key]);							
						} elseif($this->scores[$key.'_total']['mark'] !== NULL && $this->scores[$key.'_total']['mark'] >= 0  && $isPassMediumRating && $show_total_practic){						
							$totalScore = (round($this->additional['dataRatingMedium'][$key]) + round($this->additional['dataRatingTotal'][$key]) );							
						} else {				
						}
						echo str_replace('.', ',', $totalScore);
					?>					
				</div></td>
				<td><?php 
						if(!empty( $this->scores[$key.'_total']['fail_message'] )){
							foreach($this->scores[$key.'_total']['fail_message'] as $mes){								
								echo $mes['message'].'<br />';
							}
						}
				?></td>
	            <?php $temp++;?>
	        </tr>
	        <?php $temp1++;
	        endforeach;?>	       
	    </tbody>
	</table>
	
	<script type="text/javascript">
	<!--
	window.print();
	window.onload = function cleanPage(){ document.getElementById('ZFDebug_debug').innerHTML = '';};
	//-->
	</script>
	<?php endif;?>
	
	
</body>
</html>