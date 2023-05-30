<style>
	.rgsu_form_area {
		font-size: 12px;
	}

	.rgsu_form_area .element {
		margin-bottom: 12px;
	}

	.rgsu_form_area .element input {
		width: 360px;
		height: 20px;
		padding: 2px;
	}

	.rgsu_form_area .element select {
		width: 368px;
		height: 28px;	
	}
	
	.lnk {
		font-size: 14px;
		padding-right: 5px;
	}
</style>
<a class="lnk" href="<?=$this->url(array('module' => 'disabled-people', 'controller' => 'survey',  'action' => 'index'));?>"><?=_('Кабинет ОВЗ')?></a>
<a class="lnk" href="<?=$this->url(array('module' => 'disabled-people', 'controller' => 'request', 'action' => 'index'));?>"><?=_('Обращения')?></a>
<br>
<br>
<a class="lnk" href="<?=$this->url(array('module' => 'disabled-people', 'controller' => 'resume', 'action' => 'index'));?>"><?=_('Отмена')?></a>
<br>
<br>
<div class="rgsu_form_area">	
	<?=$this->form;?>
</div>