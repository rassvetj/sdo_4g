<style>	
	.lnk {
		font-size: 14px;
		padding-right: 5px;
	}
	.rgsu_form_area {
		font-size: 12px;
	}
</style>
<a class="lnk" href="<?=$this->url(array('module' => 'disabled-people', 'controller' => 'survey',  'action' => 'index'));?>"><?=_('Кабинет ОВЗ')?></a>
<a class="lnk" href="<?=$this->url(array('module' => 'disabled-people', 'controller' => 'request', 'action' => 'index'));?>"><?=_('Обращения')?></a>
<br>
<br>
<a class="lnk" href="<?=$this->url(array('module' => 'disabled-people', 'controller' => 'resume', 'action' => 'edit'));?>"><?=_('Изменить резюме')?></a>
<a class="lnk" href="<?=$this->url(array('module' => 'disabled-people', 'controller' => 'resume', 'action' => 'download'));?>"><?=_('Скачать резюме')?></a>
<br>
<br>
<div class="rgsu_form_area">	
	<table>
		<tr>
			<td><img src="<?=$this->userImg;?>"></td>
			<td>
				<p><?=$this->resume->job_vacancy;?></p>
				<p><?=$this->fio;?></p>
			</td>
		</tr>
	</table>
	
	<table>
		<tr>			
			<td><?=_('Уровень дохода:')?></td>
			<td><?=number_format($this->resume->income_level, 0, ',', ' ');?><?=_(' рублей')?></td>
		</tr>
		<tr>			
			<td><?=_('Контактный телефон:')?></td>
			<td><?=$this->resume->phone;?></td>
		</tr>
		<tr>			
			<td><?=_('E-mail:')?></td>
			<td><?=$this->resume->email;?></td>
		</tr>
		
		<!-- -->
		<tr>			
			<td colspan="2"><?=_('Абилимпикс')?></td>			
		</tr>
		<tr>			
			<td><?=_('Название компетенции:')?></td>
			<td><?=$this->resume->competence;?></td>
		</tr>		
		<tr>			
			<td><?=_('Результат регионального/национального конкурса:')?></td>
			<td><?=$this->resume->result_competition;?></td>
		</tr>
		
		<!-- -->
		<tr>			
			<td colspan="2"><?=_('Образование')?></td>			
		</tr>
		<tr>			
			<td><?=_('Учебное заведение:')?></td>
			<td><?=$this->resume->institution;?></td>
		</tr>
		<tr>			
			<td><?=_('Дата окончания:')?></td>
			<td>
				<?php $dt = DateTime::createFromFormat('Y-m-d', $this->resume->graduation_date); ?>
				<?=($dt)?($dt->format('d.m.Y')):('');?>
			</td>
		</tr>		
		<tr>			
			<td><?=_('Факультет:')?></td>
			<td><?=$this->resume->faculty;?></td>
		</tr>
		<tr>			
			<td><?=_('Специальность:')?></td>
			<td><?=$this->resume->specialty;?></td>
		</tr>
		<tr>			
			<td><?=_('Форма обучения:')?></td>
			<td><?=$this->resume->form_study;?></td>
		</tr>
				
		<!-- -->
		<tr>			
			<td colspan="2"><?=_('Опыт работы')?></td>			
		</tr>
		<tr>			
			<td><?=_('Период работы:')?></td>
			<td>
				<?php $dt_b = DateTime::createFromFormat('Y-m-d', $this->resume->work_period_begin); ?>
				<?php $dt_e = DateTime::createFromFormat('Y-m-d', $this->resume->work_period_end); ?>
				<?=_('с')?> <?=($dt_b)?($dt_b->format('d.m.Y')):('');?> <?=_('по')?> <?=($dt_e)?($dt_e->format('d.m.Y')):('');?>
			</td>
		</tr>
		<tr>			
			<td><?=_('Должность:')?></td>
			<td><?=$this->resume->position;?></td>
		</tr>
		<tr>			
			<td><?=_('Название организации:')?></td>
			<td><?=$this->resume->organization;?></td>
		</tr>
		<tr>			
			<td><?=_('Должностные обязанности и достижения:')?></td>
			<td>
				<p><?=$this->resume->job_function;?></p>
				<strong><?=_('Достижения:')?></strong> <?=$this->resume->achievements;?>
			</td>
		</tr>

		<!-- -->
		<tr>			
			<td colspan="2"><?=_('Личная информация')?></td>			
		</tr>
		<tr>			
			<td><?=_('Город проживания:')?></td>
			<td><?=$this->resume->city;?></td>
		</tr>
		<tr>			
			<td><?=_('Ближайшее метро:')?></td>
			<td><?=$this->resume->metro;?></td>
		</tr>
		<tr>			
			<td><?=_('Дата рождения:')?></td>
			<td>
				<?php $dt = DateTime::createFromFormat('Y-m-d', $this->resume->date_birth); ?>
				<?=($dt)?($dt->format('d.m.Y')):('');?>
			</td>
		</tr>
 

		<!-- -->
		<tr>			
			<td colspan="2"><?=_('Иностранные языки и компьютерные навыки')?></td>			
		</tr>
		<tr>			
			<td><?=_('Английский язык:')?></td>
			<td><?=$this->resume->english;?></td>
		</tr>
		<tr>			
			<td><?=_('Компьютерные навыки и знания:')?></td>
			<td><?=$this->resume->computer_skills;?></td>
		</tr>

		<!-- -->
		<tr>			
			<td colspan="2"><?=_('Дополнительная информация')?></td>			
		</tr>
		<tr>			
			<td><?=_('О себе:')?></td>
			<td><?=$this->resume->about;?></td>
		</tr>
		<tr>			
			<td><?=_('Рекомендации:')?></td>
			<td><?=$this->resume->recommendations;?></td>
		</tr>
		
	</table>

</div>