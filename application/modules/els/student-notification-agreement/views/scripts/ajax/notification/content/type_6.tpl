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
	
		#sna_popup .sna-btn-confirm-user-info{
			color: #1171b4;
			cursor: pointer;
			font-size:15px;
			font-weight: bold;
			
			.position: absolute;
			.bottom: 10px;
			.right: 10px;
			
			display: block;
			border: 2px solid #3192be;
			padding: 3px;
			border-radius: 3px;
			width: 106px;
			
			background-image: none;
			background-color: white;
		}
		
		#sna_popup .sna-btn-confirm-user-info:hover{
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
		}
		
		.ui-dialog-titlebar-close {
			display: block!important;
		}
		
		
		#sna_popup .sna-btn-postpone{
			color: #1171b4;
			cursor: pointer;
			font-size:15px;
			font-weight: bold;
			
			.position: absolute;
			.bottom: 10px;
			.right: 10px;
			
			display: block;
			border: 2px solid #3192be;
			padding: 3px;
			border-radius: 3px;
			width: 156px;
		}
		
		#sna_popup .sna-btn-postpone:hover{
			background-color: #3192be;
			color:white;
		}
	
	</style>


<div class="fin-notice" style="3width: 900px;">
	<p style="text-align: center; font-weight: bold;">УВАЖАМЫЕ СТУДЕНТЫ!</p>
	<p style="text-align: justify; font-weight: bold;">
		Информация: в РГСУ студентам очной, очно-заочной и заочной форм обучения (кроме обучающихся с применением  исключительно дистанционных технологий) реализована возможность 
		удаленного обучения с осуществлением образовательного процесса посредством программы Skype.
	</p>
	<p>
		Просим Вас подтвердить актуальность контактной информации или внести её.
	</p>
	<br />
	<p style="text-align: center; font-weight: bold;">
		АНКЕТА СТУДЕНТА:
	</p>
	<?php if(!$this->skype_confirmed):?>
		<div class="item-confirmed">
			<span class="sna-lbl-confirm-user-info" >Логин от Skype <span style="color:red;">*</span>:</span>
			<form method="POST" action="<?=$this->baseUrl($this->url(array('module' => 'confirm', 'controller' => 'user-info', 'action' => 'index')));?>" class="form-confirm-user-info" >
				<input type="text" name="skype" value="<?=$this->skype?>" class="sna-input"  >
				<input type="submit" value="Подтвердить" class="sna-btn-confirm-user-info">
			</form>
		</div>
	<?php endif;?>
	<?php if(!$this->phone_confirmed):?>
		<div class="item-confirmed">
			<span class="sna-lbl-confirm-user-info" >Актуальный контактный номер телефона <span style="color:red;">*</span>:</span>
			<form method="POST" action="<?=$this->baseUrl($this->url(array('module' => 'confirm', 'controller' => 'user-info', 'action' => 'index')));?>" class="form-confirm-user-info" >
				<input type="text" name="phone" value="<?=$this->phone?>" class="sna-input" >
				<input type="submit" value="Подтвердить" class="sna-btn-confirm-user-info">
			</form>
		</div>
	<?php endif;?>
	<?php if(!$this->email_confirmed):?>
		<div class="item-confirmed">
			<span class="sna-lbl-confirm-user-info" >Адрес электронной почты <span style="color:red;">*</span>:</span>
			<form method="POST" action="<?=$this->baseUrl($this->url(array('module' => 'confirm', 'controller' => 'user-info', 'action' => 'index')));?>" class="form-confirm-user-info" >
				<input type="text" name="email" value="<?=$this->email?>"  class="sna-input" >
				<input type="submit" value="Подтвердить" class="sna-btn-confirm-user-info">
			</form>
		</div>
	<?php endif;?>

	<p>
		<span style="color: red;">
			* Поля со звездочкой обязательны к заполнению.
		</span>
	</p>
	<br />
	<p>
		Актуальная информация по образовательной деятельности на канале YouTube 
		<a href="https://www.youtube.com/user/RGSUofficial" target="_blank">https://www.youtube.com/user/RGSUofficial</a> 
		(Ежедневные трансляции в 15:00)
	</p>
	<br />
	<div style="float: right;">
		<span class="sna-btn-postpone" style="display: block;" onClick="$('#sna_popup').dialog('close');" >Напомнить позднее</span>
	</div>
</div>


				
