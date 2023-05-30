<style>
	.published-to-subjects-tbl {
		border-collapse: collapse;
	}
	.published-to-subjects-tbl td {
		padding: 5px;
		border: 1px solid #b2c0c9;		
	}
</style>
<div>
	<div class="accordion-container">
		<div class="accordion-header">
			<a href="#" class="btn-accordion"><?=_('Все публичные файлы будут доступны в следующих сессиях')?></a>
		</div>
		<div class="accordion-data">
			<?php if(empty($this->publishedToSubjects)):?>
				<?=_('Нет')?>
			<?php else:?>
				<table class="published-to-subjects-tbl">
					<tr style="font-weight: bold; text-align: center;">
						<td>Сессия</td>
						<td>Направление</td>
						<td>Специализация</td>
						<td>Учебный план</td>
					</tr>
				<?php foreach($this->publishedToSubjects as $subject):?>
					<tr>
						<td>
							<a  target="_blank"
								href="<?=$this->url(array('module'=>'subject', 'controller'=>'index', 'action'=>'card', 'subject_id'=>$subject['subjectId']))?>">
								<?=$subject['name']?>
							</a>
						</td>
						<td>
							<?=$subject['direction']?>
						</td>
						<td>
							<?=$subject['specialisation']?>
						</td>
						<td>
							<?=$subject['name_plan']?>
						</td>
					<tr>
				<?php endforeach;?>
				</table>
			<?php endif;?>
		</div>
	</div>	
</div>