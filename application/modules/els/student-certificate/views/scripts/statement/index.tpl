<?php if (!$this->gridAjaxRequest):?>
<style>
	.fields-block > legend {
		pointer-events: none;
	}
	
	.fields-block > legend > .separator{
		display:none;
	}
	
	body form .fields-block dl{
		display:block!important;
	}
	.hide{
		display:none!important;
	}
	
	.statement-header{
		display: inline-block;		
	}
	
	.st-grid {
		padding-right: 410px;
		
	}
	.st-form{
		position: absolute;
		top: 0;
		right: 30px;
		width: 360px;		
	}
	.export{
		display:none;
	}
	
	.element select {
		width: 300px;
		height: 24px;
	}
	
	.element .hasDatepicker{
		width: 280px;
		
	}
</style>
<div class="st-header">
		<div style="    margin-top: 16px; height: 40px; float: left; padding: 5px; display: inline-block;  margin-bottom: -16px;">			
			<div class="_grid_gridswitcher" data-userway-font-size="11">				
				<a href="<?=$this->baseUrl($this->url(array('module' => 'student-certificate', 'controller' => 'index', 'action' => 'index')));?>">
					<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending" data-userway-font-size="11">
						<?=_('Мои заявки')?>
					</div>
				</a>				
				<div class="ending _u_selected"><?=_('Мои заявления')?></div>   
			</div>
		</div>
</div>
<div id="grid-area" class="st-grid">
	<?=$this->grid;?>
</div>
<div id="form-area" class="st-form">
	<?=$this->form?>
</div>


<?php $this->inlineScript()->captureStart()?>
	changeForm('<?=HM_StudentCertificate_Statement_StatementModel::TYPE_CHANGE_FIO?>');
	
	function changeForm(type_id){			
		$('#form-area form dt, #form-area form dd, #form-area fieldset').removeClass('hide');
		
		if(type_id == '<?=HM_StudentCertificate_Statement_StatementModel::TYPE_CHANGE_FIO?>'){
			$('#fieldset-additional_info').addClass('hide');
			$('#fieldset-additional_info_2').addClass('hide');
		}
		
		if(type_id == '<?=HM_StudentCertificate_Statement_StatementModel::TYPE_ACADEM_HOLIDAY?>'){
			$('#fieldset-passport_info').addClass('hide');			
			$('#fieldset-additional_info_2').addClass('hide');
		}
		
		if(type_id == '<?=HM_StudentCertificate_Statement_StatementModel::TYPE_REMAND?>'){
			$('#fieldset-passport_info').addClass('hide');
			$('#fieldset-additional_info').addClass('hide');
			
		}
	}
	
	
	
(function () {
	
	$(document.body).delegate('#form-area form', 'submit', _.debounce(function (event) {
			var form = $(this);
			var area_id = 'form-area';
			var url = form.attr('action');
			
			$('#'+area_id+' input[name="submit"]').prop( "disabled", true );
			
			$.ajax( url , {
				type: 'POST',
				global: false,
				data: form.serialize()		    
			}).done(function (data) {		
				_.defer(function () {
					
					$('#'+area_id).html(data);
					changeForm(	$('#type_id').val()	);
					
					// Есть сообщение об успехе.
					if($('.ui-state-success').length > 0) {
						updateGridContent();
					}
					
				});
			}).fail(function () {
				var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + area_id);
				jQuery.ui.errorbox.clear($message);
				$message.errorbox({level: 'error'});
			}).always(function () {
				$('#' + area_id).closest('.ui-portlet').removeClass('ui-state-loading');				
			});
			
	}, 50));

	$(document.body).delegate('#form-area form', 'submit', function(event) {
		event.preventDefault();
	});
})();


function updateGridContent(){
	var url = 'statement/get-grid';
	console.log(url);
	var jqxhr = $.post(url);
	jqxhr.success(function(data) { 
		$('#grid-area').html(data);
		console.log(1);
	});
}
<?php $this->inlineScript()->captureEnd()?>

<?php else : ?>
	<?=$this->grid;?>
<?php endif;?>