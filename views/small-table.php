<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'subject',
    'CONCAT(firstname," ", lastname)',
    'achievement',
    'start_date',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'schedules';

$join = ['LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'schedules.staff_id'];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], ['id']);

$output  = $result['output'];
$rResult = $result['rResult'];

json_encode($rResult);

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'subject') {
            $_data = '<a href="' . admin_url('schedules/schedule/' . $aRow['id']) . '">' . $_data . '</a>';
            $_data .= '<div class="row-options">';
            $_data .= '<a href="' . admin_url('schedules/edit/' . $aRow['id']) . '">' . _l('edit') . '</a>';

            if (has_permission('schedules', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('schedules/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }
            $_data .= '</div>';
        } elseif ($aColumns[$i] == 'start_date') {
            $_data = _d($_data);
        }

        $row[] = $_data;
    }
    ob_start();
    $achievement          = $this->ci->schedules_model->calculate_schedule_achievement($aRow['id']);
    
    ob_end_clean();
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
