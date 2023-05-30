<?php
    $score = intval($this->score);
    $score = ($score == 1 || $score == 0) ? $score : '';
?><span class="form-score-ternary">
    <span class="els-icon check <?php if ($score === 1): ?>check-checked<?php endif; ?>">
        <img src="<?php echo $this->escape($this->serverUrl('/images/content-modules/score-ternary.gif')) ?>">
    </span><span class="els-icon cross <?php if ($score === 0): ?>cross-checked<?php endif; ?>">
        <img src="<?php echo $this->escape($this->serverUrl('/images/content-modules/score-ternary.gif')) ?>">
    </span>
    <input
        type="hidden"
        name="score[<?php echo $this->userId; ?>_<?php echo $this->lessonId; ?>]"
        value="<?php echo $score; ?>">
</span>