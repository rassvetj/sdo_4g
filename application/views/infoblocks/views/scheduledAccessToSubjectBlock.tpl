<?php
	$this->headLink()->appendStylesheet( $this->serverUrl('/css/infoblocks/schedule-accordion/schedule.css') );
		
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);

?>
<div class="schedule-accordion">
    <?php if ($this->lessons):?>
    <ul>
        <?php 			
			$routerName = Zend_Controller_Front::getInstance()->getRouter()->getCurrentRouteName(); //--fix for forum
			$urlParam = null;
			if($routerName == 'forum_subject'){
				$urlParam = 'default';
			}
		?>
		<?php $i = 1; $all = count($this->lessons); ?>
        <?php foreach($this->lessons as $lesson):?>
        <li class="<?= $lesson['status']; ?><?php if ($i == 1): ?> first<?php endif; ?><?php if ($i == $all): ?> last<?php endif; ?>"><span class="pit">
            <span class="bg<?php if ($lesson['status'] == 'not-started'): ?> l-bgc<?php endif; ?>"></span><span class="text" title="<?=
                $lesson['status'] == 'infinite' ? _('Время запуска не ограничено') : _('Время запуска') . ': ' . $this->escape($lesson['date']); ?>"><?=
                $lesson['status'] == 'infinite' ? '&#x221E;' : $this->escape($lesson['date']); ?></span>
        </span><span class="title">
            <?php if ($lesson['lesson']->typeID == HM_Event_EventModel::TYPE_EMPTY): ?>
                <span><?php echo $this->escape($lesson['lesson']->title) ?></span>
            <?php else: ?>
                <?php $lessonAttribs = array(
                    'href' => $this->url(array('action' => 'index', 'controller' => 'execute', 'module' => 'lesson', 'lesson_id' => $lesson['lesson']->SHEID, 'subject_id' => $lesson['lesson']->CID), 'default', true),
                    'target' => ($lesson['lesson']->typeID == HM_Event_EventModel::TYPE_WEBINAR) ? '_blank' : '_self'
                );
                if ($lesson['lesson']->typeID == HM_Event_EventModel::TYPE_COURSE) $lessonAttribs['target']=$lesson['lesson']->isNewWindow();?>
                <a <?php echo $this->HtmlAttribs($lessonAttribs) ?>><?php  // echo '<pre>'; var_dump($lesson['lesson']); 
												
																		if($lng == 'eng' && $lesson['lesson']->title_translation != '')	
																			echo $this->escape($lesson['lesson']->title_translation);
																		else	
																			echo $this->escape($lesson['lesson']->title);

																	?></a>
            <?php endif ?>
        </span></li>
        <?php $i++; ?>
        <?php endforeach;?>
    </ul>
    <?php else:?>
    <!--<p><?//= _('Данный курс не содержит занятий.')?></p>-->
    <?php endif;?>
    <?php if ($this->isBrs): ?>
    <div class="schedule-full lh-c">
        <a href="<?= $this->url(array('action' => 'my-progress', 'module' => 'lesson', 'controller' => 'list', 'subject_id' => $this->subject->subid), $urlParam, true);?>" class="l-bgc">
            <?= _('Прогресс изучения');?>
        </a>
    </div>
    <?php endif; ?>
    <div class="schedule-full lh-c"><a href="<?= $this->url(array('action' => 'my', 'module' => 'lesson', 'controller' => 'list', 'subject_id' => $this->subject->subid), $urlParam, true);?>" class="l-bgc"><?= _('Все занятия');?></a></div>
</div>