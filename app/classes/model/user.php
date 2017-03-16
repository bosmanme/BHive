<?php
/**
 *
 * @package    BHive
 * @version    1.0
 * @author     Mathias Bosman
 * @license    MIT License
 * @copyright  2016 - Mathias Bosman
 */

namespace Model;

/**
 * User model (example)
 *
 * @package app
 * @extends Model
 */
class User extends \Model
{
    protected $username;
    protected $name;
    protected $surname;
    protected $status;

    protected $organization;

    public function __get($var)
    {
        switch ($var) {

            case 'organization':
                return null;
                break;

            default:
                return $this->$var;
        }
    }

}
