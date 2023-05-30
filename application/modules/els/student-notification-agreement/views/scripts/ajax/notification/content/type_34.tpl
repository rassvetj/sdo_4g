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
		Уважаемые студенты!
	</p>
	<p style="text-align: justify; font-weight:bold;">
		От Вас не получены данные о СНИЛС.	
	</p>
	<p style="text-align: justify; font-weight:bold;">
		Без этих персональных данных информация о выданном Вам документе об образовании не может быть передана по защищенным каналам для размещения в Федеральном реестре сведений о документах об образовании.		
	</p>
	<p style="text-align: justify;">
		При обращении к ресурсу для проверки действительности диплома указанные персональные данные отображаться 
		<span style="text-decoration:underline;">не будут</span>
		.
	</p>	
	<p style="text-align: justify; font-weight:bold; color:red;">
		В случае непредставления данных личный кабинет будет заблокирован до момента внесения информации о СНИЛС.
	</p>
	<p style="text-align: justify;">
		Для корректной загрузки данных в Федеральный реестр сведений о документах об образовании, просим Вас предоставить персональные данные о Страховом номере индивидуального лицевого счёта (СНИЛС).
	</p>
	<br />	
	<form method="POST" action="<?=$this->baseUrl($this->url(array('module' => 'confirm', 'controller' => 'user-info', 'action' => 'additional')));?>" class="form-snils-inn" >
	<?php if($this->snils):?>
		<div class="item-confirmed">
			<span class="sna-lbl-confirm-user-info" >Ваш СНИЛС:</span>
			<input type="text" value="<?=$this->snils?>" class="sna-input" disabled readonly >
		</div>
	<?php else:?>
		<div class="item-confirmed">
			<span class="sna-lbl-confirm-user-info" >СНИЛС:</span>			
			<input type="text" name="snils" value="<?=$this->snils?>" class="sna-input mask-snils" maxlength="14"   >
		</div>
	<?php endif;?>

	<?php if($this->snils_file):?>		
		<div class="item-confirmed">
			<span class="sna-lbl-confirm-user-info" >Ваш файл СНИЛС:</span>			
			<span style="display: block; padding: 5px;"><?=$this->snils_file->getName()?></span>
		</div>
	<?php else:?>
		<div class="item-confirmed">
			<p>
				Вы знаете свой СНИЛС - прикрепите скан/фото Страховой номер индивидуального лицевого счёта, либо Уведомления о регистрации в системе индивидуального (персонифицированного учета).	
			</p>			
			<div class="item-file-name"></div>
			<input name="snils_file" class="snils_file" type="file" style="display:none;" onChange="updateFieldFile($(this));" >
			<span class="sna-btn-snils-inn" onClick="$('.snils_file').click();">Прикрепить</span>			
		</div>
		
		<div class="item-confirmed">
			<p>
				Вы НЕ знаете свой СНИЛС:
				<ol>
					<li>
						Узнать свой СНИЛС можно в личном кабинете гражданина на сайте ПФР 
						<a href="http://www.pfrf.ru/knopki/online_kons/~4417" target="_blank" >http://www.pfrf.ru/knopki/online_kons/~4417</a>
						в разделе Популярные сервисы - Жизненные ситуации или в мобильном приложении «Электронные сервисы ПФР» в разделе профиль пользователя. 
					</li>
					<li>
						СНИЛС можно узнать через сайт «Госуслуги», но при одном условии – он был введен пользователем на сайте ранее в соответствующем разделе. 
						Если цифры не вводились самостоятельно, то доступны автоматически они не будут.
					</li>
				</ol>
			</p>
		</div>
	<?php endif;?>
		<input type="submit" value="Сохранить" class="sna-btn-snils-inn sna-btn-confirm-user-info" style="float: right;">
	</form>			
	<br />		
</div>

<script>
function updateFieldFile(el)
{
	var filename    = el.val().split('\\').pop();
	var destination = el.closest('.item-confirmed').find('.item-file-name');
	if(filename != '' && typeof filename !== "undefined" ){
		destination.html(filename);
	}
}
</script>