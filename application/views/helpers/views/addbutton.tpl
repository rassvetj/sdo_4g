<div style="padding-bottom: 15px;">
    <div style="float: left;">
    <?php if($this->options == null):?>
    <span class="dir_disabled"></span>&nbsp;
    <?php else:?>
        <?php echo $this->actions('grid_action', $this->options);?>
    <?php endif;?>
    </div>
    <div style="padding-left: 18px;"><a style="text-decoration: underline; font-weight: bold;" href="<?php echo $this->url?>"><?php echo $this->title?></a></div>
</div>