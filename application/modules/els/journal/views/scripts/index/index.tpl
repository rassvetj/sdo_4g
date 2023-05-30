<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/score.css'); ?>
<?php $this->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/test.css'); ?>
<?php $this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/marksheet.css')); ?>		
	<table class="main-grid">
		<thead>
			<tr class="marksheet-head">
				<th class="marksheet-rowcheckbox first-cell">Ваша оценка</th>								
			</tr>
		</thead>
		<tbody>		
			<tr class="odd fio-cell">					
				<td>
					<div class="<?=($this->mark > 0) ? 'score_red' : 'score_gray'; ?> number_number">
						<span align="center"><?=($this->mark > 0) ? round($this->mark, 2) : 'Нет'; ?></span>
					</div>
				</td>						
			</tr>				
		</tbody>
		<tfoot>
			<tr>
				<td colspan="1">&nbsp;</td>
			<tr>
		</tfoot>
	</table>		