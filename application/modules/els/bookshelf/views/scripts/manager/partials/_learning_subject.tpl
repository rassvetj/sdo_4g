<div>
	<div class="accordion-container">
		<div class="accordion-header">
			<a href="#" class="btn-accordion"><?=_('Учебный предмет')?></a>
		</div>
		<div class="accordion-data">			
				<?php if(!$this->learningSubject):?>
					<?=_('Нет')?>
				<?php else:?>
					<table>
						<tr>
							<td><?=_('Название')?></td>
							<td><?=$this->learningSubject->name?></td>
						</tr>
						<tr>
							<td><?=_('Направление подготовки')?></td>
							<td><?=$this->learningSubject->direction?></td>
						</tr>
						<tr>
							<td><?=_('Специализация')?></td>
							<td><?=$this->learningSubject->specialisation?></td>
						</tr>
						<?/*
						<tr>
							<td><?=_('Учебный план')?></td>
							<td><?=$this->learningSubject->name_plan?></td>
						</tr>
						*/?>
					</table>
				<?php endif;?>
		</div>
	</div>
</div>
