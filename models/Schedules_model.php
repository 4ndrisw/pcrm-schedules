<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Schedules_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  integer (optional)
     * @return object
     * Get single schedule
     */
    public function get($id = '', $exclude_notified = false)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'schedules')->row();
        }

        if ($exclude_notified == true) {
            $this->db->where('notified', 0);
        }

        return $this->db->get(db_prefix() . 'schedules')->result_array();
    }

    public function get_all_schedules($exclude_notified = true)
    {
        if ($exclude_notified) {
            $this->db->where('notified', 0);
        }

        $this->db->order_by('end_date', 'asc');
        $schedules = $this->db->get(db_prefix() . 'schedules')->result_array();

        foreach ($schedules as $key => $val) {
            $schedule = get_schedule_type($val['schedule_type']);

            if (!$schedule || $schedule && isset($schedule['dashboard']) && $schedule['dashboard'] === false) {
                unset($schedules[$key]);
                continue;
            }

            $schedules[$key]['achievement']    = $this->calculate_schedule_achievement($val['id']);
            $schedules[$key]['schedule_type_name'] = format_schedule_type($val['schedule_type']);
        }

        return array_values($schedules);
    }

    public function get_staff_schedules($staff_id, $exclude_notified = true)
    {
        $this->db->where('staff_id', $staff_id);

        if ($exclude_notified) {
            $this->db->where('notified', 0);
        }

        $this->db->order_by('end_date', 'asc');
        $schedules = $this->db->get(db_prefix() . 'schedules')->result_array();

        foreach ($schedules as $key => $val) {
            $schedules[$key]['achievement']    = $this->calculate_schedule_achievement($val['id']);
            $schedules[$key]['schedule_type_name'] = format_schedule_type($val['schedule_type']);
        }

        return $schedules;
    }


    private function map_shipping_columns($data)
    {
        if (!isset($data['include_shipping'])) {
            foreach ($this->shipping_fields as $_s_field) {
                if (isset($data[$_s_field])) {
                    $data[$_s_field] = null;
                }
            }
            $data['show_shipping_on_schedule'] = 1;
            $data['include_shipping']          = 0;
        } else {
            $data['include_shipping'] = 1;
            // set by default for the next time to be checked
            if (isset($data['show_shipping_on_schedule']) && ($data['show_shipping_on_schedule'] == 1 || $data['show_shipping_on_schedule'] == 'on')) {
                $data['show_shipping_on_schedule'] = 1;
            } else {
                $data['show_shipping_on_schedule'] = 0;
            }
        }

        return $data;
    }
    


    /**
     * Add new schedule
     * @param mixed $data All $_POST dat
     * @return mixed
     */
    public function add($data)
    {
        $data['notify_when_fail']    = isset($data['notify_when_fail']) ? 1 : 0;
        $data['notify_when_achieve'] = isset($data['notify_when_achieve']) ? 1 : 0;

        $data['contract_type'] = $data['contract_type'] == '' ? 0 : $data['contract_type'];
        $data['staff_id']      = $data['staff_id'] == '' ? 0 : $data['staff_id'];
        $data['start_date']    = to_sql_date($data['start_date']);
        $data['end_date']      = to_sql_date($data['end_date']);

        //----------------------------

        
        $data['datecreated'] = date('Y-m-d H:i:s');

        $data['addedfrom'] = get_staff_user_id();

        $data['prefix'] = get_option('schedule_prefix');

        $data['number_format'] = get_option('schedule_number_format');


        $data = $this->map_shipping_columns($data);

        $data['billing_street'] = trim($data['billing_street']);
        $data['billing_street'] = nl2br($data['billing_street']);

        if (isset($data['shipping_street'])) {
            $data['shipping_street'] = trim($data['shipping_street']);
            $data['shipping_street'] = nl2br($data['shipping_street']);
        }


        $data['hash'] = app_generate_hash();

        $hook = hooks()->apply_filters('before_schedule_added', [
            'data'  => $data,
            //'items' => $items,
        ]);

        $data  = $hook['data'];
        //$items = $hook['items'];

        $this->db->insert(db_prefix() . 'schedules', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {

            // Update next schedule number in settings
            $this->db->where('name', 'next_schedule_number');
            $this->db->set('value', 'value+1', false);
            $this->db->update(db_prefix() . 'options');

            log_activity('New Schedule Added [ID:' . $insert_id . ']');

            hooks()->do_action('after_schedule_added', $insert_id);

            return $insert_id;
        }

        return false;
    }

    /**
     * Update schedule
     * @param  mixed $data All $_POST data
     * @param  mixed $id   schedule id
     * @return boolean
     */
    public function update($data, $id)
    {
        $data['notify_when_fail']    = isset($data['notify_when_fail']) ? 1 : 0;
        $data['notify_when_achieve'] = isset($data['notify_when_achieve']) ? 1 : 0;

        $data['contract_type'] = $data['contract_type'] == '' ? 0 : $data['contract_type'];
        $data['staff_id']      = $data['staff_id'] == '' ? 0 : $data['staff_id'];
        $data['start_date']    = to_sql_date($data['start_date']);
        $data['end_date']      = to_sql_date($data['end_date']);


        $data['number'] = trim($data['number']);

        $original_schedule = $this->get($id);

        $original_status = $original_schedule->status;

        $original_number = $original_schedule->number;

        $original_number_formatted = format_schedule_number($id);


        $schedule = $this->get($id);

        if ($schedule->notified == 1 && date('Y-m-d') < $data['end_date']) {
            // After schedule finished, user changed/extended date? If yes, set this schedule to be notified
            $data['notified'] = 0;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'schedules', $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Schedule Updated [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete schedule
     * @param  mixed $id schedule id
     * @return boolean
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'schedules');
        if ($this->db->affected_rows() > 0) {
            log_activity('Schedule Deleted [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Notify staff members about schedule result
     * @param  mixed $id          schedule id
     * @param  string $notify_type is success or failed
     * @param  mixed $achievement total achievent (Option)
     * @return boolean
     */
    public function notify_staff_members($id, $notify_type, $achievement = '')
    {
        $schedule = $this->get($id);
        if ($achievement == '') {
            $achievement = $this->calculate_schedule_achievement($id);
        }
        if ($notify_type == 'success') {
            $schedule_desc = 'not_schedule_message_success';
        } else {
            $schedule_desc = 'not_schedule_message_failed';
        }

        if ($schedule->staff_id == 0) {
            $this->load->model('staff_model');
            $staff = $this->staff_model->get('', ['active' => 1]);
        } else {
            $this->db->where('active', 1)
            ->where('staffid', $schedule->staff_id);
            $staff = $this->db->get(db_prefix() . 'staff')->result_array();
        }

        $notifiedUsers = [];
        foreach ($staff as $member) {
            if (is_staff_member($member['staffid'])) {
                $notified = add_notification([
                    'fromcompany'     => 1,
                    'touserid'        => $member['staffid'],
                    'description'     => $schedule_desc,
                    'additional_data' => serialize([
                        format_schedule_type($schedule->schedule_type),
                        $schedule->achievement,
                        $achievement['total'],
                        _d($schedule->start_date),
                        _d($schedule->end_date),
                    ]),
                ]);
                if ($notified) {
                    array_push($notifiedUsers, $member['staffid']);
                }
            }
        }

        pusher_trigger_notification($notifiedUsers);
        $this->mark_as_notified($schedule->id);

        if (count($staff) > 0 && $this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Calculate schedule achievement
     * @param  mixed $id schedule id
     * @return array
     */
    public function calculate_schedule_achievement($id)
    {
        $schedule       = $this->get($id);
        $start_date = $schedule->start_date;
        $end_date   = $schedule->end_date;
        $type       = $schedule->schedule_type;
        $total      = 0;
        $percent    = 0;
        if ($type == 1) {
            $sql = 'SELECT SUM(amount) as total FROM ' . db_prefix() . 'invoicepaymentrecords';

            if ($schedule->staff_id != 0) {
                $sql .= ' JOIN ' . db_prefix() . 'invoices ON ' . db_prefix() . 'invoices.id = ' . db_prefix() . 'invoicepaymentrecords.invoiceid';
            }

            $sql .= ' WHERE ' . db_prefix() . "invoicepaymentrecords.date BETWEEN '" . $start_date . "' AND '" . $end_date . "'";

            if ($schedule->staff_id != 0) {
                $sql .= ' AND (sale_agent=' . $schedule->staff_id . ')';
            }
        } elseif ($type == 8) {
            $sql = 'SELECT SUM(total) as total FROM ' . db_prefix() . 'invoices';

            $sql .= ' WHERE ' . db_prefix() . "invoices.date BETWEEN '" . $start_date . "' AND '" . $end_date . "'";

            if ($schedule->staff_id != 0) {
                $sql .= ' AND (sale_agent=' . $schedule->staff_id . ')';
            }
        } elseif ($type == 2) {
            $sql = 'SELECT COUNT(' . db_prefix() . 'leads.id) as total FROM ' . db_prefix() . "leads WHERE DATE(date_converted) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND status = 1 AND " . db_prefix() . 'leads.id IN (SELECT leadid FROM ' . db_prefix() . 'clients WHERE leadid=' . db_prefix() . 'leads.id)';
            if ($schedule->staff_id != 0) {
                $sql .= ' AND CASE WHEN assigned=0 THEN addedfrom=' . $schedule->staff_id . ' ELSE assigned=' . $schedule->staff_id . ' END';
            }
        } elseif ($type == 3) {
            $sql = 'SELECT COUNT(' . db_prefix() . 'clients.userid) as total FROM ' . db_prefix() . "clients WHERE DATE(datecreated) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND leadid IS NULL";
            if ($schedule->staff_id != 0) {
                $sql .= ' AND addedfrom=' . $schedule->staff_id;
            }
        } elseif ($type == 4) {
            $sql = 'SELECT COUNT(' . db_prefix() . 'clients.userid) as total FROM ' . db_prefix() . "clients WHERE DATE(datecreated) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
            if ($schedule->staff_id != 0) {
                $sql .= ' AND addedfrom=' . $schedule->staff_id;
            }
        } elseif ($type == 5 || $type == 7) {
            $column = 'dateadded';
            if ($type == 7) {
                $column = 'datestart';
            }
            $sql = 'SELECT count(id) as total FROM ' . db_prefix() . 'contracts WHERE ' . $column . " BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND contract_type = " . $schedule->contract_type . ' AND trash = 0';
            if ($schedule->staff_id != 0) {
                $sql .= ' AND addedfrom=' . $schedule->staff_id;
            }
        } elseif ($type == 6) {
            $sql = 'SELECT count(id) as total FROM ' . db_prefix() . "estimates WHERE DATE(invoiced_date) BETWEEN '" . $start_date . "' AND '" . $end_date . "' AND invoiceid IS NOT NULL AND invoiceid NOT IN (SELECT id FROM " . db_prefix() . 'invoices WHERE status=5)';
            if ($schedule->staff_id != 0) {
                $sql .= ' AND (addedfrom=' . $schedule->staff_id . ' OR sale_agent=' . $schedule->staff_id . ')';
            }
        } else {
            $sql = hooks()->apply_filters('calculate_schedule_achievement_sql', '', $schedule);

            if ($sql === '') {
                return;
            }
        }

        $total = floatval($this->db->query($sql)->row()->total);

        if ($total >= floatval($schedule->achievement)) {
            $percent = 100;
        } else {
            if ($total !== 0) {
                $percent = number_format(($total * 100) / $schedule->achievement, 2);
            }
        }
        $progress_bar_percent = $percent / 100;

        return [
            'total'                => $total,
            'percent'              => $percent,
            'progress_bar_percent' => $progress_bar_percent,
        ];
    }

    public function mark_as_notified($id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'schedules', [
            'notified' => 1,
        ]);
    }
}
