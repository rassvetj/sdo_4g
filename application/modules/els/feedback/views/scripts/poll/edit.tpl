<?php 
echo $this->form ?>
<?php 

$this->inlineScript()->captureStart();
?>

$(document).ready(function() {


	if($('select[name="event_id"]').val() == <?php echo HM_Event_EventModel::TYPE_POLL;?>){
		$('select[name="vedomost"]').attr('disabled', 'disabled');
	}
});
    
<?php 
$this->inlineScript()->captureEnd();
?>