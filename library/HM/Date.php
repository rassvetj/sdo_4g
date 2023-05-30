<?php
class HM_Date extends Zend_Date
{
    const PERIOD_WEEK_CURRENT    = 1;
    const PERIOD_MONTH_CURRENT   = 2;
    const PERIOD_YEAR_CURRENT    = 3;

    const PERIOD_WEEK_PREVIOUS   = 4;
    const PERIOD_MONTH_PREVIOUS  = 5;

    const PERIOD_2WEEKS_RELATIVE = 6;
    const PERIOD_4WEEKS_RELATIVE = 7;

    const WEEKDAY_INDEX_LOCALEAWARE = 'ela';
    
    const SQL = 'Y-MM-dd H:m:s';

    public function __construct($date = null, $part = null, $locale = null)
    {
        parent::__construct($date, $part, $locale);
    }

    public static function dateFromUserInput($date, $locale = null)
    {
        $date = null;
        if (!$locale || !Zend_Locale::isLocale($locale)) {
            $locale = Zend_Locale::findLocale();
        }
        try {
            $date = new self($date, null, $locale);
        } catch (Exception $e) {
            $formats = array('full', 'long', 'medium', 'short');
            $date = null;
            foreach ($formats as $fmt) {
                try {
                    $format  = HM_Locale_Data::getContent('date', array('gregorian', $fmt))." ";
                    $format .= HM_Locale_Data::getContent('time', array('gregorian', $fmt));
                    $date = new self($date, $format, $locale);
                } catch (Exception $e) {
                    $date = null;
                }
            }
        }
        return $date;
    }

    public function getDurationString($seconds) {
        $time = $seconds;

        $minus = false;

        if ($time < 0) {
            $minus = true;
            $time = abs($time);
        }

        $elapsed = '';

        if ($time) {
            $days = floor($time / 60 / 60 / 24);
            if ($days > 0) {
                $time -= $days*24*60*60;
            }

            $hours = floor($time / 60 / 60);
            if ($hours) {
                $time -= $hours*60*60;
            }

            $minutes = floor($time / 60);

            if ($minutes) {
                $time -= $minutes*60;
            }

            $seconds = floor($time);

            if ($days) {
                $elapsed .= sprintf(_n('день plural', '%s день', $days), $days).' ';
            }
            if ($hours) {
                $elapsed .= sprintf(_n('час plural', '%s час', $hours), $hours).' ';
            }
            if ($minutes) {
                $elapsed .= sprintf(_n('минута plural', '%s минута', $minutes), $minutes).' ';
            }

            if ($seconds) {
                $elapsed .= sprintf(_n('секунда plural', '%s секунда', $seconds), $seconds);
            }

        } else {
            $elapsed = '0 '._('минут');
        }

        if ($minus) {
            $elapsed = '- '.$elapsed;
        }

        return $elapsed;
    }

    /**
     * Если использовать locale - месяцы в !!правильном!! падеже,
     * просто иногда нам нужны названия месяцев не для отображения в формате 1-е Января
     * А просто Январь, для этого все данные уже есть в Zend_Locale_Data
     */
    public function getStandalone($part = null, $locale = null) {
        if ($locale === null) {
            $locale = $this->getLocale();
        }

        switch ($part) {
            case self::MONTH_NAME_SHORT:
                $months = Zend_Locale_Data::getList($locale, 'month', array("gregorian", "stand-alone", "abbreviated"));
                $month = intval(parent::get(self::MONTH_SHORT, 'en'));

                $output = $months[$month];
                break;
            case self::MONTH_NAME:
                $months = Zend_Locale_Data::getList($locale, 'month', array("gregorian", "stand-alone", "wide"));
                $month = intval(parent::get(self::MONTH_SHORT, 'en'));
                // в случае если нет stand-alone в локали, то выводится блок из рут где наименования месяца это его номер,
                // при таком раскладе берем не stand-alone блок, а format
                if ($months === Zend_Locale_Data::getList('root', 'month', array("gregorian", "stand-alone", "wide"))) {
                    $months = Zend_Locale_Data::getList($locale, 'month', array("gregorian", "format", "wide"));
                }

                $output = $months[$month];
                break;
            case self::MONTH_NAME_NARROW:
                $months = Zend_Locale_Data::getList($locale, 'month', array("gregorian", "stand-alone", "narrow"));
                $month = intval(parent::get(self::MONTH_SHORT, 'en'));

                $output = $months[$month];
                break;
            case self::WEEKDAY:
                $weekdays = Zend_Locale_Data::getList($locale, 'day', array("gregorian", "stand-alone", "wide"));
                $weekday = intval(parent::get(self::WEEKDAY_DIGIT));

                $output = $weekdays[$weekday];
                break;
            case self::WEEKDAY_NARROW:
                $weekdays = Zend_Locale_Data::getList($locale, 'day', array("gregorian", "stand-alone", "narrow"));
                $weekday = intval(parent::get(self::WEEKDAY_DIGIT));

                $output = $weekdays[$weekday];
                break;
            default:
                $output = parent::get($part, $locale);
        }

        return $output;
    }

