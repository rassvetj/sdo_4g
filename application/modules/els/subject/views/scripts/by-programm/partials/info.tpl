<div class="accordion-container open">
	<div class="accordion-header">
		<a href="#" class="btn-accordion">Информация</a>
	</div>
	<div class="accordion-data">	
		<div class="byp-info">
			<table>
				<tr>
					<td><?=_('Студент')?>:</td>
					<td><?=$this->student->getName()?></td>
				</tr>
				<tr>
					<td><?=_('Студент. Код')?>:</td>
					<td><?=$this->student->getCode()?></td>
				</tr>
				<tr>
					<td><?=_('Дата начала обучения')?>:</td>
					<td><?=$this->student->getBeginLearning()?></td>
				</tr>
				<tr>
					<td><?=_('Закрепления зыков')?>:</td>
					<td>
						<?php if(empty($this->student->languages)): ?>
							<?=_('Нет')?>
						<?php else: ?>
							<?php foreach($this->student->languages as $language):?>
								<p><?=$language->name?> [сем. <?=$language->semester?>]</p>
							<?php endforeach;?>
						<?php endif;?>
					</td>
				</tr>
				<tr>
					<td><?=_('Группа')?>:</td>
					<td>
						<?=($this->student->group ? $this->student->group->name . ' [код ' . $this->student->group->id_external . ']' : _('Нет') )?>
					</td>					
				</tr>				
				<tr>
					<td><?=_('Программа')?>:</td>
					<td>
						<?=($this->programm ? $this->programm->name . ' [код ' . $this->programm->id_external . ']' : _('Нет') )?>
					</td>
				</tr>				
			</table>
		</div>
	</div>	
</div>	
