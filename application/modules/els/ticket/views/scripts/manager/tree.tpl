<style>
	.tree-content-inner .tree-ticket-item {
		cursor: pointer;
		color: #333333;
		font-size: 12px;		
	}
	
	.tree-content-inner .tree-ticket-item:hover {
		text-decoration: underline;
		color: #1171b4;
	}
	
	.tree-content-inner ul {
		list-style: none;
		padding-top: 5px;
		padding-bottom: 5px;
	}
	
	.tree-content-inner li { 
		padding-top: 5px;
	}
	
	.tree-content-inner .opened { 
		font-weight: bold;
	}
</style>
<br />
<br />
<div class="tree-content-inner">
	<?php if(empty($this->tree)):?>
		<?=_('Нет данных')?>
	<?php else: ?>
		<ul>
		<?php foreach($this->tree as $item):?>
			<li>
				<a class="tree-ticket-item"><?=$item['fio'];?> </a>(<?=$item['mid_external'];?>)
				<ul class="hidden tree-ticket-sub-items">
				<?php if(empty($item['periods'])){ continue; } ?>
				<?php foreach($item['periods'] as $period => $files): ?>
					<li>
						<a class="tree-ticket-item"><?=$period?></a>
						<ul class="hidden tree-ticket-sub-items">
						<?php if(empty($files)){ continue; } ?>
						<?php foreach($files as $file_id => $file): ?>
							<li>
								<a target="_blank" href="<?=$this->baseUrl($this->url(array('module' => 'ticket', 'controller' => 'order', 'action' => 'get-file', 'id' => $file_id, 'user_id' => $item['user_id'])));?>">
									<?=$file['name']?>
								</a>, 
								(<?=$file['size']?>,
								&nbsp;<?=date('d.m.Y', strtotime($file['date_uploaded']))?>)
							</li>
						<?php endforeach;?>
						</ul>
					</li>
				<?php endforeach;?>
				</ul>
			</li>
		<?php endforeach;?>
		</ul>
	<?php endif;?>
</div>
<br />
<br />
