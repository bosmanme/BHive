<?php
/**
 * Date class
 */
class Date extends DateTime
{
    const TYPE_DATE = 1;
    const TYPE_TIME = 2;

    const FORMAT_DEFAULT    = 1;
    const FORMAT_ISO8601    = 2;
    const FORMAT_SHORT      = 3;
    const FORMAT_MEDIUM     = 4;
    const FORMAT_FULL       = 5;
    const FORMAT_DATETIME   = 6;

    const SUNDAY    = 0;
    const MONDAY    = 1;
    const TUESDAY   = 2;
    const WEDNESDAY = 3;
    const THURSDAY  = 4;
    const FRIDAY    = 5;
    const SATURDAY  = 6;

    /**
     * Names of the weekdays
     * @var array
     */
    public static $days = [
            self::SUNDAY    => 'Sunday',
            self::MONDAY    => 'Monday',
            self::TUESDAY   => 'Tuesday',
            self::WEDNESDAY => 'Wednesday',
            self::THURSDAY  => 'Thursday',
            self::FRIDAY    => 'Friday',
            self::SATURDAY  => 'Saturday',
    ];

    /**
     * Labels for each format
     * @var array
     */
    public static $formatLabels = [
        self::FORMAT_DEFAULT    => 'mm/dd/yyyy',
        self::FORMAT_ISO8601    => 'yyyy-mm-dd',
        self::FORMAT_SHORT      => 'd M, y',
        self::FORMAT_MEDIUM     => 'd MM, y',
        self::FORMAT_FULL       => 'DD, d MM, yy',
        self::FORMAT_DATETIME   => 'yyyy-mm-dd hh:ii:ss'
    ];

    /**
     * All formats
     * @var array
     */
    public static $formats = [
        self::FORMAT_DEFAULT    => 'd/m/Y',
        self::FORMAT_ISO8601    => 'Y-m-d',
        self::FORMAT_SHORT      => 'd M, y',
        self::FORMAT_MEDIUM     => 'd F, Y',
        self::FORMAT_FULL       => 'l, d F, Y',
        self::FORMAT_DATETIME   => 'Y-m-d H:i:s',
    ];

    /**
     * Type of the date, either a plain date or a date with timestamp
     * @var int
     */
    public $type = self::TYPE_DATE;

    /**
     * Creates a timezone from a given object
     * @param  mixed $object
     * @return DateTimeZone
     */
    protected static function safeCreateDateTimeZone($object)
    {
        if ($object instanceof DateTimeZone) {
            return $object;
        }

        $tz = @timezone_open((string) $object);

        if ($tz === false) {
            throw new InvalidArgumentException('Unknown or bad timezone ('.$object.')');
        }

        return $tz;
    }

    /**
     * Returns a new Date instance based on DateTime
     * @param  DateTime $dt
     * @return Date
     */
    public static function instance(DateTime $dt)
    {
        return new self($dt->format(self::FORMAT_DATETIME), $dt->getTimeZone());
    }

    /**
     * Formats the date
     * @param  string  $format
     * @param  boolean $forceServerTimezone Force the default server time
     * @return string
     */
    public function format($format = null, $forceServerTimezone = false)
    {
        # if ( ! $forceServerTimezone) {
            //Determin timezone
            # $user = User::isLoggedIn();
            # if ($user) {
            #     $tz = $user->timezone ? $user->timezone :  date_default_timezone_get();
            # } else {
            #    $tz =  date_default_timezone_get();
            # }

        # } else {
            // Force server timezone, possibly changed via configuration (see config)
           $tz = date_default_timezone_get();
        # }
        parent::setTimezone(new DateTimeZone($tz));

        $format = $format ? $format : Setting::getValue('dateFormatPhp');

        return parent::format($format);
    }

    /**
     * Returns current date and time
     * @param  DateTimeZone|string $tz The timezone string or a DateTimeZone object
     * @return Date
     */
    public static function now($tz = null)
    {
        return new self(null, $tz);
    }

    /**
     * Returns the date of today
     * @param  DateTimeZone|string $tz The timezone string or a DateTimeZone object
     * @return Date
     */
    public static function today($tz = null)
    {
        return Date::now($tz)->startOfDay();
    }

    /**
     * Returns the date of tomorrow
     * @param  DateTimeZone|string $tz The timezone string or a DateTimeZone object
     * @return Date
     */
    public static function tomorrow($tz = null)
    {
        return Date::today($tz)->addDay();
    }

