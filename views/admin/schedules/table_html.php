<?php defined('BASEPATH') or exit('No direct script access allowed');

$table_data = array(
   _l('schedule_dt_table_heading_number'),
   _l('schedule_dt_table_heading_amount'),
   _l('schedules_total_tax'),
   array(
      'name'=>_l('invoice_schedule_year'),
      'th_attrs'=>array('class'=>'not_visible')
   ),
   array(
      'name'=>_l('schedule_dt_table_heading_client'),
      'th_attrs'=>array('class'=> (isset($client) ? 'not_visible' : ''))
   ),
   _l('project'),
   _l('tags'),
   _l('schedule_dt_table_heading_date'),
   _l('schedule_dt_table_heading_expirydate'),
   _l('reference_no'),
   _l('schedule_dt_table_heading_status'));

//$custom_fields = get_custom_fields('schedule',array('show_on_table'=>1));
/*
foreach($custom_fields as $field){
   array_push($table_data,$field['name']);
}
*/

$table_data = hooks()->apply_filters('schedules_table_columns', $table_data);

render_datatable($table_data, isset($class) ? $class : 'schedules');
