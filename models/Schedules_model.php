<?php

use app\services\AbstractKanban;
use app\services\schedules\SchedulesPipeline;

defined('BASEPATH') or exit('No direct script access allowed');

class Schedules_model extends App_Model
{
    private $statuses;

    private $shipping_fields = ['shipping_street', 'shipping_city', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country'];

    public function __construct()
    {
        parent::__construct();

        $this->statuses = hooks()->apply_filters('before_set_schedule_statuses', [
            1,
            2,
            5,
            3,
            4,
        ]);   
    }
    /**
     * Get unique sale agent for schedules / Used for filters
     * @return array
     */
    public function get_assigneds()
    {
        return $this->db->query("SELECT DISTINCT(assigned) as assigned, CONCAT(firstname, ' ', lastname) as full_name FROM " . db_prefix() . 'schedules JOIN ' . db_prefix() . 'staff on ' . db_prefix() . 'staff.staffid=' . db_prefix() . 'schedules.assigned WHERE assigned != 0')->result_array();
    }

    /**
     * Get schedule/s
     * @param mixed $id schedule id
     * @param array $where perform where
     * @return mixed
     */
    public function get($id = '', $where = [])
    {
//        $this->db->select('*,' . db_prefix() . 'currencies.id as currencyid, ' . db_prefix() . 'schedules.id as id, ' . db_prefix() . 'currencies.name as currency_name');
        $this->db->select('*,' . db_prefix() . 'schedules.id as id');
        $this->db->from(db_prefix() . 'schedules');
        //$this->db->join(db_prefix() . 'currencies', db_prefix() . 'currencies.id = ' . db_prefix() . 'schedules.currency', 'left');
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'schedules.id', $id);
            $schedule = $this->db->get()->row();
            if ($schedule) {
                $schedule->attachments                           = $this->get_attachments($id);
                $schedule->visible_attachments_to_customer_found = false;

                foreach ($schedule->attachments as $attachment) {
                    if ($attachment['visible_to_customer'] == 1) {
                        $schedule->visible_attachments_to_customer_found = true;

                        break;
                    }
                }

                $schedule->items = get_items_by_type('schedule', $id);
                if(isset($schedule->schedule_id)){

                    if ($schedule->schedule_id != 0) {
                        $this->load->model('schedules_model');
                        $schedule->schedule_data = $this->schedules_model->get($schedule->schedule_id);
                    }

                }
                $schedule->client = $this->clients_model->get($schedule->clientid);

                if (!$schedule->client) {
                    $schedule->client          = new stdClass();
                    $schedule->client->company = $schedule->deleted_customer_name;
                }

                $this->load->model('email_schedule_model');
                $schedule->scheduled_email = $this->email_schedule_model->get($id, 'schedule');
            }

            return $schedule;
        }
        $this->db->order_by('number,YEAR(date)', 'desc');

