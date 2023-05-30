<?php
class HM_View_Helper_SubjectsCalendar extends HM_View_Helper_Abstract
{
    /**
     * Дополнительная информация по каледндарю  http://arshaw.com/fullcalendar/docs/
     *
     * @param $source string|array - ссылка на экшен генерации json данных для календаря.
     * @param array $options параметры календаря
     *              editable                - редактирование (перетаскивание) событий TRUE  - редактирование разрешено
     *              disableResizing         - запрет ресайза событий TRUE - ресайз запрещен
     *              eventDropFunctionName   - имя javaScript функции-обработчика перетаскивания события.
     *                                        По умолчанию sendCalendarChange, если указан параметр saveDataUrl
     *              eventResizeFunctionName - имя javaScript функции-обработчика ресайза события
     *                                        По умолчанию sendCalendarChange, если указан параметр saveDataUrl
     *              dayClickFunctionName    - имя javaScript функции-обработчика клика по ячейке
     *              eventClickFunctionName  - имя javaScript функции-обработчика клика по событию
     *              saveDataUrl             - ссылка экшена сохранения результатов ресайза и редактирования событий в дефолтной функции-обработчике.
     * @return string
     */
    public function subjectsCalendar($source, $options = array())
    {
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/content-modules/fullcalendar.css'));
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/content-modules/fullcalendar.print.css'), 'print');
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/fullcalendar.js'));

        if (is_array($source)) {                 // источник задается ссылкой в исходного виде массива для хелпера url
            $data = $this->view->url($source);
        } elseif (is_string($source)) {          // источник задается строковой ссылкой
            $data = $source;
        }

        $locale = Zend_Locale::findLocale();
        $calendarOptions = array(
            'header' => array(
                'left'   => 'prev,next today',
                'center' => 'title',
                'right'  => false
            ),
            'selectable' => false,
            'monthNames' => array_values(HM_Locale_Data::getList($locale, 'month', array('gregorian', 'stand-alone', 'wide'))),
            'monthNamesShort' => array_values(HM_Locale_Data::getList($locale, 'month', array('gregorian', 'stand-alone', 'abbreviated'))),
            'firstDay' => 1,
            'dayNames' => array_values(HM_Locale_Data::getList($locale, 'day', array('gregorian', 'stand-alone', 'wide'))),
            'dayNamesShort' => array_values(HM_Locale_Data::getList($locale, 'day', array('gregorian', 'format', 'abbreviated'))),
            'buttonText' => array(
                'prev'     => '&nbsp;&#9668;&nbsp;',
                'next'     => '&nbsp;&#9658;&nbsp;',
                'prevYear' => '&nbsp;&lt;&lt;&nbsp;',
                'nextYear' => '&nbsp;&gt;&gt;&nbsp;',
                'today'    => _('сегодня'),
                'month'    => _('месяц'),
                'week'     => _('неделя'),
                'day'      => _('день')
            ),
            'editable' => (isset($options['editable']) && $options['editable'] === true)? true : false,
            'disableResizing' => (isset($options['disableResizing']) && $options['disableResizing'] === true)? true : false,
            'timeFormat' => '',
            'events' => $data,
            //'dayClick' => new Zend_Json_Expr('calendarNewEvent'),
            //'eventClick' => new Zend_Json_Expr('calendarEditEvent'),
            //'eventDrop' => ()? new Zend_Json_Expr($options['eventDropFunctionName']) : new Zend_Json_Expr('sendCalendarChange'),
            //'eventResize' => new Zend_Json_Expr('sendCalendarChange')
        );

        if (isset($options['eventDropFunctionName'])) {
            $calendarOptions['eventDrop']  = new Zend_Json_Expr($options['eventDropFunctionName']);
        } elseif (isset($options['saveDataUrl'])) { // если ссылка сохранения передана, указываем дефолтную функцию
            $calendarOptions['eventDrop']  = new Zend_Json_Expr('sendCalendarChange');
        }

        if (isset($options['eventResizeFunctionName'])) {
            $calendarOptions['eventResize']  = new Zend_Json_Expr($options['eventResizeFunctionName']);
        } elseif (isset($options['saveDataUrl'])) { // если ссылка сохранения передана, указываем дефолтную функцию
            $calendarOptions['eventResize']  = new Zend_Json_Expr('sendCalendarChange');
        }

        if (isset($options['dayClickFunctionName'])) {
            $calendarOptions['dayClick'] = new Zend_Json_Expr($options['dayClickFunctionName']);
        }

        if (isset($options['eventClickFunctionName'])) {
            $calendarOptions['eventClick'] = new Zend_Json_Expr($options['eventClickFunctionName']);
        }
        // TODO: сейчас 2 календаря на одной странице не смогут
        //       одновременно отсылать данные на сервер
        $calendarId = (isset($options['calendarId']))? $options['calendarId'] : $this->view->id('calendar');
        
        $js = sprintf("$(%s).fullCalendar(%s)", Zend_Json::encode("#$calendarId"), Zend_Json::encode(
            $calendarOptions,
            false,
            array('enableJsonExprFinder' => true)
        ));

        // если указан урл отправки данных для сохранения, но функция обработки ресайза или перетаскивания не определена, выводится дефолтная функция
        if (isset($options['saveDataUrl']) && (!isset($options['eventDropFunctionName']) || !isset($options['eventResizeFunctionName']))) {
            $this->view->inlineScript()->appendScript(sprintf("function sendCalendarChange (event,dayDelta,minuteDelta,allDay,revertFunc) {
                var postParam = 'eventid='+event.id+'&start='+Date.parse(event.start)+'&end='+Date.parse(event.end);
                $.ajax(%s);
                }", Zend_Json::encode(array(
                            'type'  => 'POST',
                            'url'   => $options['saveDataUrl'],
                            'data'  => new Zend_Json_Expr('postParam'),
                            'success' => new Zend_Json_Expr('function (obj) {
                if(obj.status=="fail") {
                revertFunc();
                } else {
                event.title  = event.title.replace("*" ,"")
                $("#' . $calendarId . '").fullCalendar("updateEvent", event);
                }
                alert(obj.msg);
                }'),
                            'error' => new Zend_Json_Expr('function (msg) {
                log("ERROR: " + msg + " params:" + postParam);
                }')
                    ),false,array('enableJsonExprFinder' => true))));
        }
        $this->view->jQuery()->addOnload($js);
        $this->view->id = $calendarId;
        return $this->view->render('subjects-calendar.tpl');
    }
}