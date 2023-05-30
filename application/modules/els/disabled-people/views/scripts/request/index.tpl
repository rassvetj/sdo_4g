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
	
	.rgsu_form_area .element input[type="radio"], .rgsu_form_area .element input[type="checkbox"] {
		width: 20px;
		height: 17px;
	}

	.rgsu_form_area dt label {
		font-weight: bold;
	}
	
	.lnk {
		font-size: 14px;
		padding-right: 5px;
	}
</style>
<a class="lnk" href="<?=$this->url(array('module' => 'disabled-people', 'controller' => 'survey',  'action' => 'index'));?>"><?=_('Кабинет ОВЗ')?></a>
<a class="lnk" href="<?=$this->url(array('module' => 'disabled-people', 'controller' => 'resume',  'action' => 'index'));?>"><?=_('Резюме')?></a>
<br>
<br>
<div class="rgsu_form_area">	
	<?=$this->form;?>
</div>
<br>
<div><?=$this->grid?></div>
<br>
<?php $this->inlineScript()->captureStart()?>
	changeForm(0);
	function changeForm(id){
		if(id == '<?=HM_DisabledPeople_DisabledPeopleModel::TYPE_REQUEST_PROVIDE?>'){
			$('.dp_checkbox').closest("dd").show();
			$('.dp_checkbox').closest("dd").prev().show();
			
		} else {
			$('.dp_checkbox').closest("dd").hide();
			$('.dp_checkbox').closest("dd").prev().hide();
		}
		
	}

<?php $this->inlineScript()->captureEnd()?>