<?php

use app\services\schedules\SchedulesPipeline;

defined('BASEPATH') or exit('No direct script access allowed');

class Schedules extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('schedules_model');
    }

    /* Get all schedules in case user go on index page */
    public function index($id = '')
    {
        if (!has_permission('schedules', '', 'view')) {
            access_denied('schedules');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('schedules', 'admin/tables/table'));
        }
        $data['scheduleid']            = $id;
        $data['title']                 = _l('schedules_tracking');
        $this->load->view('admin/schedules/manage', $data);
    }

    /* List all schedules datatables */
    public function list_schedules($id = '')
    {
        if (!has_permission('schedules', '', 'view') && !has_permission('schedules', '', 'view_own') && get_option('allow_staff_view_schedules_assigned') == '0') {
            access_denied('schedules');
        }

        $isPipeline = $this->session->userdata('schedule_pipeline') == 'true';

        $data['schedule_statuses'] = $this->schedules_model->get_statuses();
        if ($isPipeline && !$this->input->get('status') && !$this->input->get('filter')) {
            $data['title']           = _l('schedules_pipeline');
            $data['bodyclass']       = 'schedules-pipeline schedules-total-manual';
            $data['switch_pipeline'] = false;

            if (is_numeric($id)) {
                $data['scheduleid'] = $id;
            } else {
                $data['scheduleid'] = $this->session->flashdata('scheduleid');
            }

            $this->load->view('admin/schedules/pipeline/manage', $data);
        } else {

            // Pipeline was initiated but user click from home page and need to show table only to filter
            if ($this->input->get('status') || $this->input->get('filter') && $isPipeline) {
                $this->pipeline(0, true);
            }

            //if ($this->input->is_ajax_request()) {
               //$table_data =  $this->app->get_table_data(module_views_path('schedules', 'admin/tables/schedules'));
            //}
            //var_dump($table_data);
            //die();


            $data['scheduleid']            = $id;
            $data['switch_pipeline']       = true;
            $data['title']                 = _l('schedules');
            $data['bodyclass']             = 'schedules-total-manual';
            $data['schedules_years']       = $this->schedules_model->get_schedules_years();
            $data['schedules_assigneds'] = $this->schedules_model->get_assigneds();
            $this->load->view('admin/schedules/manage', $data);
        }
    }

    public function table($clientid = '')
    {
        if (!has_permission('schedules', '', 'view') && !has_permission('schedules', '', 'view_own') && get_option('allow_staff_view_schedules_assigned') == '0') {
            ajax_access_denied();
        }

        $this->get_schedules_table_data('schedules', [
            'clientid' => $clientid,
        ]);
    }

    /* Add new schedule or update existing */
    public function schedule($id = '')
    {


        $schedule = $this->schedules_model->get($id);

        if (!$schedule || !user_can_view_schedule($id)) {
            blank_page(_l('schedule_not_found'));
        }

        $data['schedule'] = $schedule;
        $data['edit']     = true;
        $title            = _l('edit', _l('schedule_lowercase'));
    

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }


        $data['staff']             = $this->staff_model->get('', ['active' => 1]);
        $data['schedule_statuses'] = $this->schedules_model->get_statuses();
        $data['title']             = $title;




        $schedule->date       = _d($schedule->date);
        $schedule->expirydate = _d($schedule->expirydate);
        
        if ($schedule->invoiceid !== null) {
            $this->load->model('invoices_model');
            $schedule->invoice = $this->invoices_model->get($schedule->invoiceid);
        }
        
        if ($schedule->project_id !== null) {
            $this->load->model('projects_model');
            $schedule->project_data = $this->projects_model->get($schedule->project_id);
        }

        if ($schedule->sent == 0) {

            $template_name = 'schedule_send_to_customer';
        } else {
            $template_name = 'schedule_send_to_customer_already_sent';
        }

        $data = prepare_mail_preview_data($template_name, $schedule->clientid);

        $data['schedule_members'] = $this->schedules_model->get_schedule_members($id,true);

        //$data['schedule_items']    = $this->schedules_model->get_schedule_item($id);

        $data['activity']          = $this->schedules_model->get_schedule_activity($id);
        $data['schedule']          = $schedule;
        $data['members']           = $this->staff_model->get('', ['active' => 1]);
        $data['schedule_statuses'] = $this->schedules_model->get_statuses();
        $data['totalNotes']        = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'schedule']);

        $data['send_later'] = false;
        if ($this->session->has_userdata('send_later')) {
            $data['send_later'] = true;
            $this->session->unset_userdata('send_later');
        }


        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('schedules', 'admin/tables/small_table'));
        }

        $this->load->view('admin/schedules/schedule_preview', $data);
    }



    /* Add new schedule */
    public function add()
    {
        if ($this->input->post()) {
            $schedule_data = $this->input->post();

            $save_and_send_later = false;
            if (isset($schedule_data['save_and_send_later'])) {
                unset($schedule_data['save_and_send_later']);
                $save_and_send_later = true;
            }

            /*if ($id == '') {*/
                if (!has_permission('schedules', '', 'create')) {
                    access_denied('schedules');
                }
                $id = $this->schedules_model->add($schedule_data);

                if ($id) {
                    set_alert('success', _l('added_successfully', _l('schedule')));

                    $redUrl = admin_url('schedules/schedule/' . $id);

                    if ($save_and_send_later) {
                        $this->session->set_userdata('send_later', true);
                        // die(redirect($redUrl));
                    }

                    redirect(
                        !$this->set_schedule_pipeline_autoload($id) ? $redUrl : admin_url('schedules/schedule/')
                    );
                }
            /*}*/
            /* else {
                if (!has_permission('schedules', '', 'edit')) {
                    access_denied('schedules');
                }
                $success = $this->schedules_model->update($schedule_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('schedule')));
                }
                if ($this->set_schedule_pipeline_autoload($id)) {
                    redirect(admin_url('schedules/schedule/'));
                } else {
                    redirect(admin_url('schedules/schedule/' . $id));
                }
            }*/
        }
        /*
        if ($id == '') { */
            $title = _l('create_new_schedule');
        
        /*} else {

            $schedule = $this->schedules_model->get($id);

            if (!$schedule || !user_can_view_schedule($id)) {
                blank_page(_l('schedule_not_found'));
            }

            $data['schedule'] = $schedule;
            $data['edit']     = true;
            $title            = _l('edit', _l('schedule_lowercase'));
        }*/

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }


        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $this->load->model('invoice_items_model');

        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['staff']             = $this->staff_model->get('', ['active' => 1]);
        $data['schedule_statuses'] = $this->schedules_model->get_statuses();
        $data['title']             = $title;

        $this->load->view('admin/schedules/schedule_add', $data);
    }



    /* update schedule */
    public function update($id='')
    {
        if ($this->input->post()) {
            $schedule_data = $this->input->post();

            $save_and_send_later = false;
            if (isset($schedule_data['save_and_send_later'])) {
                unset($schedule_data['save_and_send_later']);
                $save_and_send_later = true;
            }

            if ($id == '') {
                if (!has_permission('schedules', '', 'create')) {
                    access_denied('schedules');
                }
                $id = $this->schedules_model->add($schedule_data);

                if ($id) {
                    set_alert('success', _l('added_successfully', _l('schedule')));

                    $redUrl = admin_url('schedules/schedule/' . $id);

                    if ($save_and_send_later) {
                        $this->session->set_userdata('send_later', true);
                        // die(redirect($redUrl));
                    }

                    redirect(
                        !$this->set_schedule_pipeline_autoload($id) ? $redUrl : admin_url('schedules/schedule/')
                    );
                }
            } else {
                if (!has_permission('schedules', '', 'edit')) {
                    access_denied('schedules');
                }
                $success = $this->schedules_model->update($schedule_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('schedule')));
                }
                
                if ($this->set_schedule_pipeline_autoload($id)) {
                    redirect(admin_url('schedules/'));
                } else {
                    redirect(admin_url('schedules/schedule/' . $id));
                }
                
            }
        }
        if ($id == '') {
            $title = _l('create_new_schedule');
        } else {
            $schedule = $this->schedules_model->get($id);

            if (!$schedule || !user_can_view_schedule($id)) {
                blank_page(_l('schedule_not_found'));
            }

            $data['schedule'] = $schedule;
            $data['edit']     = true;
            $title            = _l('edit', _l('schedule_lowercase'));
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }
/*
        if ($this->input->get('schedule_request_id')) {
            $data['schedule_request_id'] = $this->input->get('schedule_request_id');
        }

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $this->load->model('invoice_items_model');

        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();
*/


        $data['schedule_members']  = $this->schedules_model->get_schedule_members($id);
        //$data['schedule_items']    = $this->schedules_model->get_schedule_item($id);


        $data['staff']             = $this->staff_model->get('', ['active' => 1]);
        $data['schedule_statuses'] = $this->schedules_model->get_statuses();
        $data['title']             = $title;
        $this->load->view('admin/schedules/schedule_update', $data);
    }


    public function clear_signature($id)
    {
        if (has_permission('schedules', '', 'delete')) {
            $this->schedules_model->clear_signature($id);
        }

        redirect(admin_url('schedules/schedule/' . $id));
    }

    public function update_number_settings($id)
    {
        $response = [
            'success' => false,
            'message' => '',
        ];
        if (has_permission('schedules', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'schedules', [
                'prefix' => $this->input->post('prefix'),
            ]);
            if ($this->db->affected_rows() > 0) {
                $response['success'] = true;
                $response['message'] = _l('updated_successfully', _l('schedule'));
            }
        }

        echo json_encode($response);
        die;
    }

    public function validate_schedule_number()
    {
        $isedit          = $this->input->post('isedit');
        $number          = $this->input->post('number');
        $date            = $this->input->post('date');
        $original_number = $this->input->post('original_number');
        $number          = trim($number);
        $number          = ltrim($number, '0');

        if ($isedit == 'true') {
            if ($number == $original_number) {
                echo json_encode(true);
                die;
            }
        }

        if (total_rows(db_prefix() . 'schedules', [
            'YEAR(date)' => date('Y', strtotime(to_sql_date($date))),
            'number' => $number,
        ]) > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->schedules_model->delete_attachment($id);
        } else {
            header('HTTP/1.0 400 Bad error');
            echo _l('access_denied');
            die;
        }
    }

    /* Get all schedule data used when user click on schedule number in a datatable left side*/
    public function get_schedule_data_ajax($id, $to_return = false)
    {
        if (!has_permission('schedules', '', 'view') && !has_permission('schedules', '', 'view_own') && get_option('allow_staff_view_schedules_assigned') == '0') {
            echo _l('access_denied');
            die;
        }

        if (!$id) {
            die('No schedule found');
        }

        $schedule = $this->schedules_model->get($id);

        if (!$schedule || !user_can_view_schedule($id)) {
            echo _l('schedule_not_found');
            die;
        }

        $schedule->date       = _d($schedule->date);
        $schedule->expirydate = _d($schedule->expirydate);
        if ($schedule->invoiceid !== null) {
            $this->load->model('invoices_model');
            $schedule->invoice = $this->invoices_model->get($schedule->invoiceid);
        }

        if ($schedule->sent == 0) {

            $template_name = 'schedule_send_to_customer';
        } else {
            $template_name = 'schedule_send_to_customer_already_sent';
        }

        //$data = prepare_mail_preview_data($template_name, $schedule->clientid);
        $data = schedule_mail_preview_data($template_name, $schedule->clientid);

        $data['activity']          = $this->schedules_model->get_schedule_activity($id);
        $data['schedule']          = $schedule;
        $data['members']           = $this->staff_model->get('', ['active' => 1]);
        $data['schedule_statuses'] = $this->schedules_model->get_statuses();
        $data['totalNotes']        = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'schedule']);

        $data['send_later'] = false;
        if ($this->session->has_userdata('send_later')) {
            $data['send_later'] = true;
            $this->session->unset_userdata('send_later');
        }

        if ($to_return == false) {
            $this->load->view('admin/schedules/schedule_preview_template', $data);
        } else {
            return $this->load->view('admin/schedules/schedule_preview_template', $data, true);
        }
    }

    public function get_schedules_total()
    {
        if ($this->input->post()) {
            $data['totals'] = $this->schedules_model->get_schedules_total($this->input->post());

            $this->load->model('currencies_model');

            if (!$this->input->post('customer_id')) {
                $multiple_currencies = call_user_func('is_using_multiple_currencies', db_prefix() . 'schedules');
            } else {
                $multiple_currencies = call_user_func('is_client_using_multiple_currencies', $this->input->post('customer_id'), db_prefix() . 'schedules');
            }

            if ($multiple_currencies) {
                $data['currencies'] = $this->currencies_model->get();
            }

            $data['schedules_years'] = $this->schedules_model->get_schedules_years();

            if (
                count($data['schedules_years']) >= 1
                && !\app\services\utilities\Arr::inMultidimensional($data['schedules_years'], 'year', date('Y'))
            ) {
                array_unshift($data['schedules_years'], ['year' => date('Y')]);
            }

            $data['_currency'] = $data['totals']['currencyid'];
            unset($data['totals']['currencyid']);
            $this->load->view('admin/schedules/schedules_total_template', $data);
        }
    }

    public function add_note($rel_id)
    {
        if ($this->input->post() && user_can_view_schedule($rel_id)) {
            $this->misc_model->add_note($this->input->post(), 'schedule', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        if (user_can_view_schedule($id)) {
            $data['notes'] = $this->misc_model->get_notes($id, 'schedule');
            $this->load->view('admin/includes/sales_notes_template', $data);
        }
    }

    public function mark_action_status($status, $id)
    {
        if (!has_permission('schedules', '', 'edit')) {
            access_denied('schedules');
        }
        $success = $this->schedules_model->mark_action_status($status, $id);
        if ($success) {
            set_alert('success', _l('schedule_status_changed_success'));
        } else {
            set_alert('danger', _l('schedule_status_changed_fail'));
        }
        if ($this->set_schedule_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('schedules/schedule/' . $id));
        }
    }

    public function send_expiry_reminder($id)
    {
        $canView = user_can_view_schedule($id);
        if (!$canView) {
            access_denied('Schedules');
        } else {
            if (!has_permission('schedules', '', 'view') && !has_permission('schedules', '', 'view_own') && $canView == false) {
                access_denied('Schedules');
            }
        }

        $success = $this->schedules_model->send_expiry_reminder($id);
        if ($success) {
            set_alert('success', _l('sent_expiry_reminder_success'));
        } else {
            set_alert('danger', _l('sent_expiry_reminder_fail'));
        }
        if ($this->set_schedule_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('schedules/schedule/' . $id));
        }
    }

    /* Send schedule to email */
    public function send_to_email($id)
    {
        $canView = user_can_view_schedule($id);
        if (!$canView) {
            access_denied('schedules');
        } else {
            if (!has_permission('schedules', '', 'view') && !has_permission('schedules', '', 'view_own') && $canView == false) {
                access_denied('schedules');
            }
        }

        try {
            $success = $this->schedules_model->send_schedule_to_client($id, '', $this->input->post('attach_pdf'), $this->input->post('cc'));
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        // In case client use another language
        load_admin_language();
        if ($success) {
            set_alert('success', _l('schedule_sent_to_client_success'));
        } else {
            set_alert('danger', _l('schedule_sent_to_client_fail'));
        }
        if ($this->set_schedule_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('schedules/schedule/' . $id));
        }
    }

    /* Convert schedule to invoice */
    public function convert_to_invoice($id)
    {
        if (!has_permission('invoices', '', 'create')) {
            access_denied('invoices');
        }
        if (!$id) {
            die('No schedule found');
        }
        $draft_invoice = false;
        if ($this->input->get('save_as_draft')) {
            $draft_invoice = true;
        }
        $invoiceid = $this->schedules_model->convert_to_invoice($id, false, $draft_invoice);
        if ($invoiceid) {
            set_alert('success', _l('schedule_convert_to_invoice_successfully'));
            redirect(admin_url('invoices/list_invoices/' . $invoiceid));
        } else {
            if ($this->session->has_userdata('schedule_pipeline') && $this->session->userdata('schedule_pipeline') == 'true') {
                $this->session->set_flashdata('scheduleid', $id);
            }
            if ($this->set_schedule_pipeline_autoload($id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('schedules/schedule/' . $id));
            }
        }
    }

    public function copy($id)
    {
        if (!has_permission('schedules', '', 'create')) {
            access_denied('schedules');
        }
        if (!$id) {
            die('No schedule found');
        }
        $new_id = $this->schedules_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('schedule_copied_successfully'));
            if ($this->set_schedule_pipeline_autoload($new_id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('schedules/schedule/' . $new_id));
            }
        }
        set_alert('danger', _l('schedule_copied_fail'));
        if ($this->set_schedule_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('schedules/schedule/' . $id));
        }
    }

    /* Delete schedule */
    public function delete($id)
    {
        if (!has_permission('schedules', '', 'delete')) {
            access_denied('schedules');
        }
        if (!$id) {
            redirect(admin_url('schedules'));
        }
        $success = $this->schedules_model->delete($id);
        if (is_array($success)) {
            set_alert('warning', _l('is_invoiced_schedule_delete_error'));
        } elseif ($success == true) {
            set_alert('success', _l('deleted', _l('schedule')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('schedule_lowercase')));
        }
        redirect(admin_url('schedules'));
    }
    /*
    public function clear_acceptance_info($id)
    {
        if (is_admin()) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'schedules', get_acceptance_info_array(true));
        }

        redirect(admin_url('schedules/schedule/' . $id));
    }
    */

    /* Generates schedule PDF and senting to email  */
    public function pdf($id)
    {
        $canView = user_can_view_schedule($id);
        if (!$canView) {
            access_denied('Schedules');
        } else {
            if (!has_permission('schedules', '', 'view') && !has_permission('schedules', '', 'view_own') && $canView == false) {
                access_denied('Schedules');
            }
        }
        if (!$id) {
            redirect(admin_url('schedules/schedule'));
        }
        $schedule        = $this->schedules_model->get($id);
        $schedule_number = format_schedule_number($schedule->id);
        
        try {
            $pdf = schedule_pdf($schedule);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $fileNameHookData = hooks()->apply_filters('schedule_file_name_admin_area', [
                            'file_name' => mb_strtoupper(slug_it($schedule_number)) . '.pdf',
                            'schedule'  => $schedule,
                        ]);

        $pdf->Output($fileNameHookData['file_name'], $type);
    }

    // Pipeline
    public function get_pipeline()
    {
        if (has_permission('schedules', '', 'view') || has_permission('schedules', '', 'view_own') || get_option('allow_staff_view_schedules_assigned') == '1') {
            $data['schedule_statuses'] = $this->schedules_model->get_statuses();
            $this->load->view('admin/schedules/pipeline/pipeline', $data);
        }
    }

    public function pipeline_open($id)
    {
        $canView = user_can_view_schedule($id);
        if (!$canView) {
            access_denied('Schedules');
        } else {
            if (!has_permission('schedules', '', 'view') && !has_permission('schedules', '', 'view_own') && $canView == false) {
                access_denied('Schedules');
            }
        }

        $data['id']       = $id;
        $data['schedule'] = $this->get_schedule_data_ajax($id, true);
        $this->load->view('admin/schedules/pipeline/schedule', $data);
    }

    public function update_pipeline()
    {
        if (has_permission('schedules', '', 'edit')) {
            $this->schedules_model->update_pipeline($this->input->post());
        }
    }

    public function pipeline($set = 0, $manual = false)
    {
        if ($set == 1) {
            $set = 'true';
        } else {
            $set = 'false';
        }
        $this->session->set_userdata([
            'schedule_pipeline' => $set,
        ]);
        if ($manual == false) {
            redirect(admin_url('schedules/schedule'));
        }
    }

    public function pipeline_load_more()
    {
        $status = $this->input->get('status');
        $page   = $this->input->get('page');

        $schedules = (new SchedulesPipeline($status))
            ->search($this->input->get('search'))
            ->sortBy(
                $this->input->get('sort_by'),
                $this->input->get('sort')
            )
            ->page($page)->get();

        foreach ($schedules as $schedule) {
            $this->load->view('admin/schedules/pipeline/_kanban_card', [
                'schedule' => $schedule,
                'status'   => $status,
            ]);
        }
    }

    public function set_schedule_pipeline_autoload($id)
    {
        if ($id == '') {
            return false;
        }

        if ($this->session->has_userdata('schedule_pipeline')
                && $this->session->userdata('schedule_pipeline') == 'true') {
            $this->session->set_flashdata('scheduleid', $id);

            return true;
        }

        return false;
    }

    public function get_due_date()
    {
        if ($this->input->post()) {
            $date    = $this->input->post('date');
            $duedate = '';
            if (get_option('schedule_due_after') != 0) {
                $date    = to_sql_date($date);
                $d       = date('Y-m-d', strtotime('+' . get_option('schedule_due_after') . ' DAY', strtotime($date)));
                $duedate = _d($d);
                echo $duedate;
            }
        }
    }

    /**
     * Function that will parse table data from the tables folder for amin area
     * @param  string $table  table filename
     * @param  array  $params additional params
     * @return void
     */
    public function get_schedules_table_data($table, $params = [])
    {
        $params = hooks()->apply_filters('table_params', $params, $table);

        foreach ($params as $key => $val) {
            $$key = $val;
        }

        $customFieldsColumns = [];

        $path = module_views_path('schedules', 'admin/tables/' . $table . EXT);

        if (!file_exists($path)) {
            $path = $table;
            if (!endsWith($path, EXT)) {
                $path .= EXT;
            }
        } else {
            $myPrefixedPath = module_views_path('schedules', 'admin/tables/my_' . $table . EXT);
            if (file_exists($myPrefixedPath)) {
                $path = $myPrefixedPath;
            }
        }

        include_once($path);
        echo json_encode($output);
        die;
    }


    /* Used in kanban when dragging and mark as */
    public function update_schedule_status()
    {
        //log_activity('schedule_activity_update_status status '. time() .' '. json_encode($this->input->post()));
        if ($this->input->post() && $this->input->is_ajax_request()) {
            $this->schedules_model->update_schedule_status($this->input->post());
        }
    }

}
