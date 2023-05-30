<select id="els-extended-group-filter">
	<?php foreach($this->tutors as $key => $value) {		
		echo '<option style="color: black;" value="'.$key.'">'.$value.'</option>';		
	}
	?>
</select>
<br>
<br>
<div id='content-area'>
	<div class='description-area-tutor'>
		<?php echo $this->content?>
	</div>
</div>
<script>
		
	function getReport(tutor_id) {
		var contentID = 'content-area';
		
		$('#' + contentID).html('');
		$('#' + contentID).addClass('ajax-spinner-local spinner-area-tutor');
		
		
		$.ajax(<?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'report', 'controller' => 'tutors', 'action' => 'get'))) ) ?>, {
				type: 'POST',
				global: false,
				data: {            
					tutor_id: tutor_id
				}
			}).done(function (data) {		
				_.defer(function () {
					$('#' + contentID).removeClass('ajax-spinner-local');
					$('#' + contentID).removeClass('spinner-area-tutor');
					$('#' + contentID).html(data);
				});
			}).fail(function () {
				var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + formId);
				jQuery.ui.errorbox.clear($message);
				$message.errorbox({level: 'error'});
			}).always(function () {				
			});
	}

</script>

<script>
    $(document).ready(function(){
		$('.extended-page').addClass('extended-page-narrow hgll-pc-1-column');		
		$('.page-context-accordion').hide();
		$('.page-context-accordion').remove();		
		$('.accordion-expander').hide();
		$('.accordion-expander').remove();
		
		var tutors = $('#els-extended-group-filter');
		
		tutors.on('change', function(){
            var tutorVal = $(this).val();
			if(tutorVal == -1){
				var contentID = 'content-area';		
				$('#' + contentID).html('<div class="description-area-tutor">Выберите из списка</div>');
			} else {
				getReport(tutorVal);					
			}
        });
	});
</script>