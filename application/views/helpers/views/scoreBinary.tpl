<?php
    $score = intval($this->score);
?><span class="form-score-binary">
    <span class="els-icon check <?php if ($score === 1): ?>check-checked<?php endif; ?>">
        <img src="<?php echo $this->escape($this->serverUrl('/images/content-modules/score-binary.gif')) ?>">
    </span>
    <!-- input
        type="hidden"
        name="score[<?php echo $this->userId; ?>_<?php echo $this->lessonId; ?>]"
        value="<?php echo $score; ?>"-->
</span>