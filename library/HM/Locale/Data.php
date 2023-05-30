<?php
require_once 'Zend/Locale/Data.php';

class HM_Locale_Data extends Zend_Locale_Data
{
    
    public static function getWeekDaysInOrder ($locale, $value = null)
    {
        if (empty($value)) {
            $value = array("gregorian", "format", "wide");
        }
        
        $weekData    = parent::getList($locale, 'week');
        $weekDays    = parent::getList($locale, 'day', $value);
        // haven't found any evidence, that getList will return dates in this
        // order, so reordering explicitly
        $weekDays    = array(
            'sun' => $weekDays['sun'],
            'mon' => $weekDays['mon'],
            'tue' => $weekDays['tue'],
            'wed' => $weekDays['wed'],
            'thu' => $weekDays['thu'],
            'fri' => $weekDays['fri'],
            'sat' => $weekDays['sat']
        );
        $weekDayKeys = array_keys($weekDays);
        $firstDayIdx = array_search($weekData['firstDay'], $weekDayKeys);
        
        $weekDaysInOrder = array();
        for ($i = 0; $i < 7; ++$i) {
            $index = $weekDayKeys[($firstDayIdx + $i) % 7];
            $weekDaysInOrder[$index] = $weekDays[$index];
        }
        
        return $weekDaysInOrder;
    }
    
}
