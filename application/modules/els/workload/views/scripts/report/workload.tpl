<div class="report">
<style>
	.filters_tr{
		display:none;
	}
	

	table {
		margin: 0 auto;
		border-collapse: collapse;
		text-align: center;
	}
	table td, table th {
		padding: 3px;	
	}

	table th {
		font-weight: bold;
	}

	table td, table th {
		border: 1px solid black;
	}
</style>
<p><b><?=_('Текущие сессии, Текущие + продленные сессии')?></b><?=_(' - учитываются данные за указанный период с разделением на осенний и весенний периоды c 1 сентября по 31 января и с 1 февраля по 30 июля')?></p>
<p><b><?=_('Продленные сессии')?></b><?=_(' - учитываются данные с даты окончания сессии. Без разделения на периоды.')?></p>
<p><b><?=_('Не разделять на периоды')?></b><?=_(' - отменяет разделение на осенний и весенний периоды.')?></p>
<br />
<br />
<div id="form-area">
	<?=$this->form;?>		
</div>

<div id="report-area">
</div>
<?php $this->headScript()->captureStart() ?>
	$( document ).ready(function() {
		$('#user_id-label .required-star').hide();
		if($('select[name="user_id"]').length > 0){ $('#user_id').select2(); }
	});
	
	function changeForm(){					
		var data   = $('#form-area form').serialize();
		$('#form-area').html('Загрузка...');
		$('#report-area').html('');
		$.ajax(<?= Zend_Json::encode( $this->baseUrl($this->url(array('controller' => 'report', 'action' => 'modify'))) ) ?>, {
			type: 'POST',
			global: false,
			data: data
		}).done(function (data) {		
			_.defer(function () {            		
				$('#form-area').html(data);
				
				$( "#date_begin" ).datepicker({						
					showOn : 'button',
					buttonImage : "/images/icons/calendar.png",
					buttonImageOnly : 'true'
				});
				$( "#date_end" ).datepicker({						
					showOn : 'button',
					buttonImage : "/images/icons/calendar.png",
					buttonImageOnly : 'true'
				});
				
				$('#user_id-label .required-star').hide();
				if($('select[name="user_id"]').length > 0){ $('#user_id').select2(); }
			});
		}).fail(function () {
			//var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + block_id);
			//jQuery.ui.errorbox.clear($message);
			//$message.errorbox({level: 'error'});
		}).always(function () {
			//$('#' + block_id).closest('.ui-portlet').removeClass('ui-state-loading');
			//$('#' + block_id)
				//.prop('disabled', false)
				//.find('input').prop('disabled', false);			
		});						
	}
	
	function sendForm(){		
		var data   = $('#form-area form').serialize();		
		$('#report-area').html('Отчет формируется...');
		$.ajax(<?= Zend_Json::encode( $this->baseUrl($this->url(array('controller' => 'workload', 'action' => 'get-workload-report'))) ) ?>, {
			type: 'POST',
			global: false,
			data: data
		}).done(function (data) {		
			_.defer(function () {            		
				$('#report-area').html(data);
			});
		}).fail(function () {
			//var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + block_id);
			//jQuery.ui.errorbox.clear($message);
			//$message.errorbox({level: 'error'});
		}).always(function () {
			//$('#' + block_id).closest('.ui-portlet').removeClass('ui-state-loading');
			//$('#' + block_id)
				//.prop('disabled', false)
				//.find('input').prop('disabled', false);			
		});	
		
		return false;
	}
	

<?php $this->headScript()->captureEnd() ?>
</div>