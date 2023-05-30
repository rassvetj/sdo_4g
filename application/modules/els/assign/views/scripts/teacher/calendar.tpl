<?php
if($this->editable){
echo $this->headSwitcher(array('module' => 'assign', 'controller' => 'teacher', 'action' => 'calendar', 'switcher' => 'calendar'), 'assign');
}
echo $this->subjectsCalendar(
$this->source,
array(
//'eventDropFunctionName'   => 'sendCalendarChange',
//'eventResizeFunctionName' => 'sendCalendarChange',
'editable'                => $this->editable,
'saveDataUrl'             => $this->url(array('module'=>'subject', 'controller'=>'list','action'=>'save-calendar')),
'dayClickFunctionName'    => 'calendarNewEvent',
'eventClickFunctionName'  => 'calendarEditEvent'
)
);
?>

<?php $this->inlineScript()->captureStart(); ?>
function calendarNewEvent (calEvent, jsEvent, view) {
    window.location = '<?php echo $this->url(array('module' => 'holiday','controller' => 'index', 'action' => 'edit', 'is_user_event' => 'true', 'user_id' => $this->userId))?>';
}

function calendarEditEvent (calEvent, jsEvent, view) {
    if (calEvent.editable) {
        window.location = '<?php echo $this->url(array('module' => 'holiday','controller' => 'index', 'action' => 'edit', 'is_user_event' => 'true'))?>/id/' + calEvent.id;
    }
}
<?php $this->inlineScript()->captureEnd(); ?>