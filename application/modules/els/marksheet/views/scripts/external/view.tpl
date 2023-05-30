<style>
	.not-data-area{
        font-size: 13px;
        text-align:center;
        font-weight: bold;
    }
</style>
<a style="font-size:13px;" href="<?=$this->baseUrl($this->url(array('module' => 'marksheet', 'controller' => 'external', 'action' => 'list'), 'default', true))?>">Назад</a>
<br />
<br />
<?=$this->render('/external/partials/info.tpl');?>
<br />
<?php if(empty($this->students)):?>
	<p class="not-data-area"><?=_('В ведомости нет студентов')?></p>
	
<?php else: ?>
	<?=$this->render('/external/partials/confirm.tpl');?>
	<?=$this->render('/external/partials/table.tpl');?>
	<?=$this->render('/external/partials/files.tpl');?>
	<?=$this->render('/external/partials/description.tpl');?>
<?php endif;?>