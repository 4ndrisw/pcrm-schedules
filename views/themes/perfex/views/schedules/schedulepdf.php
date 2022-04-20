<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:27px;">' . _l('schedule_pdf_heading') . '</span><br />';
$info_right_column .= '<b style="color:#4e4e4e;"># ' . $schedule_number . '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    $info_right_column .= '<br /><span style="color:rgb(' . schedule_status_color_pdf($status) . ');text-transform:uppercase;">' . format_schedule_status($status, '', false) . '</span>';
}

// Add logo
$info_left_column .= pdf_logo_url();
// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(10);

$organization_info = '<div style="color:#424242;">';
    $organization_info .= format_organization_info();
$organization_info .= '</div>';

// Schedule to
$schedule_info = '<b>' . _l('schedule_to') . '</b>';
$schedule_info .= '<div style="color:#424242;">';
$schedule_info .= format_customer_info($schedule, 'schedule', 'billing');
$schedule_info .= '</div>';

// ship to to
if ($schedule->include_shipping == 1 && $schedule->show_shipping_on_schedule == 1) {
    $schedule_info .= '<br /><b>' . _l('ship_to') . '</b>';
    $schedule_info .= '<div style="color:#424242;">';
    $schedule_info .= format_customer_info($schedule, 'schedule', 'shipping');
    $schedule_info .= '</div>';
}


$organization_info .= '<p><strong>'. _l('schedule_members_name') . '</strong></p>';

$CI = &get_instance();
$CI->load->model('schedules_model');
$schedule_members = $CI->schedules_model->get_schedule_members($schedule->id,true);
$i=1;
foreach($schedule_members as $member){
  $organization_info .=  $i.'. ' .$member['firstname'] .' '. $member['lastname']. '<br />';
  $i++;
}

$schedule_info .= '<br />' . _l('schedule_data_date') . ': ' . _d($schedule->date) . '<br />';

if (!empty($schedule->expirydate)) {
    $schedule_info .= _l('schedule_data_expiry_date') . ': ' . _d($schedule->expirydate) . '<br />';
}

if (!empty($schedule->reference_no)) {
    $schedule_info .= _l('reference_no') . ': ' . $schedule->reference_no . '<br />';
}

if ($schedule->project_id != 0 && get_option('show_project_on_schedule') == 1) {
    $schedule_info .= _l('project') . ': ' . get_project_name_by_id($schedule->project_id) . '<br />';
}


$left_info  = $swap == '1' ? $schedule_info : $organization_info;
$right_info = $swap == '1' ? $organization_info : $schedule_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// The Table
$pdf->Ln(hooks()->apply_filters('pdf_info_and_table_separator', 6));

// The items table
$items = get_schedule_items_table_data($schedule, 'schedule', 'pdf');

$tblhtml = $items->table();

$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(2);

if ($schedule->assigned != 0 && get_option('show_assigned_on_schedules') == 1) {
    $pdf->Ln(2);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('schedule_staff_string') . ":", 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', get_staff_full_name($schedule->assigned), 0, 1, false, true, 'L', true);
}

if (!empty($schedule->clientnote)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('schedule_order'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $schedule->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($schedule->terms)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('terms_and_conditions') . ":", 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $schedule->terms, 0, 1, false, true, 'L', true);
}

