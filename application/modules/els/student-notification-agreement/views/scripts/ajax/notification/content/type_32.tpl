<style>
	.fin-notice p {
		text-indent: 20px;
		padding-bottom: 0px;
    	margin-bottom: 0px;
    	margin-top: 0px;
    	padding-top: 0px;
	}
		
	.ui-dialog-titlebar-close {
		display: block!important;
	}
		
	#sna_popup .sna-btn-postpone{
		color: #1171b4;
		cursor: pointer;
		font-size:15px;
		font-weight: bold;
			
		display: block;
		border: 2px solid #3192be;
		padding: 3px;
		border-radius: 3px;
		width: 103px;
	}
		
	#sna_popup .sna-btn-postpone:hover{
		background-color: #3192be;
		color:white;
	}	
	
	.sna-btn-confirm {
		display:none!important;
	}
</style>


<div class="fin-notice" style="3width: 900px;">
	<p style="text-align: center; font-weight: bold;">Уважаемый студент!</p>
	<p style="text-align: justify;">
		Вы поданы на отчисление. 
	</p>
	<p>
		Вам необходимо обратиться в Централизованный деканат на электронную почту 
		<a href="mailto:dekanat@rgsu.net">dekanat@rgsu.net</a>.
	</p>
	<br />
	<br />
	<div style="float: right; padding-top: 17px;">
		<span class="sna-btn-postpone" style="display: block;" onClick="$('#sna_popup').dialog('close');" >Я ознакомлен</span>
	</div>
</div>


				
