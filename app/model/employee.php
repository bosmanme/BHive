<?php
/**
 * Employee model
 *
 */

class Employee extends Model
{
    /**
     * External applcation id
     */
     protected $extId;

     protected $surname;
     protected $lastname;

     const STATUS_ACTIVE    = 'A';
     const STATUS_LEAVE     = 'L';
     const STATUS_INACTIVE  = 'I';
     const STATUS_PENSION   = 'P';
     const STATUS_RESIGNED  = 'R';

     public static $statusses = [
        self::STATUS_ACTIVE     => 'Active',
        self::STATUS_LEAVE      => 'On leave',
        self::STATUS_PENSION    => 'Pension',
        self::STATUS_RESIGNED   => 'Resigned',
     ];
     public static $statusPriorities = [
        self::STATUS_ACTIVE     => 3,
        self::STATUS_LEAVE      => 2,
        self::STATUS_INACTIVE   => 1,

        self::STATUS_PENSION    => 0,
        self::STATUS_RESIGNED   => 0,
    ];

    public function getName($short = false)
    {
        if ($short) {
            return $this->lastname . ' ' . substr($this->surname, 0, 1) . '.';
        } else {
            return $this->lastname . ' ' . $this->surname;
        }
    }

    public function getEntity($date = null)
    {
         $date = new Date($date);

         // ...
    }
}
