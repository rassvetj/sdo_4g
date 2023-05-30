<style>
	.row-graduated {
		background-color: #6f6c6c4f;
	}
</style>
<p>
	Этот отчет служит для контроля назначений студентов на сессии, с которыми уже нет связи через программу обучения (стдент - группа - программа обучения - сессия)
</p>
<p>Условия отбора:</p>
<ol>
	<li>Студенты только активные</li>	
	<li><span class="row-graduated">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> - переведенные в завершенные</li>	
	<li><span class="row-removed">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> - удаленные назначения</li>	
</ol>
<hr />
<div class="default-form-area form-report-conditions">	
	<?=$this->form;?>
</div>
<br>
<br>
<div class="area-report-content"></div>

<script>
	$( document ).ready(function() {   
		$('body').on('click', '.btn-assign-graduated', function(e) { 
			var form 		 = $('.area-report-content form');
			var btn_1 		 = $(this);
			var btn_2 		 = form.find('[type="submit"]');
			var area_message = $('.area-message');
				
			btn_1.prop('disabled', true);
			btn_2.prop('disabled', true);
			area_message.html('');
			
			$.ajax({
				type    : 'POST',
				url	    : '<?=$this->baseUrl($this->url(array('module' => 'report', 'controller' => 'unlinked-program', 'action' => 'assign-graduated')))?>',
				data    : form.serialize(),
				dataType: 'json'
			}).done(function( result ) {
				if (typeof result.message !== "undefined") {
					area_message.html(result.message);
					var $message = jQuery('<div>'+result.message+'</div>').appendTo(form);
					jQuery.ui.errorbox.clear($message);
					
					if (typeof result.error !== "undefined") {
						if(result.error == 1){
							$message.errorbox({level: 'error'});
						}
					} else {
						$message.errorbox({level: 'success'});
						if (typeof result.removed !== "undefined") {
							$.each( result.removed, function( key, value ) {
								$('#'+key).addClass('row-graduated');
								$('#'+key).find('input[name="rows[]"]').prop('checked', false);	
							});	
						}								
					}
				}
				btn_1.prop('disabled', false);
				btn_2.prop('disabled', false);				
			}).fail(function() {
				var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo(form);
				jQuery.ui.errorbox.clear($message);	
				$message.errorbox({level: 'error'});
				btn_1.prop('disabled', false);
				btn_2.prop('disabled', false);
			});
			return false;
		});
	});
</script>

<?php $this->inlineScript()->captureStart()?>
	

	$('[name="student_groups[]"]').select2();
	$('[name="subject_programs[]"]').select2();
	
	
	$('.default-form-area').on('submit', '.default-form-area form', function(e) {			
				var form 		 = $(this);
				var btn	 		 = $(this).find('[type="submit"]');
				var area_content = $('.area-report-content');
				var area_form	 = $('.default-form-area');
				
				btn.prop('disabled', true);
				area_content.html('');
				
				jQuery.ajax({
					type	: 'POST',
					url		: form.attr('action'),
					dataType: 'html',
					data: form.serialize(),			
					success: function (result) {
						area_content.html(result);
						btn.prop('disabled', false);					
					},
					fail: function (result) {
						var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo(form);
						jQuery.ui.errorbox.clear($message);	
						$message.errorbox({level: 'error'});
						btn.prop('disabled', false);					
					},
					error: function (result) {
						var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo(form);
						jQuery.ui.errorbox.clear($message);
						$message.errorbox({level: 'error'});
						btn.prop('disabled', false);
					}								
				});
				e.preventDefault();
				return false;		
	});
<?php $this->inlineScript()->captureEnd()?>