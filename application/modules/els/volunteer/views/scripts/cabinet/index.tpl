<?php if($this->isVolunteerRequest) : ?>

	
	<?php if($this->volunteerStatus == HM_Volunteer_VolunteerModel::VOLUNTEER_NEW || empty($this->volunteerStatus)) : /* новая */ ?> 			
		<p class="vText">Вы уже подали заявку на вступление в волонтеры. В ближайшее время она будет рассмотрена.</p>
	<?php elseif($this->volunteerStatus == HM_Volunteer_VolunteerModel::VOLUNTEER_REJECT) :  /* отклонили */ ?>
		<p class="vText">Ваша заявка отклонена<?php if($this->volunteerReason) : ?> по причине: "<b><?=$this->volunteerReason;?></b>"<?php endif; ?>.</p>
	<?php elseif($this->volunteerStatus == HM_Volunteer_VolunteerModel::VOLUNTEER_EXILE) : /* исключили */ ?>
		<p class="vText">Вас исключили из волонтеров<?php if($this->volunteerReason) : ?> по причине: "<b> <?=$this->volunteerReason;?></b>"<?php endif; ?>.</p>
		<br>
		<p class="vText">Теперь Вы не можете учавствовать в мероприятиях и регистрироваться на них.</p>
	<?php elseif($this->volunteerStatus == HM_Volunteer_VolunteerModel::VOLUNTEER_APPROVE) : /* одобрили */ ?>
		<a id="vHelpBtn" class="help-activator volonteer-help" data-help-url="http://192.168.132.220/volunteer/cabinet/info/" href="http://192.168.132.220/volunteer/cabinet/info/" title="Информация">Информация</a>
		<?php $form_id = $this->formEventRequest->getName();?>		
		<?php if (!$this->gridAjaxRequest):?>
			
			<div class="div_<?=$form_id;?>">
				<?=$this->formEventRequest;?>	
			</div>			
			<?/* BEGIN js script */?>			
			<?php $this->inlineScript()->captureStart()?>
				function sendFormEvent (id, url) {
					
					$('#' + id).closest('.ui-portlet').addClass('ui-state-loading');
					
					$.ajax( url , {
						type: 'POST',
						global: false,
						data: $('#' + id).serialize(),		    
					}).done(function (data) {		
						_.defer(function () {			
							$('#' + id).closest('div.div_<?=$form_id;?>').html(data);
						});
					}).fail(function () {
						var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + id);
						jQuery.ui.errorbox.clear($message);
						$message.errorbox({level: 'error'});
					}).always(function () {
						$('#' + id).closest('.ui-portlet').removeClass('ui-state-loading');
						$('#' + id)
							.prop('disabled', false)
							.find('input').prop('disabled', false);
					});
				}
				
				$(document.body).delegate('#<?=$form_id;?>', 'submit', _.debounce(function (event) {
					$('#<?=$form_id;?>')
						.prop('disabled', true)
						.find('input').prop('disabled', true);
					var $portletContent = $(this).closest('.ui-portlet-content');
					if ($portletContent.length) {
						$portletContent.find('.ajax-spinner-local').appendTo($portletContent.parent());
					}	
					sendFormEvent('<?=$form_id;?>', <?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'volunteer', 'controller' => 'cabinet', 'action' => 'send-event-reqest'))) ) ?>);
				}, 50));

				$(document.body).delegate('#<?=$form_id;?>', 'submit', function(event) {
					event.preventDefault();
				});
			<?php $this->inlineScript()->captureEnd()?>
			<?/* END js script */?>
		<?php endif;?>
		<?=$this->grid_events;?>	
	<?php endif; ?>
<?php else : ?>	
	<?php $form_id = $this->member_reqest_form->getName();	?>
	<div class="div_<?=$form_id;?>">
		<?=$this->member_reqest_form;?>		
	</div>
	<div>
		<?=$this->info;?>
	</div>
	<?php $this->inlineScript()->captureStart()?>
		function sendForm (id, url) {
			
			$('#' + id).closest('.ui-portlet').addClass('ui-state-loading');

			$.ajax( url , {
				type: 'POST',
				global: false,
				data: '',		    
			}).done(function (data) {		
				_.defer(function () {			
					$('#' + id).closest('div.div_<?=$form_id;?>').html(data);
				});
			}).fail(function () {
				var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + id);
				jQuery.ui.errorbox.clear($message);
				$message.errorbox({level: 'error'});
			}).always(function () {
				$('#' + id).closest('.ui-portlet').removeClass('ui-state-loading');
				$('#' + id)
					.prop('disabled', false)
					.find('input').prop('disabled', false);
			});
		}


		$(document.body).delegate('#<?=$form_id;?>', 'submit', _.debounce(function (event) {
			$('#<?=$form_id;?>')
				.prop('disabled', true)
				.find('input').prop('disabled', true);
			var $portletContent = $(this).closest('.ui-portlet-content');
			if ($portletContent.length) {
				$portletContent.find('.ajax-spinner-local').appendTo($portletContent.parent());
			}	
			sendForm('<?=$form_id;?>', <?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'volunteer', 'controller' => 'cabinet', 'action' => 'send-member-reqest'))) ) ?>);
		}, 50));

		$(document.body).delegate('#<?=$form_id;?>', 'submit', function(event) {
			event.preventDefault();
		});
	<?php $this->inlineScript()->captureEnd()?>
<?php endif; ?>