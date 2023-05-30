<div>	
	<table>
		<tr>
			<th><?=_('Заявка')?></th>
			<td>#<?=$this->claim['id']?></td>
		</tr>
		<tr>
			<th><?=_('Название')?></th>
			<td><?=$this->club['name']?></td>
		</tr>
		<tr>
			<th><?=_('Группа')?></th>
			<td><?=$this->claim['group_name']?></td>
		</tr>
		<tr>
			<th>E-mail</th>
			<td><?=$this->claim['email']?></td>
		</tr>
		
		<tr>
			<th><?=_('Факультет')?></th>
			<td><?=$this->club['faculty']?></td>
		</tr>
		<tr>
			<th><?=_('Организатор')?></th>
			<td><?=$this->club['organizer']?></td>
		</tr>
		<tr>
			<th><?=_('Руководитель')?></th>
			<td><?=$this->club['manager']?></td>
		</tr>
		<?php if(!empty($this->claim['date_created'])):?>
		<tr>			
			<th><?=_('Создана')?></th>
			<td><?=$this->claim['date_created']?></td>
		</tr>
		<?php endif;?>
		<tr>
			<th><?=_('Дополнительно')?></th>
			<td></td>
		</tr>					
		<tr>
			<td colspan="2"><?=$this->club['description']?></td>
		</tr>
		
	</table>
	<?php if ($this->isPeriodAvailable):?>
		<a href="<?=$this->baseUrl($this->url(array('module' => 'club', 'controller' => 'index', 'action' => 'remove-claim'))) ?>" class="btn-remove-claim ui-button ui-widget ui-state-default ui-corner-all">Отменить заявку</a>
	<?php endif;?>
</div>
<?php if ($this->isPeriodAvailable):?>
	<script>
		$( document ).ready(function() {
			$('#dialog-cremove-claim').dialog({
				resizable: false,
				autoOpen: false,
				height:180,
				modal: true,
				buttons:
				{
					<?php echo _('Да')?>: function() {
						$( this ).dialog( "close" );
						document.location.href = $('.btn-remove-claim').attr('href');
					},
					<?php echo _('Нет')?>: function() {
						$( this ).dialog( "close" );
					}
				}
			});
				
			$('.btn-remove-claim').click(function() {
				$('#dialog-cremove-claim').dialog('open');
				return false;			
			});
		});	
	</script>
	<div id="dialog-cremove-claim" title="Подтверждение действия">
			<p><span style="float: left; margin: 0 7px 20px 0;">Вы действительно хотите удалить заявку?</span></p>
	</div>
<?php endif;?>
