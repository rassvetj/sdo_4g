<div id="sna_area" >
	<style>
		#sna_popup{
			font-size: 14px;
			text-align: justify;
		}
		
		#sna_popup p {
			padding-bottom: 4px;
			text-indent: 20px;
		}
		
		#sna_popup textarea{
			width: 98%;
		}
		
		#sna_popup .sna-btn-confirm{
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
		}
		
		#sna_popup .sna-btn-confirm:hover{
			background-color: #3192be;
			color:white;
		}
		
		
		#sna_popup #submit{
			color: #5ecff5;
			font-size:11px;			
		}
		
		#sna_popup #qualification_work_agreement_confirm dd {
			float: left;
			padding-right: 10px;
		}
		
		#sna_popup #qualification_work_agreement_confirm dd label {
			vertical-align: -webkit-baseline-middle;			
		}
		
		#sna_popup .qw-form-area {
			line-height: 20px;			
		}
		
		#sna_popup .ui-dialog-titlebar-close {
			display: none;
		}
		
		
		.disable{
			pointer-events: none;
			background-color: #e6e4e4;
		}
		
		
	</style>
	<div id="sna_popup" title="<?=$this->popup_title?>">
		<div class="qw-text-area">
			<?=$this->popup_content?>
			<br />
			<span class="sna-btn-confirm" data-type="<?=$this->notification_type?>"><?=_('Я ознакомлен')?></span>
		</div>
	</div>
	<? # скрипты лежат тут: \public\themes\rgsu\js\common.js ?>
</div>




