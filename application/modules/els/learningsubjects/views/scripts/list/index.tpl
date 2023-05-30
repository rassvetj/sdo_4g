<style>
    #textComment{
		width: 100%;
		height: 100px;
	}
	
	#btnSetComment span {
		color: #5ecff5;
	}
</style>
<?php if (!$this->gridAjaxRequest):?>
	<?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:learningsubjects:import:index')):?>
		<?php echo $this->Actions('learningsubjects');?>
	<?php endif;?>
	<?=$this->formButton('commentButton', _('Добавить комментарий'));?>
	<?php $this->inlineScript()->captureStart(); ?>
		function setComment(text){
			var arr = [];
			$("#grid table .checkboxes input[name='gridMassActions_grid']:checked").each(function( index ) {
				arr.push($( this ).val());			
			});
			$('#commentError').remove();
			$.ajax(<?= Zend_Json::encode( $this->baseUrl($this->url(array('module' => 'learningsubjects', 'controller' => 'list', 'action' => 'set-comment'))) ) ?>, {
				type: 'POST',
				global: false,
				dataType: 'json',
				data: {            
					ids: arr,
					text: text,					
				}
			}).done(function (data) {						
				if(data){
					if(data.error){ 		
						$('#textComment').after('<div id="commentError" style="color:red">'+data.error+'</div>');
					} else {						
						window.location.reload();						
					}
				}
			}).fail(function () {
				alert('<?= _('Произошла ошибка. Попробуйте ещё раз'); ?>');				
			}).always(function () {
				
			});			
		}
	 
		$( "#learningsubjects-comment-dialog" ).dialog({
			autoOpen: false,
			resizeable: false,
			width: 300,
			modal: true,        
		});

		$('#commentButton').click(function () {       
			$("#learningsubjects-comment-dialog").dialog('open');
		});
	<?php $this->inlineScript()->captureEnd(); ?>
	<div id="learningsubjects-comment-dialog" title="<?php echo _("Комментарии"); ?>">
		<div class="textarea-wrapper">
			<textarea id="textComment" name="comment"></textarea>
			<br />
			<button onClick="
			if($('#textComment').val() == ''){
				if (confirm('Поле комментарий не заполнено. Произойдет удаление комментария. Продолжить?')) {
					setComment($('#textComment').val());
				} 
			} else {
				setComment($('#textComment').val());
			}
			" id="btnSetComment"><?=_('Сохранить')?></button>
		</div>
	</div>
	<br />	
	<span><?=_('Для вывода данных нажмите кнопку "Искать"')?></span>
<?php endif;?>
<?=$this->grid?>