    // 0 - sun, 6 - sat
    private function _getWeekdayDigitFromName($name)
    {
        switch(strtolower($name)) {
            case "sun":
                return 0;
            case "mon":
                return 1;
            case "tue":
                return 2;
            case "wed":
                return 3;
            case "thu":
                return 4;
            case "fri":
                return 5;
            case "sat":
                return 6;
            default:
                require_once 'HM/Date/Exception.php';
                throw new HM_Date_Exception('Weekday ($name) is not a known weekday');
        }
    }

    public function get($part, $locale = null)
    {
        if ($locale === null) {
            $locale = $this->getLocale();
        }

        switch($part) {
            case self::WEEKDAY_INDEX_LOCALEAWARE:
                $weekDay         = strtolower(parent::toString(Zend_Date::WEEKDAY, 'iso', 'en'));
                $weekDaysInOrder = HM_Locale_Data::getWeekDaysInOrder($locale, array("gregorian", "format", "wide"));
                $output = 0;
                foreach($weekDaysInOrder as $idx => $day) {
                    if (strncmp($weekDay, strtolower($idx), 3) === 0) {
                      break;
                    }
                    $output += 1;
                }
                break;
            default:
                $output = parent::get($part, $locale);
        }

        return $output;
    }

    public static function now($locale = null)
    {
        return new HM_Date(time(), self::TIMESTAMP, $locale);
    }

    /* http://framework.zend.com/issues/browse/ZF-4490 */
    public function getDate($locale = null)
    {
        $result = parent::getDate($locale);

        if ($result->toString(Zend_Date::HOUR) != '00' or $result->toString(Zend_Date::MINUTE) != '00') {
            $prehistoric = new Zend_Date(0);
            $result->addTimestamp( $prehistoric->getGmtOffset() - $this->getGmtOffset() );
        }

        return $result;
    }

    public function getWeekFirstDay($locale = null)
    {
        if ($locale === null) {
            $locale = $this->getLocale();
        }

        $first = $this->getDate();
        $first->subDay( $this->get(self::WEEKDAY_INDEX_LOCALEAWARE, $locale) );

        return $first;
    }

    public function getWeekLastDay($locale = null)
    {
        if ($locale === null) {
            $locale = $this->getLocale();
        }

        $last = $this->getDate();
        $last->addDay( 7 - $this->get(self::WEEKDAY_INDEX_LOCALEAWARE, $locale) )->subSecond(1);

        return $last;
    }

    static public function getCurrendPeriod($period)
    {
        $date = HM_Date::now();
        $year = $date->get('yyyy');
        $month = $date->get('M');

        // getDate() в Zend_Date работает неправильно, и никто не чешется лечить это
        // см комментарий к HM_Date->getDate
        $date = $date->getDate();

        switch ($period) {

            case HM_Date::PERIOD_WEEK_CURRENT:

                $begin = $date->getWeekFirstDay();
                $end = $date->getWeekLastDay();

                break;

            case HM_Date::PERIOD_MONTH_CURRENT:

                $begin = new HM_Date(array(
                    'year' => $year,
                    'month' => $month,
                    'day' => 1,
                ));

                $end = clone $date;
                $end->addDay(1); //сегодня включительно

                break;

            case HM_Date::PERIOD_YEAR_CURRENT:

                $begin = new HM_Date(array(
                    'year' => $year,
                    'month' => 1,
                    'day' => 1,
                    'hour' => 12,
                ));

                $end = clone $date;
                $end->addDay(1); //сегодня включительно

                break;

            case HM_Date::PERIOD_WEEK_PREVIOUS:

                $begin = $date->getWeekFirstDay();
                $end = $date->getWeekLastDay();

                $begin->subWeek(1);
                $end->subWeek(1);

                break;

            case HM_Date::PERIOD_MONTH_PREVIOUS:

                $end = new HM_Date(array(
                    'year' => $year,
                    'month' => $month,
                    'day' => 1,
                ));

                $begin = clone $end;
                $begin->subMonth(1);

                break;

            case HM_Date::PERIOD_2WEEKS_RELATIVE:

                $begin = clone $date;
                $begin->subDay(13);

                $end = clone $date;
                $end->addDay(1); //сегодня включительно

                break;

            case HM_Date::PERIOD_4WEEKS_RELATIVE:

                $begin = clone $date;
                $begin->subDay(27);

                $end = clone $date;
                $end->addDay(1); //сегодня включительно

                break;

            default:
                break;
        }

        return array(
            'begin' => $begin,
            'end' => $end,
        );
    }

