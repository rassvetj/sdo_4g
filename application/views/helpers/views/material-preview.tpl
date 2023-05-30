<?php
$this->headLink()->appendStylesheet($this->serverUrl('/css/content-modules/material-icons.css'))
                 ->appendStylesheet($this->serverUrl('/css/content-modules/course-index.css'));
?>
<div class="material-preview<?php if (strlen($this->lesson->descript) > 1000): ?> col2<?php endif; ?>">
    <div class="grip"><div class="handle"></div></div>
    <div class="content">
        <?php $lessonAttribs = array(
                    'href' => $this->launchUrl);
        if(is_a($lesson->material, 'HM_Course_CourseModel')) $lessonAttribs['target']=$lesson->material->isNewWindow();
        ?>
        <a <?php echo $this->HtmlAttribs($lessonAttribs)?> class="material material-icon <?= $this->lesson->material->getIconClass(); ?>"></a>
        <h4><a <?php echo $this->HtmlAttribs($lessonAttribs)?>><?php echo $this->lesson->title ?></a>&nbsp;<?php
            if ($this->isStatsAllowed): ?><a class="stats-icon" href="<?= $this->statsUrl ?>"><img src="<?= $this->serverUrl('/images/content-modules/course-index/stats.png') ?>"></a><?php endif;?><?php
            if ($this->isEditAllowed): ?><a class="edit-action" href="<?= $this->url(array('module' => 'subject', 'controller' => 'materials', 'action' => 'edit', 'SHEID' => $this->lesson->SHEID)) ?>"><img src="<?= $this->serverUrl('/images/blog/controls-edit.png') ?>"></a><?php endif; ?></h4>
        <p><?= nl2br($this->escape($this->lesson->descript)) ?></p>
    </div>
    <input type="hidden" name="material[]" value="<?= $this->lesson->SHEID ?>">
</div>
