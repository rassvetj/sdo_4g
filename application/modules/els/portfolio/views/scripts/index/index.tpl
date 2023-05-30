<div style="width:100%">
<?php 
if(count($this->urls)){
	foreach($this->urls as $f){
		echo '<div style="margin: 0 auto; display: table;">';
			echo '<br><img style="max-width:700px;" src="/'.$f.'"><br>';
		echo '</div>';
	}
} else {
	echo 'У Вас пока нет портфолио';}
?>
</div>
<br>