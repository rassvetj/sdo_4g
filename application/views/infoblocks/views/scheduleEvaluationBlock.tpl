<?php $this->headLink()->appendStylesheet( $this->serverUrl('/css/infoblocks/schedule-accordion/schedule.css') ); ?>
<div class="schedule-accordion">
    <ul>
        <?php foreach($this->lessons as $lesson):?>
        <li class="<?php echo $lesson['date_limit'] ? 'in-process' : 'infinite'; ?>">
            <span class="pit">
                <span class="bg"></span>
                <span class="text" title="<?php echo $lesson['date_limit'] ? 'проверить до даты' : 'время на проверку не ограничено'; ?>"><?php echo $lesson['date_limit'] ? $lesson['date_limit'] : '&#x221E;'; ?></span></span>
            <span class="title">
                <a href="<?php echo $lesson['url']; ?>" target="_self"><?php echo $lesson['title']; ?></a>
            </span>
        </li>
        <?php endforeach;?>
    </ul>
</div>