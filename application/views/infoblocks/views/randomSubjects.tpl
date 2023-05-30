<div id="random-subjects-info" class="random-subjects-info">
	<h1 id="random-subjects-title" class="random-subjects-title"><?php echo $this->subject->name?></h1>
	<div id="random-subjects-description" class="random-subjects-description"><?php echo $this->subject->description?></div>
	<div id="random-subjects-url" class="random-subjects-url"><a href="<?php echo $this->url(array(
											'module' => 'user', 
											'controller' => 'reg', 
											'action' => 'subject', 
											'subid' => $this->subject->subid
											))?>">
	<?php echo _('подать заявку')?></a>	
	</div>
</div>
<div class="random-subjects-form">
	<form id="subject-next-form">
		<input type="hidden" id="subject-next-url" name="subject-next-url" value="<? echo $this->url(array('module' => 'infoblock', 'controller' => 'random-subjects', 'action' => 'next'))?>">
		<input type="hidden" name="format" value="json">
		<input type="hidden" name="classifier_type" value="<?php echo $this->classifier_type?>">
		<input id="subject-next-submit" type="image" src="/images/infoblocks/randomSubjects/blue_refresh.png">
		<div style="clear: both"></div>
	</form>
</div>