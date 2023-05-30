<div class="gmail-checkbox"><span class="wrapper"><button class="has-checkbox"><em class="arrow">&#9660;</em></button><input type="checkbox"></span><?php
    if ($this->options != null):?><menu class="gmail-checkbox-menu dropdown"><ul>
        <?php foreach($this->options as $option):?>
        <li><a href="#" onclick="<?php echo $this->escape($option['onClick']); ?>"><?php echo $this->escape($option['title']); ?></a></li>
        <?php endforeach;?>
    </ul></menu><?php endif;?></div>