<style>
	.m-info-area{
		font-size: 13px;
		border: 1px solid #ccc;
		display: inline-block;
		border-radius: 3px;
		width: 99%;
		min-height: 50px;
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
	
	.btn-to-view:visited{
		color: #333333;
	}
	.m-info-area:hover{
		background-color: #e1f0ff;
	}
	.not-data-area{
        font-size: 13px;
        text-align:center;
        font-weight: bold;
    }
</style>

<?php if(empty($this->marksheet)):?>
	<p class="not-data-area"><?=_('Нет данных')?></p>
<?php else:?>

	<?php foreach($this->marksheet as $i):?>
		<a	class = "btn-to-view" 
			href  = "<?=$this->url(array('module' => 'marksheet', 'controller' => 'external', 'action' => 'view', 'marksheet_id' => $i['id']), 'default', true);?>">
		
		<div class="m-info-area">
			<div class="m-info-item">
				<span class="m-info-caption"><?=_('Номер ведомости')?>:</span> <?=$i['number']?>		
			</div>
			<div class="m-info-item">		
				<span class="m-info-caption"><?=_('Дисциплина')?>:</span> <?=$i['discipline']?>
			</div>
			<div class="m-info-item">		
				<span class="m-info-caption"><?=_('Попытка')?>:</span> <?=$i['attempt']?>
			</div>
			<div class="m-info-item">		
				<span class="m-info-caption"><?=_('Дата')?>:</span> <?=date('d.m.Y', strtotime($i['date_issue']))?>
			</div>
			<div class="m-info-item">		
				<span class="m-info-caption"><?=_('Группа')?>:</span> <?=(empty($i['group_name']) ? 'нет' : $i['group_name']);?>
			</div>
			<div class="m-info-item">		
				<span class="m-info-caption"><?=_('Основа')?>:</span> <?=(empty($i['study_base']) ? 'нет' : $i['study_base']);?>
			</div>
			<div class="m-info-item">		
				<span class="m-info-caption"><?=_('Контроль')?>:</span> <?=(empty($i['form_control']) ? 'нет' : $i['form_control']);?>
			</div>
			<div class="m-info-item">		
				<span class="m-info-caption"><?=_('Семестр')?>:</span> <?=$i['semester']?>
			</div>
			<div class="m-info-item">		
				<span class="m-info-caption"><?=_('Форма обучения')?>:</span> <?=(empty($i['form_study']) ? 'нет' : $i['form_study']);?>
			</div>
			<div class="m-info-item">		
				<span class="m-info-caption"><?=_('Преподаватель')?>:</span> <?=(empty($i['tutor']) ? 'нет' : $i['tutor']);?>
			</div>
			<div class="m-info-item">		
				<span class="m-info-caption"><?=_('Декан')?>:</span> <?=(empty($i['dean']) ? 'нет' : $i['dean']);?>
			</div>	
				
			
		</div>
		</a>
		<div style="clear:both;"></div>
	<?php endforeach;?>
<?php endif;?>