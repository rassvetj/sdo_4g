<style>
	.block-internships {
		max-width: 400px;		
		width: 100%;
	}
	
	.block-internships select{
		height: 30px;
		font-size: 13px;
		border-radius: 4px;
		width: 100%;
	}
	
	.block-internships input{
		width: 100%;
		height: 25px;
		font-size: 13px;
		border-radius: 4px;		
	}
	
	.block-internships [type="text"]{
		width: 99%;
		box-shadow: none;
		border: 1px solid #aaaaaa;
		text-indent: 5px;
	}
	
	
	
	.block-internships .select2-container {
		font-size: 13px;
	}
	
	.block-internships .select2-selection--single{
		height: 30px;
	}
	
	.block-internships [type="submit"]{
		width: 85px;
	}
	
	.block-internships #type{
		width: 99%
	}
	
	
	
	
	
	.block-internships .lbl{
		font-size: 13px;
	}
	
	.block-internships .f-item {
		padding-top: 10px;
	}
	
	.block-internships .f-item-half{
		width: 49%;
		float: left;	
		padding-right: 1%;
	}
	
	.block-internships .language_row{
		font-size: 13px;
		padding-left: 20px;
	}
	
	.block-internships .language_row .remove_language_row{
		float: right;
		padding-right: 5px;
	}
	
	.hidden{
		display: none;
	}

	.internships-description {
		font-size: 13px;
		text-align: justify;
	}
	
	.description_full p {
		text-indent: 0px;
	}
</style>
<div class="internships-description">

	<?=$this->description?>
	
	<br />
	<p style="font-style: italic;">
		Для участия в конкурсе просьба заполнить заявку:
	</p>
	
	
	
</div>

<div class="block-internships form-area-default">
	<form id="<?=$this->form->getId()?>" enctype="<?=$this->form->getEnctype()?>" method="<?=$this->form->getMethod()?>" action="<?=$this->form->getAction()?>">
		<div class="f-item">
			<span class="lbl">
				<?=$this->form->getElement('type')->getLabel();?> <span class="required-star">*</span>
			</span>
			<?=$this->form->getElement('type')->removeDecorator('label');?>
		</div>
		<div class="f-item">
			<span class="lbl">
				<?=$this->form->getElement('fio')->getLabel();?> <span class="required-star">*</span>
			</span>
			<?=$this->form->getElement('fio')->removeDecorator('label');?>
		</div>
		<div class="f-item f-item-half">
			<span class="lbl">
				<?=$this->form->getElement('phone')->getLabel();?> <span class="required-star">*</span>
			</span>
			<?=$this->form->getElement('phone')->removeDecorator('label');?>
		</div>
		<div class="f-item f-item-half">
			<span class="lbl">
				<?=$this->form->getElement('email')->getLabel();?> <span class="required-star">*</span>
			</span>
			<?=$this->form->getElement('email')->removeDecorator('label');?>
		</div>
		<div class="f-item" style="float: left; width: 100%; clear: both;" >
			<div class="selected_language_area" >
				<span class="lbl">
					<?=_('Выбранные языки:')?>
				</span>			
			</div>
		</div>
		<div class="f-item f-item-half">
			<span class="lbl">
				<?=$this->form->getElement('language_list')->getLabel();?> <span class="required-star">*</span>
			</span>
			<?=$this->form->getElement('language_list')->removeDecorator('label');?>
		</div>
		<div class="f-item f-item-half">
			<span class="lbl">
				<?=$this->form->getElement('degree_list')->getLabel();?> <span class="required-star">*</span>
			</span>
			<?=$this->form->getElement('degree_list')->removeDecorator('label');?>
		</div>
		<div class="f-item">
			<?=$this->form->getElement('submit');?>
		</div>
	</form>
</div>
<br />
<br />
<br />
<script>
	$( document ).ready(function() {
		
		updateLanguageList();
		updateTypeDescription();
		
		$('#language_list').select2();
		$('#degree_list').select2();

		$('body').on('change', '#language_list', function(e) {
			var val = $(this).val();
			if(val == '' || val <= 0)	{	
				$('#degree_list').prop('disabled', true);				
			} else {
				$('#degree_list').prop('disabled', false);
			}
			$('#degree_list').val('');
			$('#degree_list').trigger('change');
		});
		
		
		$('body').on('change', '#degree_list', function(e) {
			var val = $(this).val();
			if(val == '' || val <= 0)	{
				
			} else {
				addLanguageRow();
				updateLanguageList();
			}
		});
		
		
		$('body').on('change', '#type', function(e) {
			updateTypeDescription();
		});
		
		$('body').on('click', '.remove_language_row', function(e) {		
			$(this).closest('.language_row').remove();
			updateLanguageList();
			return false;
		});
		
		
		$('body').on('submit', '.form-area-default form', function(e) {
			e.preventDefault();			
			var form = $(this);
			var btns = form.find('input[type="submit"]');
			btns.prop('disabled', true);
			
			$.ajax({
				type	: 'POST',
				url		: form.attr('action'),
				data	: form.serialize(),
				dataType: 'json',
				success	: function(data){
					
					message 		= '';
					message_type	= 'success';
					
					if (typeof data.error !== "undefined") {
						message_type = 'error';
					} else {
						resetForm();
					}
					
					if (typeof data.message !== "undefined") {
						message = data.message;
					}
					
					var $message = jQuery('<div>'+message+'</div>');
					jQuery.ui.errorbox.clear($message);
					$message.errorbox({level: message_type});
					
					btns.prop('disabled', false);
				},
				error: function (xhr, ajaxOptions, thrownError) {
					btns.prop('disabled', false);
					message 		= 'Произошла ошибка. Попробуйте позже';
					message_type	= 'error';
					var $message = jQuery('<div>'+message+'</div>');
					jQuery.ui.errorbox.clear($message);
					$message.errorbox({level: message_type});
					
					btns.prop('disabled', false);
				}
			});			
		});
		
		
		function addLanguageRow()
		{
			var language_id = $('#language_list').val();
			var language_name = $('#language_list option:selected').text();
			
			var degree_id	= $('#degree_list').val();
			var degree_name = $('#degree_list option:selected').text();
			
			// чтобы не было одного языка с разными степенями знания
			$('#language_row_'+language_id).remove();
			
			html = '<div id="language_row_' + language_id + '" class="language_row">'					
					+ '<input type="hidden" name="degree[' + language_id +']"   value="' + degree_id + '" >'
					+ '' + language_name + ', ' + degree_name
					+ '&nbsp;<a href="#" class="remove_language_row">Удалить</a>'
				 + '</div>';				 
			$('.selected_language_area').append(html);
			
		}
		
		
		function updateLanguageList()
		{
			if($('.selected_language_area .language_row').length > 0){
				$('.selected_language_area').closest('.f-item').removeClass('hidden');
			} else {
				$('.selected_language_area').closest('.f-item').addClass('hidden');
			}
		}

		function updateTypeDescription()
		{
			var type_id	= $('#type').val();
			var country	= $('#type option:selected').text();
			var caption		= 'Стажировка в ' + country;			
			$('#page-title').html(caption);
			
			$('.description_full').addClass('hidden');
			$('.description_' + type_id).removeClass('hidden');
			
		}
		
		function resetForm()
		{
			$('.selected_language_area .language_row').remove();
			$('#internship')[0].reset();
			$('#internship select').trigger('change');
		}
		
		

	});
</script>