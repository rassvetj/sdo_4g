<?php
    $score = intval($this->score);
    $score = ($score === HM_Scale_Value_ValueModel::VALUE_TERNARY_ON || $score === HM_Scale_Value_ValueModel::VALUE_TERNARY_OFF) ? $score : HM_Scale_Value_ValueModel::VALUE_NA;
?><span class="hm-score-ternary-icon hm-score-ternary-icon-readonly form-score-ternary score_checkbox">
    <span class="els-icon check <?php if ($score === HM_Scale_Value_ValueModel::VALUE_TERNARY_ON): ?>check-checked<?php endif; ?>">
        <img src="<?php echo $this->escape($this->serverUrl('/images/content-modules/score-ternary.gif')) ?>">
    </span><span class="els-icon cross <?php if ($score === HM_Scale_Value_ValueModel::VALUE_TERNARY_OFF): ?>cross-checked<?php endif; ?>">
        <img src="<?php echo $this->escape($this->serverUrl('/images/content-modules/score-ternary.gif')) ?>">
    </span>
</span>