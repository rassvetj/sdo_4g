<?php $this->headLink()->appendStylesheet( $this->serverUrl('/css/infoblocks/materials-recent/materials-recent.css') )
                       ->appendStylesheet( $this->serverUrl('/css/content-modules/material-icons.css') ); ?>
<div class="materials-recent">
    <?php if(count($this->materials) > 0): ?>
    <!--h4><?= _("Последние открытые"); ?>:</h4-->
    <ul>
		<?php 			
			$routerName = Zend_Controller_Front::getInstance()->getRouter()->getCurrentRouteName(); //--fix for forum
			$subject_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('subject_id');						
		?>
        <?php foreach($this->materials as $lesson):?>
            <?php 				
				if($routerName == 'forum_subject'){					
					$params = $lesson->getFreeModeUrlParam();
					if($subject_id && !isset($params['subject_id'])){
						$params['subject_id'] = $subject_id;
					}
					$href = $this->url($params, 'default', false);
				} else {					
					$href = $this->url($lesson->getFreeModeUrlParam());
				}
			?>
			<?php $lessonAttribs = array(
                    'href' => $href);
                    if($lesson->getType()==HM_Event_EventModel::TYPE_COURSE) $lessonAttribs['target']=$lesson->isNewWindow();
            ?>
        <li class="material"><a <?php echo $this->HtmlAttribs($lessonAttribs)?> class="material-icon-small <?= $lesson->material->getIconClass();?>"></a><a  <?php echo $this->HtmlAttribs($lessonAttribs)?>><?= $lesson->title;?></a></li>
        <?php endforeach;?>
    </ul>
    <div class="materials-all"><a href="<?= $this->url(array('action' => 'index', 'module' => 'subject', 'controller' => 'materials', 'subject_id' => $this->subject->subid), 'default', true);?>" class="l-bgc"><?= _('Все материалы');?></a></div>
    <?php else:?>
    <p><?= _('В данном курсе нет материалов, открытых для свободного доступа.'); ?></p>
    <?php endif;?>
</div>
