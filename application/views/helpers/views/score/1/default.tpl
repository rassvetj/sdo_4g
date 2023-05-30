<?php if(intval($this->score)>=0 && $this->score !== null):?>
<div class="<?php echo (intval($this->score) > 0 ? 'score_red' : 'score_gray') ?> number_number">
    <span>
            <?php echo ((intval($this->score) >= 0 && $this->score !== null) ? $this->score : _('Нет')) ?>
    </span>
</div>        
<?php else:?>
<div class="score_gray number_number">
    <span align="center"><?php echo _('Нет');?></span>
</div> 
<?php endif?>