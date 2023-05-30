<?#=implode('<br />', $this->debug);?>

<?php if(!empty($this->error)):?>
	<?=$this->error?>
	<br />
	<br />
<?php else: ?>
	<?php if(empty($this->content)):?>
		<?=_('Нет данных');?>		
	<?php else:?>
		<a href="#" class="btn-select-all">Выделить всех</a>
		&nbsp;
		<a href="#" class="btn-select-remove">Снять выделение</a>
		<br />
		<br />
		<form method="POST" class="form-remove-assigns" action="<?=$this->baseUrl($this->url(array('module' => 'report', 'controller' => 'unlinked-program', 'action' => 'remove-assigns')))?>" >
			<table class="tbl-default-report">	
				<tr>
					<th>#</th>
				<?php foreach($this->fields as $field_code => $field_name):?>
					<th><?=$field_name?></th>
				<?php endforeach;?>
				</tr>	
					<?php foreach($this->content as $row):?>
						<?php $key = $row['student_id'].'_'.$row['subject_id']; ?>
						<tr id="<?=$key?>">
							<td><input type="checkbox" name="rows[]" value="<?=$key?>"></td>
							
							<td><?=$row['student_mid_external']?></td>
							<td><a href="/user/edit/card/user_id/<?=$row['student_id']?>" target="_blank"><?=$row['student_fio']?></a></td>
							<td><?=$row['student_semester']?></td>
							<td>
								<?php foreach($row['student_groups'] as $id => $name):?>
									<a href="/study-groups/users/index/group_id/<?=$id?>" target="_blank"><?=$name?></a>, 						
								<?php endforeach;?>
							</td>
							<td>
								<?php foreach($row['student_programs'] as $id => $name):?>
									<a href="/programm/index/index/programm_id/<?=$id?>" target="_blank"><?=$name?></a>,					
								<?php endforeach;?>
							</td>
							<td><?=implode(', ', $row['student_activity'])?></td>
							<td><?=$row['subject_external_id']?>.</td>
							<td><a href="/subject/index/card/subject_id/<?=$row['subject_id']?>" target="_blank"><?=$row['subject_name']?></a></td>
							<td><?=$row['subject_semester']?></td>
							<td>
								<?php foreach($row['subject_programs'] as $id => $name):?>
									<a href="/programm/index/index/programm_id/<?=$id?>" target="_blank"><?=$name?></a>,						
								<?php endforeach;?>
							</td>
						</tr>
					<?php endforeach;?>					
			</table>
			<button type="submit" class="btn-remove-assigns" style="float: right;">Удалить студентов с выделенных с сессий</button>
		</form>
		
			<button class="btn-assign-graduated">Перевести в завершенные студентов с выделенных с сессий</button>
		<div class="area-message"></div>
		
		<script>		
			$('.btn-select-all').click(function(){
				$('.form-remove-assigns input[name="rows[]"]').prop('checked', true);				
				return false;
			});
			
			$('.btn-select-remove').click(function(){
				$('.form-remove-assigns input[name="rows[]"]').prop('checked', false);				
				return false;
			});
			
			$('.form-remove-assigns').submit(function(e){
				var form 		 = $(this);
				var btn	 		 = $(this).find('[type="submit"]');
				var area_message = $('.area-message');
				
				btn.prop('disabled', true);
				area_message.html('');
				
				jQuery.ajax({
					type	: 'POST',
					url		: form.attr('action'),
					dataType: 'json',
					data: form.serialize(),
					success: function (result) {
						if (typeof result.message !== "undefined") {
							area_message.html(result.message);
							var $message = jQuery('<div>'+result.message+'</div>').appendTo(form);
							jQuery.ui.errorbox.clear($message);
							
							if (typeof result.error !== "undefined") {
								if(result.error == 1){
									$message.errorbox({level: 'error'});
								}
							} else {
								$message.errorbox({level: 'success'});
								if (typeof result.removed !== "undefined") {
									$.each( result.removed, function( key, value ) {
										$('#'+key).addClass('row-removed');
										$('#'+key).find('input[name="rows[]"]').prop('checked', false);	
									});	
								}								
							}
						}
						btn.prop('disabled', false);
					},
					fail: function (result) {
						var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo(form);
						jQuery.ui.errorbox.clear($message);	
						$message.errorbox({level: 'error'});
						btn.prop('disabled', false);
					},
					error: function (result) {
						var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo(form);
						jQuery.ui.errorbox.clear($message);
						$message.errorbox({level: 'error'});
						btn.prop('disabled', false);
					}
				});
				
				e.preventDefault();
				return false;				
			});		
		</script>
	<?php endif;?>
	<br />
<?php endif;?>