    public function pluralFormsPeriods($periods)
    {
        $periodPluralForms = array(
            self::PERIOD_WEEK_CURRENT    => _('неделя'),
            self::PERIOD_MONTH_CURRENT   => _('месяц'),
            self::PERIOD_YEAR_CURRENT    => _('год'),
            self::PERIOD_WEEK_PREVIOUS   => _('предыдущая неделя'),
            self::PERIOD_MONTH_PREVIOUS  => _('предыдущий месяц'),
            self::PERIOD_2WEEKS_RELATIVE => _('прошедшие 14 дней'),
            self::PERIOD_4WEEKS_RELATIVE => _('прошедшие 28 дней'),
        );

        if (!is_array($periods)) {
            $periods = array($periods);
        }
        $return = array();
        foreach ($periods as $period) {
            if (isset($periodPluralForms[$period])) {
                $return[$period] = $periodPluralForms[$period];
            }
        }
        return $return;
    }

    // @todo учитывать WEEKDAY_INDEX_LOCALEAWARE - ?
    static public function getWeekdays()
    {
        return array(
            _('Понедельник'),
            _('Вторник'),
            _('Среда'),
            _('Четверг'),
            _('Пятница'),
            _('Суббота'),
            _('Воскресенье'),
        );
    }

    static public function getRelativeDate(Zend_Date $base, $duration)
    {
    	$iterate = ($duration > 0) ? 'add' : 'sub';
    	$duration = abs($duration);

    	$holidays = Zend_Registry::get('serviceContainer')->getService('Holiday')->getHolidaysCached($base->get('Y-MM-dd'));

    	if (Zend_Registry::get('serviceContainer')->getService('Option')->getOption('use_holidays') && count($holidays)) {
    		for ($countDays = 1; (($countDays < $duration) || in_array($base->get('Y-MM-dd'), $holidays)); $base->$iterate(1, Zend_Date::DAY)) {
    			if (!in_array($base->get('Y-MM-dd'), $holidays)) {
	    			$countDays++;
	    		}
    		}
    		return $base;
    	} else {
    		return $base->$iterate($duration - 1, Zend_Date::DAY);
    	}
    }

    static public function hasDSTBug()
    {
        $beforeDstChange = new HM_Date();
        $afterDstChange  = new HM_Date();
        $tz    = $beforeDstChange->getTimeZone();

        if ($tz == 'Europe/Moscow') {
            $beforeDstChange->setTimeZone('UTC');
            $afterDstChange->setTimeZone('UTC');
            $beforeDstChange->set('01.01.2011 12:00:00', 'dd.MM.YYYY HH:mm:ss');
            $afterDstChange->set('01.01.2012 12:00:00', 'dd.MM.YYYY HH:mm:ss');

            $beforeDstChange->setTimeZone($tz);
            $afterDstChange->setTimeZone($tz);

            return ($beforeDstChange->toString(HM_Date::HOUR) == $afterDstChange->toString(HM_Date::HOUR));
        }

        return false;
    }

    static public function getPeriodSinceDate($date)
    {
    	$return = '';
    	
    	if ($date) {
	    	$date = new HM_Date($date);
	    	$now = new HM_Date();
	    	$diff = $now->sub($date)->toValue();
	    	$years = floor($diff / 31536000); // приблизительно..
	    	$monthes = floor($diff / 2592000) % 12;
	    	
	    	if ($years && $monthes) {
	    		$return = sprintf(_n('год plural', '%s год', $years), $years) . ' ' . sprintf(_n('месяц plural', '%s месяц', $monthes), $monthes);
	    	} elseif ($years && !$monthes) {
	    		$return = sprintf(_n('год plural', '%s год', $years), $years);
	    	} elseif (!$years && $monthes) {
	    		$return = sprintf(_n('месяц plural', '%s месяц', $monthes), $monthes);
	    	} else {
	    		$return = _('Менее месяца');
	    	}
    	}
    	
    	return $return;    	
    }
}