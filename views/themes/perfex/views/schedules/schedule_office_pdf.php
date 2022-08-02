<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:27px;">' . _l('schedule_office_pdf_heading') . '</span><br />';
$info_right_column .= '<b style="color:#4e4e4e;"># ' . str_replace("SCH","SCH-UPT",$schedule_number) . '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    $info_right_column .= '<br /><span style="color:rgb(' . schedule_status_color_pdf($status) . ');text-transform:uppercase;">' . format_schedule_status($status, '', false) . '</span>';
}

// Add logo
$info_left_column .= pdf_logo_url();
// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(8);

$organization_info = '<div style="color:#424242;">';
    $organization_info .= format_organization_info();
$organization_info .= '</div>';

// Schedule to
$schedule_info = '<b>' . _l('schedule_office_to') . '</b>';
$schedule_info .= '<div style="color:#424242;">';
$schedule_info .= format_office_info($schedule->office, 'schedule', 'billing');
$schedule_info .= '</div>';

$left_info  = $swap == '1' ? $schedule_info : $organization_info;
$right_info = $swap == '1' ? $organization_info : $schedule_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// Schedule to
$left_info ='<p>' . _l('schedule_opening') . ',</p>';
$left_info .= _l('schedule_client');
$left_info .= '<div style="color:#424242;">';
$left_info .= format_customer_info($schedule, 'schedule', 'billing');
$left_info .= '</div>';

$right_info = '';

$pdf->ln(4);
pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 1) - $dimensions['lm']);

$organization_info = '<strong>'. _l('schedule_members') . ': </strong><br />';

$CI = &get_instance();
$CI->load->model('schedules_model');
$schedule_members = $CI->schedules_model->get_schedule_members($schedule->id,true);
$i=1;
foreach($schedule_members as $member){
  $organization_info .=  $i.'. ' .$member['firstname'] .' '. $member['lastname']. '<br />';
  $i++;
}

$schedule_info = '<br />' . _l('schedule_data_date') . ': ' . _d($schedule->date) . '<br />';


if ($schedule->project_id != 0 && get_option('show_project_on_schedule') == 1) {
    $schedule_info .= _l('project') . ': ' . get_project_name_by_id($schedule->project_id) . '<br />';
}


$left_info  = $swap == '1' ? $schedule_info : $organization_info;
$right_info = $swap == '1' ? $organization_info : $schedule_info;

$pdf->ln(4);
pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// The Table
$pdf->Ln(hooks()->apply_filters('pdf_info_and_table_separator', 6));

// The items table
$items = get_schedule_items_table_data($schedule, 'schedule', 'pdf');

$tblhtml = $items->table();

$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->SetFont($font_name, '', $font_size);

$assigned_path = <<<EOF
        <img width="150" height="150" src="$schedule->assigned_path">
    EOF;    
$assigned_info = '<div style="text-align:center;">';
    $assigned_info .= get_option('invoice_company_name') . '<br />';
    $assigned_info .= $assigned_path . '<br />';

if ($schedule->assigned != 0 && get_option('show_assigned_on_schedules') == 1) {
    $assigned_info .= get_staff_full_name($schedule->assigned);
}
$assigned_info .= '</div>';

$acceptance_path = <<<EOF
    <img src="$schedule->acceptance_path">
EOF;
$client_info = '<div style="text-align:center;">';
    $client_info .= $schedule->client_company .'<br />';

if ($schedule->signed != 0) {
    $client_info .= _l('schedule_signed_by') . ": {$schedule->acceptance_firstname} {$schedule->acceptance_lastname}" . '<br />';
    $client_info .= _l('schedule_signed_date') . ': ' . _dt($schedule->acceptance_date_string) . '<br />';
    $client_info .= _l('schedule_signed_ip') . ": {$schedule->acceptance_ip}" . '<br />';

    $client_info .= $acceptance_path;
    $client_info .= '<br />';
}
$client_info .= '</div>';


$left_info  = $swap == '1' ? $client_info : $assigned_info;
$right_info = $swap == '1' ? $assigned_info : $client_info;
pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);
