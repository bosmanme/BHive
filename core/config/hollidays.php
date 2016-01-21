<?php
/**
 * Part of the BHive framework.
 *
 * @package    BHive
 * @version    1.0
 * @author     Mathias Bosman
 * @license    MIT License
 * @copyright  2016 - Mathias Bosman
 */
 
return [
    /*
	 * Public hollidays
	 *
	 * Format: mm/dd
	 * Or as calculation (+)
	 * Or as function (())
	 */
	 'hollidays' => [
		 'New Year\'s Day'				=> '01/01',
		 'Labour Day'					=> '05/01',
		 'Day of the Flemish Community'	=> '07/11',
		 'Belgian National Day' 		=> '07/21',
		 'Assumption of Mary'			=> '08/15',
		 'All Saints\' Day'				=> '11/01',
		 'All Souls\' Day'				=> '11/02',
		 'Armistice Day'				=> '11/11',
		 'King\'s Feast'				=> '11/15',
		 'Christmas'					=> '12/25',
		 'Second Christmas Day'			=> '12/26',

		 // Function dates, defined as array ['function', 'parameter']
		 'Easter' => [
			 'function' 	=> 'easter_date',
			 'parameters'	=> ['year'],
		 ],

		 // Calculated hollidays
		 'Ascension'		=> 'Easter + 39', // Usually in May
		 'Easter Monday'	=> 'Easter + 1', // April
		 'Pentecost'		=> 'Easter + 49', // May/June
		 'Pentecost Monday'	=> 'Easter + 50',
	 ],
];
