<?php echo $this->headSwitcher(array('module' => 'lesson', 'controller' => 'result', 'action' => 'listlecture', 'switcher' => 'listlecture'), 'result', $this->disabledMods);?>
<?php $this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/tracklog.css')); ?>

<?php // выглядит плохо, работает неверно, не факт что это вообще здесь нужно?>
<?php if (0 && !Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_STUDENT)):?>

<?php echo $this->formSelect('user_id', $this->userId, array('data-url' => $this->url(array('user_id' => ''))), $this->students); ?>



<?php $this->inlineScript()->captureStart(); ?>
$(document).delegate('#user_id', 'change', function (event) {
    document.location.href = $(event.target).data('url') + event.target.value;
});
<?php $this->inlineScript()->captureEnd(); ?>


<?php endif;?>

<?php if(count($this->items) > 0):?>
<div class="block_course_result">
    <?php if($this->fullProgress != 'no'):?>
    <div class="bcr_title">
        <div class="bcr_title_txt"><?php echo _('Общий результат за модуль');?></div>
        <div class="bcr_title_success bts_<?php echo $this->fullProgress;?>"></div>
    </div>
    <?php endif;?>
<?php
$currLevel = 0;
    foreach($this->items as $item):?>
    	<?php
    	    if($currLevel < $item->level){
    	        $currLevel = $item->level;
    	?>
    		<?php // Вот тут временное число для отступа слева. Надо Сделать верстку покомпактнее и выровнять градусники по правому краю.?>
    		<!--<div style="margin-left: 20px;">-->
    	<?php
    	    }
    	?>
    	<?php if($currLevel > $item->level):
    	    for($i = ($currLevel - $item->level); $i > 0; $i--):
    	?>
    	<!--	</div>-->
		<?php
		    endfor;
		    $currLevel = $item->level;
		endif;?>
		<?php echo $this->modulePreview($item, $this->items);?>
<?php endforeach;?>
<?php for($i = $currLevel; $i >= 0; $i--): ?>
<?php endfor; ?>
</div>
<?php endif;?>
<?php echo $this->footnote();?>