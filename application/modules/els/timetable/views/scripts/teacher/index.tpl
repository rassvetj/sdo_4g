<style>
	.timetable-gridswitcher{
		font-size: 13px;
		
	}
	.timetable-gridswitcher  a .ending{
		cursor: pointer!important;
	}
	.timetable-period-area{
		font-size: 13px;
		padding: 10px;
		text-align:center;
	}
	.not-data-area{
		font-size: 13px;
		text-align:center;
		font-weight: bold;
	}
	
	
</style>
<div class="_grid_gridswitcher timetable-gridswitcher">		
		<?php if($this->show_next_week):?>
			<a href="<?=$this->url(array('module' => 'timetable', 'controller' => 'teacher', 'action' => 'index'), 'default', true);?>">
				<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending">
					<?= _('Текущая неделя') ?>
				</div>
			</a>
			<div class="ending _u_selected">
				<?= _('Следующая неделя') ?>
			</div>
		<?php else:?>
			<div class="ending _u_selected">
				<?= _('Текущая неделя') ?>
			</div>
			<a href="<?=$this->url(array('module' => 'timetable', 'controller' => 'teacher', 'action' => 'index', 'week' => 'next'));?>">
				<div style="text-decoration: underline;" onclick="$('._grid_gridswitcher div').removeClass('_u_selected');$(this).addClass('_u_selected')" class="ending">
					<?= _('Следующая неделя') ?>
				</div>
			</a>
		<?php endif;?>			
</div>
<div style="clear:both"></div>
<div class="timetable-period-area">
	Период с <?=date('d.m.Y', strtotime($this->monday))?> по <?=date('d.m.Y', strtotime($this->sunday))?>
</div>

<?php if(empty($this->timetable)): ?>
	<p class="not-data-area" ><?=_('Нет данных')?></p>
