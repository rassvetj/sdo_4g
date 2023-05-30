<div style="font-size: medium;">
	Уважаемый студент!
	<br>
	Вам доступны следующие анкеты:		
	<br>
	<br>
	<?php if(!empty($this->allowTypes)): ?>
		<?php foreach($this->allowTypes as $type_id => $name): ?>
		<a href="<?=$this->baseUrl($this->url(array('module' => 'survey', 'controller' => 'single', 'action' => 'index', 'type' => $type_id)));?>"><?=$name?></a>		
		<br>
		<?php endforeach; ?>
	<?php endif; ?>		
</div>