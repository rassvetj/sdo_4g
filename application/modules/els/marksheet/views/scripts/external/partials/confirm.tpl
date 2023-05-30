<style>	
	.m-confirm-area{
		font-size: 13px;
		border: 1px solid #ccc;
		display: inline-block;
		border-radius: 3px;
		padding: 5px;
		width: 99%;
	}
</style>
<br />
<div class="m-confirm-area">
<?php if(!$this->all_confirmed):?>
	<span style="color: red;">Для формирования ведомости необходимо подтверждение всех членов комиссии</span><br />
<?php endif;?>
	<?php 
	if(!empty($this->marksheet->commission_members)){
		echo '<table>';
		foreach($this->marksheet->commission_members as $member){
			echo '<tr>';
				echo '<td>';
					echo $member->getName();
				echo '</td>';
				echo '<td>';
					if($member->confirm){
						echo ' <span style="color: green;">подтвердил '.date('d.m.Y', strtotime($member->confirm->date_created)).'</span>';
					} else {
						if($member->MID == $this->current_user->MID){
							echo '<a href="' . $this->baseUrl($this->url(array(	'module'		=> 'marksheet', 
															'controller'	=> 'external', 
															'action' 		=> 'confirm-mark',
															'marksheet_id'	=> $this->marksheet->marksheet_id,
															),'default', true)) . '" >подтвердить</a>';
						} else {
							echo ' <span style="color: red;">не подтвердил</span>';
						}
					}
				echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	?>
</div>
<br />
<br />
<div style="clear:both;"></div>