<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/score.css'); ?>
<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/test.css'); ?>
<style>
	#els-extended-group {
		overflow: auto;
		max-height: 540px;
	}
	
	#els-extended-group table tr.active {	
		background-color: rgb(223, 231, 243)!important;
	}
</style>
<script>
    $(document).ready(function(){
        var filter = $('#els-extended-group-filter');
        var group = $('#els-extended-group');
        var content = $('#els-extended-content');
        var defaultContent = content.html();

        var lastGroupSelect = $.cookie('lastGroupSelect');

        filter.on('change', function(){
            var filterVal = $(this).val();
            $.cookie('lastGroupSelect',filterVal);

            $('input[name="group_id"]').each(function(){
                var $tr = $(this).parents('tr:first');
                content.html(defaultContent);
                group.find('.active').removeClass('active');
                if (filterVal == 'show_all') {
                    $tr.show();
                } else {
                    if (filterVal == 'show_new') {
						if($tr.hasClass('is_new') === true){
							$tr.show();
						} else {
							$tr.hide();
						}						
					} else {					
						if ($(this).val().indexOf(','+filterVal+',', 0) >= 0) {
							$tr.show();
						} else {
							$tr.hide();
						}
					}
                }
            });
        });

        if (lastGroupSelect = $.cookie('lastGroupSelect')) {
            filter.val(lastGroupSelect).trigger('change');
        } else {
            var firstVal = filter.children('option:first').val();
            filter.val(firstVal).trigger('change');
            $.cookie('lastGroupSelect',0);
        }

        $('.els-extended-user-interview').on('click', function(e){
            e.preventDefault();
            var url = $(this).attr('href');
            group.find('.active').removeClass('active');
            $(this).parents('tr:first').addClass('active');
            content.html('<p><?=_('Загрузка...')?></p>').load(url);
        });
    });
</script>

<div style="padding-bottom: 15px;">
<?php if($this->showGraduated):?>
	<a href="<?=$this->urlTypeStudents?>"><?=_('Стандартное отображение')?></a>
<?php else:?>
	<a href="<?=$this->urlTypeStudents?>"><?=_('Показать студентов в завершенных')?></a>
<?php endif;?>
</div>

<div class="els-extended-users els-scloll">
    <select id="els-extended-group-filter">
        <?php foreach($this->groups as $key => $value) {
            if ($value['new_count'] > 0) {
                echo '<option style="color: red;" value="'.$key.'">'.$value['name'].' (New +'.$value['new_count'].')'.'</option>';
            } else {
                echo '<option style="color: black;" value="'.$key.'">'.$value['name'].'</option>';
            }
        }
        ?>
    </select>

    <div id="els-extended-group">
        <table width="100%">
            <?php foreach($this->users as $user) : ?>
                <tr style="display: none;" class="<?=($user['is_new']) ? ('is_new') : ('');?>">
                    <td>
                        <input type="hidden" name="group_id" value="<?php echo ','.implode(',',$user['groups']).','; ?>">
                        <?php echo $user['card']; ?>
                    </td>
                    <td>
                        <a class="els-extended-user-interview" href="<?php echo $user['url']; ?>"><?php echo $user['fio']; ?></a>&nbsp;<?php if ($user['is_new']) echo '<b><sup style="font-size: 0.8em; color: red;">New</sup></b>'; ?>
                        <br/>
                        <b class="els-extended-user-variant"><?php echo $user['interview_title']; ?></b>
                    </td>
                    <td>					
                        <div class="<?php echo ($user['mark'] > -1) ? 'score_red' : 'score_gray'; ?> number_number">
                            <span align="center"><?php echo ($user['mark'] > -1) ? round($user['mark'], 2) : _('Нет'); ?></span>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>


