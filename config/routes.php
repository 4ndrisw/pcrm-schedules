<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['schedules/schedule/(:num)/(:any)'] = 'schedule/index/$1/$2';

/**
 * @since 2.0.0
 */
$route['schedules/list'] = 'myschedule/list';
$route['schedules/show/(:num)/(:any)'] = 'myschedule/show/$1/$2';
$route['schedules/office/(:num)/(:any)'] = 'myschedule/office/$1/$2';
$route['schedules/pdf/(:num)'] = 'myschedule/pdf/$1';
$route['schedules/office_pdf/(:num)'] = 'myschedule/office_pdf/$1';
