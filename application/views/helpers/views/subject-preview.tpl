<?php require_once APPLICATION_PATH .  '/views/helpers/Score.php';?>
<?php $isStudent = (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_ENDUSER))) ? (true) : (false); ?>

<?php

	$lng = 'rus';
	$request = Zend_Controller_Front::getInstance()->getRequest();
	$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);	
		
	if($lng == 'eng' && $this->subject->name_translation != '')
		$this->subject->name = $this->subject->name_translation;   

?>

<div class="lesson">
<a name="lesson_<?php echo $this->subject->subid?>"></a>
<div class="lesson_wrapper_1">
<div class="lesson_wrapper_2">
<div  <?php if (strtotime($this->studentCourseData['end']) && $this->showScore): // если обучение закончено - выделить цветом
?> id="lesson_block_active" <?php else: ?> class="lesson_block" <?php endif;?>>
<div class="lesson_table">
<table border="0" cellpadding="0" cellspacing="0" >
  <tr>
    <td width="109" align="center" valign="top" class="lesson_bg">

        <?php $isStudent = Zend_Registry::get('serviceContainer')->getService('Subject')->isStudent($this->subject->subid, $this->currentUserId); ?>

<div id="lesson_bg_img">
<?php if ($this->subject->getIcon()):?>
    <?php if (!$this->subject->isAccessible() && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER) || (!$isStudent && !$this->isTeacher)): ?>
        <img class="subject-icon" src="<?php echo $this->subject->getIcon()?>" alt="<?php echo $this->escape($this->subject->name)?>" title="<?php echo $this->escape($this->subject->name)?>"/>
    <?php else: ?>
    <a href="<?php echo $this->subject->getDefaultUri();?>">
        <img class="subject-icon" src="<?php echo $this->subject->getIcon()?>" alt="<?php echo $this->escape($this->subject->name)?>" title="<?php echo $this->escape($this->subject->name)?>"/>
    </a>
    <?php endif;?>
<?php endif;?>
</div>
<div id="lesson_type"><?php echo $this->subject->isBase() ? _('Базовый курс') : $this->subject->getType();?></div>

