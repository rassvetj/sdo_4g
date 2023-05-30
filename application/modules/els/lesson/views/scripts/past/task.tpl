<style>
	.read-only{
		pointer-events: none;
		
		background-image: none!important;		
		color: #484747!important; 
		background-color: #3a3a3a!important;		
		text-shadow: white 1px 1px 0!important; 
		opacity: 0.5!important; 
		filter: none!important; 
		-ms-filter: none!important; 
		cursor: default!important;
	}
</style>
<?php
$this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/test.css'));
$keyshowform = $kods = array();
foreach($this->messages as $message){    
    if ($this->taskPreview && in_array($message->question_id, $kods)) continue; // при предпросмотре не показываем одни и те же назначенные варианты
    if($message->ball){
		$mark = $message->ball;
	} else {		
		$mark = 0.000000001; //--в шаблоне будет 0.
	}	

    echo $this->interviewMessage($message, $this->teacher, $this->lesson, $mark);
	$kods[] = $message->question_id;
}
?>

<div style="margin-top: 30px;"></div>
