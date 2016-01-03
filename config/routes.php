<?php
/**
* Routing file
*
* Enables specific default controllers and actions. Also used to specify custom
* redirects using regular expressions.
*
*/

$routing = [
    '/login(.*)/'   => 'user/login/',
    '/logout(.*)/'  => 'user/logout/',
];
?>
