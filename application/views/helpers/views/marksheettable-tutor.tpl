<?php
    $this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/marksheet.css'));
    $this->headScript()->appendFile($this->baseUrl('js/application/marksheet/index/index/script.js'));
    $this->headScript()->appendFile($this->baseUrl('/js/lib/jquery/datefilter.js'));
    $js = "
	$('#from,#to').dfilter({
		startDate: '-3y',
		endDate: '+3y',
		fillFrom: '.date-from',
		fillTo: '.date-to',
		descFrom: '"._('от')."',
		descTo: '"._('до')."'
	});
    ";
    $this->jQuery()->addOnload($js);
?>
<style>
	.hm-marksheet-table-header-item-title {
		max-height: inherit;
		height: inherit;
		font-size: 10px;
	}
	.total-ball {		
		border: 0 solid;
		background-color: transparent;
		display: inline-block;
		width: 94%;
		color: #b55b5b;		
		text-align: center;
		font-size: 1.636em;
	}
	
	.total-ball-text {
		font-size: 1.210em;
		font-weight: bold;
	}
	
	.cell125 {
		width: 360px;
		min-width: 360px;		
	}
	
	.score-cell.lesson-cell > div > .hm_score_numeric {
		height: 25px;		
	}
	
	.score-cell {		
		min-width: 50px;
		vertical-align: middle!important;
	}
	table.main-grid .marksheet-head td.score-cell {
		vertical-align: middle;		
	}
	

	.fail-lesson {
		background-color: #feaec9!important;
	}
	
	.cell-practice {
		min-width: 11em;
	}
	
	.hide-column{		
		display:none;
	}
</style>

<form id="marksheet-form-filters" method="POST">
<div class="filter_wrap <?php if (!count($this->persons)):?>filter_wrap_nodata<?php endif; ?>">

	<div class="dateFilterGroup classFWrap">
		<div class="filter_desc"><?php echo _('Фильтр по группе/подгруппе:'); ?></div>
		<div class="filterContent">
            <div class="constructorVis">
<span class="field-cell field-filters field-filters-group disabled-field-filters">
    <span class="field-icon"></span>
    <span class="field-filters-value filterSelect">
        <?php echo $this->formSelect('groupname', $this->current_groupname, null, $this->groupname);?>
    </span>
</span>
            </div>
		</div>
	</div>
	<div class="filterSubmit classFWrap">
		<button class="dateFilter">Фильтровать</button>
	</div>
</div>
</form>


<?php if (!count($this->persons)):?>
    <!-- TODO: Это вынести в отдельный файл при стайлинге -->
    <div style="padding:10px;text-align:center;color:brown;font-size:14px;"><?php echo _('Отсутствуют данные для отображения');?></div>
<?php else:?>
<?php
	foreach($this->schedules as $key => $schedule) {
        if($schedule->max_ball <= 0) { 
			unset($this->schedules[$key]);
		}
	}
?>

<?php
$totalSchedules = count($this->schedules) + 6;
$totalPersons 	= count($this->persons); 
$isMarkBrs 		= ($this->subject->mark_type == HM_Mark_StrategyFactory::MARK_BRS) ? true : false;
?>

