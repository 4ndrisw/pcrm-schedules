<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->model('schedules_model');

$statuses = $CI->schedules_model->get_statuses();

$aColumns = [
    //'subject',
    'number',
    db_prefix() . 'clients.company',
    'project_id',
    'status',
    //db_prefix() . 'projects.name',
    db_prefix() . 'schedules.date',
    'acceptance_firstname',
    db_prefix() . 'schedules.acceptance_date',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'schedules';


$join = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'schedules.clientid',
    //'LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . db_prefix() . 'schedules.project_id',
];



$where  = [];

$additionalColumns = hooks()->apply_filters('schedules_table_additional_columns_sql', [
    'id',
]);

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalColumns);

$output  = $result['output'];
$rResult = $result['rResult'];

json_encode($rResult);

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'number') {
            $_data = '<a href="' . admin_url('schedules/schedule/' . $aRow['id']) . '">' . format_schedule_number($aRow['id']) . '</a>';
            $_data .= '<div class="row-options">';
            $_data .= '<a href="' . admin_url('schedules/update/' . $aRow['id']) . '">' . _l('edit') . '</a>';

            if (has_permission('schedules', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('schedules/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }
            $_data .= '</div>';
        }elseif ($aColumns[$i] == 'project_id') {
            $_data = get_project_name_by_id($_data);
        }
        /*elseif ($aColumns[$i] == 'projects_name') {
            $_data = $_data;
        }*/
        elseif ($aColumns[$i] == 'status') {

            $span = '';
                //if (!$locked) {
                    $span .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
                    $span .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                    $span .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
                    $span .= '</a>';

                    $span .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['id'] . '">';
                    foreach ($statuses as $scheduleChangeStatus) {
                        if ($aRow['status'] != $scheduleChangeStatus) {
                            $span .= '<li>
                          <a href="#" onclick="schedule_mark_as(' . $scheduleChangeStatus . ',' . $aRow['id'] . '); return false;">
                             ' . format_schedule_status($scheduleChangeStatus) . '
                          </a>
                       </li>';
                        }
                    }
                    $span .= '</ul>';
                    $span .= '</div>';
                //}
                $span .= '</span>';
            

            if ($aRow['status'] == 1) {
                $outputStatus = '<span class="label label-danger inline-block">' . _l('schedule_status_draft') . $span;
            } elseif ($aRow['status'] == 2) {
                $outputStatus = '<span class="label label-info inline-block">' . _l('schedule_status_sent') . $span;
            } elseif ($aRow['status'] == 3) {
                $outputStatus = '<span class="label label-default inline-block">' . _l('schedule_status_declined') . $span;
            } elseif ($aRow['status'] == 4) {
                $outputStatus = '<span class="label label-success inline-block">' . _l('schedule_status_accepted') . '</span>';
            } elseif ($aRow['status'] == 5) {
                $outputStatus = '<span class="label label-primary inline-block">' . _l('schedule_status_expired') . $span;
            }

            $_data = $outputStatus;


        } elseif ($aColumns[$i] == 'date') {
            $_data = _d($_data);
        } elseif ($aColumns[$i] == 'acceptance_firstname') {
            //$_data = $_data;
            $_data = $aRow['acceptance_firstname'] .' '. $aRow['acceptance_lastname'];
        }elseif ($aColumns[$i] == 'acceptance_date') {
            $_data = _dt($_data); 
        }


        $row[] = $_data;
    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
