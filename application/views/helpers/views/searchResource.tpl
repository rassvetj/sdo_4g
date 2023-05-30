<div class="seach_block">
	<div class="seach_title"><div class="s_count"><?php echo $this->count;?>.</div> <span class="s_title"><a href="<?php echo $this->url(array('module' => 'resource', 'action' => 'index', 'controller' => 'index', 'resource_id' => $this->resourceModel->resource_id, 'page' => null, 'query' => null));?>"><?php echo $this->resourceModel->title;?></a></span></div>
	<div class="res_keywords"><?php echo $this->resourceModel->keywords;?></div>
	<div class="res_discr"><?php echo $this->resourceModel->description;?></div>
</div>