<form id="marksheet-form" method="POST" action="<?php echo $this->escape( $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'set-score')) );?>">
<table id="marksheet" class="main-grid" cellspacing="0" data-schedules="<?php echo $totalSchedules;?>" data-persons="<?php echo $totalPersons;?>">
    <colgroup><col><col><col span="<?php echo $totalSchedules + 1;?>"></colgroup>
    <thead>
        <tr class="marksheet-labels">
            <!-- TODO: Убрать в нужное место кнопулю комментирования -->
            <td class="first-cell cell125" colspan="1"></td>
            <?php foreach($this->schedules as $key => $schedule):?>
			<?php $isJPractice = ($schedule->typeID == HM_Event_EventModel::TYPE_JOURNAL_PRACTICE) ? (true) : (false);?>
			<?php $subject_id  = $schedule->CID;?>
            <td class="lesson-cell score-cell" >
                <?php if ($schedule->getResultsUrl()):?>
                <a title="<?php echo _('Подробная статистика')?>" href="<?php echo $schedule->getResultsUrl();?>">
                <?php endif;?>                    
                    <div class="hm-marksheet-table-header-item-title" title="<?=$this->escape($schedule->title)?>"><?=$this->escape($schedule->title)?></div>					
                <?php if ($schedule->getResultsUrl()):?>
                </a>
                <?php endif;?>
				<?php 
				$max_ball_html = $schedule->max_ball;
				if($isJPractice){
					if (empty($schedule->max_ball_practice_or_lab)) {
						$max_ball_html = $max_ball_html.'/'.Zend_Registry::get('serviceContainer')->getService('Lesson')->getTaskSumMaxBall($schedule->CID);	
					}
					else {
                        $max_ball_html = $max_ball_html.'/'.$schedule->max_ball_practice_or_lab;
					}	
				}				
				?>
				(<?=$max_ball_html?>)
            </td>
            <?php endforeach;?>
			
			
			<td class="score-cell total-cell last-cell"><span class="total-score-label"><?php echo _("Причины недопуска") ?></span></td>
			<td class="score-cell total-cell last-cell"><span class="total-score-label"><?php echo _("Итоговый текущий рейтинг") ?></span><span style="width: 100%; display: inline-block;">(<?=$this->maxBallMediumRating?>)</span></td>
			<td class="score-cell total-cell last-cell"><span class="total-score-label"><?php echo _("Рубежный рейтинг") ?> </span><span style="width: 100%; display: inline-block;">(<?=$this->maxBallTotalRating?>)</span></td>
            <td class="score-cell total-cell last-cell"><span class="total-score-label"><?php echo _("Оценка") ?></span></td>
            <td class="score-cell total-cell last-cell"><span class="total-score-label"><?php echo _("Итог") ?></span></td>            
            <?php if($this->isShowSingleVedomost):?><td class="score-cell total-cell last-cell">&nbsp;</td><?php endif;?>
        </tr>
        <tr class="marksheet-head">
            <th class="marksheet-rowcheckbox first-cell hide-column" rowspan="2"><?php echo $this->formCheckbox('')?></th>
            <th class="fio-cell" rowspan="2" style="border-left-width: 1px;"><?php echo _('ФИО');?></th>
            <?php foreach($this->schedules as $key => $schedule):?>
			<?php $isJPractice = ($schedule->typeID == HM_Event_EventModel::TYPE_JOURNAL_PRACTICE) ? (true) : (false);?>
            <td class="lesson-cell score-cell" >
				<input id="schedule_<?php echo $schedule->SHEID?>" tabindex="0" type="checkbox" name="schedule[<?php echo $schedule->SHEID;?>]" value="1">
				<?php if($isJPractice): ?>
					<div style="clear:both; padding: 0px;"></div>
					<div style="border-right: 1px solid #c5d0d7; border-top: 1px solid #c5d0d7; float: left; width: 49%;">АА</div>
					<div style="border-top: 1px solid #c5d0d7; float: left; width: 49%;">ПР</div>
				<?php endif; ?>	
			</td>
            
			<?php endforeach;?>
            <td class="total-cell last-cell score-cell" >&nbsp;</td>
            <td class="total-cell last-cell score-cell" >&nbsp;</td>
            <td class="total-cell last-cell score-cell" >&nbsp;</td>
            <td class="total-cell last-cell score-cell" >&nbsp;</td>
            <td class="total-cell last-cell score-cell" ><input tabindex="0" type="checkbox" name="total"></td>
			<?php if($this->isShowSingleVedomost):?><td class="total-cell last-cell score-cell" >&nbsp;</td><?php endif;?>			
        </tr>
    </thead>
    <tbody>
		<?php if(count($this->persons) > 5): ?>
			<tr class="last-row ui-helper-hidden">
				<td class="first-cell" colspan="2"></td>
				<td class="slider-cell 12" colspan="12"><div id="marksheet-slider-top"></div></td>
				<td class="last-cell" colspan="100%"></td>
			</tr>
		<?php endif; ?>	
        <?php
        $temp1 = 1;
		#$isFullMarksheet = true; # признак - оценка есть у всех студентов.
        foreach($this->persons as $key => $person):?>
			<?php
				$isPassTotalRating  = Zend_Registry::get('serviceContainer')->getService('Lesson')->isPassTotalRating($this->maxBallTotalRating, $this->dataRatingTotal[$key], $this->subject->isDO, $this->subject->is_practice);
				$isPassMediumRating = Zend_Registry::get('serviceContainer')->getService('Lesson')->isPassMediumRating($this->maxBallMediumRating, $this->dataRatingMedium[$key], $this->subject->isDO);
				
				# Не допущен к экзамену или сдал его ниже проходного балла.
				#$isFailSubject = empty( $this->scores[$key.'_total']['fail_message'] ) ? false : true; 
				$isPassTotalRating = empty( $this->scores[$key.'_total']['fail_message'] ) ? $isPassTotalRating : false; 
			?>
			
			<?php
				$fail_lessons = array(); # занятия, из-за которых недопуск к экзамену
				if(!empty($this->scores[$key.'_total']['fail_message'])){
					foreach($this->scores[$key.'_total']['fail_message'] as $mes){
						if(isset($mes['lessons'])){
							foreach($mes['lessons'] as $les_id){
								$fail_lessons[$les_id] = $les_id;
							}
						}
					}			
				}
			?>
			
			
        <tr class="<?php echo ($temp1 % 2 == 0) ? "even" : "odd"; if ($temp1 == 1) { echo " first-row"; } else if ($temp1 == $totalPersons) { echo " last-row"; } ?>"
        >
            <td class="marksheet-rowcheckbox first-cell hide-column"><input tabindex="0" type="checkbox" name="person[<?php echo $key;?>]" value="1"></td>
            <td class="fio-cell cell125" style="border-left-width: 1px;">            
            <a href="<?php echo $this->url(array('module' => 'lesson', 'controller' => 'list','action' => 'my', 'user_id' => $person->MID));?>">
                <?php echo $this->escape($person->getName());?>
            </a><br><?php
                        $groups = $person->studyGroups;
                        if ($groups) {
                            $tmp = array();
                            foreach ($groups as $group) {
                                $tmp [] = $group['name'];
                            }
                            echo implode(', ',$tmp);
                        }
                     ?></td>
            <?php
            $temp = 1;
            foreach($this->schedules as $schedule):?>
			<?php $isJPractice = ($schedule->typeID == HM_Event_EventModel::TYPE_JOURNAL_PRACTICE) ? (true) : (false);?>
            <?php #$fail_lesson_class = (!empty($this->scores[$key.'_'.$schedule->SHEID]->isFail) && $this->scores[$key.'_'.$schedule->SHEID]->V_STATUS >= 0) ? ('fail-lesson') : (''); ?>
			<?php $fail_lesson_class = (isset($fail_lessons[$schedule->SHEID])) ? ('fail-lesson'):(''); ?>
			<?php 
				#if($schedule->SHEID == 114531 && $key == 58271){
					#$serviceLesson = Zend_Registry::get('serviceContainer')->getService('Lesson');
					#echo '<div style="display:none;">';
					#echo '....';
					#var_dump($schedule->max_ball, $this->scores[$key.'_'.$schedule->SHEID]->V_STATUS);
					#var_dump($serviceLesson->isPassTotalRating($schedule->max_ball, $this->scores[$key.'_'.$schedule->SHEID]->V_STATUS));
					#echo '</div>';
				#}
				
			?>
			<?php
				if(empty($fail_lesson_class)){
					$serviceLesson = Zend_Registry::get('serviceContainer')->getService('Lesson');
					if($serviceLesson->isTotalRating($schedule)){
						if(
							$this->scores[$key.'_'.$schedule->SHEID]->V_STATUS <= 0
							||
							!$serviceLesson->isPassTotalRating($schedule->max_ball, $this->scores[$key.'_'.$schedule->SHEID]->V_STATUS)
						){
							$fail_lesson_class = 'fail-lesson';
						} 				
					}
				}
			?>
			
			<?php 
			if($isJPractice): ?>
				<td class="score-cell lesson-cell cell-practice <?=(!isset($this->scores[$key.'_'.$schedule->SHEID])) ? ('no-score') : ('')?>" <?=($isMarkBrs) ? ('style="background-color:'.$schedule->getColor().'"') : ('');?> >
				<div style="border-right: 1px solid #c5d0d7; float: left; width: 49%;">
				<?php if (isset($this->scores[$key.'_'.$schedule->SHEID])):?>
					<?php echo $this->score(array(
						'score' 	=> $this->scores[$key.'_'.$schedule->SHEID]->ball_academic,
						'user_id' 	=> $key,
						'lesson_id' => $schedule->SHEID,
						'scale_id' 	=> $schedule->getScale($this->subject->mark_type), //HM_Scale_ScaleModel::TYPE_CONTINUOUS,
						'mode' 		=> HM_View_Helper_Score::MODE_MARKSHEET,
						'tabindex' 	=> $temp . '00' . $temp1,
						'mark_type' => $this->subject->mark_type
					));?>
					<?php endif;?>
					<?php if(strlen($this->scores[$key.'_'.$schedule->SHEID]->comments)):?>
						<div class="score-comments" title="<?php echo $this->escape($this->scores[$key.'_'.$schedule->SHEID]->comments);?>"></div>
					<?php endif;?>
				</div>				
				<div  style="float: left; width: 49%;">
				<?php if (isset($this->scores[$key.'_'.$schedule->SHEID])):?>
					<?php echo $this->score(array(
						'score' 	=> $this->scores[$key.'_'.$schedule->SHEID]->ball_practic,
						'user_id' 	=> $key,
						'lesson_id' => $schedule->SHEID,
						'scale_id' 	=> $schedule->getScale($this->subject->mark_type), //HM_Scale_ScaleModel::TYPE_CONTINUOUS,
						'mode' 		=> HM_View_Helper_Score::MODE_MARKSHEET,
						'tabindex' 	=> $temp . '00' . $temp1,
						'mark_type' => $this->subject->mark_type
					));?>
					<?php endif;?>
					<?php if(strlen($this->scores[$key.'_'.$schedule->SHEID]->comments)):?>
						<div class="score-comments" title="<?php echo $this->escape($this->scores[$key.'_'.$schedule->SHEID]->comments);?>"></div>
					<?php endif;?>
				</div></td>
			<?php else: ?>
			<td class="score-cell lesson-cell <?=(!isset($this->scores[$key.'_'.$schedule->SHEID])) ? ('no-score') : ('')?> <?=$fail_lesson_class;?>" <?=($isMarkBrs) ? ('style="background-color:'.$schedule->getColor().'"') : ('');?> >
			<div>
            <?php if (isset($this->scores[$key.'_'.$schedule->SHEID])):?>
                <?php 
					$score = $this->scores[$key.'_'.$schedule->SHEID]->V_STATUS;					
					$score = (stripos($schedule->title, 'итоговый контроль') !== false) ? round($score) : $score;
					#if($schedule->SHEID == 114531){
						#echo '<div style="display:none;">';
						#echo $this->scores[$key.'_'.$schedule->SHEID]->V_STATUS;
						#echo '</div>';
					#}
				?>
				<?php echo $this->score(array(
                    'score' => $score,
                    'user_id' => $key,
                    'lesson_id' => $schedule->SHEID,
                    'scale_id' 	=> $schedule->getScale($this->subject->mark_type), //HM_Scale_ScaleModel::TYPE_CONTINUOUS,
                    'mode' 		=> HM_View_Helper_Score::MODE_MARKSHEET,
                    'tabindex' 	=> $temp . '00' . $temp1,
                    'mark_type' => $this->subject->mark_type
                ));?>
                <?php endif;?>
                <?php if(strlen($this->scores[$key.'_'.$schedule->SHEID]->comments)):?>
					<div class="score-comments" title="<?php echo $this->escape($this->scores[$key.'_'.$schedule->SHEID]->comments);?>"></div>
                <?php endif;?>
            </div></td>
			<?php endif; ?>			
            <?php $temp++;
            endforeach;?>

			<?php 				
				$show_total_practic 	   = !$this->scores[$key.'_failTotalPractic'];
				$fail_class_rating_medium  = (!$isPassMediumRating && $show_total_practic) ? ('fail-lesson') : ('');
				$fail_class_message		   = !empty( $this->scores[$key.'_total']['fail_message'] ) ? 'fail-lesson' : '';
			?>
			
			<td class="score-cell total-cell last-cell <?=$fail_class_message;?>"><div>			
					<?php 						
						if(!empty( $this->scores[$key.'_total']['fail_message'] )){
							foreach($this->scores[$key.'_total']['fail_message'] as $mes){								
								echo $mes['message'].'<hr />';
							}
							echo 'Недопуск';
							#$this->isShowGraduateAction = false;
							
						} 
						#elseif(	round($this->moduleData['integrate'][$key]['medium']) < 55 && round($this->moduleData['integrate'][$key]['medium']) >= 52 ){
						#	$this->scores[$key.'_total']['fail_message'] = array('message' => 'Добавьте баллы до 55');
						#	echo 'Добавьте баллы до 55';
						#	#$this->isShowGraduateAction = false;
						#}
					?>					
			</div></td>
			
			<td class="score-cell total-cell last-cell <?=$fail_class_rating_medium;?>"><div class="total-ball">			
					<?#=(isset($this->dataRatingMedium[$key]) && $show_total_practic) ? ( round($this->dataRatingMedium[$key]) ):('');?>
					<?=(isset($this->dataRatingMedium[$key]) ) ? ( round($this->dataRatingMedium[$key]) ):('');?>
			</div></td>
			<td class="score-cell total-cell last-cell"><div class="total-ball" data-par="<?=round($this->dataRatingTotal[$key])?>" >
				<?=(isset($this->dataRatingTotal[$key]) && $isPassMediumRating && $show_total_practic  && empty( $this->scores[$key.'_total']['fail_message'] ) ) ? ( round($this->dataRatingTotal[$key]) ):('');?>
			</div></td>

			<td class="score-cell total-cell last-cell"><div class="total-ball total-ball-text">				
				<?
				#if(!empty($this->dataRatingTotal[$key]) && $isPassTotalRating && $isPassMediumRating && $show_total_practic){
				/*
				if(!empty($this->dataRatingTotal[$key]) && $isPassMediumRating && $show_total_practic){
					echo Zend_Registry::get('serviceContainer')->getService('Lesson')->getTextFiveScaleMark($this->scores[$key.'_total']['mark_5'], $this->subject->exam_type);
				} elseif($this->dataRatingTotal[$key] <= 0 || !empty( $this->scores[$key.'_total']['fail_message'] ) ){
					echo 'н/я';
				}
				*/
				
				if(
					!empty( $this->scores[$key.'_total']['fail_message'] ) 
					||
					$this->dataRatingTotal[$key] <= 0 )
				{
					echo 'н/я';
					
					
				}elseif( !$isPassTotalRating ){
					$mark_5 = 2; #Неважно, как много набрано баллов. Если завалил экзамен, в любом случае это неуд.
					echo Zend_Registry::get('serviceContainer')->getService('Lesson')->getTextFiveScaleMark($mark_5, $this->subject->exam_type);	
					
				} else{ 
					echo Zend_Registry::get('serviceContainer')->getService('Lesson')->getTextFiveScaleMark($this->scores[$key.'_total']['mark_5'], $this->subject->exam_type);						
				}
				?>				
			</div></td>
            <td class="score-cell total-cell last-cell">
				<?php
					###########
					#if( $this->subjectId == 26862 && $key == 64328 ){
					#	echo '<div style="display:none" class="qqqqqqq">';
					#	var_dump(
					#		$this->scores[$key.'_total']['mark'],
					#		$this->scores[$key.'_failTotalPractic']						
					#	);
					#	echo '</div>';				
					#}
				?>
                <div>		
				<?php
				if(!empty( $this->scores[$key.'_total']['fail_message'] )){
					$totalScore = round($this->dataRatingMedium[$key]);
					
					$totalScore = Zend_Registry::get('serviceContainer')->getService('Lesson')->normalizeTotalBall($totalScore);
					
					echo $this->score(array(
						'score' 	=> $totalScore,
						'user_id' 	=> $key,
						'lesson_id' => 'total',
						'scale_id' 	=> $this->subject->getScale($this->subject->mark_type), //HM_Scale_ScaleModel::TYPE_CONTINUOUS,
						'mode' 		=> HM_View_Helper_Score::MODE_MARKSHEET,
						'tabindex' 	=> $temp . '00' . $temp1,
						'mark_type' => $this->subject->mark_type
					));	
				} elseif($this->scores[$key.'_total']['mark'] !== NULL && $this->scores[$key.'_total']['mark'] >= 0 /*&&  $isPassTotalRating && !empty($this->dataRatingTotal[$key])*/ && $isPassMediumRating && $show_total_practic){
				#if($this->scores[$key.'_total']['mark'] !== NULL && $this->scores[$key.'_total']['mark'] >= 0 &&  $isPassTotalRating && !empty($this->dataRatingTotal[$key]) && $isPassMediumRating && $show_total_practic){
				
					$totalScore = (round($this->dataRatingMedium[$key]) + round($this->dataRatingTotal[$key]) );
					
					$totalScore = Zend_Registry::get('serviceContainer')->getService('Lesson')->normalizeTotalBall($totalScore);
					
					echo $this->score(array(
						'score' => $totalScore, #round( $this->scores[$key.'_total']['mark'] ), #round($this->scores[$key.'_total']['mark']),
						'user_id' => $key,
						'lesson_id' => 'total',
						'scale_id' => $this->subject->getScale($this->subject->mark_type), //HM_Scale_ScaleModel::TYPE_CONTINUOUS,
						'mode' => HM_View_Helper_Score::MODE_MARKSHEET,
						'tabindex' => $temp . '00' . $temp1,
						'mark_type' => $this->subject->mark_type
					));				
				} else {
					#$isFullMarksheet = false;
				}
				
				?>
				<?php if(strlen($this->scores[$key.'_total']->comments)):?>
                <div class="score-comments" title="<?php echo $this->escape($this->scores[$key.'_total']->comments);?>"></div>
                <?php endif;?>
                <?php if(strlen($this->scores[$key.'_total']['comment'])):?>
                <div class="score-comments" title="<?php echo $this->escape($this->scores[$key.'_total']['comment']);?>"></div>
                <?php endif;?>
                </div>
            </td>
			<?php if($this->isShowSingleVedomost):?>
				<?php
					# Если оценка есть и нет непроверенных работ со стороны тьютора - показать кнопку завершения Индивидуальной ведомости				
					# Эту же проверку добавить в момент формирования ведомости ИН (Завершить)
					$isShowBtnIndividualVedomost = 
						(	
							#empty( $this->scores[$key.'_total']['fail_message'] )
							#&&
							# эта штука работает долго. Или переписать для одного студента, или в результат добавить не только уроки, но и id студентов для разделения потом.
							!Zend_Registry::get('serviceContainer')->getService('Subject')->isNewActionStudent($subject_id, array($key)) 
						)
						? true : false;
						
						
				?>			
				<td class="score-cell total-cell last-cell <?=($isShowBtnIndividualVedomost)?'':' fail-lesson ';?>">				
					<?php if($isShowBtnIndividualVedomost): ?>
						<a target="_blank" class="btn-grad-vedomost-single" href="#" data-fio="<?=$this->escape($person->getName());?>" data-url="<?=$this->url(array('module' => 'marksheet', 'controller' => 'graduate', 'action' => 'individual-vedomost', 'user_id' => $key));?>">Завершить</a>
					<?php endif;?>
				</td>
			<?php endif;?>
            <?php $temp++;?>
        </tr>
        <?php $temp1++;
        endforeach;?>
        <tr class="last-row ui-helper-hidden">
            <td class="first-cell" colspan="2"></td>
            <td class="slider-cell 12" colspan="12"><div id="marksheet-slider"></div></td>
            <td class="last-cell" colspan="100%" style="border-top-width: 1px;"></td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="100%">
                <table cellspacing="0">
                    <colgroup><col width="1"><col width="1"><col width="1"><col width="*"></colgroup>
                    <tr class="first-row">
                        <td class="first-cell hide-column">
                            <?php #echo _('Действия со слушателями');?>
                        </td>
                        <td>
							<?/*
							<?
							
							$studentMassAction = array(
														'none' => _('Не выбрано'), 
                                                        $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'set-total-score')) => _('Выставить оценку за курс'),														
												 );
							#if($isFullMarksheet){
							if($this->isShowGraduateAction){
								$studentMassAction[$this->url(array('module' =>'marksheet', 'controller' => 'graduate', 'action' => 'index'))] =  _('Завершить курс');
							}
							?>						
                            <?php echo $this->formSelect
                                (
                                    'studentMassAction', 'none', '',
                                    $studentMassAction
                                )
                            ;?>
							*/?>
                        </td>
                        <td class="button-cell">
                            <?php #echo $this->formButton('StudentButton', _('Выполнить'), '');?>
                        </td>
                        <td class="last-cell" rowspan="2">
                            <?php echo $this->formButton('commentButton', _('Добавить комментарий'), array('disabled' => 'disabled'));?>
                            <?/*
							<?php echo $this->formButton(
                                                'printButton',
                                                _('Распечатать'),
                                                array('onClick' => 'window.window.open("'.$this->serverUrl($this->url(array(
                                                        'module' => 'marksheet',
                                                        'controller' => 'index',
                                                        'action' => 'print',
                                                        'subject_id' => $this->subjectId
                                                        ))).'"
                                                )')
                            );?>
							*/ ?>
                            <?php echo $this->formButton(
                                                'excelButton',
                                                _('Excel'),
                                                array('onClick' => 'window.open("'.$this->serverUrl($this->url(array(
                                                        'module' => 'marksheet',
                                                        'controller' => 'index',
                                                        'action' => 'excel',
                                                        'subject_id' => $this->subjectId
                                                        ))).'"
                                                )')
                            );?>
                            <?php echo $this->formButton(
                                                'wordButton',
                                                _('Зач./Экз.ведомость = ЧЕРНОВИК'),
                                                array('onClick' => 'window.open("'.$this->serverUrl($this->url(array(
                                                        'module' => 'marksheet',
                                                        'controller' => 'index',
                                                        'action' => 'vedomost',
                                                        'subject_id' => $this->subjectId
                                                        ))).'"
                                                )')
                            );?>
							
							<?php
								if($this->isShowGraduateAction){
									echo $this->formButton(
                                                'wordButton',
                                                _('Завершить курс'),
                                                array(
													#'onClick' => 'window.open("'.$this->serverUrl($this->url(array(
													'data-url' => $this->serverUrl($this->url(array(
                                                        'module' 		=> 'marksheet',
                                                        'controller' 	=> 'graduate',
                                                        'action' 		=> 'vedomost',
                                                        'subject_id' 	=> $this->subjectId
                                                        ))),
													'class' => 'btn-grad-vedomost-all',													
												)
									);
								}
							?>
                        </td>
                    </tr>
                    <tr class="last-row">
                        <td class="first-cell hide-column">
                            <?php #echo _('Действия с занятиями');?>
                        </td>
                        <td>
                            <?php #echo $this->formSelect('scheduleMassAction', 'none', '', array('none' => _('Не выбрано'), $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'clear-schedule')) => _('Очистить оценки')));?>
                        </td>
                        <td class="button-cell">
                            <?php #echo $this->formButton('scheduleButton', _('Выполнить'), '');?>
                        </td>
                        <!-- <td class="last-cell"></td> -->
                    </tr>
                </table>
            </td>
        </tr>
    </tfoot>
