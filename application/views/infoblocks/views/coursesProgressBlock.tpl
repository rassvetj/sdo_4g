<?php $this->headLink()->appendStylesheet( $this->serverUrl('/css/infoblocks/subject-progress/subject-progress.css') ); ?>
<div class="subject-progress">
<?php if (count($this->courses)): ?>
    <ul>
    <?php foreach ($this->courses as $course): ?>
        <?php $rowClass = !$i++ ? 'first' : ($i == count($this->courses) ? 'last' : ''); ?>
        <li class="<?php echo $rowClass;?> <?php echo $course['status'];?>">
            <h4>
                <a <?php if ($course['new_window']) echo 'target="_blank"'; ?> href="<?php echo $course['launchUrl']; ?>"><?php echo $course['lesson']->title; ?></a>
                <?php if ($course['isStatsAllowed']): ?>
                <a class="stats-icon" href="<?php echo $course['statsUrl']?>"><img src="<?= $this->serverUrl('/images/content-modules/course-index/stats.png') ?>"></a>
                <?php endif;?>
            </h4>
            <div class="progress"><?= $this->progress($course['progress'], 'large'); ?></div>
            <div class="status">
                <?php if ($course['isLaunchAllowed']): ?>
                <a <?php if ($course['new_window']) echo 'target="_blank"'; ?> href="<?php echo $course['launchUrl']?>" class="ui-button"><span class="ui-icon ui-icon-play"></span></a>
                <?php endif;?>
                <span class="label"><?php echo $course['statusLabel']?></span>
            </div>
            <?php if ($course['isfree']): ?>
                <?php if ($course['status'] != 'not-attempted'): ?>
    			<div class="last-used"><?php echo _('последнее посещение') . ' ' . date('d.m.Y', strtotime($course['lessonAssign']->launched));?></div>
                <?php endif;?>
            <?php else: ?>
                <div class="last-used"><?php echo $course['datetimeLabel'] . ': ' . $course['datetime']; ?></div>
            <?php endif;?>
        </li>
        <?php endforeach; ?>
    </ul>
<?php endif;?>
</div>