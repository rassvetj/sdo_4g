<?php
$blockId = $this->id('trcalendar');
$listItemTemplateId = $this->id('trcalendar');
$emptyListTemplateId = $this->id('trcalendar');
$monthSummaryTemplateId = $this->id('trcalendar');
$monthNames = Zend_Locale_Data::getList(Zend_Locale::findLocale(), 'month', array('gregorian', 'stand-alone', 'wide'));
$monthNamesFormat = Zend_Locale_Data::getList(Zend_Locale::findLocale(), 'month', array('gregorian', 'format', 'wide'));
?>
<div id="<?php echo $this->escape($blockId); ?>">
    <div class="calendar-container">
        <div class="calendar-header ui-selectmenu">
            <a class="els-calendar-button els-calendar-prev"><span><?php echo _("Предыдущий месяц"); ?></span></a>
            <span class="els-calendar-month-label"><?php
                $dt = new HM_Date();
                echo $monthNames[(int)$dt->toString(Zend_Date::MONTH_SHORT)]." ".$dt->toString(Zend_Date::YEAR_8601);
            ?></span>
            <a class="els-calendar-button els-calendar-next"><span><?php echo _("Следующий месяц"); ?></span></a>
        </div>
        <div class="calendar"><span class="pseudo"></span></div>
        <div class="month-summary">&nbsp;</div>
    </div>
    <div class="trainings-list">
        <h2><span class="els-calendar-date-selected"></span>&nbsp;<span>|</span>&nbsp;<a class="today" href="#today-<?php echo $this->escape($blockId); ?>"><?php echo _('Сегодня');?></a></h2>
        <div class="trainings-list-list"><div class="scroll-document"></div></div>
    </div>
</div>
<script type="text/template" id="<?php echo $this->escape($listItemTemplateId); ?>">
    <a href="<%- url %>"><%- title %></a>
    <p><%= description %></p>
</script>
<script type="text/template" id="<?php echo $this->escape($emptyListTemplateId); ?>">
    <?php echo _("Нет тренингов в указанную дату"); ?>
</script>
<script type="text/template" id="<?php echo $this->escape($monthSummaryTemplateId); ?>">
    <?php echo _("Всего"); ?> <?php echo _("учебных сессий"); ?>&nbsp;&mdash; <strong><%- trainingsTotal %></strong>, <?php echo _("участников"); ?>&nbsp;&mdash; <strong><%- attendeesTotal %></strong>
</script>
<?php $this->inlineScript()->captureStart(); ?>
$(document).ready(function () {
    $(<?php echo Zend_Json::encode("#$blockId"); ?>).infoblockTrainingsCalendar({
        url:    <?php echo Zend_Json::encode($this->url(array('module' => 'infoblock', 'controller' => 'session-calendar', 'action' => 'get')) . '/month/') ?>,
        months: <?php echo Zend_Json::encode($monthNamesFormat); ?>,
        templates: {
            summary: _.template($(<?php echo Zend_Json::encode("#$monthSummaryTemplateId"); ?>).html()),
            empty:   _.template($(<?php echo Zend_Json::encode("#$emptyListTemplateId"); ?>).html()),
            item:    _.template($(<?php echo Zend_Json::encode("#$listItemTemplateId"); ?>).html())
        }
    });
});
<?php $this->inlineScript()->captureEnd(); ?>