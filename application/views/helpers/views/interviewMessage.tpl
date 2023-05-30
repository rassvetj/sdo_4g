<?php
	require_once APPLICATION_PATH .  '/views/helpers/Score.php';
	
	$request = Zend_Controller_Front::getInstance()->getRequest();
	$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);	
	
?>
    <?php if($this->message instanceof HM_Interview_Task_TaskModel):?>
    <div class="test_name_wrap <?php echo $this->message->getStyleClass();?>">
        <div class="test_name">
			<?php
				if($lng == 'eng' && $this->lesson->title_translation != '') 
					echo $this->lesson->title_translation; 	
				else
					echo $this->lesson->title; 
			?></div>
        <?php if ($this->lesson && $this->lesson->timetype != HM_Lesson_LessonModel::TIMETYPE_FREE):?>
            <div class="test_deadline">
                <? if($this->lesson->recommend == 1):?>
                <? echo _('Желательно выполнить до ') . $this->lesson->getEndDatetime($this->message->date); ?>
                <? else: ?>
                <? echo _('Выполнить до ') . $this->lesson->getEndDatetime($this->message->date); ?>
                <? endif; ?>
            </div>
        <?php endif;?>
        <hr class="test_info"/>
        <div class="test_usr_name">
              <?php if($this->teacher instanceof HM_User_UserModel):?>
              <?php echo $this->teacher->getName();?>,
              <?php endif;?>
              <?php echo _('дата редактирования варианта задания'). ': ' . $this->date; ?>
          </div>
    </div>
    <div class="test_name_wrap2">
            <div class="test_descr formatted-text">
                <p><?php   
					if($lng == 'eng' && $this->message->message_translation != '') 
						echo $this->message->message_translation;
					else
						echo $this->message->message;			
					
					?></p>
            </div>

        <?php

        if ($this->message->question_id) {
            $taskFiles = Zend_Registry::get('serviceContainer')->getService('QuestionFiles')->fetchAll(array('kod = ?' => $this->message->question_id));
            if(count($taskFiles) > 0){
                $filesArr = array_keys($taskFiles->getList('file_id', 'kod'));
                $files = Zend_Registry::get('serviceContainer')->getService('Files')->fetchAll(array('file_id IN (?)' => $filesArr));
            }
        }

        if(!empty($files)){?>
        <ul class="test_attach">
        <?php
                //foreach($this->message->file as $file){
                foreach($files as $file){
            ?>
                <li class="test_mime_0"><a href="<?php echo $this->url(array('action' => 'file', 'controller' => 'get', 'module' => 'file', 'file_id' => $file->file_id, 'download' => 1));?>"><?php echo $file->name; ?></a></li>
            <?php }?>
        </ul>
        <?php }?>
        </div>
    <?php else:?>
<?php /*
.test_unlight - темный фон,
без .test_unlight - светлый фон
*/
$class = (in_array($this->message->type, array_keys(HM_Interview_InterviewModel::getStudentTypes()))) ? 'test_unlight' : '';
?>
		<div class="test_item_wrap <?php echo $class;?>" style="overflow: hidden;">
            <?php $temp = 1;if(!empty($this->mark) and $this->message->type==5){
                echo "<div style='position:relative; float: right;'>".
                    $this->score(array(
                        'score' => $this->mark,
                        'lesson_id' => 'total',
                        'scale_id' => 1,
                        // 'mode' => HM_View_Helper_Score::MODE_MARKSHEET,
                        'mode' => HM_View_Helper_Score::MODE_DEFAULT,
                    ))
                    ."</div>";
            }?>
<?php /*
.test_item_checked - зеленая галочка
.test_item_quest - знак вопроса
*/?>
			<div class="test_item_name test_item_<?php echo $this->message->type?>"><?php echo ($this->message->author) ? $this->message->author->getName() : _('Слушатель');?>, <?php echo $this->message->getDate();?></div>
			<div class="test_item_desc formatted-text">
                <p><?php  
					if($lng == 'eng' && $this->message->message_translation != '') 
						echo $this->message->message_translation;
					else
						echo $this->message->message;				
				?></p>
            </div>
            <?php
            if(!empty($this->message->file)){?>
            <ul class="test_attach">
            <?php
                foreach($this->message->file as $file){
                    ?>
                    <li class="test_mime_0"><a href="<?php echo $this->url(array('action' => 'file', 'controller' => 'get', 'module' => 'file', 'file_id' => $file->file_id, 'download' => 1));?>"><?php echo $file->name; ?></a></li>
                <?php }?>
            </ul>
            <?php }?>
        </div>
    <?php endif;?>
