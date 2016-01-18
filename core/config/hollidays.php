<?php

return [
    /*
	 * Public hollidays
	 *
	 * Format: dd/mm
	 * Or as calculation (+)
	 * Or as function (())
	 */
	 'hollidays' => [
		 'New Year\'s Day'					=> '01/01',
		 'Labour Day'						=> '01/05',
		 'Day of the Flemisch Community'	=> '11/07',
		 'Belgian National Day' 			=> '21/07',
		 'Assumption of Mary'				=> '15/08',
		 'All Saints\' Day'					=> '01/11',
		 'All Souls\' Day'					=> '02/11',
		 'Armistice Day'					=> '11/11',
		 'King\'s Feast'					=> '15/11',
		 'Christmas'						=> '25/12',
		 'Second Christmas Day'				=> '26/12',

		 // Function dates, defined as array ['function', 'parameter']
		 'Easter' => [
			 'function' 	=> 'easter_day',
			 'parameters'	=> ['year'],
		 ],

		 // Calculated hollidays
		 'Ascension'		=> 'Easter + 39', // Usually in May
		 'Easter Monday'	=> 'Easter + 1', // April
		 'Pentecost'		=> 'Easter + 49', // May/June
		 'Pentecost Monday'	=> 'Easter + 50',
	 ],
];
