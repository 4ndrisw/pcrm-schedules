<?php

use app\services\schedules\SchedulesPipeline;

defined('BASEPATH') or exit('No direct script access allowed');

class Schedules extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('schedules_model');
        $this->load->model('clients_model');
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


    /* Add new schedule or update existing */
    public function schedule($id)
    {

        $schedule = $this->schedules_model->get($id);

        if (!$schedule || !user_can_view_schedule($id)) {
            blank_page(_l('schedule_not_found'));
        }

        $data['schedule'] = $schedule;
        $data['edit']     = false;
        $title            = _l('preview_schedule');
    

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        $data['staff']             = $this->staff_model->get('', ['active' => 1]);
        $data['schedule_statuses'] = $this->schedules_model->get_statuses();
        $data['title']             = $title;

        $schedule->date       = _d($schedule->date);        
        
        if ($schedule->project_id !== null) {
            $this->load->model('projects_model');
            $schedule->project_data = $this->projects_model->get($schedule->project_id);
        }

        //$data = schedule_mail_preview_data($template_name, $schedule->clientid);

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
    public function create()
    {
        if ($this->input->post()) {

            $schedule_data = $this->input->post();

            $save_and_send_later = false;
            if (isset($schedule_data['save_and_send_later'])) {
                unset($schedule_data['save_and_send_later']);
                $save_and_send_later = true;
            }

            if (!has_permission('schedules', '', 'create')) {
                access_denied('schedules');
            }

            $next_schedule_number = get_option('next_schedule_number');
            $_format = get_option('schedule_number_format');
            $_prefix = get_option('schedule_prefix');
            
            $prefix  = isset($schedule->prefix) ? $schedule->prefix : $_prefix;
            $format  = isset($schedule->number_format) ? $schedule->number_format : $_format;
            $number  = isset($schedule->number) ? $schedule->number : $next_schedule_number;

            $date = date('Y-m-d');
            
            $schedule_data['formatted_number'] = schedule_number_format($number, $format, $prefix, $date);

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
        }
        $title = _l('create_new_schedule');

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }
        /*
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        */

        $data['staff']             = $this->staff_model->get('', ['active' => 1]);
        $data['schedule_statuses'] = $this->schedules_model->get_statuses();
        $data['title']             = $title;

        $this->load->view('admin/schedules/schedule_create', $data);
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


        $data['schedule_members']  = $this->schedules_model->get_schedule_members($id);
        //$data['schedule_items']    = $this->schedules_model->get_schedule_item($id);


        $data['staff']             = $this->staff_model->get('', ['active' => 1]);
        $data['schedule_statuses'] = $this->schedules_model->get_statuses();
        $data['title']             = $title;
        $this->load->view('admin/schedules/schedule_update', $data);
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

    /* Convert schedule to jobreport */
    public function convert_to_jobreport($id)
    {
        if (!has_permission('jobreports', '', 'create')) {
            access_denied('jobreports');
        }
        if (!$id) {
            die('No schedule found');
        }
        $draft_jobreport = false;
        if ($this->input->get('save_as_draft')) {
            $draft_jobreport = true;
        }
        $jobreportid = $this->schedules_model->convert_to_jobreport($id, false, $draft_jobreport);
        if ($jobreportid) {
            set_alert('success', _l('schedule_convert_to_jobreport_successfully'));
            redirect(admin_url('jobreports/jobreport/' . $jobreportid));
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
    
    /* Used in kanban when dragging and mark as */
    public function update_schedule_status()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            $this->schedules_model->update_schedule_status($this->input->post());
        }
    }

    public function clear_signature($id)
    {
        if (has_permission('schedules', '', 'delete')) {
            $this->schedules_model->clear_signature($id);
        }

        redirect(admin_url('schedules/schedule/' . $id));
    }

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
            redirect(admin_url('schedules'));
        }
        $schedule        = $this->schedules_model->get($id);
        $schedule_number = format_schedule_number($schedule->id);
        
        $schedule->assigned_path = FCPATH . get_schedule_upload_path('schedule').$schedule->id.'/assigned-'.$schedule_number.'.png';
        $schedule->acceptance_path = FCPATH . get_schedule_upload_path('schedule').$schedule->id.'/signature.png';
        $schedule->client_company = $this->clients_model->get($schedule->clientid)->company;
        $schedule->acceptance_date_string = _dt($schedule->acceptance_date);


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
}