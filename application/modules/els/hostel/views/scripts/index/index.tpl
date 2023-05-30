<style>
	.filters_tr, .grid-actions, .bottom-grid {
		display: none;
	}	
</style>

<?php if (!$this->gridAjaxRequest):?>
<div class="hostel-head-text">
	<p><?=_('Уважаемые студенты!')?></p>
	<p><?=_('На данной странице Вы можете подать заявление на заселение в общежития РГСУ.')?></p>
	<p><?=_('Нажмите кнопку отправить.')?></p>
	<p><?=_('Если у Вас есть договор на проживание, Вы можете подать заявление на переселение, выбрав общежитие и комнату (заполнение данной информации не обязательно).')?></p>	
	<a href="/upload/files/hostel/Общежития. Инструкция студенту.docx" target="_blank"><?=_('Инструкция')?></a>
</div>

<div class="grid-area">
	<?=$this->grid;?>
</div>
<div class="form-area default-form-style-area">
	<?php if($this->isShowFormArea) : ?>
		<div id="area-description">
			<p><?=_('Ваше сообщение будет рассмотрено в установленном порядке')?>.</p>
			<p><?=_('Результат будет доступен на данной странице')?>.</p>
		</div>
		<?=$this->form;?>
	<?php endif; ?>
</div>

	<?php $this->headScript()->captureStart() ?>
		
		$( document ).ready(function() {
			$('.form-area form').submit(function(){				
				$('#submit').attr('disabled','disabled');
			});
			
			if($("#type_id").val() == '<?=HM_Hostel_Claims_ClaimsModel::TYPE_SETTLEMENT;?>'){
				$('#addres_id-element').hide();
				$('#addres_id-label').hide();
				
				$('#room_id-element').hide();
				$('#room_id-label').hide();
			} else {
				$('#addres_id-element').show();
				$('#addres_id-label').show();
				
				$('#room_id-element').show();
				$('#room_id-label').show();
			}	
			
			
			$( "#type_id" ).change(function() {
				if($(this).val() == '<?=HM_Hostel_Claims_ClaimsModel::TYPE_SETTLEMENT;?>'){
					$('#addres_id-element').hide();
					$('#addres_id-label').hide();
					
					$('#room_id-element').hide();
					$('#room_id-label').hide();
				} else {
					$('#addres_id-element').show();
					$('#addres_id-label').show();
					
					$('#room_id-element').show();
					$('#room_id-label').show();
				}				
			});
			
		});
		
		
		function getRooms(){					
			var data   = $('.form-area form').serialize();		
			 $('#room_id').attr('disabled','disabled');
        
			$.ajax(<?= Zend_Json::encode( $this->baseUrl($this->url(array('controller' => 'index', 'action' => 'get-rooms'))) ) ?>, {
				type: 'POST',
				global: false,
				data: data,
				dataType: 'json'
			}).done(function (data) {		
				_.defer(function () { 										
					$('#room_id').empty();			
					$.each(data, function(i, value) {																
						$('#room_id').append($('<option>').text(value.value).attr('value', value.key));
					});
					$('#room_id').removeAttr('disabled');
				});
			}).fail(function () {			
			}).always(function () {			
			});						
		}
	<?php $this->headScript()->captureEnd() ?>

<?php else : ?>
	<?php echo $this->grid?>
<?php endif;?>
