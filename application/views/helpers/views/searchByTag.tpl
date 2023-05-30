<div class="seach_block">
	<div class="seach_title">
		<div class="s_count">
	        <?php echo $this->count;?>.
	    </div> 
	    <span class="s_title">
	    	<img src='<?php echo $this->resourceModel->icon['src']; ?>' title='<?php echo $this->resourceModel->icon['title']; ?>' />
	    	<?php
	    	if ($this->resourceModel->viewAction):
	    	?>
                <a href="<?php echo $this->url($this->resourceModel->viewAction);?>">
                    <?php echo $this->resourceModel->title;?>
                </a>
            <?php
            else:
            ?>
                <?php echo $this->resourceModel->title;?>
            <?
            endif;
            ?>
	    </span>
	</div>
	<div class="res_keywords"><?php echo $this->resourceModel->keywords;?></div>
	<div class="res_discr"><?php echo $this->resourceModel->description;?></div>
</div>