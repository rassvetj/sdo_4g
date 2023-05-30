<div class="Progress">
<div id="progressTablePlace"></div>
<div style="with: 100%; text-align: right; margin: 5px 2px 0px 0px;"><a href="<?php echo $this->url(array('module' => 'infoblock', 'controller' => 'progress', 'action' => 'get-cvs')); ?>" title="<? echo _('Экспортировать данные в .csv')?>" target="_blank" class="ui-button export-button" id="<?php echo $id; ?>"><span class="button-icon"></span></a></div>
</div>

<?php 
$this->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/progress/jquery.dataTables.js');
$this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/progress/style.css');
$this->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/progress/script.js');

$this->inlineScript()->captureStart();
?>

$(document).ready(function() {

	var arrayData = JSON.parse('<?php echo addslashes($this->array);?>');


	$('#progressTablePlace').html( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="progressTable"></table>' );
	$('#progressTable').dataTable( {
		"bPaginate": false,
		"bLengthChange": false,
		"bFilter": false,
		"bSort": true,
		"bInfo": false,
		"bAutoWidth": true,
		"sScrollY": "<?php if($this->rowCount >= 5) echo 200; else echo 100;?>px",
		"aaData": arrayData,
		"aoColumns": [
			{ "sTitle": '<a href="javascript: return false;"><?php echo _(' Название курса');?> </a>' },
			{ "sTitle": '<a href="javascript: return false;">&nbsp;&nbsp;<?php echo _('Записаны');?>&nbsp;&nbsp;</a>' },
			{ "sTitle": '<a href="javascript: return false;">&nbsp;&nbsp;<?php echo _('Учатся');?>&nbsp;&nbsp;</a>' },
			{ "sTitle": '<a href="javascript: return false;">&nbsp;&nbsp;<?php echo _('Завершили');?>&nbsp;&nbsp;</a>' },
			{ "sTitle": '<a href="javascript: return false;">&nbsp;&nbsp;<?php echo _('%');?>&nbsp;&nbsp;</a>' }
		]
	} );
	
	$(window).resize(function(){
		$('.Progress table th a:first-child').click();
		$('.Progress table th a:first-child').click();
	});
	
	
	$('.Progress th a').click(function () { 
		$('.Progress table th a').removeClass('arrowDown' );
        $(this).toggleClass( 'arrowDown' ); 
    });
	
	$('.Progress table td:last-child, .Progress table th:last-child').css('border-right', "0px");
	$('.Progress table td:first-child, .Progress table th:first-child').css('border-left', "0px");
	
	$('.Progress table td:first-child, .Progress table th:first-child a').css('text-align', "left");
	$('.Progress table td:first-child, .Progress table th:first-child a').css('padding-left', "15px");
	
	//$('.Progress table td:first-child').css('font-size', "1em");
	//$('.Progress table td:last-child').css('font-weight', "700");
	
	$('.Progress table th a:first-child').click();
		
} );
<?php $this->inlineScript()->captureEnd();?>
