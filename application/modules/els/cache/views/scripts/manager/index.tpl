<style>
.cache-area td {
	padding: 5px;
    font-size: 14px;
}
</style>
<div class="cache-area">
	<table>
	<?php  foreach($this->items as $item):?>
		<tr>
			<td><?=$item['name']?></td>
			<td><a href="<?=$item['url']?>" ><?=_('Сбросить')?></a></td>
		</tr>
	<?php endforeach;?>
	</table>
</div>