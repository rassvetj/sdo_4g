<?php 
				$request = Zend_Controller_Front::getInstance()->getRequest();
		$lng = $request->getCookie(HM_User_UserService::COOKIE_NAME_LANG);
?>
<div class="all_ques_title"><?php echo _("Варианты задания");?> «<?php if($lng == 'eng' && $this->task->title_translation != '')  echo $this->task->title_translation; else echo $this->task->title;?>»</div>
<?php
$this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/test.css'));
foreach($this->questions as $question):
?>
    <div class="test_name_wrap">
        <div class="test_name"></div>
        <div class="test_deadline"></div>
        <hr class="test_info"/>
        <div class="test_usr_name"><?php if(count ($this->task->author)){ echo $this->task->author[0]->getName();}?></div>
    </div>
    <div class="test_name_wrap2">
        <div class="test_descr formatted-text">
		<p><?php
					if($lng == 'eng' && $question->qdata_translation != '') 
						echo $question->qdata_translation;
					else
						echo $question->qdata;
					
					
					?></p>
          <!--<p><?php echo $question->qdata;?></p>-->
        </div>
        <ul class="test_attach">
        <?php foreach($this->questionFiles[$question->kod] as $file):?>
            <li class="test_mime_0"><a href="<?php echo $this->url(array('action' => 'file', 'controller' => 'get', 'module' => 'file', 'file_id' => $file->file_id));?>"><?php echo $file->name; ?></a></li>
        <?php endforeach;?>
        </ul>
    </div>
<?php endforeach;?>