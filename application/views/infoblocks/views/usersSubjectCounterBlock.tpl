<div class="usersUsersCounter">
<?php 
$from = $this->from;
$to = $this->to;

$translate = new Zend_Translate(
    array(
        'adapter' => 'array',
        'content' => array(
                        'temp' => array(
                            'temp',
                            'temp'
                        ),
                        'temp' => ''
                    ),
        'locale'  => 'ru'
    )
);
$translate->getAdapter();
?>
	<div class="usersUsersCounter_datePickers">
		<div class="usersUsersCounter_datePickers_center"><div>За период</div><div class="usersUsersCounter_datePickers_right">с &nbsp;&nbsp;<input type="text" id="from" name="from" value="<?php echo $from;?>"></div></div>
		<div class="usersUsersCounter_datePickers_center" style="margin-top: 3px;"><div class="usersUsersCounter_datePickers_right">по <input type="text" id="to" name="to" value="<?php echo $to;?>"></div></div>
	</div>
	<div><hr/></div>
	<div class="usersUsersCounter_stats">
		За выбранный интервал времени учебный курс <span id="usersUsersCounter_count"><?php echo $translate->translate(array('посетил', 'посетило', 'посетили', $this->counter['count'], 'ru_RU')); echo " "; echo $this->counter['count']; echo " "; echo $translate->translate(array('человек', 'человека', 'человек', $this->counter['count'], 'ru_RU'));?></span>, 
		суммарное время изучения материала <span id="usersUsersCounter_time"><?php echo round($this->counter['time']/3600); echo " "; echo $translate->translate(array('час', 'часа', 'часов', round($this->counter['time']/3600), 'ru_RU')); ?></span>.
	</div>
</div>

<?php
$this->inlineScript()->captureStart();
?>
$(document).ready(function() {
	$( '.usersUsersCounter #to' ).datepicker( "option", 'minDate', '<?php echo $from;?>' );
	$( '.usersUsersCounter #from' ).datepicker( "option", 'maxDate', '<?php echo $to;?>' );
	
	$( '.usersUsersCounter #to' ).datepicker( "setDate" , '<?php echo $to;?>' );
	$( '.usersUsersCounter #from' ).datepicker( "setDate" , '<?php echo $from;?>');
});
var usersUsersCounterUrl = '<?php echo $this->url(array('module' => 'infoblock', 'controller' => 'user-counter', 'action' => 'get-subject-stats', 'subject_id' => $this->subjectId));?>';
<?php
$this->inlineScript()->captureEnd();
?>

<?php 
$this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/usersuserscounter/style.css');
$this->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/usersuserscounter/script.js');
?>