    /**
     * Returns the date of yesterday
     * @param  DateTimeZone|string $tz The timezone string or a DateTimeZone object
     * @return Date
     */
    public static function yesterday($tz = null)
    {
        return Date::today($tz)->subDay();
    }

    /**
     * Create new Date based on parameters
     * @param  int $year
     * @param  int $month
     * @param  int $day
     * @param  int $hour
     * @param  int $minute
     * @param  int $second
     * @param  DateTimeZone|string $tz The timezone string or a DateTimeZone object
     * @return Date
     */
    public static function create($year = null, $month = null, $day = null, $hour = null, $minute = null, $second = null, $tz = null)
    {
        $year   = ($year === null) ? date('Y') : $year;
        $month  = ($month === null) ? date('n') : $month;
        $day    = ($day === null) ? date('j') : $day;

        if ($hour === null) {
            $hour   = date('G');
            $minute = ($minute === null) ? date('i') : $minute;
            $second = ($second === null) ? date('s') : $second;
        } else {
            $minute = ($minute === null) ? 0 : $minute;
            $second = ($second === null) ? 0 : $second;
        }

        return self::createFromFormat('Y-n-j G:i:s', sprintf('%s-%s-%s %s:%02s:%02s', $year, $month, $day, $hour, $minute, $second), $tz);
    }

    /**
     * Create new Date based on parameters
     * @param  int $year
     * @param  int $month
     * @param  int $day
     * @param  DateTimeZone|string $tz The timezone string or a DateTimeZone object
     * @return Date
     */
    public static function createFromDate($year = null, $month = null, $day = null, $tz = null)
    {
        return self::create($year, $month, $day, null, null, null, $tz);
    }

    /**
     * Create new Date based on parameters
     * @param  int $hour
     * @param  int $minute
     * @param  int $second
     * @param  DateTimeZone|string $tz The timezone string or a DateTimeZone object
     * @return Date
     */
    public static function createFromTime($hour = null, $minute = null, $second = null, $tz = null)
    {
        return self::create(null, null, null, $hour, $minute, $second, $tz);
    }

    /**
     * Create Date based upon format
     * @param  string $format
     * @param  int $time
     * @param  DateTimeZone|string $object The tz object or string
     * @return Date
     */
    public static function createFromFormat($format, $time, $object = null)
    {
        if ($object !== null) {
            $dt = parent::createFromFormat($format, $time, self::safeCreateDateTimeZone($object));
        } else {
            $dt = parent::createFromFormat($format, $time);
        }

        if ($dt instanceof DateTime) {
            return self::instance($dt);
        }

        $errors = DateTime::getLastErrors();
        throw new InvalidArgumentException(implode(PHP_EOL, $errors['error']));
    }

    /**
     * Create Date based upon timestamp
     * @param  int $timestamp
     * @param  DateTimeZone|string $object The tz object or string
     * @return Date
     */
    public static function createFromTimestamp($timestamp, $tz = null)
    {
        return self::now($tz)->setTimestamp($timestamp);
    }

    public function __get($name)
    {
        switch ($name) {
            case 'year':            return intval($this->format('Y')); break;
            case 'month':           return intval($this->format('n')); break;
            case 'day':             return intval($this->format('j')); break;
            case 'hour':            return intval($this->format('G')); break;
            case 'minute':          return intval($this->format('i')); break;
            case 'second':          return intval($this->format('s')); break;
            case 'dayOfWeek':       return intval($this->format('w')); break;
            case 'dayOfYear':       return intval($this->format('z')); break;
            case 'weekOfYear':      return intval($this->format('W')); break;
            case 'daysInMonth':     return intval($this->format('t')); break;
            case 'timestamp':       return intval($this->format('U')); break;
            case 'age':             return intval($this->diffInYears()); break;
            case 'quarter':         return intval(($this->month - 1) / 3) + 1; break;
            case 'offset':          return $this->getOffset(); break;
            case 'offsetHours':     return $this->getOffset() / 60 / 60; break;
            case 'dst':             return $this->format('I') == '1'; break;
            case 'timezone':        return $this->getTimezone(); break;
            case 'timezoneName':    return $this->getTimezone()->getName(); break;
            case 'tz':              return $this->timezone; break;
            case 'tzName':          return $this->timezoneName; break;

            default:
                throw new InvalidArgumentException(sprintf('Unknown getter "%s"', $name));
        }
    }

