<div class="seach_block">
	<div class="seach_title"><div class="s_count"><?php echo $this->count;?>.</div> <span class="s_title"><a href="<?php echo $this->url(array('module' => 'course', 'action' => 'index', 'controller' => 'index', 'course_id' => $this->courseModel->CID, 'page' => null, 'query' => null));?>"><?php echo $this->courseModel->Title;?></a></span></div>
	<div class="course_discr"><?php echo $this->courseModel->Description;?></div>
</div>