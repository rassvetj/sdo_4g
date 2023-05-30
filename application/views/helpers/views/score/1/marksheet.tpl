<input
    type="text"
    tabindex="<?php echo $this->tabindex;?>"
    value="<?php echo ((intval($this->score) >= 0 && $this->score !== null) ? $this->score : '') ?>"
    pattern="^[1-9]{1}\d?$|^0$|^100$"
    data-target="<?php echo $this->userId; ?>_<?php echo $this->lessonId; ?>"
    class="hm_score_numeric"
    <?php if ((isset($this->allowTutors) && !$this->allowTutors) ||
        $this->mark_type == HM_Mark_StrategyFactory::MARK_BRS):?> readonly <?php endif;?>
>
<input 
  type="hidden"
  id="<?php echo $this->userId; ?>_<?php echo $this->lessonId; ?>"
  name="score[<?php echo $this->userId; ?>_<?php echo $this->lessonId; ?>]"
  value="<?php echo $this->score; ?>"
  pattern="^[1-9]{1}\d?$|^0$|^100$|^-1$"
>