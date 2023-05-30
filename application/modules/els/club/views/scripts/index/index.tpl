<?php if ($this->claimExist):?>
	<div class="area-claim">
		<?=$this->claim_info?>		
	</div>
	
<?php else : ?>
	<?php if (!$this->isPeriodAvailable):?>
		<div class="default-form-area">
			<?=_('Время подачи заявки истекло или еще не наступило')?>
		</div>
	<?php else : ?>
		<div class="default-form-area">
			<?=$this->form;?>
			<div class="area-club-info"></div>
		</div>
		<div class="area-claim"></div>



		<?php $this->inlineScript()->captureStart()?>
			
			$('.default-form-area form [name="club_id"]').select2();
			
			$('.default-form-area').on('change', '.default-form-area form [name="club_id"]', function(e) {
				
				var block_info 	= $('.area-club-info');
				var url 		= '<?=$this->baseUrl($this->url(array('module' => 'club', 'controller' => 'index', 'action' => 'get-club-info'))) ?>';
				var club_id 	= $(this).val();
				var btn			= $(this).closest('form').find('[type="submit"]');
				
				btn.prop('disabled', true);
				block_info.html('');
				
				jQuery.ajax({
					type	: 'POST',
					url		: url,
					dataType: 'html',
					data	: {club_id:club_id},			
					success	: function (result) {
						block_info.html(result);
						btn.prop('disabled', false);
					}
				});
			});
			
			$('.default-form-area').on('submit', '.default-form-area form', function(e) {			
				var form 		= $(this);
				var btn	 		= $(this).find('[type="submit"]');
				var area_claim 	= $('.area-claim');
				var area_form	= $('.default-form-area');
				
				btn.prop('disabled', true);
				
				jQuery.ajax({
					type	: 'POST',
					url		: form.attr('action'),
					dataType: 'json',
					data: form.serialize(),			
					success: function (result) {
						if (typeof result.message !== "undefined") {
							var $message = jQuery('<div>'+result.message+'</div>').appendTo(form);
							jQuery.ui.errorbox.clear($message);
							
							if (typeof result.error !== "undefined") {
								if(result.error == 1){
									$message.errorbox({level: 'error'});								
								}
							} else {
								$message.errorbox({level: 'success'});
								if (typeof result.claim !== "undefined") {
									area_form.remove();
									area_claim.html(result.claim);								
								}							
							}
						}
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

	<?php endif;?>
<?php endif;?>