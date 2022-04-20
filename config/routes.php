<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['schedules/schedule/(:num)/(:any)'] = 'schedule/index/$1/$2';

/**
 * @deprecated
 */
$route['viewschedule/(:num)/(:any)'] = 'schedule/index/$1/$2';

/**
 * @since 2.0.0
 */
$route['schedules/show/(:num)/(:any)'] = 'myschedule/show/$1/$2';
