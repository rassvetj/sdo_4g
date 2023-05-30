<div>
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
	<div class="usersSystemCounter_datePickers"><? echo _('За период c');?> <input type="text" id="from" name="from" value="<?php echo $from;?>"> <? echo _('по');?> <input type="text" id="to" name="to" value="<?php echo $to;?>"></div>
	<div class="usersSystemCounter_stats">
		<div class="usersSystemCounter_statsInner"><span id="usersSystemCounter_guests"><?php echo $this->stats['guests']?></span></div>
		<div><? echo _('Гостей:');?></div>
	</div>
	<div class="usersSystemCounter_stats">
		<div  class="usersSystemCounter_statsInner"><span id="usersSystemCounter_users"><?php echo $this->stats['users'];?></span></div>
		<div><? echo _('Пользователей:');?></div>
	</div>
</div>

<?php
$this->inlineScript()->captureStart();
?>
$(document).ready(function() {
	$( '#usersSystemCounterBlock .ui-portlet-content #to' ).datepicker( "option", 'minDate', '<?php echo $from;?>' );
	$( '#usersSystemCounterBlock .ui-portlet-content #from' ).datepicker( "option", 'maxDate', '<?php echo $to;?>' );
	
	$( '#usersSystemCounterBlock .ui-portlet-content #to' ).datepicker( "setDate" , '<?php echo $to;?>' );
	$( '#usersSystemCounterBlock .ui-portlet-content #from' ).datepicker( "setDate" , '<?php echo $from;?>');
});
var usersSystemCounterUrl = '<?php echo $this->url(array('module' => 'infoblock', 'controller' => 'user-counter', 'action' => 'get-stats'));?>';
<?php
$this->inlineScript()->captureEnd();
?>

<?php 
$this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/userssystemcounter/style.css');
$this->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/userssystemcounter/script.js');
?>