    public function __isset($name)
    {
        try {
             $this->__get($name);
        } catch (InvalidArgumentException $e) {
             return false;
        }

        return true;
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case 'year':
                parent::setDate($value, $this->month, $this->day);
                break;
            case 'month':
                parent::setDate($this->year, $value, $this->day);
                break;
            case 'day':
                parent::setDate($this->year, $this->month, $value);
                break;
            case 'hour':
                parent::setTime($value, $this->minute, $this->second);
                break;
            case 'minute':
                parent::setTime($this->hour, $value, $this->second);
                break;
            case 'second':
                parent::setTime($this->hour, $this->minute, $value);
                break;
            case 'timestamp':
                parent::setTimestamp($value);
                break;
            case 'timezone':
                $this->setTimezone($value);
                break;
            case 'tz':
                $this->setTimezone($value);
                break;
            default:
                throw new InvalidArgumentException(sprintf("Unknown setter '%s'", $name));
        }
    }

    /**
     * Sets the year
     * @param  int $value
     * @return Date
     */
    public function year($value)
    {
        $this->year = $value;
        return $this;
    }

    /**
     * Sets the month
     * @param  int $value
     * @return Date
     */
    public function month($value)
    {
        $this->month = $value;
        return $this;
    }

    /**
     * Sets the day
     * @param  int $value
     * @return Date
     */
    public function day($value)
    {
        $this->day = $value;
        return $this;
    }

    /**
     * Sets the date
     * @param int $year
     * @param int $month
     * @param int $day
     * @return Date
     */
    public function setDate($year, $month, $day)
    {
        return $this->year($year)->month($month)->day($day);
    }

    /**
     * Sets the hour
     * @param  int $value
     * @return Date
     */
    public function hour($value)
    {
        $this->hour = $value;
        return $this;
    }

    /**
     * Sets the minute
     * @param  int $value
     * @return Date
     */
    public function minute($value)
    {
        $this->day = $value;
        return $this;
    }

    /**
     * Sets the second
     * @param  int $value
     * @return Date
     */
    public function second($value)
    {
        $this->second = $value;
        return $this;
    }

    /**
     * Set the time
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @return Date
     */
    public function setTime($hour, $minute, $second = null)
    {
        return $this->hour($hour)->minute($minute)->second($second);
    }

    /**
     * Set the date and time
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @return Date
     */
    public function setDateTime($year, $month, $day, $hour, $minute, $second)
    {
        return $this->setDate($year, $month, $day)->setTime($hour, $minute, $second);
    }

    /**
     * Sets the timestamp
     * @param  int $value
     * @return Date
     */
    public function timestamp($value)
    {
        $this->timestamp = $value;
        return $this;
    }

    /**
     * Sets the timezone
     * @param  DateTimezone|string $value
     * @return Date
     */
    public function timezone($value)
    {
        return $this->setTimezone($value);
    }

    /**
     * Sets the timezone
     * @param  DateTimezone|string $value
     * @return Date
     */
    public function tz($value)
    {
        return $this->setTimezone($value);
    }

    /**
     * Sets the timezone
     * @param  DateTimezone|string $value
     * @return Date
     */
    public function setTimezone($value)
    {
        parent::setTimezone(self::safeCreateDateTimeZone($value));

        return $this;
    }

    /**
     * Returns DateTimeString format (Y-m-d H:i:s)
     * @return string
     */
    public function __toString()
    {
        return $this->toDateTimeString();
    }

    /**
     * Returns the datestring in ISO8601 format (Y-m-d)
     * @return string
     */
    public function toDateString()
    {
        return $this->format(self::$formats[self::FORMAT_ISO8601]);
    }

    /**
     * Returns the full time string (H:i:s)
     * @return string
     */
    public function toTimeString()
    {
        return $this->format('H:i:s');
    }

    /**
     * Returns DateTimeString format (Y-m-d H:i:s)
     * @return string
     */
    public function toDateTimeString()
    {
        return $this->format(self::$formats[self::FORMAT_DATETIME]);
    }

    /**
     * Returns if the Date is equal to a given date
     * @param  Date   $dt
     * @return boolean
     */
    public function eq(Date $dt)
    {
        return $this == $dt;
    }

    /**
     * Returns if the Date is not equal to a given date
     * @param  Date   $dt
     * @return boolean
     */
    public function ne(Date $dt)
    {
        return !$this->eq($dt);
    }

    /**
     * Returns if the Date is greater then a given date
     * @param  Date   $dt
     * @return boolean
     */
    public function gt(Date $dt)
    {
        return $this > $dt;
    }

    /**
     * Returns if the Date is greater then or equal to a given date
     * @param  Date   $dt
     * @return boolean
     */
    public function gte(Date $dt)
    {
        return $this >= $dt;
    }

    /**
     * Returns if the Date is lesser then a given date
     * @param  Date   $dt
     * @return boolean
     */
    public function lt(Date $dt)
    {
        return $this < $dt;
    }

    /**
     * Returns if the Date is lesser then or equal to a given date
     * @param  Date   $dt
     * @return boolean
     */
    public function lte(Date $dt)
    {
        return $this <= $dt;
    }

    /**
     * Returns if the Date is a holliday (as configured)
     * @return boolean
     */
    public function isHolliday()
    {
        Config::load('hollidays.php');
        $hollidays = Config::get('hollidays', []);

        $hollidays = $this->parseHollidays($hollidays);
        foreach ($hollidays as $holliday) {
            if ($this->eq($holliday)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse an array of given hollidays based on a given year
     * @param array $hollidays array of hollidays
     * @return array|Date Array with parsed Dates
     */
    private function parseHollidays($hollidays)
    {
        $result = [];
        
        foreach ($hollidays as $name => $value) {
            $value = str_replace(' ', '', $value);

            if (is_array($value)) { // Call function

                $function = $value['function'];
                $parameters = $value['parameters'];

                foreach ($parameters as $var) {
                    $pars[] = $this->$var;
                }

                $resultTS = call_user_func_array($function, $pars);
                $date = date('m/d/Y', $resultTS);
                $result[$name] = new Date($date);

            } elseif (strpos($value, '+') == true) { // Calculation
                $parts = [];
                $parts = explode('+', $value);

                $baseDate = clone $result[$parts[0]];
                $resultDate = $baseDate->addDays($parts[1]);

                $result[$name] = $resultDate;

            } else { // Just add the year
                $result[$name] = new Date($value . '/' . $this->year);
            }
        }

        return $result;
    }

    /**
     * Returns if the Date is a week day
     * @return boolean
     */
    public function isWeekday()
    {
      return ($this->dayOfWeek != self::SUNDAY && $this->dayOfWeek != self::SATURDAY);
    }

    /**
     * Returns if the Date is a Saturday or Sunday
     * @return boolean
     */
    public function isWeekend()
    {
        return !$this->isWeekDay();
    }

    /**
     * Checks if the date was yesterday
     * @return boolean
     */
    public function isYesterday()
    {
        return $this->toDateString() === self::now($this->tz)->subDay()->toDateString();
    }

    /**
     * Checks if the date is today
     * @return boolean
     */
    public function isToday()
    {
        return $this->toDateString() === self::now($this->tz)->toDateString();
    }

    /**
     * Checks if the date is tomorrow
     * @return boolean
     */
    public function isTomorrow()
    {
        return $this->toDateString() === self::now($this->tz)->addDay()->toDateString();
    }

    /**
     * Checks if the date is in the future
     * @return boolean
     */
    public function isFuture()
    {
        return $this->gt(self::now($this->tz));
    }

    /**
     * Checks if the date is in the past
     * @return boolean
     */
    public function isPast()
    {
        return !$this->isFuture();
    }

    /**
     * Returns if the date is in a leap year
     * @return boolean
     */
    public function isLeapYear()
    {
        return $this->format('L') == '1';
    }

    /**
     * Adds years to the date
     * @param int $value Amount of years to add
     * @return Date
     */
    public function addYears($value)
    {
        $interval = new DateInterval(sprintf("P%dY", abs($value)));
        if ($value >= 0) {
            $this->add($interval);
        } else {
            $this->sub($interval);
        }

      return $this;
    }

    /**
     * Adds a year
     * @return Date
     */
    public function addYear()
    {
        return $this->addYears(1);
    }

    /**
     * Substracts a year
     * @return Date
     */
    public function subYear()
    {
        return $this->addYears(-1);
    }

    /**
     * Substract multiple years
     * @param  int $value Amount of years to substract
     * @return Date
     */
    public function subYears($value)
    {
        return $this->addYears(-1 * $value);
    }

    /**
     * Add several months
     * @param int $value Amount
     * @return Date
     */
    public function addMonths($value)
    {
        $interval = new DateInterval(sprintf("P%dM", abs($value)));
        if ($value >= 0) {
            $this->add($interval);
        } else {
            $this->sub($interval);
        }

        return $this;
    }

    /**
     * Add a month
     * @return Date
     */
    public function addMonth()
    {
       return $this->addMonths(1);
    }

    /**
     * Substract a month
     * @return Date
     */
    public function subMonth()
    {
       return $this->addMonths(-1);
    }

    /**
     * Substract several months
     * @param int $value Amount of months to substract
     * @return Date
     */
    public function subMonths($value)
    {
       return $this->addMonths(-1 * $value);
    }

    /**
     * Add several days
     * @param int $value Amount of days to add
     * @return Date
     */
    public function addDays($value)
    {
       $interval = new DateInterval(sprintf("P%dD", abs($value)));
       if ($value >= 0) {
          $this->add($interval);
       } else {
          $this->sub($interval);
       }

       return $this;
    }

    /**
     * Add a day
     * @return Date
     */
    public function addDay()
    {
       return $this->addDays(1);
    }

    /**
     * Substract a day
     * @return Date
     */
    public function subDay()
    {
       return $this->addDays(-1);
    }

    /**
     * Substract several days
     * @param int $value The amount of days to substract
     * @return Date
     */
    public function subDays($value)
    {
       return $this->addDays(-1 * $value);
    }

    /**
     * Add several weekdays
     * @param int $value Amount of weekdays to add
     * @return Date
     */
    public function addWeekdays($value)
    {
       $absValue = abs($value);
       $direction = $value < 0 ? -1 : 1;

       while ($absValue > 0) {
          $this->addDays($direction);

          while ($this->isWeekend()) {
             $this->addDays($direction);
          }

          $absValue--;
       }

       return $this;
    }

    /**
     * Adds a weekday
     * @return Date
     */
    public function addWeekday()
    {
       return $this->addWeekdays(1);
    }

    /**
     * Substract a weekday
     * @return Date
     */
    public function subWeekday()
    {
       return $this->addWeekdays(-1);
    }

    /**
     * Substract several weekdays
     * @param int $value The amount of weekdays to substract
     * @return Date
     */
    public function subWeekdays($value)
    {
       return $this->addWeekdays(-1 * $value);
    }

    /**
     * Add weeks
     * @param int $value the amount of weeks to add
     * @return Date
     */
    public function addWeeks($value)
    {
       $interval = new DateInterval(sprintf("P%dW", abs($value)));
       if ($value >= 0) {
          $this->add($interval);
       } else {
          $this->sub($interval);
       }

       return $this;
    }

    /**
     * Add a week
     * @return Date
     */
    public function addWeek()
    {
       return $this->addWeeks(1);
    }

    /**
     * Substract a week
     * @return Date
     */
    public function subWeek()
    {
       return $this->addWeeks(-1);
    }

    /**
     * Substract several weeks
     * @param int $value The amount of weeks to substract
     * @return Date
     */
    public function subWeeks($value)
    {
       return $this->addWeeks(-1 * $value);
    }

    /**
     * Add several hours
     * @param int $value Amount of hours to add
     * @return Date
     */
    public function addHours($value)
    {
       $interval = new DateInterval(sprintf("PT%dH", abs($value)));
       if ($value >= 0) {
          $this->add($interval);
       } else {
          $this->sub($interval);
       }

       return $this;
    }

    /**
     * Add an hour to the date
     * @return Date
     */
    public function addHour()
    {
       return $this->addHours(1);
    }

    /**
     * Substract an hour
     * @return Date
     */
    public function subHour()
    {
       return $this->addHours(-1);
    }

    /**
     * Substract several hours
     * @param int $value Amount of hours to substract
     * @return Date
     */
    public function subHours($value)
    {
       return $this->addHours(-1 * $value);
    }

    /**
     * Add several minutes
     * @param int $value Amount of minutes to add
     * @return Date
     */
    public function addMinutes($value)
    {
       $interval = new DateInterval(sprintf("PT%dM", abs($value)));
       if ($value >= 0) {
          $this->add($interval);
       } else {
          $this->sub($interval);
       }

       return $this;
    }

    /**
     * Add one minute
     * @return Date
     */
    public function addMinute()
    {
       return $this->addMinutes(1);
    }

    /**
     * Substract a minute
     * @return Date
     */
    public function subMinute()
    {
       return $this->addMinutes(-1);
    }

    /**
     * Substract several minutes
     * @param int $value The amount of minutes to substract
     * @return Date
     */
    public function subMinutes($value)
    {
       return $this->addMinutes(-1 * $value);
    }

    /**
     * Add several seconds
     * @param int $value The amount of seconds to add
     * @return Date
     */
    public function addSeconds($value)
    {
       $interval = new DateInterval(sprintf("PT%dS", abs($value)));
       if ($value >= 0) {
          $this->add($interval);
       } else {
          $this->sub($interval);
       }

       return $this;
    }

    /**
     * Add one second
     * @return Date
     */
    public function addSecond()
    {
       return $this->addSeconds(1);
    }

    /**
     * Substract a second
     * @return Date
     */
    public function subSecond()
    {
       return $this->addSeconds(-1);
    }

    /**
     * Substract several seconds
     * @param int $value Substract several seconds
     * @return Date
     */
    public function subSeconds($value)
    {
       return $this->addSeconds(-1 * $value);
    }

    /**
     * Return the start of the date (00:00:00)
     * @return Date
     */
    public function startOfDay()
    {
       return $this->hour(0)->minute(0)->second(0);
    }

    /**
     * Returns the end of the day (23:59:59)
     * @return type
     */
    public function endOfDay()
    {
       return $this->hour(23)->minute(59)->second(59);
    }

    /**
     * Returns the start of the month (of this month)
     * @return Date
     */
    public function startOfMonth()
    {
       return $this->startOfDay()->day(1);
    }

    /**
     * Returns this date's month
     * @return Date
     */
    public function endOfMonth()
    {
       return $this->day($this->daysInMonth)->endOfDay();
    }

    /**
     * Returns the difference in years
     * @param Date $dt
     * @param boolean $abs
     * @return int
     */
    public function diffInYears(Date $dt = null, $abs = true)
    {
       $dt = ($dt === null) ? Date::now($this->tz) : $dt;
       $sign = ($abs) ? '' : '%r';

       return intval($this->diff($dt)->format($sign.'%y'));
    }

    /**
     * Returns the difference in months
     * @param Date $dt
     * @param boolean $abs
     * @return int
     */
    public function diffInMonths(Date $dt = null, $abs = true)
    {
       $dt = ($dt === null) ? Date::now($this->tz) : $dt;
       list($sign, $years, $months) = explode(':', $this->diff($dt)->format('%r:%y:%m'));
       $value = ($years * 12) + $months;

       if ($sign === '-' && !$abs) {
          $value = $value * -1;
       }

       return $value;
    }

    /**
     * Returns the difference in days
     * @param Date $dt
     * @param boolean $abs
     * @return int
     */
    public function diffInDays(Date $dt = null, $abs = true)
    {
       $dt = ($dt === null) ? Date::now($this->tz) : $dt;
       $sign = ($abs) ? '' : '%r';

       return intval($this->diff($dt)->format($sign.'%a'));
    }

    /**
     * Returns the difference in hours
     * @param Date $dt
     * @param boolean $abs
     * @return int
     */
    public function diffInHours(Date $dt = null, $abs = true)
    {
       $dt = ($dt === null) ? Date::now($this->tz) : $dt;

       return intval($this->diffInMinutes($dt, $abs) / 60);
    }

    /**
     * Returns the difference in minutes
     * @param Date $dt
     * @param boolean $abs
     * @return int
     */
    public function diffInMinutes(Date $dt = null, $abs = true)
    {
       $dt = ($dt === null) ? Date::now($this->tz) : $dt;

       return intval($this->diffInSeconds($dt, $abs) / 60);
    }

    /**
     * Returns the difference in seconds
     * @param Date $dt
     * @param boolean $abs
     * @return int
     */
    public function diffInSeconds(Date $dt = null, $abs = true)
    {
       $dt = ($dt === null) ? Date::now($this->tz) : $dt;
       list($sign, $days, $hours, $minutes, $seconds) = explode(':', $this->diff($dt)->format('%r:%a:%h:%i:%s'));
       $value = ($days * 24 * 60 * 60) +
                ($hours * 60 * 60) +
                ($minutes * 60) +
                $seconds;

       if ($sign === '-' && !$abs) {
          $value = $value * -1;
       }

       return intval($value);
    }

    /**
    * Modify to the next occurance of a given day of the week.
    * If no dayOfWeek is provided, modify to the next occurance
    * of the current day of the week.  Use the supplied consts
    * to indicate the desired dayOfWeek, ex. Date::MONDAY.
    *
    * @param  int  $dayOfWeek
    * @return mixed
    */
    public function next($dayOfWeek = null)
    {
       $this->startOfDay();
       if ($dayOfWeek === null) $dayOfWeek = $this->dayOfWeek;
       return $this->modify('next ' . self::$days[$dayOfWeek]);
    }

    /**
    * Modify to the last occurance of a given day of the week.
    * If no dayOfWeek is provided, modify to the last occurance
    * of the current day of the week.  Use the supplied consts
    * to indicate the desired dayOfWeek, ex. Date::MONDAY.
    *
    * @param  int  $dayOfWeek
    * @return mixed
    */
    public function previous($dayOfWeek = null)
    {
       $this->startOfDay();
       if ($dayOfWeek === null) $dayOfWeek = $this->dayOfWeek;
       return $this->modify('last ' . self::$days[$dayOfWeek]);
    }

    /**
    * Modify to the first occurance of a given day of the week
    * in the current month. If no dayOfWeek is provided, modify to the
    * first day of the current month.  Use the supplied consts
    * to indicate the desired dayOfWeek, ex. Date::MONDAY.
    *
    * @param  int  $dayOfWeek
    * @return mixed
    */
    public function firstOfMonth($dayOfWeek = null)
    {
       $this->startOfDay();
       if ($dayOfWeek === null) return $this->day(1);
       return $this->modify('first ' . self::$days[$dayOfWeek] . ' of ' . $this->format('F') . ' ' . $this->year);
    }

    /**
    * Modify to the last occurance of a given day of the week
    * in the current month. If no dayOfWeek is provided, modify to the
    * last day of the current month.  Use the supplied consts
    * to indicate the desired dayOfWeek, ex. Date::MONDAY.
    *
    * @param  int  $dayOfWeek
    * @return mixed
    */
    public function lastOfMonth($dayOfWeek = null)
    {
       $this->startOfDay();
       if ($dayOfWeek === null) return $this->day($this->daysInMonth);
       return $this->modify('last ' . self::$days[$dayOfWeek] . ' of ' . $this->format('F') . ' ' . $this->year);
    }

    /**
    * Modify to the given occurance of a given day of the week
    * in the current month. If the calculated occurance is outside the scope
    * of the current month, then return false and no modifications are made.
    * Use the supplied consts to indicate the desired dayOfWeek, ex. Date::MONDAY.
    *
    * @param  int  $nth
    * @param  int  $dayOfWeek
    * @return mixed
    */
    public function nthOfMonth($nth, $dayOfWeek)
    {
       $dt = $this->copy();
       $dt->firstOfMonth();
       $month = $dt->month;
       $year = $dt->year;
       $dt->modify('+' . $nth . ' ' . self::$days[$dayOfWeek]);
       if ($month !== $dt->month || $year !== $dt->year) return false;
       return $this->modify($dt);
    }

    /**
    * Modify to the first occurance of a given day of the week
    * in the current quarter. If no dayOfWeek is provided, modify to the
    * first day of the current quarter.  Use the supplied consts
    * to indicate the desired dayOfWeek, ex. Date::MONDAY.
    *
    * @param  int  $dayOfWeek
    * @return mixed
    */
    public function firstOfQuarter($dayOfWeek = null)
    {
       $this->month(($this->quarter * 3) - 2);
       return $this->firstOfMonth($dayOfWeek);
    }

    /**
    * Modify to the last occurance of a given day of the week
    * in the current quarter. If no dayOfWeek is provided, modify to the
    * last day of the current quarter.  Use the supplied consts
    * to indicate the desired dayOfWeek, ex. Date::MONDAY.
    *
    * @param  int  $dayOfWeek
    * @return mixed
    */
    public function lastOfQuarter($dayOfWeek = null)
    {
       $this->month(($this->quarter * 3));
       return $this->lastOfMonth($dayOfWeek);
    }

    /**
    * Modify to the given occurance of a given day of the week
    * in the current quarter. If the calculated occurance is outside the scope
    * of the current quarter, then return false and no modifications are made.
    * Use the supplied consts to indicate the desired dayOfWeek, ex. Date::MONDAY.
    *
    * @param  int  $nth
    * @param  int  $dayOfWeek
    * @return mixed
    */
    public function nthOfQuarter($nth, $dayOfWeek)
    {
       $dt = $this->copy();
       $dt->month(($this->quarter * 3));
       $last_month = $dt->month;
       $year = $dt->year;
       $dt->firstOfQuarter();
       $dt->modify('+' . $nth . ' ' . self::$days[$dayOfWeek]);
       if ($last_month < $dt->month || $year !== $dt->year) return false;
       return $this->modify($dt);
    }

    /**
    * Modify to the first occurance of a given day of the week
    * in the current year. If no dayOfWeek is provided, modify to the
    * first day of the current year.  Use the supplied consts
    * to indicate the desired dayOfWeek, ex. Date::MONDAY.
    *
    * @param  int  $dayOfWeek
    * @return mixed
    */
    public function firstOfYear($dayOfWeek = null)
    {
       $this->month(1);
       return $this->firstOfMonth($dayOfWeek);
    }

    /**
    * Modify to the last occurance of a given day of the week
    * in the current year. If no dayOfWeek is provided, modify to the
    * last day of the current year.  Use the supplied consts
    * to indicate the desired dayOfWeek, ex. Date::MONDAY.
    *
    * @param  int  $dayOfWeek
    * @return mixed
    */
    public function lastOfYear($dayOfWeek = null)
    {
       $this->month(12);
       return $this->lastOfMonth($dayOfWeek);
    }

    /**
    * Modify to the given occurance of a given day of the week
    * in the current year. If the calculated occurance is outside the scope
    * of the current year, then return false and no modifications are made.
    * Use the supplied consts to indicate the desired dayOfWeek, ex. Date::MONDAY.
    *
    * @param  int  $nth
    * @param  int  $dayOfWeek
    * @return mixed
    */
    public function nthOfYear($nth, $dayOfWeek)
    {
       $dt = $this->copy();
       $year = $dt->year;
       $dt->firstOfYear();
       $dt->modify('+' . $nth . ' ' . self::$days[$dayOfWeek]);
       if ($year !== $dt->year) return false;
       return $this->modify($dt);
    }

    /**
     * When comparing a value in the past to default now:
     * 1 hour ago
     * 5 months ago
     *
     * When comparing a value in the future to default now:
     * 1 hour from now
     * 5 months from now
     *
     * When comparing a value in the past to another value:
     * 1 hour before
     * 5 months before
     *
     * When comparing a value in the future to another value:
     * 1 hour after
     * 5 months after
     */
    public function diffForHumans(Date $other = null)
    {
       $txt = '';

       $isNow = $other === null;

       if ($isNow) {
          $other = self::now();
       }

       $isFuture = $this->gt($other);

       $delta = abs($other->diffInSeconds($this));

       // 30 days per month, 365 days per year... good enough!!
       $divs = array(
          'second' => 60,
          'minute' => 60,
          'hour' => 24,
          'day' => 30,
          'month' => 12
       );

       $unit = 'year';

       foreach ($divs as $divUnit => $divValue) {
          if ($delta < $divValue) {
             $unit = $divUnit;
             break;
          }

          $delta = floor($delta / $divValue);
       }

       if ($delta == 0) {
          $delta = 1;
       }

       $txt = $delta . ' ' . $unit;
       $txt .= $delta == 1 ? '' : 's';

       if ($isNow) {
          if ($isFuture) {
             return $txt . ' from now';
          }

          return $txt . ' ago';
       }

       if ($isFuture) {
          return $txt . ' after';
       }

       return $txt . ' before';
    }

     /**
      * Checks if the date is valid
      * @return boolean
      */
     public function isValid()
     {
         return checkdate($this->format("m"), $this->format("d"), $this->format("Y"));
     }

     /**
      * Checks a given datestring
      * @param string $dateString
      * @return boolean
      */
     public static function checkDate($dateString)
     {
         $d = date('d', strtotime($dateString));
         $m = date('m', strtotime($dateString));
         $y = date('Y', strtotime($dateString));

         if (checkdate($m, $d, $y)) return true;
     }

}