</td>
<td width="450" class="lesson_options">
    <div id="lesson_title">
	<?php
		if($this->isDebt) {
			$debtText = _('(долг)');
		}
		$class = '';
		if(Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_TUTOR,HM_Role_RoleModelAbstract::ROLE_TEACHER))) {
			
			if($this->isDebt) { $debtText = _('(продлено)'); }
			if($this->subject->isDO) {
				$class = 'subject-do';							
				$doText = _('(ДО)');				
			}			
			
			if(isset($this->studentCourseData['isNewActionStudent']) && $this->studentCourseData['isNewActionStudent'] === true) {				
				$class = 'subject-new-action';
			}			
		} else {
			if($this->isDebt) {				
				$class = 'subject-debt';
			}					
		}
	?>
	
	
    <?php if (!$this->subject->isAccessible() && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_ENDUSER) || (!$isStudent && !$this->isTeacher)): ?>		
		<?php if($this->isActiveDebt): ?><a class="<?=$class;?>" href="<?php echo $this->subject->getDefaultUri();?>"><?php else : ?><span class="lesson-title-past"><?php endif;?>			
			<?=$this->escape($this->subject->name);?>
			<?=$doText;?>
			<?=$debtText;?>
		<?php if($this->isActiveDebt): ?></a><?php else : ?></span><?php endif;?>		
	<?php else: ?>        
		
		<a class="<?=$class;?>" href="<?php echo $this->subject->getDefaultUri();?>">
            <?=$this->escape($this->subject->name);?>
			<?=$doText;?>
			<?=$debtText;?>
        </a>
    <?php endif;?>
	</div>
	
	
	<?php if($this->subject->getModuleName()):?>
		<div style="padding: 0px 10px 0px 15px;  line-height: 20px;">
			<p><span class="subject-info-caption"><?=_('Дисциплина')?>:</span> <span class="subject-info-value"><?=$this->subject->getModuleName()?></span></p>
		</div>
	<?php endif;?>
	

    <?php if ($this->switcher != 'programm') { ?>
    <?php $programms = Zend_Registry::get('serviceContainer')->getService('Programm')->getProgrammsBySubjectId($this->subject->subid, $this->currentUserId);?>
    <?php if (count($programms)) { ?>
    <div style="padding: 0px 10px 0px 15px;">
		<p>
			<span class="subject-info-caption"><?=count($programms)==1 ? _('Программа'):('Программы'); ?></span>
			<?php
			$i=0; 
			foreach ($programms as $programm) { 
				$i++; 
				if ($i == 1) 	{	echo ': ' . $programm->name;} 
				else 			{	echo ', ' . $programm->name;}  
			}
			?>
		</p>
    </div>
    <?php } else { ?>
		<?php if( $this->isEndUser && !$this->graduated ):?>
			<div style="padding: 0px 10px 0px 15px;">
				<p>
					<span class="subject-info-caption"><?=_('Программа');?>: </span>
					<span style="color:red;"><?=_('Нет')?>!!!</span>
				</p>
			</div>
		<?php endif;?>
	<?php } ?>
    <?php } ?>


    <div id="lesson_go">
        <div id="lesson_begin" class="<?php if ((!$this->subject->begin) || $this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT):?>recomended<?php endif;?>">
        <?php if (strtotime($this->studentCourseData['end'])): ?>
        	<?php if(strtotime($this->subject->begin) > 0) : ?>
				<p><span class="subject-info-caption"><?=_('Дата начала сессии');?>:</span> <span class="subject-info-value"><?php $begin = new Zend_Date($this->subject->begin); echo $begin->toString(Zend_Date::DATES);?></span></p>
			<?php endif;?>
			<?php if(strtotime($this->subject->end) > 0) : ?>
				<p><span class="subject-info-caption"><?=_('Дата окончания сессии');?>:</span> <span class="subject-info-value"><?php $end = new Zend_Date($this->subject->end); echo $end->toString(Zend_Date::DATES);?></span></p>
			<?php endif;?>
			<?php /*if(!$isStudent) : */ ?>
				<?php if($this->subject->isGia): ?>
					<p><?=_('Дата окончания обучения');?>: <?=_('не позднее срока, указанного в')?> <a target="_blank" href="https://rgsu.net/for-students/timetable/"><?=_('графике учебного процесса')?></a></p>
				<?php else :?>
					<p><span class="subject-info-caption"><?=_('Дата окончания обучения')?></span>: <span class="subject-info-value"><?php $end = new Zend_Date($this->studentCourseData['end']); echo $end->toString(Zend_Date::DATES);?></span></p>
				<?php endif;?>
			<?php /*endif; */ ?>	
        <?php else:?>
	        <?php if ($this->subject->period == HM_Subject_SubjectModel::PERIOD_FREE):?>
	            <span class="subject-info-caption"><?php echo _('Время обучения');?></span> <?=_('не ограничено')?></span>
	        <?php else:?>
	        	<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_RoleModelAbstract::ROLE_ENDUSER, HM_Role_RoleModelAbstract::ROLE_TEACHER, HM_Role_RoleModelAbstract::ROLE_TUTOR))) :?>

	        	    <?php if (in_array($this->subject->period, array(HM_Subject_SubjectModel::PERIOD_FREE, HM_Subject_SubjectModel::PERIOD_FIXED))): // когда зачислен - тогда и начало?>

                        <?php if ($this->studentCourseData['begin']):?>
                            <p><?php echo _('Дата начала обучения');?>:
                            <?php $begin = new Zend_Date($this->studentCourseData['begin']); echo $begin->toString(Zend_Date::DATES);?></p>
                        <?php elseif ($this->subject->longtime):?>
                            <p><?php echo sprintf(_('Время обучения, дней: %s'), $this->subject->longtime);?></p>
                        <?php endif;?>

		            <?php else: // PERIOD_DATES?>

    		            <?php if ($this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT): ?>
    		                <p>
    		                <?php if (strtotime($this->subject->begin) > time()): ?>
        		            <?php echo _('Дата начала обучения, не ранее');?>:
        		            <?php else: ?>
        		            <span class="subject-info-caption"><?php echo _('Дата начала обучения');?>:</span>
        		            <?php endif; ?>
        		            <?php $begin = new Zend_Date($this->subject->begin); echo $begin->toString(Zend_Date::DATES);?>
        		            </p>
    		            <?php elseif ($this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT): ?>
    		                <p>
    		                <span class="subject-info-caption">
								<?php if (strtotime($this->subject->begin) > time()): ?>
									<?=_('Рекомендуемая дата начала обучения');?>:
								<?php else: ?>
									<?=_('Дата начала обучения');?>:
								<?php endif; ?>
							</span>
							<span class="subject-info-value">
								<?php $begin = new Zend_Date($this->subject->begin); echo $begin->toString(Zend_Date::DATES);?>
        		            </span>
							</p>
    		            <?php elseif ($this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL): // ручной режим?>
        		            <?php if (!empty($this->subject->begin)): // уже стартовал ?>
            		            <p><?php echo _('Дата начала обучения');?>: <?php $begin = new Zend_Date($this->subject->begin); echo $begin->toString(Zend_Date::DATES);?></p>
        		            <?php else: // еще не стартовал?>
        		                <?php $begin = new Zend_Date($this->subject->begin_planned); $begin = $begin->toString(Zend_Date::DATES);?>
                                <p><?php echo sprintf(_('Дата начала обучения определяется преподавателем (ориентировочно: %s)'), $begin);?></p>
    		                <?php endif; ?>
    		            <?php endif; ?>

		            <? endif; // end date begin?>
					<?php /*if(!$isStudent) : */ ?>
						<?php if (($this->subject->period == HM_Subject_SubjectModel::PERIOD_FIXED) || ($this->subject->period == HM_Subject_SubjectModel::PERIOD_DATES && $this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT)): ?>

							<?php if (strtotime($this->studentCourseData['end_planned'])): ?>							
								<?php if($this->subject->isGia): ?>
									<p><?=_('Дата окончания обучения');?>: <?=_('не позднее срока, указанного в')?> <a target="_blank" href="https://rgsu.net/for-students/timetable/"><?=_('графике учебного процесса')?></a></p>
								<?php else :?>
									
									<?php if($this->isDO):?>
										<?php if(!empty($this->date_end)):?>
											<p><span class="subject-info-caption"><?=_('Дата окончания обучения, не позднее')?>:</span> <span class="subject-info-value"><?=$this->date_end?></span></p>
										<?php endif;?>
										<?php if(!empty($this->date_end_landmark_control)):?>
											<p><span class="subject-info-caption"><?=_('Дата приема рубежного контроля')?>:</span> <span class="subject-info-value"><?=$this->date_end_landmark_control?></p>
										<?php endif;?>
									<?php else :?>
										<p><span class="subject-info-caption"><?=_('Дата окончания обучения, не позднее');?>:</span> <?php $end = new Zend_Date($this->studentCourseData['end_planned']); echo $end->toString(Zend_Date::DATES);?></p>
									<?php endif;?>
								
								<?php endif;?>
							<?php elseif(strtotime($this->subject->end)) :?>
								<?php if($this->subject->isGia): ?>
									<p><?=_('Дата окончания обучения');?>: <?=_('не позднее срока, указанного в')?> <a target="_blank" href="https://rgsu.net/for-students/timetable/"><?=_('графике учебного процесса')?></a></p>
								<?php else :?>							
									
									<?php if($this->isDO):?>
										<?php if(!empty($this->date_end)):?>
											<p><span class="subject-info-caption"><?=_('Дата окончания обучения, не позднее')?>:</span> <span class="subject-info-value"><?=$this->date_end?></span></p>
										<?php endif;?>
										<?php if(!empty($this->date_end_landmark_control)):?>
											<p><span class="subject-info-caption"><?=_('Дата приема рубежного контроля')?>:</span> <span class="subject-info-value"><?=$this->date_end_landmark_control?></p>
										<?php endif;?>
									<?php else :?>
										<p><?php echo _('Дата окончания обучения');?>: <?php $end = new Zend_Date($this->subject->end); echo $end->toString(Zend_Date::DATES);?></p>
									<?php endif;?>
									
								<?php endif;?>
							<?php endif;?>

						<?php elseif ($this->subject->period == HM_Subject_SubjectModel::PERIOD_DATES && $this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT): // нестрого?>

							<?php if (strtotime($this->studentCourseData['end_planned'])): ?>
								<?php if($this->subject->isGia): ?>
									<p><?=_('Дата окончания обучения');?>: <?=_('не позднее срока, указанного в')?> <a target="_blank" href="https://rgsu.net/for-students/timetable/"><?=_('графике учебного процесса')?></a></p>
								<?php else :?>
								
									<?php if($this->isDO):?>
										<?php if(!empty($this->date_end)):?>
											<p><span class="subject-info-caption"><?=_('Дата окончания обучения, не позднее')?>:</span> <span class="subject-info-value"><?=$this->date_end?></span></p>
										<?php endif;?>
										<?php if(!empty($this->date_end_landmark_control)):?>
											<p><span class="subject-info-caption"><?=_('Дата приема рубежного контроля')?>:</span> <span class="subject-info-value"><?=$this->date_end_landmark_control?></p>
										<?php endif;?>
									<?php else :?>								
										<p><?php echo _('Рекомендуемая дата окончания обучения');?>: <?php $end = new Zend_Date($this->studentCourseData['end_planned']); echo $end->toString(Zend_Date::DATES);?></p>
									<?php endif;?>
									
								<?php endif;?>
							<?php elseif(strtotime($this->subject->end)) :?>					
								<?php if($this->subject->isGia): ?>
									<p><?=_('Дата окончания обучения');?>: <?=_('не позднее срока, указанного в')?> <a target="_blank" href="https://rgsu.net/for-students/timetable/"><?=_('графике учебного процесса')?></a></p>
								<?php else :?>
								
									<?php if($this->isDO):?>
										<?php if(!empty($this->date_end)):?>
											<p><span class="subject-info-caption"><?=_('Дата окончания обучения, не позднее')?>:</span> <span class="subject-info-value"><?=$this->date_end?></span></p>
										<?php endif;?>
										<?php if(!empty($this->date_end_landmark_control)):?>
											<p><span class="subject-info-caption"><?=_('Дата приема рубежного контроля')?>:</span> <span class="subject-info-value"><?=$this->date_end_landmark_control?></p>
										<?php endif;?>
									<?php else :?>									
										<p><?php echo _('Дата окончания обучения');?>: <?php $end = new Zend_Date($this->subject->end); echo $end->toString(Zend_Date::DATES);?></p>
									<?php endif;?>
									
								<?php endif;?>
							<?php endif;?>

						<?php elseif ($this->subject->period == HM_Subject_SubjectModel::PERIOD_DATES && $this->subject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL): // вручную?>

							<?php $end = new Zend_Date($this->subject->end_planned); $end = $end->toString(Zend_Date::DATES);?>
							<p><?php echo sprintf(_('Дата окончания обучения определяется преподавателем (ориентировочно: %s)'), $end);?></p>

						<? endif; // end date end?>
						
						<?php if($this->subject->isPractice() && $this->subject->getPracticePeriod()):?>
							<p><span class="subject-info-caption"><?=_('Даты прохождения практики')?>:</span> <span class="subject-info-value"><?=$this->subject->getPracticePeriod()?></p>
						<?php endif;?>
						
					<?php /* endif;*/ ?>
		        <?php endif;?>
	        <?php endif;?>
        <?php endif;?>		
        </div>
    </div>
	<?php if(isset($this->studentCourseData['exam_type'])) : ?>
	<div id="lesson_go">
		<p><span class="subject-info-caption"><?=_('Форма рубежного контроля')?></span>: <?=$this->studentCourseData['exam_type']; ?></p>				
	</div>	
	<?php endif;?>
	
	<div id="lesson_go">	
		<?php if(!empty($this->studentCourseData['semester'])):?><p><span class="subject-info-caption"><?=_('Семестр')?></span>: <span class="subject-info-value"><?=$this->studentCourseData['semester']?></span></p><?php endif;?>
	</div>	
	
	
	<?php if(!$this->isStudent):?>
	<div id="lesson_go">				
		<p>
			<span class="subject-info-caption"><?=_('Группы')?>:</span>
			<?php if(!count($this->groups)) : ?>
				<?=_('нет');?> 
			<?php else: ?>
				<?php foreach($this->groups as $group): ?>		
					<?=$group->getName()?>&nbsp;
				<?php endforeach;?>			
			<?php endif;?>
		</p>
	</div>	
	<?php endif;?>
	
	

		
	
	
    <?php if (count($this->subject->teachers) > 0):?>
    <div class="lesson_teacher">
    <?php echo _('Преподаватели').': ';
    $res = array();
    foreach($this->subject->teachers as $teacher){
        $res[] = '<div>' . $this->cardLink(
            $this->url(
                array(
    				'module' => 'user',
    				'controller' => 'list',
    				'action' => 'view',
    				'user_id' => $teacher->MID
                )
            )
        ) . $teacher->getName() . '</div>';

    }
    echo implode('', $res);
    ?>

	</div>
    <?php endif;?>
    <?php if (count($this->subject->tutors) > 0):?>
    <div class="lesson_teacher">
    <?php echo _('Тьюторы').': ';
    $res = array();
    if(Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(
									Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), 
									array(HM_Role_RoleModelAbstract::ROLE_ENDUSER)
	)){    
		foreach($this->subject->tutors as $mid => $name){
            $res[] = '<div>' . $this->cardLink(
                $this->url(
                    array(
                        'module' => 'user',
                        'controller' => 'list',
                        'action' => 'view',
                        'user_id' => $mid
                    )
                )
			) . $name . '</div>';
		}
	} else {
        foreach($this->subject->tutors as $tutor){
            $res[] = '<div>' . $this->cardLink(
                $this->url(
                    array(
                        'module' => 'user',
                        'controller' => 'list',
                        'action' => 'view',
                        'user_id' => $tutor->MID
                    )
				)
			) . $tutor->getName() . '</div>';  
		}
    }
    echo implode('', $res);
    ?>

	</div>
	<?php else: ?>
		<div class="lesson_teacher">
			<?=_('Тьюторы') . ': ' . '<span style="color: red;">' . _('не назначены') . '</span>' ?>
		</div>
    <?php endif;?>
    <?php if ($this->graduated) : ?>		
    	<div class="lesson_ended">
			<?php echo _('Курс завершён');?>
			<?php if(isset($this->graduated->reason) && !empty($this->graduated->reason)) { echo _(' по причине: "').$this->graduated->reason.'"'; } ?>
		</div>		
    <?php endif;?>

	<?php if($this->isActiveDebt && !$this->graduated && !empty($this->endDebtDate)) : ?>
		<?php #if(!$isStudent) : ?>
			<div class="lesson_ended">
				<?php echo _('Курс продлен до: ');?>
				<?php $endDebt = new Zend_Date($this->endDebtDate); echo $endDebt->toString(Zend_Date::DATES);?></p>
			</div>
		<?php #endif;?>
	<?php elseif (!$this->graduated && !$this->subject->isAccessible()) : ?> 
		<div class="lesson_ended"><?php echo _('Курс не доступен по ограничению во времени');?></div>
    <?php endif;?>
