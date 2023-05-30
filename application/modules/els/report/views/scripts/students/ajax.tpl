<style>
	.report-user-tbl {
		border-collapse: collapse;
	}
	
	.report-user-tbl td, .report-user-tbl th {
		border: 1px solid black;
		padding: 3px;
		vertical-align: middle;		
	}
</style>
<div class='description-area-tutor'>	
	<?php echo $this->info?>
</div>

<div class='grid-area-tutor'>	
	<?php		
		if(!empty($this->cols)){			
			
			echo '<table class="report-user-tbl">';
				$s1 = (count($this->cols[1]) > 0) ? (count($this->cols[1])) : (1);
				$s2 = (count($this->cols[2]) > 0) ? (count($this->cols[2])) : (1);
				
				echo '<tr>';
					echo '<td>'.$this->group_name.'</td>';
					echo '<th colspan="'.$s1.'">1 семестр</th>';	
					echo '<th colspan="'.$s2.'">2 семестр</th>';	
				echo '</tr>';
				
				//--сессии
				echo '<tr>';
					echo '<td>&nbsp;</td>';						
						if(!count($this->cols[1])){
							echo '<td>&nbsp;</td>';						
						} else {
							foreach($this->cols[1] as $i){
								echo '<td>'.$i.'</td>';	
							}						
						}
						
						if(!count($this->cols[2])){
							echo '<td>&nbsp;</td>';						
						} else {
							foreach($this->cols[2] as $i){
								echo '<td>'.$i.'</td>';	
							}						
						}						
				echo '</tr>';
				
				//--строки
				foreach($this->rows as $k => $i){
					echo '<tr>';
						
						echo '<td>'.$this->rowsUser[$k].'</td>'; //--студенты
						
						if(!count($this->cols[1])){ //--оценки первого семестра
							echo '<td>&nbsp;</td>';	
						} else {
							foreach($this->cols[1] as $ks => $s){							
								echo '<td>';
								if($i[$ks] === NULL){
									echo _('нет');
								} else {
									echo $i[$ks];
								}			
								echo '</td>';								
							}							
						}

						if(!count($this->cols[2])){ //--оценки второго семестра
							echo '<td>&nbsp;</td>';	
						} else {
							foreach($this->cols[2] as $ks => $s){							
								echo '<td>'.$i[$ks].'</td>';
							}							
						}						
					echo '</tr>';					
				}
			echo '</table>';
		} else {
			echo _('Данные отсутствуют');
		}
		
	?>
</div>