<?php if(!$this->readOlny):?>
<?php if($this->isShowAttemptButton) : ?>
	<form style="text-align: right;" id="newAttemptFormGroup" enctype="multipart/form-data" method="post" action="<?=$this->url(array('module' => 'interview', 'controller' => 'attempt', 'action' => 'add-by-group'), null, false);?>">
		<input type="hidden" name="group_id"   value="0" >
		<input type="hidden" name="group_name" value="0" >
		<input type="hidden" name="lesson_id"  value="<?=$this->lesson_id ?>">
		<input type="hidden" name="subject_id"  value="<?=$this->subject_id ?>">
		<input type="submit" name="button" 	   class="btn" value="Добавить попытку группе:" disabled >
		<span class="attempt-group-name" data-default-text="<?=_('Выберите группу')?>" ></span>
	</form>
	

	<script>
	$( document ).ready(function() {
		
		$('#els-extended-group-filter').change(function(){
			var formA 		= $('#newAttemptFormGroup');
			var group_id 	= $('#els-extended-group-filter').val();
			var group_name	= $( "#els-extended-group-filter option:selected" ).text();
			
			if(group_id < 1 || group_id == 'show_all' || group_id == 'show_new'){
				formA.find('.btn').prop('disabled', true);
				group_id 	= 0;
				group_name 	= formA.find('.attempt-group-name').data('default-text');
			} else {
				formA.find('.btn').prop('disabled', false);	
			}
			
			formA.find('input[name="group_id"]').val( group_id );
			formA.find('input[name="group_name"]').val( group_name );
			formA.find('.attempt-group-name').html( group_name );			
		});
		
		$('#els-extended-group-filter').change();
		
		$( "#dialog-confirm-attempt-group" ).dialog({
			resizable: false,
			autoOpen: false,
			height:180,
			modal: true,
			buttons:
			{
				<?php echo _('Да')?>: function() {
					$( this ).dialog( "close" );
					$("#newAttemptFormGroup").submit();									
				},
				<?php echo _('Нет')?>: function() {
					$( this ).dialog( "close" );
				}
			}
		});
			
		$('#newAttemptFormGroup .btn').click(function() {
			$( "#dialog-confirm-attempt-group" ).dialog( "open" );
			return false;			
		}); 
		
		
		$("#newAttemptFormGroup").submit(function( event ) {
			
			event.preventDefault();		
			var message_id = 'error-box';
			var progress   = '<span class="spinner-progress"> <b>Выполнение...</b></span>';
			
			var form = $(this);
			
			var btn = form.find('[type="submit"]');
			
			btn.prop('disabled', true);	
			$('.spinner-progress').remove();
			$('.attempt-group-name').after(progress);
			
			var jqxhr = $.ajax({				
				url:  form.attr('action'),
				type: 'POST',
				data: form.serialize(),	
				dataType: 'json',
			});
			
			jqxhr.done(function(data) {
				var msg = '';
				var lvl = 'error';
				
				if(typeof data.message !== "undefined"){
					msg = data.message;
					lvl = 'success';
				}
				
				if(typeof data.error !== "undefined"){
					msg = data.error;	
				}
				
				var $message = jQuery("<div>"+msg+"</div>").appendTo('#' + message_id);
				jQuery.ui.errorbox.clear($message);
				$message.errorbox({level: lvl});
				
				btn.prop('disabled', false);
				$('.spinner-progress').remove();
			});
			
			jqxhr.fail(function() {				
				var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + message_id);
				jQuery.ui.errorbox.clear($message);
				$message.errorbox({level: 'error'});
				btn.prop('disabled', false);	
				$('.spinner-progress').remove();
			});
			
			event.preventDefault();	
			
		});		
		
	});
	
	</script>
	<div id="dialog-confirm-attempt-group" title="Подтверждение действия">
		<p><span style="float: left; margin: 0 7px 20px 0;"><?= _('Вы действительно желаете добавить попытку за данное занятие всем студентам группы? Студенты смогут повторно прикрепить задание.') ?></span></p>
	</div>
<?php endif; ?>
<?php endif; ?>



<div id="els-extended-content" style="">
    <div class="els-extended-default">
        <p><?=_('Нет данных для отображения.')?></p><br>
        <p><?=_('Необходимо выбрать пользователя в меню слева.')?></p>
    </div>
</div>