</td>
<td width="100" align="center" valign="top" class="showscore ball-area" data-id="<?=$this->subject->subid?>" id="ball-area-<?=$this->subject->subid?>">
<?php if ($this->showScore):?>
	<?php 
		$message = '';
		$ad_class = '';
		if(!empty($this->reasonFail)){
			$message  = current($this->reasonFail);
			$message  = $message['message'];
			$ad_class = 'not-pass';			
		}
		
		if(!empty($this->reasonFailModule[$this->subject->subid])){
			foreach($this->reasonFailModule[$this->subject->subid] as $subject_id => $i){
				$cur_reason = current($i['reasons']);
				$message   .= $cur_reason['message'].' в сессии <b>'.$i['name'].'</b>'.'<br />';	
			}
			$ad_class = 'not-pass';	
		}
	?>	
	<div class="score-block hidden <?=$ad_class;?>">    
	<?php echo $this->score(array(
        'score' => isset($this->marks[$this->subject->subid]) ? $this->marks[$this->subject->subid] : -1,
        'lesson_id' => 'total',
        'scale_id' => $this->subject->getScale(),
        'mode' => HM_View_Helper_Score::MODE_DEFAULT,
    ));?>
	</div>
	<div style="clear: both;" class="message-block hidden"><?=$message?></div>
	
	<?php if($this->mark):?>
	<div class="score-block">    
		<?=$this->score(array(
			'score'     => $this->mark ? $this->mark->getStudentBall() : '-1',
			'lesson_id' => 'total',
			'scale_id'  => $this->subject->getScale(),
			'mode'      => HM_View_Helper_Score::MODE_DEFAULT,
		));?>
	</div>
	<?php endif;?>
	
	<?php if($this->markFailInfo):?>		
		<div style="clear: both;" class="message-block">	
		<?php foreach($this->markFailInfo as $info): ?>
			<p style="padding-bottom: 10px;"><?=$info['description']?></p>			
		<?php endforeach;?>
		</div>		
	<?php endif;?>	
<?php endif;?>

<?php if($this->disperse == true): ?>
<div class="score_desc">
    <?php if ($isStudent) { ?>
        <?php  if ($this->subject->isUnsubscribleSubject) { ?>
            <a href="<?php echo $this->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'disperse', 'subject_id' => $this->subject->subid));?>" onClick="javascript: return confirm('<?php echo _('Вы действительно хотите отказаться от обучения на этом курсе? Это не исключает возможности в будущем заново подать заявку и пройти обучение.');?>');"><?php echo _('Отказаться от обучения');?></a>
        <?php } ?>
    <?php } else { ?>
        <a href="<?php echo $this->url(array('module' => 'user', 'controller' => 'reg', 'action' => 'subject', 'gridmod' => '', 'subid' => $this->subject->subid, 'programm_id' => 1)); ?>" onClick="javascript: return confirm('<?php echo _('Вы действительно хотите подать заявку на обучение на этом курсе?'); ?>');"><?php echo _('Подать заявку');?></a>
    <?php } ?>
</div>
<?php endif; ?>
</td>
<td width="150" valign="top" class="lesson_descript_td" id="<?php echo $this->descriptionId ?>"></td>
</tr>
</table>



</div>
</div>
</div>
</div>

</div>
