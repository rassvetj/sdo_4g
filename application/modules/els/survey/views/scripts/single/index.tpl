<br>
<div class="sr-caption">
	<b><?=$this->type_name?></b>			
</div>

<div class="survey-result">
	<?=$this->rules?>

	<p><a href="<?=$this->url(array('module' => 'survey', 'controller' => 'single', 'action' => 'index', 'start' => 1));?>">Начать</a></p>
</div>