<style>
	.m-info-area{
		font-size: 13px;
		border: 1px solid #ccc;
		display: inline-block;
		border-radius: 3px;
		width: 99%;
	}
	.m-info-item{
		float: left;
		padding: 5px;
		font-weight: bold;
	}
	
	.m-info-item .m-info-caption{
		color: gray;
		font-weight: normal;
	}
</style>
<div class="m-info-area">
	<div class="m-info-item">
		<span class="m-info-caption"><?=_('Номер ведомости')?>:</span> <?=$this->marksheet->external_id?>		
	</div>
	<div class="m-info-item">
		<span class="m-info-caption"><?=_('Дисциплина')?>:</span> <?=$this->marksheet->discipline?>		
	</div>
	<div class="m-info-item">		
		<span class="m-info-caption"><?=_('Попытка')?>:</span> <?=$this->marksheet->attempt?>
	</div>
	<div class="m-info-item">		
		<span class="m-info-caption"><?=_('Дата')?>:</span> <?=date('d.m.Y', strtotime($this->marksheet->date_issue))?>
	</div>
	<div class="m-info-item">		
		<span class="m-info-caption"><?=_('Группа')?>:</span> <?=empty($this->marksheet->group_name) ? 'нет' : $this->marksheet->group_name;?>
	</div>
	<div class="m-info-item">		
		<span class="m-info-caption"><?=_('Факультет')?>:</span> <?=empty($this->marksheet->faculty) ? 'нет' : $this->marksheet->faculty;?>
	</div>
	<div class="m-info-item">		
		<span class="m-info-caption"><?=_('Основа')?>:</span> <?=empty($this->marksheet->study_base) ? 'нет' : $this->marksheet->study_base;?>
	</div>
	<div class="m-info-item">		
		<span class="m-info-caption"><?=_('Год')?>:</span> <?=empty($this->marksheet->year) ? 'нет' : $this->marksheet->year;?>
	</div>	
	<div class="m-info-item">		
		<span class="m-info-caption"><?=_('Контроль')?>:</span> <?=empty($this->marksheet->form_control) ? 'нет' : $this->marksheet->form_control;?>
	</div>
	<div class="m-info-item">		
		<span class="m-info-caption"><?=_('Семестр')?>:</span> <?=empty($this->marksheet->semester) ? 'нет' : $this->marksheet->semester;?>
	</div>
	<div class="m-info-item">		
		<span class="m-info-caption"><?=_('Форма обучения')?>:</span> <?=empty($this->marksheet->form_study) ? 'нет' : $this->marksheet->form_study;?>
	</div>
	<div class="m-info-item">		
		<span class="m-info-caption"><?=_('Преподаватель')?>:</span> <?=empty($this->marksheet->tutor) ? 'нет' : $this->marksheet->tutor;?>
	</div>
	<div class="m-info-item">		
		<span class="m-info-caption"><?=_('Декан')?>:</span> <?=empty($this->marksheet->dean) ? 'нет' : $this->marksheet->dean;?>
	</div>
	<div class="m-info-item">		
		<span class="m-info-caption"><?=_('Председатель комиссии')?>:</span> <?=($this->marksheet->commission_chairman) ? $this->marksheet->commission_chairman->getName() : 'нет'?>
	</div>
	<div class="m-info-item">		
		<span class="m-info-caption"><?=_('Члены комиссии')?>:</span>
		<?php 
		if(!empty($this->marksheet->commission_members)){
			$fio_str = '';
			foreach($this->marksheet->commission_members as $member){
				$fio_str .= ' '.$member->getName().',';
			}
			echo trim($fio_str, ',');
		} else {
			echo 'нет';
		}
		?>
	</div>
</div>
<div style="clear:both;"></div>