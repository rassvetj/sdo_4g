<style>		
		.accordion-container{
			font-size:17px;
			text-align: justify;
			border: 1px solid #fdfdfd;
			margin-bottom: 10px;	
			padding: 0px;
			padding-top: 10px;
		}

		.accordion-header a{
			margin: 0 !important;
			padding: 10px;
			font-size: 18px;
			background: #f9f9f9;
			display: block;
			padding-right: 30px;
			position: relative;	
			border-bottom: none;
			color: #3d3d3d;
			text-decoration: none;
		}

		.accordion-container.open .accordion-header a{
			background: #effaff;
			
		}

		.accordion-header a::after{
			content: '';
			position: absolute;
			right: 20px;
			top: 20px;
			border: 5px solid transparent;
			border-top: 5px solid #ccc;
		}

		.accordion-container.open .accordion-header a::after{
			content: '';
			position: absolute;
			right: 20px;
			top: 15px;
			border: 5px solid transparent;
			border-bottom: 5px solid #3467A0;
		}

		.accordion-container.open .accordion-data{
			display:block;
		}

		.accordion-container .accordion-data {
			display:none;
			font-size: 15px;
			padding: 10px;
		}

		.info-message {
			font-size: 15px;
		}		
</style>

<?php if(empty($this->students)):?>
	<p class="info-message">Нет студентов для отображения</p>
<?php else: ?>
	
	<?php foreach($this->students as $group_name => $students):?>
		<div class="accordion-container">
			<div class="accordion-header">
				<a href="#" class="btn-accordion"><?=$group_name?></a>
			</div>
			<div class="accordion-data">
				<table>
				<?php $cur_row = 0; ?>
				<?php foreach($students as $student):?>					
					<?php $cur_row++; ?>
					<tr>
						<td><?=$cur_row?>. </td>
						<td>
							<?=$this->cardLink($this->url(array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $student['student_id']), null, true), 'Карточка пользователя')?>
							<?=$student['fio']?>
						</td>
						<td>
							<?php if(!empty($student['skype'])):?>
								Skype: <a href="skype:<?=$student['skype']?>?chat" ><?=$student['skype']?></a>
							<?php endif;?>
						</td>						
					</tr>						
				<?php endforeach?>					
				</table>				
			</div>
		</div>
		
		
	<?php endforeach?>
<?php endif;?>


<script>
	$( document ).ready(function() {
		$('body').on('click', '.btn-accordion', function(event) {
			event.preventDefault();
			var container = $(this).closest('.accordion-container');
			if ( container.hasClass('open')){
				container.removeClass('open');
			} else {
				container.addClass('open');
			}
		});
	});
</script>