<div style="font-size: 14px;">
	Выдача справок, подтверждающих статус студента, в электронном виде, осуществляется в тестовом режиме. Просим отнестись с пониманием. 
	<br />
	В случае обнаружения ошибок, обращайтесь, пожалуйста,в деканат <a href="mailto:dekanat@rgsu.net" target="_blank">dekanat@rgsu.net</a>
	<br />
	<br />
</div>

<div style="font-size: 17px; text-align: center;" >	

	<div class="services-list" >
		<ul>
			<?php if($this->isCanCreateNewItem):?>
				<li>
					<a target="_blank"  href="<?=$this->url(array('module' => 'certificates', 		'controller' => 'export',	'action' => 'confirming-student-pdf'), 'default', true);?>" >
						Сформировать новую
					</a>
					<br />					
					<br />					
				</li>
			<?php endif;?>
			
			<?php if(!empty($this->items)):?>
				<?php foreach($this->items as $i):?>
					<li>
						<a target="_blank" href="<?=$this->url(array('module' => 'certificates', 		'controller' => 'export',	'action' => 'confirming-student-pdf', 'item' => $i->certificate_id), 'default', true);?>" >
							Скачать №<?=$i->number?> от <?=date('d.m.Y', strtotime($i->date_created))?>
						</a>
					</li>
				<?php endforeach;?>
			<?php endif;?>
		</ul>
	</div>
</div>

<br />
<br />
<br />
<div class="accordion-container">
	<div class="accordion-header">
		<a href="#" class="btn-accordion">Сообщить об ошибке в деканат</a>
	</div>
	<div class="accordion-data">
		<div class="form-area-default">
			<?=$this->form?>
		</div>
	</div>
</div>