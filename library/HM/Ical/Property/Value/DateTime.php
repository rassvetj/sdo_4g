<?php
class HM_Ical_Property_Value_DateTime implements HM_Ical_Property_Value_Interface
{
    /**
     * Days in months.
     *
     * @var array
     */
    protected static $daysInMonths = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    /**
     * Days in year passed per month.
     *
     * The first array is for non-leap years, the second for leap years.
     *
     * @var array
     */
    protected static $daysInYearPassedPerMonth = array(
        array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334, 365),
        array(0, 31, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335, 366),
    );

    /**
     * Year.
     *
     * @var integer
     */
    protected $year;

    /**
     * Month.
     *
     * @var integer
     */
    protected $month;

    /**
     * Day.
     *
     * @var integer
     */
    protected $day;

    /**
     * Hour.
     *
     * @var integer
     */
    protected $hour = 0;

    /**
     * Minute.
     *
     * @var integer
     */
    protected $minute = 0;

    /**
     * Second.
     *
     * @var integer
     */
    protected $second = 0;

    /**
     * Whether this DateTime is in UTC.
     *
     * @var boolean
     */
    protected $isUtc = false;

    /**
     * Whether this DateTime has no time.
     *
     * @var boolean
     */
    protected $isDate = false;

    protected $value;

    public function __construct($year, $month, $day, $hour = null, $minute = null, $second = null, $isUtc = false)
    {
        $this->setYear($year)
             ->setMonth($month)
             ->setDay($day);

        if ($hour === null && $minute === null && $second === null) {
            $this->isDate(true);
            $this->isUtc(false);
        } else {
            $this->setHour($hour)
                 ->setMinute($minute)
                 ->setSecond($second)
                 ->isUtc($isUtc);
        }
    }

    /**
     * Set year.
     *
     * @param  integer $year
     * @return self
     */
    public function setYear($year)
    {
        if (!is_numeric($year)) {
            throw new HM_Ical_Exception(sprintf('Year "%s" is not a number', $year));
        } elseif ($year < 0) {
            throw new HM_Ical_Exception(sprintf('Year "%s" is lower than 0', $year));
        } elseif ($year > 3000) {
            throw new HM_Ical_Exception(sprintf('Year "%s" is greater than 3000', $year));
        }

        $this->year = (int) $year;

        return $this;
    }

    /**
     * Get year.
     *
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set month.
     *
     * @param  integer $month
     * @return self
     */
    public function setMonth($month)
    {
        if (!is_numeric($month)) {
            throw new HM_Ical_Exception(sprintf('Month "%s" is not a number', $month));
        } elseif ($month < 1) {
            throw new HM_Ical_Exception(sprintf('Month "%s" is lower than 1', $month));
        } elseif ($month > 12) {
            throw new HM_Ical_Exception(sprintf('Month "%s" is greater than 12', $month));
        }

        $this->month = (int) $month;
        $this->day   = min($this->day, $this->getDaysInMonth());

        return $this;
    }

    /**
     * Get month.
     *
     * @return integer
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set day.
     *
     * @param  integer $day
     * @return self
     */
    public function setDay($day)
    {
        if (!is_numeric($day)) {
            throw new HM_Ical_Exception(sprintf('Day "%s" is not a number', $day));
        } elseif ($day < 1) {
            throw new HM_Ical_Exception(sprintf('Day "%s" is lower than 1', $day));
        } elseif ($day > 31) {
            throw new HM_Ical_Exception(sprintf('Day "%s" is greater than 31', $day));
        }

        $this->day = min((int) $day, $this->getDaysInMonth());

        return $this;
    }

    /**
     * Get day.
     *
     * @return integer
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set hour.
     *
     * @param  integer $hour
     * @return self
     */
    public function setHour($hour)
    {
        if (!is_numeric($hour)) {
            throw new HM_Ical_Exception(sprintf('Hour "%s" is not a number', $hour));
        } elseif ($hour < 0) {
            throw new HM_Ical_Exception(sprintf('Hour "%s" is lower than 0', $hour));
        } elseif ($hour > 23) {
            throw new HM_Ical_Exception(sprintf('Hour "%s" is greater than 23', $hour));
        }

        $this->hour = (int) $hour;

        if ($this->isDate()) {
            $this->isDate(false);
            $this->minute = 0;
            $this->second = 0;
        }

        return $this;
    }

    /**
     * Get hour.
     *
     * @return integer
     */
    public function getHour()
    {
        return $this->hour;
    }

    /**
     * Set minute.
     *
     * @param  integer $minute
     * @return self
     */
    public function setMinute($minute)
    {
        if (!is_numeric($minute)) {
            throw new HM_Ical_Exception(sprintf('Minute "%s" is not a number', $minute));
        } elseif ($minute < 0) {
            throw new HM_Ical_Exception(sprintf('Minute "%s" is lower than 0', $minute));
        } elseif ($minute > 59) {
            throw new HM_Ical_Exception(sprintf('Minute "%s" is greater than 59', $minute));
        }

        $this->minute = (int) $minute;

        if ($this->isDate()) {
            $this->isDate(false);
            $this->hour   = 0;
            $this->second = 0;
        }

        return $this;
    }

    /**
     * Get minute.
     *
     * @return integer
     */
    public function getMinute()
    {
        return $this->minute;
    }

    /**
     * Set second.
     *
     * @param  integer $second
     * @return self
     */
    public function setSecond($second)
    {
        if (!is_numeric($second)) {
            throw new HM_Ical_Exception(sprintf('Second "%s" is not a number', $second));
        } elseif ($second < 0) {
            throw new HM_Ical_Exception(sprintf('Second "%s" is lower than 0', $second));
        } elseif ($second > 59) {
            throw new HM_Ical_Exception(sprintf('Second "%s" is greater than 59', $second));
        }

        $this->second = (int) $second;

        if ($this->isDate()) {
            $this->isDate(false);
            $this->hour   = 0;
            $this->minute = 0;
        }

        return $this;
    }

    /**
     * Get second.
     *
     * @return integer
     */
    public function getSecond()
    {
        return $this->second;
    }

    /**
     * Check whether the date is within a leap year.
     *
     * @return boolean
     */
    public function isLeapYear()
    {
        if ($this->year <= 1752) {
            return ($this->year % 4 === 0);
        } else {
            return ($this->year % 4 === 0 && $this->year % 100 !== 0 && $this->year % 400 === 0);
        }
    }

    /**
     * Get the number of days in date's month.
     *
     * @return integer
     */
    public function getDaysInMonth()
    {
        $days = self::$daysInMonths[$this->month];

        if ($this->month === 2 && $this->isLeapYear()) {
            $days += 1;
        }

        return $days;
    }

    /**
     * Get the number of days in date's year.
     *
     * @return integer
     */
    public function getDaysInYear()
    {
        if ($this->isLeapYear()) {
            return 366;
        } else {
            return 365;
        }
    }

    /**
     * Set the day of the year.
     *
     * @param  integer $doy
     * @return void
     */
    public function setDayOfYear($doy)
    {
        if ($doy < 1 && $doy > $this->getDaysInYear()) {
            return;
        }

        $isLeap = $this->isLeapYear() ? 1 : 0;

        for ($month = 11; $month >= 0; $month--) {
            if ($doy > self::$daysInYearPassedPerMonth[$isLeap][$month]) {
                $this->month = $month + 1;
                $this->day   = $doy - self::$daysInYearPassedPerMonth[$isLeap][$month];
                break;
            }
        }
    }

    /**
     * Get the day of year of of this date.
     *
     * @return integer
     */
    public function getDayOfYear()
    {
        return self::$daysInYearPassedPerMonth[$this->isLeapYear() ? 1 : 0][$this->month - 1] + $this->day;
    }

    /**
     * Get the julian date.
     *
     * @return integer
     */
    public function getJulianDate()
    {
        $gyr = $this->year + (0.01 * $this->month) + (0.0001 * $this->day) + 1.0e-9;

        if ($this->month <= 2) {
            $iy0 = $this->year - 1;
            $im0 = $this->month + 12;
        } else {
            $iy0 = $this->year;
            $im0 = $this->month;
        }

        $ia = (int) ($iy0 / 100);
        $ib = 2 - $ia + ($ia >> 2);

        $julianDate = (int) (365.25 * $iy0) + (int) (30.6001 * ($im0 + 1)) + (int) ($this->day + 1720994);

        if ($gyr > 1582.1015) {
            $julianDate += $ib;
        }

        return $julianDate + 0.5;
    }

    /**
     * Get the weekday of this date.
     *
     * Returns 1 for Sunday, 7 for Saturday.
     *
     * @return integer
     */
    public function getWeekday()
    {
        return (($this->getJulianDate() + 1.5) % 7) + 1;
    }

    /**
     * Get the week number of this date.
     *
     * @param  integer $firstWeekDay
     * @return integer
     */
    public function getWeekNo($firstWeekDay = 1)
    {
        $dayOfYear = $this->getDayOfYear();
        $weekday   = $this->getWeekday();

        if ($firstWeekDay > 1 && $firstWeekDay < 8) {
            $weekday -= $firstWeekDay - 1;

            if ($weekday < 1) {
                $weekday = 7 + $weekday;
            }
        }

        return (int) (($dayOfYear - $weekday + 10) / 7);
    }

    /**
     * Set or check whether the datetime is in UTC.
     *
     * @param  boolean $isUtc
     * @return boolean
     */
    public function isUtc($isUtc = null)
    {
        if ($isUtc !== null) {
            $this->isUtc = (bool) $isUtc;
        }

        return $this->isUtc;
    }

    /**
     * Set or check whether the datetime is a date without time.
     *
     * @param  boolean $isDate
     * @return boolean
     */
    public function isDate($isDate = null)
    {
        if ($isDate !== null) {
            $this->isDate = (bool) $isDate;

            if ($isDate) {
                $this->hour   = null;
                $this->minute = null;
                $this->second = null;
            } else {
                $this->hour   = 0;
                $this->minute = 0;
                $this->second = 0;
            }
        }

        return $this->isDate;
    }

    /**
     * Get unix timestamp representation.
     *
     * @param  HM_Ical_Timezone $timezone
     * @return integer
     */
    public function getTimestamp(HM_Ical_Timezone $timezone = null)
    {
        if ($timezone === null) {
            if ($this->isUtc()) {
                // Fixed time
                return gmmktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
            } else {
                // Floating time (relative to the user)
                return mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
            }
        } else {

        }
    }

    /**
     * fromString(): defined by Value interface.
     *
     * @see    Value::fromString()
     * @param  string $string
     * @return HM_Ical_Property_Value_Interface
     */
    public static function fromString($string)
    {
        if (!preg_match('(^(?<year>\d{4})(?<month>\d{2})(?<day>\d{2})(?<timepart>T(?<hour>\d{2})(?<minute>\d{2})(?<second>\d{2})(?<UTC>Z)?)?$)S', $string, $match)) {
            return null;
        }

        if (isset($match['timepart'])) {
            return new self($match['year'], $match['month'], $match['day'], $match['hour'], $match['minute'], $match['second'], isset($match['UTC']));
        } else {
            return new self($match['year'], $match['month'], $match['day']);
        }
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return str_pad($this->year, 4, '0',STR_PAD_LEFT)
            .str_pad($this->month, 2, '0', STR_PAD_LEFT)
            .str_pad($this->day, 2, '0', STR_PAD_LEFT)
            .'T'
            .str_pad($this->hour, 2, '0', STR_PAD_LEFT)
            .str_pad($this->minute, 2, '0', STR_PAD_LEFT)
            .str_pad($this->second, 2, '0', STR_PAD_LEFT);
    }

}