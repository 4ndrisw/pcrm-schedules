<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Schedule_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
                [
                    'name'      => 'Schedule Link',
                    'key'       => '{schedule_link}',
                    'available' => [
                        'schedule',
                    ],
                ],
                [
                    'name'      => 'Schedule Number',
                    'key'       => '{schedule_number}',
                    'available' => [
                        'schedule',
                    ],
                ],
                [
                    'name'      => 'Reference no.',
                    'key'       => '{schedule_reference_no}',
                    'available' => [
                        'schedule',
                    ],
                ],
                [
                    'name'      => 'Schedule Expiry Date',
                    'key'       => '{schedule_expirydate}',
                    'available' => [
                        'schedule',
                    ],
                ],
                [
                    'name'      => 'Schedule Date',
                    'key'       => '{schedule_date}',
                    'available' => [
                        'schedule',
                    ],
                ],
                [
                    'name'      => 'Schedule Status',
                    'key'       => '{schedule_status}',
                    'available' => [
                        'schedule',
                    ],
                ],
                [
                    'name'      => 'Schedule Sale Agent',
                    'key'       => '{schedule_assigned}',
                    'available' => [
                        'schedule',
                    ],
                ],
                [
                    'name'      => 'Schedule Total',
                    'key'       => '{schedule_total}',
                    'available' => [
                        'schedule',
                    ],
                ],
                [
                    'name'      => 'Schedule Subtotal',
                    'key'       => '{schedule_subtotal}',
                    'available' => [
                        'schedule',
                    ],
                ],
                [
                    'name'      => 'Project name',
                    'key'       => '{project_name}',
                    'available' => [
                        'schedule',
                    ],
                ],
            ];
    }

    /**
     * Merge fields for schedules
     * @param  mixed $schedule_id schedule id
     * @return array
     */
    public function format($schedule_id)
    {
        $fields = [];
        $this->ci->db->where('id', $schedule_id);
        $schedule = $this->ci->db->get(db_prefix().'schedules')->row();

        if (!$schedule) {
            return $fields;
        }

        $currency = get_currency($schedule->currency);

        $fields['{schedule_assigned}']   = get_staff_full_name($schedule->assigned);
        $fields['{schedule_total}']        = app_format_money($schedule->total, $currency);
        $fields['{schedule_subtotal}']     = app_format_money($schedule->subtotal, $currency);
        $fields['{schedule_link}']         = site_url('schedules/show/' . $schedule_id . '/' . $schedule->hash);
        $fields['{schedule_number}']       = format_schedule_number($schedule_id);
        $fields['{schedule_reference_no}'] = $schedule->reference_no;
        $fields['{schedule_expirydate}']   = _d($schedule->expirydate);
        $fields['{schedule_date}']         = _d($schedule->date);
        $fields['{schedule_status}']       = format_schedule_status($schedule->status, '', false);
        $fields['{project_name}']          = get_project_name_by_id($schedule->project_id);
        $fields['{schedule_short_url}']    = get_schedule_shortlink($schedule);

        $custom_fields = get_custom_fields('schedule');
        foreach ($custom_fields as $field) {
            $fields['{' . $field['slug'] . '}'] = get_custom_field_value($schedule_id, $field['id'], 'schedule');
        }

        return hooks()->apply_filters('schedule_merge_fields', $fields, [
        'id'       => $schedule_id,
        'schedule' => $schedule,
     ]);
    }
}