</table>
</form>

<div class="marksheet-messages" style="text-align: right;">
	<?=empty($this->messages) ? '' : implode('<br />', $this->messages )?>
</div>

<div id="marksheet-comment-dialog" title="<?php echo _("Комментарии"); ?>">
    <div class="textarea-wrapper"><textarea id="textComment" name="comment"></textarea></div>
</div>

<div id="dialog-grad-vedomost-all" title="Завершить курс" data-url="">
    <p>
		<span style="float: left;">
			Вы уверены, что хотите завершить курс для всей группы студентов?
			<br/>
			<br/>
			После завершения курса изменить оценки будет невозможно.
		</span>
	</p>
</div>

<div id="dialog-grad-vedomost-single" title="Завершить" data-url="" >
    <p>
		<span style="float: left;">
			Вы уверены, что хотите завершить курс для студента <strong><span class="student-fio"></span></strong>?
			<br />
			<br />
			После завершения курса изменить оценки будет невозможно.
		</span>
	</p>
</div>


<?php
$this->inlineScript()->captureStart();
?>

$( ".btn-grad-vedomost-single" ).click(function() {
	var url = $(this).data('url');
	
	//$('#dialog-grad-vedomost-single').attr('data-url', url );
	
	$('#dialog-grad-vedomost-single').data("url", url);	
	$('.student-fio').html( $(this).data('fio') );
	$( "#dialog-grad-vedomost-single" ).dialog( "open" );	
	
	return false;	
});

	$( "#dialog-grad-vedomost-single" ).dialog({
		resizable: false,
		autoOpen: false,
		height:180,
		width:315,
		modal: true,
		buttons:
		{
			<?php echo _('Да')?>: function() {
				$( this ).dialog( "close" );				
				console.log(	$('#dialog-grad-vedomost-single').data('url')	);
				window.open(	$('#dialog-grad-vedomost-single').data('url')	);
				$('#dialog-grad-vedomost-single').attr('data-url', '');
				$('.student-fio').html( '' );
			},
			<?php echo _('Нет')?>: function() {
				$( this ).dialog( "close" );
            }
		}
	});



