<style>
	.rl-area {
		font-size:14px;
	}
	.rl-area p {
		padding: 5px 0;
	}
</style>
<div class="rl-area">
<?php if (!empty($this->reports)):?>
	<?php foreach($this->reports as $report): ?>
		<p><a href="<?=$report['url']?>"><?=$report['name']?></a></p>
	<?php endforeach;?>
<?php endif;?>
</div>