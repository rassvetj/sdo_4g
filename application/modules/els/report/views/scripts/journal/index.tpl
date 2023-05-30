<style>
.accordion-data ol{
    list-style: none;
	margin-left: 20px;
}
.accordion-data ol > li{
    padding-left: 20px;
    position: relative;
    margin: 10px;
}
.accordion-data ol > li:before{
    content: '✔';
    position: absolute; top: 0; left: 0;    
}

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

.main-grid thead th {
    text-align: center;
	font-size: 11px;
	height: 45px;
}

table.thead.tr.th.date-caption {
    font-size: 12px;
    font-weight: bold;
}

.is-be-yes {
	color: green;
}
.is-be-no {
	color: red;
}
</style>

<?php $content_area_id 	= 'content-area'; ?>
<?php $form_area_id 	= 'form-area'; ?>
<?php if ($this->gridAjaxRequest):?>
	
<?php else : ?>
	<style>
		.no_students {
			color: pink;
			font-weight: bold;
		}
		.btn-rules{
			cursor: pointer;
			font-weight:bold;			
		}
		
		.content-rules{
			padding-bottom: 15px;
		}
		.isGraduated {
			color: red;
		}	
	</style>
	<div style="font-size: 13px;">
		<div class="item-rules" >
			<span  class="btn-rules">Условия формирования</span>
			<div class="hidden content-rules">
				<ol>
					<li>Все сессии, в которых есть занятие с типом "Журнал"</li>
					<li>Все студенты, которые назначены или в завершенных</li>
					<li>Cтуденты, выделенные <span class="isGraduated">красным цветом</span>, завершили курс</li>
					<li>Приоритет фильтров следующий: Сессия -> Группа -> План</li>
					<li>Ex: Если выбрана сессия, выбранные группа и учебный план игнорируются</li>
					<li>Ex: Если выбрана группа, выбранный учебный план игнорируются</li>
				</ol>
			</div>
		</div>
	</div>
	<br />
	<div class="default-form-area form-report-conditions"  id="<?=$form_area_id;?>">
		<?=$this->form;?>
	</div>
	<br>
	<br>
	<div id='content-area'>		
	</div>
	<?php $this->inlineScript()->captureStart()?>	
		$('body').on('click', '.btn-accordion', function(event) {
			event.preventDefault();
			var container = $(this).closest('.accordion-container');
			if ( container.hasClass('open')){
				container.removeClass('open');
			} else {
				container.addClass('open');
			}
		});
	
		$('#programmId').select2();
		$('#groupId').select2();
		initSubjectList($('#subjectId'));
		
		$('.btn-rules').click(function(){			
			var content = $(this).closest('.item-rules').find('.content-rules');
			if(content.hasClass('hidden')){
				content.removeClass('hidden');
			} else {
				content.addClass('hidden');				
			}
			return false;
		});
	
		$(document.body).delegate('#<?=$form_area_id;?> form', 'submit', _.debounce(function (event) {
			var form_id 	= $(this).attr('id');
			var form_action = $(this).attr('action');
			var form_data 	= $(this).serialize();	
			$('#'+form_id+' #submit').prop('disabled', true);	
			
			$.ajax(form_action, {
					type: 'POST',
					global: false,
					data: form_data,				
				}).done(function (data) {		
					_.defer(function () {					
						$('#<?=$content_area_id;?>').html('');
						$('#<?=$content_area_id;?>').append(data);									
						$('#'+form_id+' #submit').prop('disabled', false);											
					});
				}).fail(function () {				
					var $message = jQuery("<div><?= _('Произошла ошибка. Попробуйте ещё раз'); ?></div>").appendTo('#' + form_id);
					jQuery.ui.errorbox.clear($message);
					$message.errorbox({level: 'error'});
					$('#'+form_id+' #submit').prop('disabled', false);
				}).always(function () {				
			});
			
		}, 50));

		$(document.body).delegate('#<?=$form_area_id;?> form', 'submit', function(event) {
			event.preventDefault();
		});	
	<?php $this->inlineScript()->captureEnd()?>
<?php endif;?>