<?php else:?>
	<style>
		.tbl-timetable {
			font-size: 14px;
			border: 1px solid #ccc;
			border-collapse: collapse;
		}
		
		.tbl-timetable td, .tbl-timetable th {
			border: 1px solid #ccc;
			padding: 5px;
		}
		.in-progress {
			opacity: 0.5;
		}
		.link-area .message-error{
			color:red;
		}
		
		.in-progress{
			opacity: 0.5;
			pointer-events: none;
		}
		
		.has-error{
			color:red;
		}
		#popup-timetable-teacher form {			
			font-size: 14px;
		}
		
		#popup-timetable-teacher form input {
			width: 98%;
			font-size: 14px;
			padding: 3px;
		}
		#popup-timetable-teacher form .element{
			padding-bottom: 10px;
		}
		
		.btn-set-timetable-data{
			font-size: 12px;
			white-space: nowrap;
			margin-top: 0px;
			margin: 0px;
		}
		
		.popup-message-area{
			font-size: 13px;
			color: green;
			font-weight: bold;
			padding-bottom: 5px;
		}
		
		.tbl-timetable th{
			background-color: #0067a4;
			color: white;
			font-weight: bold;
			text-align: center;
		}
		.hidden{
			display: none!important;
		}
		
		.message-area{
			color: green;
			font-size: 13px;
			font-weight: bold;
		}
		.link-area form [type="submit"]  {
			font-size: 12px!important;	
		}
		.link-area	{
			min-width: 200px;
		}
	</style>
	<table class="tbl-timetable" >
		<tr>
			<th><?=_('Время')?></th>
			<th><?=_('Аудитория')?></th>
			<th><?=_('Дисциплина')?></th>
			<th><?=_('Группа')?></th>
			<th><?=_('Тип')?></th>
			<th><?=_('Неделя')?></th>
			<th><?=_('День')?></th>
			<th><?=_('Ссылка')?></th>
			<th></th>
		</tr>
		<?php foreach($this->timetable as $item):?>
			<?php
				#$is_show_multiple_link = HM_Timetable_TimetableModel::isShowMultipleLink($item->discipline);
				$is_show_multiple_link = false;
			?>
			<tr class="tt-row">
				<td><?=$item->time;?>	</td>	
				<td><?=$item->classroom;?>		</td> 
				<td><?=$item->discipline;?></td>
				<td><?=$item->group_name;?>		</td>
				
				<td><?=$item->discipline_type;?> 	</td>
				<td><?=$item->even_odd;?> 			</td>
				<td><?=$item->week_day;?>				</td>
				<td class="link-area">
					<?=empty($item->linkTrueConf) ? '' : ' <a href="' . $item->linkTrueConf . '" target="_blnk">On-line занятие в TrueConf</a>'?>

					<form method="POST" action="#" >
						<input type="hidden" name="timetable_id" 	value="<?=$item->timetable_id;?>">
						
						<?php if($is_show_multiple_link):?>
							<style>
							.tbl-multiple_link, .tbl-multiple_link td {
								border: none; 
								border-collapse: collapse;
							}
							
							</style>
							<table class="tbl-multiple_link">
							<tr><td><?=_('Начальный')?>:</td><td><input type="text"   name="link" 			value="<?=$item->link;?>" style="width: 98%;" ></td></tr>
							<tr><td><?=_('Базовый')?>:</td><td><input type="text"   name="link2" 			value="<?=$item->link2;?>" style="width: 98%;" ></td></tr>
							<tr><td><?=_('Продвинутый')?>:</td><td><input type="text"   name="link3" 			value="<?=$item->link3;?>" style="width: 98%;" ></td></tr>
							</table>
						<?php else :?>
							<input type="text"   name="link" 			value="<?=$item->link;?>" style="width: 98%;" >
							<?=(empty($item->link2) ? '' : '<a href="' . $item->link2 . '" terget="_blank">' . $item->link2 . '</a>')?>
							<?=(empty($item->link3) ? '' : '<a href="' . $item->link3 . '" terget="_blank">' . $item->link3 . '</a>')?>
						<?php endif;?>
						
						<input type="submit" 						value="Сохранить" class="hidden">
					</form>
				</td>
				<td style="text-align:center;">
					<button type="button" 
							class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only ui-state-hover btn-set-timetable-data"
							id="btn-set-timetable-data-<?=$item->timetable_id;?>"
							data-timetable_id	= "<?=$item->timetable_id;?>" 
							data-users			= "<?=$item->users;?>"
							data-file_path		= "<?=$item->file_path;?>"
							data-subject_path	= "<?=$item->subject_path;?>"
					>
						Отчет по занятию
					</button>
					<span style="color: green;"><?=(	(empty($item->users) && empty($item->subject_path)) ? '' : 'заполнен'	)?></span>
					
				</td>
			</tr>
		<?php endforeach;?>
	</table>
	<br />
	<script>
	$( document ).ready(function() {
		$('.link-area form [name="link"], .link-area form [name="link2"], .link-area form [name="link3"]').keyup(function(){
			$(this).closest('form').find('[type="submit"]').removeClass('hidden');
			$(this).closest('.link-area').find('.message-area').remove();
		});
		$('.link-area form [name="link"], .link-area form [name="link2"], .link-area form [name="link3"]').change(function(){
			$(this).closest('form').find('[type="submit"]').removeClass('hidden');
			$(this).closest('.link-area').find('.message-area').remove();
		});
		$('.link-area form [name="link"], .link-area form [name="link2"], .link-area form [name="link3"]').bind('paste', function(e) { 
			$(this).closest('form').find('[type="submit"]').removeClass('hidden');
			$(this).closest('.link-area').find('.message-area').remove();
		});
		
		$('.link-area form').submit(function(){
			event.preventDefault(); 
			
			var form = $(this);
			var btn  = form.find('[type="submit"]')
			var url  = '<?=$this->baseUrl($this->url(array('module' => 'timetable', 'controller' => 'teacher', 'action' => 'save-link'), 'default', true))?>';
			var row	 = form.closest('.tt-row');
			
			form.closest('.link-area').find('.message-area').remove();
			
			row.addClass('in-progress');
			
			$.ajax({
				type	 : "POST",
				url  	 : url,
				data     : form.serialize(),
				dataType: 'json'
			}).done(function( data ) {
				btn.addClass('hidden');
				
				row.removeClass('in-progress');
				
				if (typeof data.message !== "undefined") {
					form.after('<span class="message-area">' + data.message + '</span>');
				}
				
				if (typeof data.error !== "undefined") {
					form.after('<span class="message-area message-error">' + data.error + '</span>');
				}
			}).fail(function(  ) {
				btn.addClass('hidden');
				
				row.removeClass('in-progress');
				form.after('<span class="message-area message-error">Ошибка. Попробуйте позже</span>');
			});

        	
		});
	});
	</script>
	
	
	
	
	
	<div class="popup-default" id="popup-timetable-teacher" >
		<div class="form-area-default form-area-full-width" >
			<div class="popup-message-area"></div>
			<?=$this->form?>
		</div>
	</div>
	<script>
		$( document ).ready(function() {
			var popup = $('#popup-timetable-teacher');
			
			$('.btn-set-timetable-data').click(function() {
				var btn 		 	= $(this);
				var timetable_id 	= btn.data('timetable_id');
				var users 			= btn.data('users');
				var file_path 		= btn.data('file_path');
				var subject_path 	= btn.data('subject_path');
				
				
				popup.find('[name="timetable_id"]').val(timetable_id);
				popup.find('[name="users"]').val(users);
				popup.find('[name="file_path"]').val(file_path);
				popup.find('[name="subject_path"]').val(subject_path);
				
				$('.popup-message-area').html('');
				
				popup.dialog( "open" );	
				return false;	
			});

		
			popup.dialog({
				resizable: false,
				autoOpen: false,			
				width:440,
				modal: true,
				buttons:
				{
					<?=_('Сохранить')?>: function() {
						popup.find('form').submit();
					},
					<?=_('Закрыть')?>: function() {
						$( this ).dialog( "close" );
					}
				}
			});
			
			popup.find('form').submit(function(event){
				event.preventDefault(); 
				
				var form 			= $(this);
				var url  			= form.attr('action');
				var timetable_id 	= form.find('[name="timetable_id"]').val();
				var btn 			= $('#btn-set-timetable-data-' + timetable_id);
				var btns  			= popup.closest('.ui-dialog').find('.ui-dialog-buttonset');
				var mesage_area 	= $('.popup-message-area');
				
				btns.addClass('in-progress');
				mesage_area.html('');
				
				$.ajax({
					type	 : "POST",
					url  	 : url,
					data     : form.serialize(),
					dataType: 'json'
				}).done(function( data ) {
					btns.removeClass('in-progress');
					
					if (typeof data.message !== "undefined") {
						mesage_area.html(data.message);
						
						setTimeout(function(){
							popup.dialog( "close" );
							form[0].reset();
							mesage_area.html('');
						}, 1000);
						
					}
					
					if (typeof data.error !== "undefined") {
						mesage_area.html('<span class="has-error">' + data.error + '</span>');
					}
					
					if (typeof data.users !== "undefined") {
						btn.data('users', data.users);
					}
					
					if (typeof data.file_path !== "undefined") {
						btn.data('file_path', data.file_path);
					}
					
					if (typeof data.subject_path !== "undefined") {
						btn.data('subject_path', data.subject_path);
					}
					
				}).fail(function(  ) {
					console.log('ошибка');
					btns.removeClass('in-progress');
					mesage_area.html('<span class="has-error">Ошибка. Попробуйте позже</span>');
				});

				
			});
			
			
		});
	</script>
	
	
	
<?php endif;?>
<br />
<br />



