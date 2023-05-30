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
	
	.form-student-info {		
		color: #0067a4;
		.padding-left: 10px;
		font-weight: bold;
	}
	
	.selected-user{
		background-color: #bfd9e6b0;
	}
	
	.btn-show-user-data-area{
		cursor: pointer;
	}
</style>
<script>
	function setDefaultContent()
	{
		var content = $('#els-extended-content');
		content.find('.els-extended-default').removeClass('hidden');
		content.find('.els-extended-form-area').addClass('hidden');
	}

    $(document).ready(function(){
        var filter = $('#els-extended-group-filter');
        var group = $('#els-extended-group');
        //var content = $('#els-extended-content');
        //var defaultContent = content.html();

        var lastGroupSelect = $.cookie('lastGroupSelect');

        filter.on('change', function(){
            var filterVal = $(this).val();
            $.cookie('lastGroupSelect',filterVal);

            $('input[name="group_id"]').each(function(){
                var $tr = $(this).parents('tr:first');
                setDefaultContent();
				//content.html(defaultContent);
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
		
		<?/*
        $('.els-extended-user-interview').on('click', function(e){
            e.preventDefault();
            var url = $(this).attr('href');
            group.find('.active').removeClass('active');
            $(this).parents('tr:first').addClass('active');
            content.html('<p><?=_('Загрузка...')?></p>').load(url);
        });
		*/?>

		$('.btn-show-user-data-area').click(function(){
			$(this).find('.btn-show-user-data').click();
		});
		
		$('.btn-show-user-data').on('click', function(e){
			var btn 		= $(this);
			var student_fio = btn.data('student_fio');
			var student_id 	= btn.data('student_id');
			
			$('.els-extended-default').addClass('hidden');
			$('.els-extended-form-area').removeClass('hidden');
			
			
			$('.form-student-info').remove();
			var html = '<span class="form-student-info">.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Студент: ' + student_fio + '</span>';
			$('#fieldset-mark legend').append(html);
			
			$('.els-extended-form-area form #student_id').val(student_id);
			
			btn.closest('table').find('tr').removeClass('selected-user');
			btn.closest('tr').addClass('selected-user');
			
			return false;			
		});
		
		$("[name='range_mark']").change(function(){    	
			var el 			 = $("[name='range_mark']:checked");
			var markSelected = el.val();
			var id			 = el.attr('id');
			
			if (typeof markSelected != "undefined"){			
				var ballListScales = JSON.parse('<?=Zend_Json::encode(HM_Interview_InterviewModel::getBallListScales()); ?>');
				if (typeof ballListScales[markSelected] != "undefined"){
					$("#ball").empty();		
					var maxBall = 0;
					$.each(ballListScales[markSelected], function(i, value) {										
						if(maxBall < value) { maxBall = value; }
						$("#ball").append($('<option>').text(value).attr('value', value));
					});
					if(id == 'range_mark-2'){
						maxBall = 10;
					}
					$("#ball").val(maxBall);
					
					el.closest('form').find('[type="submit"]').prop('disabled', false);					
				}
			}		
		}).change();


		$('.els-extended-form-area form').submit(function(e) {
			
			e.preventDefault(); 
			
			var form 				= $(this);			
			var btn 				= form.find('[type="submit"]');
			var error_box 			= $('#error-box');
			var student_id 			= form.find('[name="student_id"]').val();
			var ball_area_student 	= $('.ball\-area\-student\-' + student_id);
			
			error_box.html('');
			btn.prop('disabled', true);			
			
			$.ajax({
				type	: 'POST',
				url		: form.attr('action'),
				data	: form.serialize(),
				dataType: 'json',
				success	: function(data){
					
					btn.prop('disabled', false);
					
					message 		= '';
					message_type	= 'success';
					
					if (typeof data.error !== "undefined") {
						message_type = 'error';
					} else {
						formReset();						
					}
					
					if (typeof data.message !== "undefined") {
						message = data.message;
					}
					
					
					if (typeof data.additional !== "undefined") {
						if (typeof data.additional.ball_new !== "undefined") {
							ball_area_student.html(data.additional.ball_new);
							ball_area_student.closest('.number_number').addClass('score_red').removeClass('score_gray');
							
						}
					}
					
					
					
					var $message = jQuery('<div>'+message+'</div>');
					jQuery.ui.errorbox.clear($message);
					$message.errorbox({level: message_type});
					
					
				},
				error: function (xhr, ajaxOptions, thrownError) {
					
					btn.prop('disabled', false);
					
					message 		= 'Произошла ошибка. Попробуйте позже';
					message_type	= 'error';
					var $message = jQuery('<div>'+message+'</div>');
					jQuery.ui.errorbox.clear($message);
					$message.errorbox({level: message_type});
					
					
				}
			});	
			
			
			
			
			
		});
		
		function formReset()
		{
			//var form	= $('.els-extended-form-area form');
			//var btn		= form.find('[type="submit"]');
			//btn.prop('disabled', true);
			//form[0].reset();
		}
	
	
	
		
    });
</script>

<div class="els-extended-users els-scloll">
    <select id="els-extended-group-filter">
        <?php foreach($this->groups as $key => $value): ?>
			<option style="color: black;" value="<?=$key?>"><?=$value['name']?></option>            
        <?php endforeach;?>        
    </select>

    <div id="els-extended-group">
        <table width="100%">
            
			<?php foreach($this->users as $user) : ?>
                
				<tr style="display: none;">
                    <td>
                        <input type="hidden" name="group_id" value="<?=(','.implode(',',$user['groups']).',')?>">
                        <?=$user['card']; ?>
                    </td>
                    <td class="btn-show-user-data-area">
                        <a	class="els-extended-user-interview btn-show-user-data" 
							href="<?=$user['url'];?>"
							data-student_fio="<?=$user['fio']; ?>"
							data-student_id="<?=$user['user_id']; ?>"
						>							
							<?=$user['fio']; ?>							
						</a>
					</td>                    
                    <td>					
                        <div class="<?=(($user['mark'] > -1) ? 'score_red' : 'score_gray')?> number_number">
                            <span align="center" class="ball-area-student-<?=$user['user_id']; ?>">
								<?=(($user['mark'] > -1) ? round($user['mark'], 2) : _('Нет'))?>
							</span>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
			
        </table>
    </div>
</div>

<div id="els-extended-content" style="">
    <div class="els-extended-default">
        <p><?=_('Нет данных для отображения.')?></p><br>
        <p><?=_('Необходимо выбрать пользователя в меню слева.')?></p>
    </div>
	<div class="els-extended-form-area hidden">
		<?=$this->form?>
	</div>
</div>
