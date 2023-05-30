<style>
	.fin-notice p {
		text-indent: 20px;
		padding-bottom: 0px;
    	margin-bottom: 0px;
    	margin-top: 0px;
    	padding-top: 0px;
	}
	.sna-btn-confirm{
		display:none!important;
	}
	
		#sna_popup .sna-btn-snils-inn{
			color: #1171b4;
			cursor: pointer;
			font-size:15px;
			font-weight: bold;
			
			display: block;
			border: 2px solid #3192be;
			padding: 3px;
			border-radius: 3px;
			width: 106px;
			
			background-image: none;
			background-color: white;
			text-align: center;
		}
		
		#sna_popup .sna-btn-snils-inn:hover{
			background-color: #3192be;
			color:white;
		}
		
		.sna-input{
			float: left;
			height: 18px;
			margin-right: 5px;
			padding: 3px;
			width: 34%;
		}
		
		.btn-in-progress{
			opacity: 0.5;
			pointer-events: none;
		}
		
		.sna-lbl-confirm-user-info{
			float: left;
			padding: 6px;
			width: 45%;
			text-align: right;
		}
		
		.item-confirmed{
			padding-bottom: 3px;
			clear: both;
		}
		
		#sna_popup .sna-btn-later {
			width: 150px;
		}
		
		.item-file-name{
			color: #3192be;			
		}
		
	</style>

<div class="fin-notice" style="3width: 900px;">
	<p style="text-align: center; font-weight: bold;">
		Уважаемый(ая) <?=$this->userName?>!
	</p>
	<p>
		Просим Вас заполнить информацию ниже, которая будет отображаться/не отображаться в приложении к диплому:
	</p>

	<br />	
	<form method="POST" 
		  action="<?=$this->baseUrl($this->url(array('module' => 'diplom', 'controller' => 'option', 'action' => 'confirm')));?>" 
		  class="form-snils-inn" >

		<div class="item-confirmed">
			<span style="font-weight: bold;">
				1. Сведения об освоении факультативных дисциплин:
			</span>
			<br />
			<label for="item-1_1"><input type="radio" name="q_1" id="item-1_1" value="1" required="required" >Указывать</label>			
			<label for="item-1_2"><input type="radio" name="q_1" id="item-1_2" value="0">Не указывать</label>

			<div style="padding-top: 10px;">
				<div style="font-size: 11px; text-align: left;">Пример заполнения приложения к диплому:</div>
				<img src="\upload\files\img\diplom\confirm-format\diplom_format_1.png" style="max-width: 580px; margin: 0 auto; display: block;">
			</div>
		</div>

		<div class="item-confirmed">
			<span style="font-weight: bold;">
				2.	Сведения о форме обучения (очная, очно-заочная, заочная):
			</span>
			<br />
			<label for="item-2_1"><input type="radio" name="q_2" id="item-2_1" value="1"  onChange="updateForm($(this))" required="required" >Указывать</label>			
			<label for="item-2_2"><input type="radio" name="q_2" id="item-2_2" value="0"  onChange="updateForm($(this))" >Не указывать</label>

			<div style="padding-top: 10px;">
				<div style="font-size: 11px; text-align: left;">Пример заполнения приложения к диплому:</div>
				<img src="\upload\files\img\diplom\confirm-format\diplom_format_2.png" style="max-width: 450px; margin: 0 auto; display: block;">
			</div>
		</div>

		<div class="item-confirmed">
			<span style="font-weight: bold;">
				3. Сочетание форм обучения (очная, очно-заочная, заочная):
			</span>
			<br />
			<label for="item-3_1"><input type="radio" name="q_3" id="item-3_1" value="1"  onChange="updateForm($(this))" >Указывать</label>			
			<label for="item-3_2"><input type="radio" name="q_3" id="item-3_2" value="0"  onChange="updateForm($(this))" >Не указывать</label>

			<div style="padding-top: 10px;">
				<div style="font-size: 11px; text-align: left;">Пример заполнения приложения к диплому:</div>
				<img src="\upload\files\img\diplom\confirm-format\diplom_format_3.png" style="max-width: 440px; margin: 0 auto; display: block;">
			</div>
		</div>

		<div class="item-confirmed">
			<span style="font-weight: bold;">
				4. Пройдено ускоренное обучение по образовательной программе:
			</span>
			<br />
			<label for="item-4_1"><input type="radio" name="q_4" id="item-4_1" value="1" required="required" >Указывать</label>			
			<label for="item-4_2"><input type="radio" name="q_4" id="item-4_2" value="0">Не указывать</label>

			<div style="padding-top: 10px;">
				<div style="font-size: 11px; text-align: left;">Пример заполнения приложения к диплому:</div>
				<img src="\upload\files\img\diplom\confirm-format\diplom_format_4.png" style="max-width: 450px; margin: 0 auto; display: block;">
			</div>
		</div>

		<div class="item-confirmed">
			<span style="font-weight: bold;">
				5. Сведения об обучении в другом вузе:				
			</span>
			<br />
			<label for="item-5_1"><input type="radio" name="q_5" id="item-5_1" value="1" required="required" >Указывать</label>			
			<label for="item-5_2"><input type="radio" name="q_5" id="item-5_2" value="0">Не указывать</label>
			<div style="font-size: 11px;">
				(Зачетные единицы, которые были перезачтены во время перевода из другой образовательной организации могут быть указаны в приложении к диплому)
			</div>
			<div style="padding-top: 10px;">
				<div style="font-size: 11px; text-align: left;">Пример заполнения приложения к диплому:</div>
				<img src="\upload\files\img\diplom\confirm-format\diplom_format_5.png" style="max-width: 550px; margin: 0 auto; display: block;">
			</div>
		</div>

		<input type="submit" value="Сохранить" class="sna-btn-snils-inn sna-btn-confirm-user-info" style="float: right;">
	</form>			
	<br />		
</div>

<script>
	function updateForm(el)
	{
		var form = el.closest('form');
		if(el.attr('name') == 'q_2'){
			form.find('[name="q_3"]').filter('[value=0]').prop('checked', true);
		}

		if(el.attr('name') == 'q_3'){
			form.find('[name="q_2"]').filter('[value=0]').prop('checked', true);
		}
	}
</script>