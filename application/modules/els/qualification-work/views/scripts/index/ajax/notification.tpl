<div id="qw_area" >
	<style>
		#qw_popup{
			font-size: 14px;
			text-align: justify;
		}
		
		#qw_popup p {
			padding-bottom: 4px;
			text-indent: 20px;
		}
		
		#qw_popup textarea{
			width: 98%;
		}
		
		#qw_popup .qw-btn-show-form{
			color: #1171b4;
			cursor: pointer;
			font-size:15px;
			font-weight: bold;
			
			position: absolute;
			bottom: 10px;
			right: 10px;
			
			display: block;
			border: 2px solid #3192be;
			padding: 3px;
			border-radius: 3px;
		}
		
		#qw_popup .qw-btn-show-form:hover{
			background-color: #3192be;
			color:white;
		}
		
		
		#qw_popup #submit{
			color: #5ecff5;
			font-size:11px;			
		}
		
		#qw_popup #qualification_work_agreement_confirm dd {
			float: left;
			padding-right: 10px;
		}
		
		#qw_popup #qualification_work_agreement_confirm dd label {
			vertical-align: -webkit-baseline-middle;			
		}
		
		#qw_popup .qw-form-area {
			line-height: 20px;			
		}
		
		
		
		
	</style>
	<div id="qw_popup" title="Подтверждение ВКР">
		<div class="qw-text-area">
			<div style="text-align: center; padding-bottom: 4px;">
				<b>УВЕДОМЛЕНИЕ</b>
			</div>
			<div style="text-align: center; padding-bottom: 4px;">
				Уважаемый(ая) <?=$this->fio?>!
			</div>		
			<p>
				Информируем Вас, что приказом РГСУ Вам утверждена следующая тема выпускной квалификационной работы 
				&laquo;<?=$this->theme?>&raquo;
				и назначен руководитель выпускной квалификационной работы
				<?=$this->manager?>.
			</p>
			<p>
				В случае несоответствия темы выпускной квалификационной работы и ФИО руководителя выпускной квалификационной работы просим Вас 
				<b>в срок до 20 января 2020 года</b> сообщить об этом. 
			</p>
			<br />					
			<?=$this->form_confirm?>
			<br />
			<span class="qw-btn-show-form">Сообщить</span>	
		</div>
		
		<div style="display:none;" class="qw-form-area">
			<span class="qw-btn-show-form">Назад</span>			
			<b>Студент</b>: <?=$this->fio?>
			<br />
			<b>Тема ВКР</b>: <?=$this->theme?>
			<br />
			<b>Руководитель</b>: <?=$this->manager?>
			<br />
			<br />
			<?=$this->form?>
		</div>	
		
	</div>
	<? # скрипты лежат тут: \public\themes\rgsu\js\common.js ?>
</div>


