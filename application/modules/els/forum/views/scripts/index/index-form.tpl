<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/forum2.css')); ?>
<div class="forum forum-index">
    <?php if (!empty($this->formSection)): ?>
    <div><?= $this->formSection ?></div>
    <?php elseif (!empty($this->formMessage)): ?>
    <?php elseif (!empty($this->formTheme)): ?>
    <div class="topic-createeditor"><?= $this->formTheme ?></div>
    <?php endif; ?>
</div>