        return $this->db->get()->result_array();
    }

    /**
     * Get schedule statuses
     * @return array
     */
    public function get_statuses()
    {
        return $this->statuses;
    }


    /**
     * Get schedule statuses
     * @return array
     */
    public function get_status($status,$id)
    {
        $this->db->where('status', $status);
        $this->db->where('id', $id);
        $schedule = $this->db->get(db_prefix() . 'schedules')->row();

        return $this->status;
    }

    public function clear_signature($id)
    {
        $this->db->select('signed','signature','status');
        $this->db->where('id', $id);
        $schedule = $this->db->get(db_prefix() . 'schedules')->row();

        if ($schedule) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'schedules', ['signed'=>0,'signature' => null, 'status'=>2]);

            if (!empty($schedule->signature)) {
                unlink(get_upload_path_by_type('schedule') . $id . '/' . $schedule->signature);
            }

            return true;
        }

        return false;
    }

    /**
     * Convert schedule to invoice
     * @param mixed $id schedule id
     * @return mixed     New invoice ID
     */
    public function convert_to_invoice($id, $client = false, $draft_invoice = false)
    {
        // Recurring invoice date is okey lets convert it to new invoice
        $_schedule = $this->get($id);

        $new_invoice_data = [];
        if ($draft_invoice == true) {
            $new_invoice_data['save_as_draft'] = true;
        }
        $new_invoice_data['clientid']   = $_schedule->clientid;
        $new_invoice_data['schedule_id'] = $_schedule->id;
        $new_invoice_data['number']     = get_option('next_invoice_number');
        $new_invoice_data['date']       = _d(date('Y-m-d'));
        $new_invoice_data['duedate']    = _d(date('Y-m-d'));
        if (get_option('invoice_due_after') != 0) {
            $new_invoice_data['duedate'] = _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime(date('Y-m-d')))));
        }
        $new_invoice_data['show_quantity_as'] = $_schedule->show_quantity_as;
        $new_invoice_data['assigned']       = $_schedule->assigned;
        // Since version 1.0.6
        $new_invoice_data['billing_street']   = clear_textarea_breaks($_schedule->billing_street);
        $new_invoice_data['billing_city']     = $_schedule->billing_city;
        $new_invoice_data['billing_state']    = $_schedule->billing_state;
        $new_invoice_data['billing_zip']      = $_schedule->billing_zip;
        $new_invoice_data['billing_country']  = $_schedule->billing_country;
        $new_invoice_data['shipping_street']  = clear_textarea_breaks($_schedule->shipping_street);
        $new_invoice_data['shipping_city']    = $_schedule->shipping_city;
        $new_invoice_data['shipping_state']   = $_schedule->shipping_state;
        $new_invoice_data['shipping_zip']     = $_schedule->shipping_zip;
        $new_invoice_data['shipping_country'] = $_schedule->shipping_country;

        if ($_schedule->include_shipping == 1) {
            $new_invoice_data['include_shipping'] = 1;
        }

        $new_invoice_data['show_shipping_on_invoice'] = $_schedule->show_shipping_on_schedule;
        $new_invoice_data['terms']                    = get_option('predefined_terms_invoice');
        $new_invoice_data['clientnote']               = get_option('predefined_clientnote_invoice');
        // Set to unpaid status automatically
        $new_invoice_data['status']    = 1;
        $new_invoice_data['adminnote'] = '';

        $this->load->model('payment_modes_model');
        $modes = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $temp_modes = [];
        foreach ($modes as $mode) {
            if ($mode['selected_by_default'] == 0) {
                continue;
            }
            $temp_modes[] = $mode['id'];
        }
        $new_invoice_data['allowed_payment_modes'] = $temp_modes;
        $new_invoice_data['newitems']              = [];
        $custom_fields_items                       = get_custom_fields('items');
        $key                                       = 1;
        foreach ($_schedule->items as $item) {
            $new_invoice_data['newitems'][$key]['description']      = $item['description'];
            $new_invoice_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
            $new_invoice_data['newitems'][$key]['qty']              = $item['qty'];
            $new_invoice_data['newitems'][$key]['unit']             = $item['unit'];
            $taxes                                                  = get_schedule_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                // tax name is in format TAX1|10.00
                array_push($new_invoice_data['newitems'][$key]['taxname'], $tax['taxname']);
            }
            $new_invoice_data['newitems'][$key]['rate']  = $item['rate'];
            $new_invoice_data['newitems'][$key]['order'] = $item['item_order'];
            foreach ($custom_fields_items as $cf) {
                $new_invoice_data['newitems'][$key]['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'], $cf['id'], 'items', false);

                if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                    define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
                }
            }
            $key++;
        }
        $this->load->model('invoices_model');
        $id = $this->invoices_model->add($new_invoice_data);
        if ($id) {
            // Customer accepted the schedule and is auto converted to invoice
            if (!is_staff_logged_in()) {
                $this->db->where('rel_type', 'invoice');
                $this->db->where('rel_id', $id);
                $this->db->delete(db_prefix() . 'schedule_activity');
                $this->invoices_model->log_invoice_activity($id, 'invoice_activity_auto_converted_from_schedule', true, serialize([
                    '<a href="' . admin_url('schedules/list_schedules/' . $_schedule->id) . '">' . format_schedule_number($_schedule->id) . '</a>',
                ]));
            }
            // For all cases update addefrom and sale agent from the invoice
            // May happen staff is not logged in and these values to be 0
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'invoices', [
                'addedfrom'  => $_schedule->addedfrom,
                'assigned' => $_schedule->assigned,
            ]);

            // Update schedule with the new invoice data and set to status accepted
            $this->db->where('id', $_schedule->id);
            $this->db->update(db_prefix() . 'schedules', [
                'invoiced_date' => date('Y-m-d H:i:s'),
                'invoiceid'     => $id,
                'status'        => 4,
            ]);


            if (is_custom_fields_smart_transfer_enabled()) {
                $this->db->where('fieldto', 'schedule');
                $this->db->where('active', 1);
                $cfSchedules = $this->db->get(db_prefix() . 'customfields')->result_array();
                foreach ($cfSchedules as $field) {
                    $tmpSlug = explode('_', $field['slug'], 2);
                    if (isset($tmpSlug[1])) {
                        $this->db->where('fieldto', 'invoice');

                        $this->db->group_start();
                        $this->db->like('slug', 'invoice_' . $tmpSlug[1], 'after');
                        $this->db->where('type', $field['type']);
                        $this->db->where('options', $field['options']);
                        $this->db->where('active', 1);
                        $this->db->group_end();

                        // $this->db->where('slug LIKE "invoice_' . $tmpSlug[1] . '%" AND type="' . $field['type'] . '" AND options="' . $field['options'] . '" AND active=1');
                        $cfTransfer = $this->db->get(db_prefix() . 'customfields')->result_array();

                        // Don't make mistakes
                        // Only valid if 1 result returned
                        // + if field names similarity is equal or more then CUSTOM_FIELD_TRANSFER_SIMILARITY%
                        if (count($cfTransfer) == 1 && ((similarity($field['name'], $cfTransfer[0]['name']) * 100) >= CUSTOM_FIELD_TRANSFER_SIMILARITY)) {
                            $value = get_custom_field_value($_schedule->id, $field['id'], 'schedule', false);

                            if ($value == '') {
                                continue;
                            }

                            $this->db->insert(db_prefix() . 'customfieldsvalues', [
                                'relid'   => $id,
                                'fieldid' => $cfTransfer[0]['id'],
                                'fieldto' => 'invoice',
                                'value'   => $value,
                            ]);
                        }
                    }
                }
            }

            if ($client == false) {
                $this->log_schedule_activity($_schedule->id, 'schedule_activity_converted', false, serialize([
                    '<a href="' . admin_url('invoices/list_invoices/' . $id) . '">' . format_invoice_number($id) . '</a>',
                ]));
            }

            hooks()->do_action('schedule_converted_to_invoice', ['invoice_id' => $id, 'schedule_id' => $_schedule->id]);
        }

        return $id;
    }

    /**
     * Copy schedule
     * @param mixed $id schedule id to copy
     * @return mixed
     */
    public function copy($id)
    {
        $_schedule                       = $this->get($id);
        $new_schedule_data               = [];
        $new_schedule_data['clientid']   = $_schedule->clientid;
        $new_schedule_data['schedule_id'] = $_schedule->schedule_id;
        $new_schedule_data['number']     = get_option('next_schedule_number');
        $new_schedule_data['date']       = _d(date('Y-m-d'));
        $new_schedule_data['expirydate'] = null;

        if ($_schedule->expirydate && get_option('schedule_due_after') != 0) {
            $new_schedule_data['expirydate'] = _d(date('Y-m-d', strtotime('+' . get_option('schedule_due_after') . ' DAY', strtotime(date('Y-m-d')))));
        }

        $new_schedule_data['show_quantity_as'] = $_schedule->show_quantity_as;
    
        $new_schedule_data['terms']            = $_schedule->terms;
        $new_schedule_data['assigned']       = $_schedule->assigned;
        $new_schedule_data['reference_no']     = $_schedule->reference_no;
        // Since version 1.0.6
        $new_schedule_data['billing_street']   = clear_textarea_breaks($_schedule->billing_street);
        $new_schedule_data['billing_city']     = $_schedule->billing_city;
        $new_schedule_data['billing_state']    = $_schedule->billing_state;
        $new_schedule_data['billing_zip']      = $_schedule->billing_zip;
        $new_schedule_data['billing_country']  = $_schedule->billing_country;
        $new_schedule_data['shipping_street']  = clear_textarea_breaks($_schedule->shipping_street);
        $new_schedule_data['shipping_city']    = $_schedule->shipping_city;
        $new_schedule_data['shipping_state']   = $_schedule->shipping_state;
        $new_schedule_data['shipping_zip']     = $_schedule->shipping_zip;
        $new_schedule_data['shipping_country'] = $_schedule->shipping_country;
        if ($_schedule->include_shipping == 1) {
            $new_schedule_data['include_shipping'] = $_schedule->include_shipping;
        }
        $new_schedule_data['show_shipping_on_schedule'] = $_schedule->show_shipping_on_schedule;
        // Set to unpaid status automatically
        $new_schedule_data['status']     = 1;
        $new_schedule_data['clientnote'] = $_schedule->clientnote;
        $new_schedule_data['adminnote']  = '';
        $new_schedule_data['newitems']   = [];
        $custom_fields_items             = get_custom_fields('items');
        $key                             = 1;
        foreach ($_schedule->items as $item) {
            $new_schedule_data['newitems'][$key]['description']      = $item['description'];
            $new_schedule_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
            $new_schedule_data['newitems'][$key]['qty']              = $item['qty'];
            $new_schedule_data['newitems'][$key]['unit']             = $item['unit'];
            $taxes                                                   = get_schedule_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                // tax name is in format TAX1|10.00
                array_push($new_schedule_data['newitems'][$key]['taxname'], $tax['taxname']);
            }
            $new_schedule_data['newitems'][$key]['rate']  = $item['rate'];
            $new_schedule_data['newitems'][$key]['order'] = $item['item_order'];
            foreach ($custom_fields_items as $cf) {
                $new_schedule_data['newitems'][$key]['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'], $cf['id'], 'items', false);

                if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                    define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
                }
            }
            $key++;
        }
        $id = $this->add($new_schedule_data);
        if ($id) {
            $custom_fields = get_custom_fields('schedule');
            foreach ($custom_fields as $field) {
                $value = get_custom_field_value($_schedule->id, $field['id'], 'schedule', false);
                if ($value == '') {
                    continue;
                }

                $this->db->insert(db_prefix() . 'customfieldsvalues', [
                    'relid'   => $id,
                    'fieldid' => $field['id'],
                    'fieldto' => 'schedule',
                    'value'   => $value,
                ]);
            }

            $tags = get_tags_in($_schedule->id, 'schedule');
            handle_tags_save($tags, $id, 'schedule');

            log_schedule_activity('Copied Schedule ' . format_schedule_number($_schedule->id));

            return $id;
        }

        return false;
    }

    /**
     * Performs schedules totals status
     * @param array $data
     * @return array
     */
    public function get_schedules_total($data)
    {
        $statuses            = $this->get_statuses();
        $has_permission_view = has_permission('schedules', '', 'view');
        $this->load->model('currencies_model');
        if (isset($data['currency'])) {
            $currencyid = $data['currency'];
        } elseif (isset($data['customer_id']) && $data['customer_id'] != '') {
            $currencyid = $this->clients_model->get_customer_default_currency($data['customer_id']);
            if ($currencyid == 0) {
                $currencyid = $this->currencies_model->get_base_currency()->id;
            }
        } elseif (isset($data['schedule_id']) && $data['schedule_id'] != '') {
            $this->load->model('schedules_model');
            $currencyid = $this->schedules_model->get_currency($data['schedule_id'])->id;
        } else {
            $currencyid = $this->currencies_model->get_base_currency()->id;
        }

        $currency = get_currency($currencyid);
        $where    = '';
        if (isset($data['customer_id']) && $data['customer_id'] != '') {
            $where = ' AND clientid=' . $data['customer_id'];
        }

        if (isset($data['schedule_id']) && $data['schedule_id'] != '') {
            $where .= ' AND schedule_id=' . $data['schedule_id'];
        }

        if (!$has_permission_view) {
            $where .= ' AND ' . get_schedules_where_sql_for_staff(get_staff_user_id());
        }

        $sql = 'SELECT';
        foreach ($statuses as $schedule_status) {
            $sql .= '(SELECT SUM(total) FROM ' . db_prefix() . 'schedules WHERE status=' . $schedule_status;
            $sql .= ' AND currency =' . $this->db->escape_str($currencyid);
            if (isset($data['years']) && count($data['years']) > 0) {
                $sql .= ' AND YEAR(date) IN (' . implode(', ', array_map(function ($year) {
                    return get_instance()->db->escape_str($year);
                }, $data['years'])) . ')';
            } else {
                $sql .= ' AND YEAR(date) = ' . date('Y');
            }
            $sql .= $where;
            $sql .= ') as "' . $schedule_status . '",';
        }

        $sql     = substr($sql, 0, -1);
        $result  = $this->db->query($sql)->result_array();
        $_result = [];
        $i       = 1;
        foreach ($result as $key => $val) {
            foreach ($val as $status => $total) {
                $_result[$i]['total']         = $total;
                $_result[$i]['symbol']        = $currency->symbol;
                $_result[$i]['currency_name'] = $currency->name;
                $_result[$i]['status']        = $status;
                $i++;
            }
        }
        $_result['currencyid'] = $currencyid;

        return $_result;
    }

    /**
     * Insert new schedule to database
     * @param array $data invoiec data
     * @return mixed - false if not insert, schedule ID if succes
     */
    public function add($data)
    {
        $affectedRows = 0;
        
        $data['datecreated'] = date('Y-m-d H:i:s');

        $data['addedfrom'] = get_staff_user_id();

        $data['prefix'] = get_option('schedule_prefix');

        $data['number_format'] = get_option('schedule_number_format');

        $save_and_send = isset($data['save_and_send']);

        $scheduleRequestID = false;
        if (isset($data['schedule_request_id'])) {
            $scheduleRequestID = $data['schedule_request_id'];
            unset($data['schedule_request_id']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $data['hash'] = app_generate_hash();
        $tags         = isset($data['tags']) ? $data['tags'] : '';

        $items = [];
        if (isset($data['newitems'])) {
            $items = $data['newitems'];
            unset($data['newitems']);
        }

        $schedule_members = [];
        if (isset($data['schedule_members'])) {
            $schedule_members = $data['schedule_members'];
            unset($data['schedule_members']);
        }

        $data = $this->map_shipping_columns($data);

        $data['billing_street'] = trim($data['billing_street']);
        $data['billing_street'] = nl2br($data['billing_street']);

        if (isset($data['shipping_street'])) {
            $data['shipping_street'] = trim($data['shipping_street']);
            $data['shipping_street'] = nl2br($data['shipping_street']);
        }

        $hook = hooks()->apply_filters('before_schedule_added', [
            'data'  => $data,
            'items' => $items,
            'schedule_members' => $schedule_members,
        ]);

        $data  = $hook['data'];
        $items = $hook['items'];
        $schedule_members      = $hook['schedule_members'];

        unset($data['tags']);
            
        $this->db->insert(db_prefix() . 'schedules', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            // Update next schedule number in settings
            $this->db->where('name', 'next_schedule_number');
            $this->db->set('value', 'value+1', false);
            $this->db->update(db_prefix() . 'options');

            if ($scheduleRequestID !== false && $scheduleRequestID != '') {
                $this->load->model('schedule_request_model');
                $completedStatus = $this->schedule_request_model->get_status_by_flag('completed');
                $this->schedule_request_model->update_request_status([
                    'requestid' => $scheduleRequestID,
                    'status'    => $completedStatus->id,
                ]);
            }

            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }

            handle_tags_save($tags, $insert_id, 'schedule');

            foreach ($items as $key => $item) {
                if ($new_item_added = add_new_schedule_item_post($item, $insert_id, 'schedule')) {
                    $affectedRows++;
                }
            }

            $_sm = [];
            if (isset($schedule_members)) {
                $_sm['schedule_members'] = $schedule_members;
            }
            if ($this->add_edit_schedule_members($_sm, $insert_id)) {
                $affectedRows++;
            }

            hooks()->do_action('after_schedule_added', $insert_id);

            if ($save_and_send === true) {
                $this->send_schedule_to_client($insert_id, '', true, '', true);
            }

            return $insert_id;
        }

        return false;
    }

    /**
     * Get item by id
     * @param mixed $id item id
     * @return object
     */
    public function get_schedule_item($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'schedule');

        return $this->db->get(db_prefix() . 'schedule_items')->result();
    }

    /**
     * Update schedule data
     * @param array $data schedule data
     * @param mixed $id scheduleid
     * @return boolean
     */
    public function update($data, $id)
    {
        $affectedRows = 0;

        $data['number'] = trim($data['number']);

        $original_schedule = $this->get($id);

        $original_status = $original_schedule->status;

        $original_number = $original_schedule->number;

        $original_number_formatted = format_schedule_number($id);

        $save_and_send = isset($data['save_and_send']);

        $items = [];
        if (isset($data['items'])) {
            $items = $data['items'];
            unset($data['items']);
        }

        $newitems = [];
        if (isset($data['newitems'])) {
            $newitems = $data['newitems'];
            unset($data['newitems']);
        }

        $schedule_members = [];
        if (isset($data['schedule_members'])) {
            $schedule_members = $data['schedule_members'];
            unset($data['schedule_members']);
        }

        if (isset($data['tags'])) {
            if (handle_tags_save($data['tags'], $id, 'schedule')) {
                $affectedRows++;
            }
        }

        $data['billing_street'] = trim($data['billing_street']);
        $data['billing_street'] = nl2br($data['billing_street']);

        $data['shipping_street'] = trim($data['shipping_street']);
        $data['shipping_street'] = nl2br($data['shipping_street']);

        $data = $this->map_shipping_columns($data);

        $hook = hooks()->apply_filters('before_schedule_updated', [
            'data'             => $data,
            'items'            => $items,
            'newitems'         => $newitems,
            'schedule_members' => $schedule_members,
            'removed_items'    => isset($data['removed_items']) ? $data['removed_items'] : [],
        ], $id);

        $data                  = $hook['data'];
        $items                 = $hook['items'];
        $newitems              = $hook['newitems'];
        $schedule_members      = $hook['schedule_members'];
        $data['removed_items'] = $hook['removed_items'];

        // Delete items checked to be removed from database
        foreach ($data['removed_items'] as $remove_item_id) {
            $original_item = $this->get_schedule_item($remove_item_id);
            if (handle_removed_sales_item_post($remove_item_id, 'schedule')) {
                $affectedRows++;
                $this->log_schedule_activity($id, 'invoice_schedule_activity_removed_item', false, serialize([
                    $original_item->description,
                ]));
            }
        }

        unset($data['removed_items']);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'schedules', $data);

        if ($this->db->affected_rows() > 0) {
            // Check for status change
            if ($original_status != $data['status']) {
                $this->log_schedule_activity($original_schedule->id, 'not_schedule_status_updated', false, serialize([
                    '<original_status>' . $original_status . '</original_status>',
                    '<new_status>' . $data['status'] . '</new_status>',
                ]));
                if ($data['status'] == 2) {
                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'schedules', ['sent' => 1, 'datesend' => date('Y-m-d H:i:s')]);
                }
            }
            if ($original_number != $data['number']) {
                $this->log_schedule_activity($original_schedule->id, 'schedule_activity_number_changed', false, serialize([
                    $original_number_formatted,
                    format_schedule_number($original_schedule->id),
                ]));
            }
            $affectedRows++;
        }

        foreach ($items as $key => $item) {
            $original_item = $this->get_schedule_item($item['itemid']);

            if (update_schedule_item_post($item['itemid'], $item, 'item_order')) {
                $affectedRows++;
            }

            if (update_schedule_item_post($item['itemid'], $item, 'unit')) {
                $affectedRows++;
            }


            if (update_schedule_item_post($item['itemid'], $item, 'qty')) {
                $this->log_schedule_activity($id, 'invoice_schedule_activity_updated_qty_item', false, serialize([
                    $item['description'],
                    $original_item->qty,
                    $item['qty'],
                ]));
                $affectedRows++;
            }

            if (update_schedule_item_post($item['itemid'], $item, 'description')) {
                $this->log_schedule_activity($id, 'invoice_schedule_activity_updated_item_short_description', false, serialize([
                    $original_item->description,
                    $item['description'],
                ]));
                $affectedRows++;
            }

            if (update_schedule_item_post($item['itemid'], $item, 'long_description')) {
                $this->log_schedule_activity($id, 'invoice_schedule_activity_updated_item_long_description', false, serialize([
                    $original_item->long_description,
                    $item['long_description'],
                ]));
                $affectedRows++;
            }

        }

        foreach ($newitems as $key => $item) {
            if ($new_item_added = add_new_schedule_item_post($item, $id, 'schedule')) {
                $affectedRows++;
            }
        }



        $_sm = [];
        if (isset($schedule_members)) {
            $_sm['schedule_members'] = $schedule_members;
        }
        if ($this->add_edit_schedule_members($_sm, $id)) {
            $affectedRows++;
        }

        if ($save_and_send === true) {
            $this->send_schedule_to_client($id, '', true, '', true);
        }

        if ($affectedRows > 0) {
            hooks()->do_action('after_schedule_updated', $id);

            return true;
        }

        return false;
    }

    public function mark_action_status($action, $id, $client = false)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'schedules', [
            'status' => $action,
            'signed' => ($action == 4) ? 1 : 0,
        ]);

        $notifiedUsers = [];

        if ($this->db->affected_rows() > 0) {
            $schedule = $this->get($id);
            if ($client == true) {
                $this->db->where('staffid', $schedule->addedfrom);
                $this->db->or_where('staffid', $schedule->assigned);
                $staff_schedule = $this->db->get(db_prefix() . 'staff')->result_array();

                $invoiceid = false;
                $invoiced  = false;

                $contact_id = !is_client_logged_in()
                    ? get_primary_contact_user_id($schedule->clientid)
                    : get_contact_user_id();

                if ($action == 4) {
                    if (get_option('schedule_auto_convert_to_invoice_on_client_accept') == 1) {
                        $invoiceid = $this->convert_to_invoice($id, true);
                        $this->load->model('invoices_model');
                        if ($invoiceid) {
                            $invoiced = true;
                            $invoice  = $this->invoices_model->get($invoiceid);
                            $this->log_schedule_activity($id, 'schedule_activity_client_accepted_and_converted', true, serialize([
                                '<a href="' . admin_url('invoices/list_invoices/' . $invoiceid) . '">' . format_invoice_number($invoice->id) . '</a>',
                            ]));
                        }
                    } else {
                        $this->log_schedule_activity($id, 'schedule_activity_client_accepted', true);
                    }

                    // Send thank you email to all contacts with permission schedules
                    $contacts = $this->clients_model->get_contacts($schedule->clientid, ['active' => 1, 'project_emails' => 1]);

                    foreach ($contacts as $contact) {
                        // (To fix merge field) send_mail_template('schedule_accepted_to_customer','schedules', $schedule, $contact);
                    }

                    foreach ($staff_schedule as $member) {
                        $notified = add_notification([
                            'fromcompany'     => true,
                            'touserid'        => $member['staffid'],
                            'description'     => 'not_schedule_customer_accepted',
                            'link'            => 'schedules/list_schedules/' . $id,
                            'additional_data' => serialize([
                                format_schedule_number($schedule->id),
                            ]),
                        ]);

                        if ($notified) {
                            array_push($notifiedUsers, $member['staffid']);
                        }

                        // (To fix merge field) send_mail_template('schedule_accepted_to_staff','schedules', $schedule, $member['email'], $contact_id);
                    }

                    pusher_trigger_notification($notifiedUsers);
                    hooks()->do_action('schedule_accepted', $id);

                    return [
                        'invoiced'  => $invoiced,
                        'invoiceid' => $invoiceid,
                    ];
                } elseif ($action == 3) {
                    foreach ($staff_schedule as $member) {
                        $notified = add_notification([
                            'fromcompany'     => true,
                            'touserid'        => $member['staffid'],
                            'description'     => 'not_schedule_customer_declined',
                            'link'            => 'schedules/list_schedules/' . $id,
                            'additional_data' => serialize([
                                format_schedule_number($schedule->id),
                            ]),
                        ]);

                        if ($notified) {
                            array_push($notifiedUsers, $member['staffid']);
                        }
                        // Send staff email notification that customer declined schedule
                        // (To fix merge field) send_mail_template('schedule_declined_to_staff', 'schedules',$schedule, $member['email'], $contact_id);
                    }

                    pusher_trigger_notification($notifiedUsers);
                    $this->log_schedule_activity($id, 'schedule_activity_client_declined', true);
                    hooks()->do_action('schedule_declined', $id);

                    return [
                        'invoiced'  => $invoiced,
                        'invoiceid' => $invoiceid,
                    ];
                }
            } else {
                if ($action == 2) {
                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'schedules', ['sent' => 1, 'datesend' => date('Y-m-d H:i:s')]);
                }
                // Admin marked schedule
                $this->log_schedule_activity($id, 'schedule_activity_marked', false, serialize([
                    '<status>' . $action . '</status>',
                ]));

                return true;
            }
        }

        return false;
    }

    /**
     * Get schedule attachments
     * @param mixed $schedule_id
     * @param string $id attachment id
     * @return mixed
     */
    public function get_attachments($schedule_id, $id = '')
    {
        // If is passed id get return only 1 attachment
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('rel_id', $schedule_id);
        }
        $this->db->where('rel_type', 'schedule');
        $result = $this->db->get(db_prefix() . 'files');
        if (is_numeric($id)) {
            return $result->row();
        }

        return $result->result_array();
    }

    /**
     *  Delete schedule attachment
     * @param mixed $id attachmentid
     * @return  boolean
     */
    public function delete_attachment($id)
    {
        $attachment = $this->get_attachments('', $id);
        $deleted    = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_by_type('schedule') . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_schedule_activity('Schedule Attachment Deleted [ScheduleID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_by_type('schedule') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('schedule') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_by_type('schedule') . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * Delete schedule items and all connections
     * @param mixed $id scheduleid
     * @return boolean
     */
    public function delete($id, $simpleDelete = false)
    {
        if (get_option('delete_only_on_last_schedule') == 1 && $simpleDelete == false) {
            if (!is_last_schedule($id)) {
                return false;
            }
        }
        $schedule = $this->get($id);
        /*
        if (!is_null($schedule->invoiceid) && $simpleDelete == false) {
            return [
                'is_invoiced_schedule_delete_error' => true,
            ];
        }
        */
        hooks()->do_action('before_schedule_deleted', $id);

        $number = format_schedule_number($id);

        $this->clear_signature($id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'schedules');

        if ($this->db->affected_rows() > 0) {
            if (!is_null($schedule->short_link)) {
                app_archive_short_link($schedule->short_link);
            }

            if (get_option('schedule_number_decrement_on_delete') == 1 && $simpleDelete == false) {
                $current_next_schedule_number = get_option('next_schedule_number');
                if ($current_next_schedule_number > 1) {
                    // Decrement next schedule number to
                    $this->db->where('name', 'next_schedule_number');
                    $this->db->set('value', 'value-1', false);
                    $this->db->update(db_prefix() . 'options');
                }
            }

            delete_tracked_emails($id, 'schedule');

            $this->db->where('relid IN (SELECT id from ' . db_prefix() . 'schedule_items WHERE rel_type="schedule" AND rel_id="' . $this->db->escape_str($id) . '")');
            $this->db->where('fieldto', 'items');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'schedule');
            $this->db->delete(db_prefix() . 'notes');

            $this->db->where('rel_type', 'schedule');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'views_tracking');

            $this->db->where('rel_type', 'schedule');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'taggables');

            $this->db->where('rel_type', 'schedule');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'reminders');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'schedule');
            $this->db->delete(db_prefix() . 'schedule_items');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'schedule');
            $this->db->delete(db_prefix() . 'item_tax');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'schedule');
            $this->db->delete(db_prefix() . 'schedule_activity');

            // Delete the custom field values
            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'schedule');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            $attachments = $this->get_attachments($id);
            foreach ($attachments as $attachment) {
                $this->delete_attachment($attachment['id']);
            }

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'schedule');
            $this->db->delete('scheduled_emails');

            // Get related tasks
            $this->db->where('rel_type', 'schedule');
            $this->db->where('rel_id', $id);
            $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id']);
            }
            if ($simpleDelete == false) {
                $this->log_schedule_activity('Schedules Deleted [Number: ' . $number . ']');
            }

            return true;
        }

        return false;
    }

    /**
     * Set schedule to sent when email is successfuly sended to client
     * @param mixed $id scheduleid
     */
    public function set_schedule_sent($id, $emails_sent = [])
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'schedules', [
            'sent'     => 1,
            'datesend' => date('Y-m-d H:i:s'),
        ]);

        $this->log_schedule_activity($id, 'invoice_schedule_activity_sent_to_client', false, serialize([
            '<custom_data>' . implode(', ', $emails_sent) . '</custom_data>',
        ]));

        // Update schedule status to sent
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'schedules', [
            'status' => 2,
        ]);

        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'schedule');
        $this->db->delete('scheduled_emails');
    }

    /**
     * Send expiration reminder to customer
     * @param mixed $id schedule id
     * @return boolean
     */
    public function send_expiry_reminder($id)
    {
        $schedule        = $this->get($id);
        $schedule_number = format_schedule_number($schedule->id);
        set_mailing_constant();
        $pdf              = schedule_pdf($schedule);
        $attach           = $pdf->Output($schedule_number . '.pdf', 'S');
        $emails_sent      = [];
        $sms_sent         = false;
        $sms_reminder_log = [];

        // For all cases update this to prevent sending multiple reminders eq on fail
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'schedules', [
            'is_expiry_notified' => 1,
        ]);

        $contacts = $this->clients_model->get_contacts($schedule->clientid, ['active' => 1, 'project_emails' => 1]);

        foreach ($contacts as $contact) {
            $template = mail_template('schedule_expiration_reminder', $schedule, $contact);

            $merge_fields = $template->get_merge_fields();

            $template->add_attachment([
                'attachment' => $attach,
                'filename'   => str_replace('/', '-', $schedule_number . '.pdf'),
                'type'       => 'application/pdf',
            ]);

            if ($template->send()) {
                array_push($emails_sent, $contact['email']);
            }

            if (can_send_sms_based_on_creation_date($schedule->datecreated)
                && $this->app_sms->trigger(SMS_TRIGGER_SCHEDULE_EXP_REMINDER, $contact['phonenumber'], $merge_fields)) {
                $sms_sent = true;
                array_push($sms_reminder_log, $contact['firstname'] . ' (' . $contact['phonenumber'] . ')');
            }
        }

        if (count($emails_sent) > 0 || $sms_sent) {
            if (count($emails_sent) > 0) {
                $this->log_schedule_activity($id, 'not_expiry_reminder_sent', false, serialize([
                    '<custom_data>' . implode(', ', $emails_sent) . '</custom_data>',
                ]));
            }

            if ($sms_sent) {
                $this->log_schedule_activity($id, 'sms_reminder_sent_to', false, serialize([
                    implode(', ', $sms_reminder_log),
                ]));
            }

            return true;
        }

        return false;
    }

    /**
     * Send schedule to client
     * @param mixed $id scheduleid
     * @param string $template email template to sent
     * @param boolean $attachpdf attach schedule pdf or not
     * @return boolean
     */
    public function send_schedule_to_client($id, $template_name = '', $attachpdf = true, $cc = '', $manually = false)
    {
        $schedule = $this->get($id);

        if ($template_name == '') {
            $template_name = $schedule->sent == 0 ?
                'schedule_send_to_customer' :
                'schedule_send_to_customer_already_sent';
        }

        $schedule_number = format_schedule_number($schedule->id);

        $emails_sent = [];
        $send_to     = [];

        // Manually is used when sending the schedule via add/edit area button Save & Send
        if (!DEFINED('CRON') && $manually === false) {
            $send_to = $this->input->post('sent_to');
        } elseif (isset($GLOBALS['scheduled_email_contacts'])) {
            $send_to = $GLOBALS['scheduled_email_contacts'];
        } else {
            $contacts = $this->clients_model->get_contacts(
                $schedule->clientid,
                ['active' => 1, 'project_emails' => 1]
            );

            foreach ($contacts as $contact) {
                array_push($send_to, $contact['id']);
            }
        }

        $status_auto_updated = false;
        $status_now          = $schedule->status;

        if (is_array($send_to) && count($send_to) > 0) {
            $i = 0;

            // Auto update status to sent in case when user sends the schedule is with status draft
            if ($status_now == 1) {
                $this->db->where('id', $schedule->id);
                $this->db->update(db_prefix() . 'schedules', [
                    'status' => 2,
                ]);
                $status_auto_updated = true;
            }

            if ($attachpdf) {
                $_pdf_schedule = $this->get($schedule->id);
                set_mailing_constant();
                $pdf = schedule_pdf($_pdf_schedule);

                $attach = $pdf->Output($schedule_number . '.pdf', 'S');
            }

            foreach ($send_to as $contact_id) {
                if ($contact_id != '') {
                    // Send cc only for the first contact
                    if (!empty($cc) && $i > 0) {
                        $cc = '';
                    }

                    $contact = $this->clients_model->get_contact($contact_id);

                    if (!$contact) {
                        continue;
                    }

                    $template = mail_template($template_name, $schedule, $contact, $cc);

                    if ($attachpdf) {
                        $hook = hooks()->apply_filters('send_schedule_to_customer_file_name', [
                            'file_name' => str_replace('/', '-', $schedule_number . '.pdf'),
                            'schedule'  => $_pdf_schedule,
                        ]);

                        $template->add_attachment([
                            'attachment' => $attach,
                            'filename'   => $hook['file_name'],
                            'type'       => 'application/pdf',
                        ]);
                    }

                    if ($template->send()) {
                        array_push($emails_sent, $contact->email);
                    }
                }
                $i++;
            }
        } else {
            return false;
        }

        if (count($emails_sent) > 0) {
            $this->set_schedule_sent($id, $emails_sent);
            hooks()->do_action('schedule_sent', $id);

            return true;
        }

        if ($status_auto_updated) {
            // Schedule not send to customer but the status was previously updated to sent now we need to revert back to draft
            $this->db->where('id', $schedule->id);
            $this->db->update(db_prefix() . 'schedules', [
                'status' => 1,
            ]);
        }

        return false;
    }

    /**
     * All schedule activity
     * @param mixed $id scheduleid
     * @return array
     */
    public function get_schedule_activity($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'schedule');
        $this->db->order_by('date', 'desc');

        return $this->db->get(db_prefix() . 'schedule_activity')->result_array();
    }

    /**
     * Log schedule activity to database
     * @param mixed $id scheduleid
     * @param string $description activity description
     */
    public function log_schedule_activity($id, $description = '', $client = false, $additional_data = '')
    {
        $staffid   = get_staff_user_id();
        $full_name = get_staff_full_name(get_staff_user_id());
        if (DEFINED('CRON')) {
            $staffid   = '[CRON]';
            $full_name = '[CRON]';
        } elseif ($client == true) {
            $staffid   = null;
            $full_name = '';
        }

        $this->db->insert(db_prefix() . 'schedule_activity', [
            'description'     => $description,
            'date'            => date('Y-m-d H:i:s'),
            'rel_id'          => $id,
            'rel_type'        => 'schedule',
            'staffid'         => $staffid,
            'full_name'       => $full_name,
            'additional_data' => $additional_data,
        ]);
    }

    /**
     * Updates pipeline order when drag and drop
     * @param mixe $data $_POST data
     * @return void
     */
    public function update_pipeline($data)
    {
        $this->mark_action_status($data['status'], $data['scheduleid']);
        AbstractKanban::updateOrder($data['order'], 'pipeline_order', 'schedules', $data['status']);
    }

    /**
     * Get schedule unique year for filtering
     * @return array
     */
    public function get_schedules_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM ' . db_prefix() . 'schedules ORDER BY year DESC')->result_array();
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

    public function do_kanban_query($status, $search = '', $page = 1, $sort = [], $count = false)
    {
        _deprecated_function('Schedules_model::do_kanban_query', '2.9.2', 'SchedulesPipeline class');

        $kanBan = (new SchedulesPipeline($status))
            ->search($search)
            ->page($page)
            ->sortBy($sort['sort'] ?? null, $sort['sort_by'] ?? null);

        if ($count) {
            return $kanBan->countAll();
        }

        return $kanBan->get();
    }


    public function get_schedule_members($id, $with_name = false)
    {
        if ($with_name) {
            $this->db->select('firstname,lastname,email,schedule_id,staff_id');
        } else {
            $this->db->select('email,schedule_id,staff_id');
        }
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid=' . db_prefix() . 'schedule_members.staff_id');
        $this->db->where('schedule_id', $id);

        return $this->db->get(db_prefix() . 'schedule_members')->result_array();
    }

    public function add_edit_schedule_members($data, $id)
    {
        $affectedRows = 0;
        if (isset($data['schedule_members'])) {
            $schedule_members = $data['schedule_members'];
        }

        $new_schedule_members_to_receive_email = [];
        $this->db->select('id,number,clientid,project_id');
        $this->db->where('id', $id);
        $schedule      = $this->db->get(db_prefix() . 'schedules')->row();
        $schedule_number = format_schedule_number($id);
        $schedule_id    = $id;
        $client_id    = $schedule->clientid;
        $project_id    = $schedule->project_id;

        $schedule_members_in = $this->get_schedule_members($id);
        if (sizeof($schedule_members_in) > 0) {
            foreach ($schedule_members_in as $schedule_member) {
                if (isset($schedule_members)) {
                    if (!in_array($schedule_member['staff_id'], $schedule_members)) {
                        $this->db->where('schedule_id', $id);
                        $this->db->where('staff_id', $schedule_member['staff_id']);
                        $this->db->delete(db_prefix() . 'schedule_members');
                        if ($this->db->affected_rows() > 0) {
                            $this->db->where('staff_id', $schedule_member['staff_id']);
                            $this->db->where('schedule_id', $id);
                            $this->db->delete(db_prefix() . 'pinned_schedules');

                            $this->log_schedule_activity($id, 'schedule_activity_removed_team_member', get_staff_full_name($schedule_member['staff_id']));
                            $affectedRows++;
                        }
                    }
                } else {
                    $this->db->where('schedule_id', $id);
                    $this->db->delete(db_prefix() . 'schedule_members');
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
            if (isset($schedule_members)) {
                $notifiedUsers = [];
                foreach ($schedule_members as $staff_id) {
                    $this->db->where('schedule_id', $id);
                    $this->db->where('staff_id', $staff_id);
                    $_exists = $this->db->get(db_prefix() . 'schedule_members')->row();
                    if (!$_exists) {
                        if (empty($staff_id)) {
                            continue;
                        }
                        $this->db->insert(db_prefix() . 'schedule_members', [
                            'schedule_id' => $id,
                            'staff_id'   => $staff_id,
                        ]);
                        if ($this->db->affected_rows() > 0) {
                            if ($staff_id != get_staff_user_id()) {
                                $notified = add_notification([
                                    'fromuserid'      => get_staff_user_id(),
                                    'description'     => 'not_staff_added_as_schedule_member',
                                    'link'            => 'schedules/schedule/' . $id,
                                    'touserid'        => $staff_id,
                                    'additional_data' => serialize([
                                        $schedule_number,
                                    ]),
                                ]);
                                array_push($new_schedule_members_to_receive_email, $staff_id);
                                if ($notified) {
                                    array_push($notifiedUsers, $staff_id);
                                }
                            }

                            $this->log_schedule_activity($id, 'schedule_activity_added_team_member', get_staff_full_name($staff_id));
                            $affectedRows++;
                        }
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            }
        } else {
            if (isset($schedule_members)) {
                $notifiedUsers = [];
                foreach ($schedule_members as $staff_id) {
                    if (empty($staff_id)) {
                        continue;
                    }
                    $this->db->insert(db_prefix() . 'schedule_members', [
                        'schedule_id' => $id,
                        'staff_id'   => $staff_id,
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        if ($staff_id != get_staff_user_id()) {
                            $notified = add_notification([
                                'fromuserid'      => get_staff_user_id(),
                                'description'     => 'not_staff_added_as_schedule_member',
                                'link'            => 'schedules/schedule/' . $id,
                                'touserid'        => $staff_id,
                                'additional_data' => serialize([
                                    $schedule_number,
                                ]),
                            ]);
                            array_push($new_schedule_members_to_receive_email, $staff_id);
                            if ($notifiedUsers) {
                                array_push($notifiedUsers, $staff_id);
                            }
                        }
                        $this->log_schedule_activity($id, 'schedule_activity_added_team_member', get_staff_full_name($staff_id));
                        $affectedRows++;
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            }
        }

        if (count($new_schedule_members_to_receive_email) > 0) {
            $all_members = $this->get_schedule_members($id);
            foreach ($all_members as $data) {
                if (in_array($data['staff_id'], $new_schedule_members_to_receive_email)) {

                try {
                    // init bootstrapping phase
                 
                    //$send = // (To fix merge field) send_mail_template('schedule_staff_added_as_member', $data, $id, $client_id);
                    $this->log_schedule_activity($id, 'schedule_activity_added_team_member', get_staff_full_name($staff_id));
                    $this->log_schedule_activity($id, 'schedule_activity_added_team_member', $client_id);
                    $send = 1;
                    if (!$send)
                    {
                      throw new Exception("Mail not send.");
                    }
                  
                    // continue execution of the bootstrapping phase
                } catch (Exception $e) {
                    echo $e->getMessage();
                        $this->log_schedule_activity($id, 'schedule_activity_added_team_member', get_staff_full_name($staff_id));
                        $this->log_schedule_activity($id, 'schedule_activity_added_team_member', $client_id);
                }

                }
            }
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }




    /**
     * Update canban schedule status when drag and drop
     * @param  array $data schedule data
     * @return boolean
     */
    public function update_schedule_status($data)
    {
        $this->db->select('status');
        $this->db->where('id', $data['scheduleid']);
        $_old = $this->db->get(db_prefix() . 'schedules')->row();

        $old_status = '';

        if ($_old) {
            $old_status = format_schedule_status($_old->status);
        }

        $affectedRows   = 0;
        $current_status = format_schedule_status($data['status']);


        $this->db->where('id', $data['scheduleid']);
        $this->db->update(db_prefix() . 'schedules', [
            'status' => $data['status'],
        ]);

        $_log_message = '';

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            if ($current_status != $old_status && $old_status != '') {
                $_log_message    = 'not_schedule_activity_status_updated';
                $additional_data = serialize([
                    get_staff_full_name(),
                    $old_status,
                    $current_status,
                ]);

                hooks()->do_action('schedule_status_changed', [
                    'schedule_id'    => $data['scheduleid'],
                    'old_status' => $old_status,
                    'new_status' => $current_status,
                ]);
            }
            $this->db->where('id', $data['scheduleid']);
            $this->db->update(db_prefix() . 'schedules', [
                'last_status_change' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($affectedRows > 0) {
            if ($_log_message == '') {
                return true;
            }
            $this->log_schedule_activity($data['scheduleid'], $_log_message, false, $additional_data);

            return true;
        }

        return false;
    }


    /**
     * Get the schedules about to expired in the given days
     *
     * @param  integer|null $staffId
     * @param  integer $days
     *
     * @return array
     */
    public function get_schedules_this_week($staffId = null, $days = 7)
    {
        $diff1 = date('Y-m-d', strtotime('-' . $days . ' days'));
        $diff2 = date('Y-m-d', strtotime('+' . $days . ' days'));

        if ($staffId && ! staff_can('view', 'schedules', $staffId)) {
            $this->db->where('addedfrom', $staffId);
        }

        $this->db->select(db_prefix() . 'schedules.id,' . db_prefix() . 'schedules.number,' . db_prefix() . 'clients.userid,' . db_prefix() . 'clients.company,' . db_prefix() . 'projects.name,' . db_prefix() . 'schedules.date');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'schedules.clientid', 'left');
        $this->db->join(db_prefix() . 'projects', db_prefix() . 'projects.id = ' . db_prefix() . 'schedules.project_id', 'left');
        $this->db->where('expirydate IS NOT NULL');
        $this->db->where('expirydate >=', $diff1);
        $this->db->where('expirydate <=', $diff2);

        return $this->db->get(db_prefix() . 'schedules')->result_array();
    }


    /**
     * Get the schedules for the client given
     *
     * @param  integer|null $staffId
     * @param  integer $days
     *
     * @return array
     */
    public function get_client_schedules($client = null)
    {
        /*
        if ($staffId && ! staff_can('view', 'schedules', $staffId)) {
            $this->db->where('addedfrom', $staffId);
        }
        */

        $this->db->select(db_prefix() . 'schedules.id,' . db_prefix() . 'schedules.number,' . db_prefix() . 'clients.userid,' . db_prefix() . 'schedules.hash,' . db_prefix() . 'projects.name,' . db_prefix() . 'schedules.date');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'schedules.clientid', 'left');
        $this->db->join(db_prefix() . 'projects', db_prefix() . 'projects.id = ' . db_prefix() . 'schedules.project_id', 'left');
        $this->db->where('expirydate IS NOT NULL');
        $this->db->where(db_prefix() . 'schedules.clientid =', $client->userid);

        return $this->db->get(db_prefix() . 'schedules')->result_array();
    }

}
