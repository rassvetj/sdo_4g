
<div style="font-size: 17px; text-align: center;" >
	<div class="services-list" >
		<ul>
			
			<?php if(!empty($this->isStudent)):?>
				<li><a href="<?=$this->url(array('module' => 'student-id', 			'controller' => 'index', 	'action' => 'index'), 'default', true);?>" target="_blank">Студенческий билет</a></li>
				<li><a href="<?=$this->url(array('module' => 'student-recordbook', 	'controller' => 'export', 	'action' => 'pdf'), 'default', true);?>" target="_blank">Зачетная книжка</a></li>
			
				
				<li><a href="<?=$this->url(array('module' => 'certificates', 		'controller' => 'index',	'action' => 'confirming-student'), 'default', true);?>" >Справка, подтверждающая статус студента</a></li>
				
				
				<li><a href="<?=$this->url(array('module' => 'my-payments', 'controller' => 'index', 'action' => 'index'), 'default', true);?>" >Мои оплаты</a></li>
				
				
				<?php if($this->is_can_internships):?>
					<li><a href="<?=$this->url(array('module' => 'internships', 'controller' => 'index', 'action' => 'index'), 'default', true);?>" >Стажировка</a></li>
				<?php endif;?>
				
			<?php endif;?>
			
			<li>&nbsp;</li>
			<li><a target="_blank" href="https://docs.google.com/forms/d/e/1FAIpQLSfNJtghycxTXpuTU99s-Kw1F3epPcOGXF6xUSQbEBLRTogKlA/viewform" ><?=_('Оценить услуги РГСУ')?></a></li>
		</ul>
	</div>	
	<br />
	<br />
	
</div>
<br />








