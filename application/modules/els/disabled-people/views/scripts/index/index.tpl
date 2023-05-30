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

<a class="lnk" href="<?=$this->url(array('module' => 'disabled-people', 'controller' => 'resume',  'action' => 'index'));?>"><?=_('Резюме')?></a>
<a class="lnk" href="<?=$this->url(array('module' => 'disabled-people', 'controller' => 'request', 'action' => 'index'));?>"><?=_('Обращения')?</a>
<a class="lnk" href="<?=$this->url(array('module' => 'disabled-people', 'controller' => 'survey',  'action' => 'index'));?>"><?=_('Анкетирование')?</a>
<br>
<br>
<?php if($this->isEmpty) : ?>
	<p>Отсутствую данные.</p>
<?php else : ?>	
		<div class="rgsu_form_area">
			<?php if($this->selected_way): ?>
				<p>Вы выбрали вариант освоения программы: <strong><?=$this->ways[$this->selected_way];?></strong></p>
				<p>Выберанные специальные средствва и программы: <strong><?=(implode(', ', $this->special_funds));?></strong></p>
				<p>Статус: <?=$this->status;?></p>				
				<hr>								
			<?php endif; ?>
			<br>
			<? if($this->isDisableForm) : ?>
				Ваша заявка находится на согласовании.
			<? else : ?>
				<p><?=($this->selected_way)?('Изменить программу:'):('Выберите программу освоения материала и дополнительные средства'); ?></p>
				<form method="POST" action="<?= $this->baseUrl($this->url(array('module' => 'disabled-people', 'controller' => 'index', 'action' => 'save-way'))) ?>">			
					<?php
					foreach($this->ways as $code => $way) {					
						$checked = ($code == $this->selected_way)?('checked'):('');
						echo '<input type="radio" id="selected_way_'.$code.'" name="selected_way" value="'.$code.'" '.$checked.'><label for="selected_way_'.$code.'">
									<strong>'.$way.'</strong>
									<br>
									<p style="padding-left: 16px;">'.$this->description_ways[$code].'</p>
									</label>';										
						echo '<br />';				
					}
					echo 'Выберите специальные средствва и программы:<br />';
					foreach($this->specialFunds as $i) {
						echo '<input type="checkbox" name="fund[]" id="fund_'.$i['code'].'" value="'.$i['code'].'" /><label for="fund_'.$i['code'].'">'.$i['name'].'</label><br>';
					}					
					?>
					Свой вариант: <input type="text" name="special_fund_manual">
					<br />
					<input type="submit" value="Сохранить">
				</form>
			<? endif; ?>
		</div>
	
<?php endif; ?>
