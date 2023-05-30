<?php
    $score = intval($this->score);
?><span class="form-score-binary score_checkbox">
    <span class="hm-score-binary-icon hm-score-binary-icon-readonly els-icon check <?php if ($score === HM_Scale_Value_ValueModel::VALUE_BINARY_ON): ?>check-checked<?php endif; ?>">
        <img src="<?php echo $this->escape($this->serverUrl('/images/content-modules/score-binary.gif')) ?>">
    </span>
</span>