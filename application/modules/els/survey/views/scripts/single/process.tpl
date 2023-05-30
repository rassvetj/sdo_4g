<br>
<div class="sr-caption">
	<b><?=$this->type_name?></b>
</div>

<div class="survey-form">
	<?=$this->form?>
</div>
<?php $this->inlineScript()->captureStart()?>
	$('.survey-form input:radio').click(function(){
		$('.survey-form input[type="submit"]').prop('disabled', false);
		console.log(1);
		
	});
<?php $this->inlineScript()->captureEnd()?>