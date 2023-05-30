<?php
    $this->headLink()->appendStylesheet($this->baseUrl('css/content-modules/score.css'));
    $this->headScript()->appendFile($this->baseUrl('js/application/marksheet/index/index/scoreList.js'));
?>
<?php if(intval($this->score)>=0  && $this->score !== null ):?>
<div class="<?php echo ((intval($this->score) >= 0 && $this->score !== null) ? 'score_red' : 'score_gray') ?> number_number">
    <span>
        <input
            tabindex="<?php echo $this->tabindex;?>"
            data-target="<?php echo $this->userId; ?>_<?php echo $this->lessonId; ?>"
            type="text"
            placeholder="<?php echo _("Нет") ?>"
            value="<?php echo (intval($this->score) != -1 ? $this->score : '') ?>"
            pattern="^[1-9]{1}\d?$|^0$|^100$"
            class="hm_score_numeric"
        >
        <?php if(strlen($this->comments)):?>
        <div class="score-comments" title="<?php echo $this->escape($this->comments);?>"></div>
        <?php endif;?>
    </span>
</div>
<?php else:?>
<div class="score_gray number_number">
    <span align="center">
        <input
            tabindex="8001"
            data-target="<?php echo $this->userId; ?>_<?php echo $this->lessonId; ?>"
            type="text"
            placeholder="Нет"
            pattern="^[1-9]{1}\d?$|^0$|^100$"
            class="hm_score_numeric"
        >
    </span>
</div> 
<?php endif?>
<input 
  type="hidden"
  id="<?php echo $this->userId; ?>_<?php echo $this->lessonId; ?>"
  name="score[<?php echo $this->userId; ?>_<?php echo $this->lessonId; ?>]"
  value="<?php echo $this->score; ?>"
  pattern="^[1-9]{1}\d?$|^0$|^100$|^-1$"
>