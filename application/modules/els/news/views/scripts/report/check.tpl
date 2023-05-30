<style>
.nr-error {
	color: red;	
}
</style>
<div>
	<?php if($this->error): ?>
		<div class="nr-error">
			<?=$this->error?>
		</div>
	<?php else: ?>
		<b><?=_('Заполнение новостей')?>:</b>
		<br />
		<b><span style="padding-left: 120px;"><?=_('Корректно')?><span>:</b> <?=$this->news_count?> <?=_('из')?> <?=$this->news_max_count?>.
		
		<?php if(
			   !empty($this->incorrect_links)
			|| !empty($this->incorrect_landmarks)
			|| !empty($this->incorrect_tasks)
		):?>
			<br />
			<div style="padding-left: 118px;">
				<table>
					<tr>
						<td><b><?=_('Некорректно')?></b>:</td><td>&nbsp;</td>
					</tr>
		<?php endif;?>
		
		<?php if(!empty($this->incorrect_links)):?>						
			<?php foreach($this->incorrect_links as $number => $item):?>
				<tr>
					<td>&nbsp;</td><td style="color: red;"><?=_('Ссылка')?> <?=$number?></td>
				</tr>
			<?php endforeach;?>			
		<?php endif;?>
		
		<?php if(!empty($this->incorrect_landmarks)):?>			
			<?php foreach($this->incorrect_landmarks as $number => $item):?>
				<tr>
					<td>&nbsp;</td><td style="color: red;"><?=_('Рубежный контроль')?> <?=$number?></td>
				</tr>
			<?php endforeach;?>			
		<?php endif;?>
		
		<?php if(!empty($this->incorrect_tasks)):?>			
			<?php foreach($this->incorrect_tasks as $number => $item):?>
				<tr>
					<td>&nbsp;</td><td style="color: red;"><?=_('Задание')?> <?=$number?></td>
				</tr>
			<?php endforeach;?>			
		<?php endif;?>
		
		<?php if(
			   !empty($this->incorrect_links)
			|| !empty($this->incorrect_landmarks)
			|| !empty($this->incorrect_tasks)
		):?>
				</table>
			</div>
		<?php endif;?>
		
	<?php endif;?>
</div>