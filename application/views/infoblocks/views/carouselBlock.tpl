<?php if (!$this->error): ?>
<?php $idPrefix = $this->id('carousel'); ?>
<div id="<?= $idPrefix; ?>-message" class="error-box"></div>
<div id="<?= $idPrefix; ?>-form" class="carousel-container">
    <ul id="carousel">
    <?php foreach ($this->subjectmates as $subjectmate):?>
        <li>
            <img src="<?php echo $subjectmate['user']->getPhoto() . '#' . $subjectmate['user']->MID;?>">
            <div class="tooltip">
                <p class="fio" id="carousel-item-name-<?php echo $subjectmate['user']->MID;?>"><?php echo $subjectmate['user']->getName()?></p>
                <p><?php echo _('Общие курсы');?>:</p>
                <ul>
                    <?php foreach($subjectmate['subjects'] as $subject):?>
                        <li><?php echo $subject;?></li>
                    <?php endforeach;?>
                </ul>
            </div>
        </li>
    <?php endforeach;?>
    </ul>
    <form method="POST" action="<?php echo $this->serverUrl($this->url(array('module' => 'infoblock', 'controller' => 'carousel', 'action' => 'index')));?>">
        <h3><?php echo _('Пригласить в чат:');?></h3>
        <ul class="carousel-list">
            <li class="empty"><?php echo _('никто не выбран');?></li>
        </ul>  
        <input type="submit" value="<?php echo _('Послать уведомление и перейти в чат');?>">  
        <input type="button" value="<?php echo _('Очистить');?>">  
    </form>
</div>
<?php $this->inlineScript()->captureStart(); ?>
jQuery(document).ready(function($){
	<?php if(!empty($this->subjectmates)):?>
	$('#carousel').carousel({
	    width: 550, 
        height: 400, 
        itemWidth:114, 
        horizontalRadius:200, 
        verticalRadius:75, 
        resize:false, 
        mouseScroll:false, 
        mouseDrag:true, 
        scaleRatio:0.4, 
        scrollbar:false, 
        tooltip:true, 
        mouseWheel:true, 
        mouseWheelReverse:true
    });
	<?php endif;?>						 
	 $('.carousel-container input[type="button"]').click(function(){
	     $('.carousel-list .empty').show();
	     $('.carousel-list-user').remove();
	 });
	 
	 $('.carousel-item').click(function(){
	     i = 0;
	     arr = $(this).attr('src').split('#');
	     userId = arr[1];
	     userIds = new Array();
	     userFio =  $('#carousel-item-name-' + userId).html();
	     
	     newUser = true;
	     $('.carousel-list .empty').hide();
	     $('.carousel-list li span').each(function(){
	         if ($(this).html() == userFio) newUser = false;
	         if ($(this).parent().attr('id')) userIds[i++] = $(this).parent().attr('id').replace('carousel-list-user-', '');
	     });
	     
	     if (newUser) {
            $('.carousel-list').append('<li id="carousel-list-user-' + userId + '" class="carousel-list-user offline"><input id="carousel-list-user-' + userId + '" type="checkbox" name="users[]" value="' + userId + '"><span>' + userFio + '</span></li>');
            userIds[i++] = userId;
            
            // check if online
            $.ajax({
            	type: "POST",
            	url: "<?php echo $this->serverUrl($this->url(array('module' => 'infoblock', 'controller' => 'carousel', 'action' => 'check-online')))?>",
            	dataType: 'json',
            	data: {
            	    users: userIds
        	    },
            	success: function(userIdsOnline){
            	    if ($(userIdsOnline).length) {
                	     $('.carousel-list li').each(function(){
                	         if ($(this).attr('id')) userId = $(this).attr('id').replace('carousel-list-user-', '');
                	         if (userIdsOnline[userId]) {
                	             $(this).removeClass('offline');
                	             $(this).addClass('online');
                	             $(this).attr('title', '<?php echo _('Пользователь online');?>');
                	             $(this).find('input[type="checkbox"]').attr('checked', true);
                	         }
                	     });            	        
            	    }
            	}
            });						
	     }
	     return true;
	 });
	 
    var formId = <?= Zend_Json::encode("{$idPrefix}-form"); ?>;	 
    var $message = jQuery("<div><?= _('Для выбора пользователя кликните на его фото'); ?></div>").appendTo('#' + formId);
    jQuery.ui.errorbox.clear($message);
    $message.errorbox({level: 'notice'});
	 
});
<?php $this->inlineScript()->captureEnd(); ?>
<?php else: ?>
<ul class="carousel-list">
    <li class="empty"><?php echo $this->error;?></li>
</ul>  

<?php endif;?>