$( ".btn-grad-vedomost-all" ).click(function() {	
	$('#dialog-grad-vedomost-all').attr('data-url', $(this).data('url') );
	$( "#dialog-grad-vedomost-all" ).dialog( "open" );	
	return false;	
});


	$( "#dialog-grad-vedomost-all" ).dialog({
		resizable: false,
		autoOpen: false,
		height:180,
		modal: true,
		buttons:
		{
			<?php echo _('Да')?>: function() {
				$( this ).dialog( "close" );				
				console.log(	$('#dialog-grad-vedomost-all').data('url')	);
				window.open(	$('#dialog-grad-vedomost-all').data('url')	);
				$('#dialog-grad-vedomost-all').attr('data-url', '');
			},
			<?php echo _('Нет')?>: function() {
				$( this ).dialog( "close" );
            }
		}
	});





initMarksheet({
    url: {
        comments: "<?php echo $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'set-comment'));?>",
        score: "<?php echo $this->url(array('module' =>'marksheet', 'controller' => 'index', 'action' => 'set-score'));?>"
    },
    l10n: {
        save: "<?php echo _("Сохранить"); ?>",
        noStudentActionSelected: "<?php echo _("Не выбрано ни одного действия со слушателем"); ?>",
        noStudentSelected: "<?php echo _("Не выбрано ни одного слушателя"); ?>",
        noLessonActionSelected: "<?php echo _("Не выбрано ни одного действия с занятием"); ?>",
        noLessonSelected: "<?php echo _("Не выбрано ни одного занятия"); ?>",
        formError: "<?php echo _("Ошибка формы") ?>",
        ok: "<?php echo _("Хорошо"); ?>",
        confirm: "<?php echo _("Подтверждение"); ?>",
        areUShure: "<?php echo _("Вы уверены?"); ?>",
        yes: "<?php echo _("Да"); ?>",
        no: "<?php echo _("Нет"); ?>"
    }
});
$( document ).ready(function() {
	vv = $('.hm-marksheet-scrollbar').width();
	vv = vv + 100;
	$('.hm-marksheet-scrollbar').attr('width', vv+'px');
	console.log(vv);
});
<?php
$this->inlineScript()->captureEnd();
?>
<?php endif;?>