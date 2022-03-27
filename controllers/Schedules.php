<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Schedules extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('schedules_model');
    }

    /* List all announcements */
    public function index()
    {
        if (!has_permission('schedules', '', 'view')) {
            access_denied('schedules');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('schedules', 'table'));
        }
        $this->app_scripts->add('circle-progress-js','assets/plugins/jquery-circle-progress/circle-progress.min.js');
        $data['title']                 = _l('schedules_tracking');
        $this->load->view('manage', $data);
    }

    public function schedule($id = '')
    {
        if($id == ''){
            $data['heading'] = 'ERROR !';
            $data['message'] = 'You do not have access this page !';
            $this->load->view('error_404', $data);
            return;
        }


        if (!has_permission('schedules', '', 'view')) {
            access_denied('schedules');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('schedules', 'small-table'));
        }
        $this->app_scripts->add('circle-progress-js','assets/plugins/jquery-circle-progress/circle-progress.min.js');
        
        $data['schedule']        = $this->schedules_model->get($id);
        $data['achievement'] = $this->schedules_model->calculate_schedule_achievement($id);

        $title = _l('edit', _l('schedule_lowercase'));

        $data['object']['schedule']        = $this->schedules_model->get($id);


        $this->load->model('staff_model');
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active'=>1]);

        $this->load->model('contracts_model');
        $data['contract_types']        = $this->contracts_model->get_contract_types();
        $data['title']                 = $title;
        $this->app_scripts->add('circle-progress-js','assets/plugins/jquery-circle-progress/circle-progress.min.js');
        $this->load->view('schedule', $data);
    }


    public function create($id = '')
    {
        if (!has_permission('schedules', '', 'view')) {
            access_denied('schedules');
        }
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('schedules', '', 'create')) {
                    access_denied('schedules');
                }
                $id = $this->schedules_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('schedule')));
                    redirect(admin_url('schedules/schedule/' . $id));
                }
            } else {
                if (!has_permission('schedules', '', 'edit')) {
                    access_denied('schedules');
                }
                $success = $this->schedules_model->update($this->input->post(), $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('schedule')));
                }
                redirect(admin_url('schedules/schedule/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('schedule_lowercase'));
        } else {
            $data['schedule']        = $this->schedules_model->get($id);
            $data['achievement'] = $this->schedules_model->calculate_schedule_achievement($id);

            $title = _l('edit', _l('schedule_lowercase'));
        }

        $this->load->model('staff_model');
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active'=>1]);

        $this->load->model('contracts_model');
        $data['contract_types']        = $this->contracts_model->get_contract_types();
        $data['title']                 = $title;
        $this->app_scripts->add('circle-progress-js','assets/plugins/jquery-circle-progress/circle-progress.min.js');
        $this->load->view('schedule-add', $data);
    }


    public function edit($id = '')
    {
        if (!has_permission('schedules', '', 'view')) {
            access_denied('schedules');
        }


        if ($this->input->post()) {
            if (!has_permission('schedules', '', 'edit')) {
                access_denied('schedules');
            }
            $success = $this->schedules_model->update($this->input->post(), $id);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('schedule')));
            }
            redirect(admin_url('schedules/schedule/' . $id));
        }

        $data['schedule']        = $this->schedules_model->get($id);
        $title = _l('edit', _l('schedule_lowercase'));
        

        $this->load->model('staff_model');
        $data['members'] = $this->staff_model->get('', ['is_not_staff' => 0, 'active'=>1]);

        $this->load->model('contracts_model');
        $data['contract_types']        = $this->contracts_model->get_contract_types();
        $data['title']                 = $title;

        $this->load->view('schedule-edit', $data);
    }

    /* Delete announcement from database */
    public function delete($id)
    {
        if (!has_permission('schedules', '', 'delete')) {
            access_denied('schedules');
        }
        if (!$id) {
            redirect(admin_url('schedules'));
        }
        $response = $this->schedules_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('schedule')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('schedule_lowercase')));
        }
        redirect(admin_url('schedules'));
    }

    public function notify($id, $notify_type)
    {
        if (!has_permission('schedules', '', 'edit') && !has_permission('schedules', '', 'create')) {
            access_denied('schedules');
        }
        if (!$id) {
            redirect(admin_url('schedules'));
        }
        $success = $this->schedules_model->notify_staff_members($id, $notify_type);
        if ($success) {
            set_alert('success', _l('schedule_notify_staff_notified_manually_success'));
        } else {
            set_alert('warning', _l('schedule_notify_staff_notified_manually_fail'));
        }
        redirect(admin_url('schedules/schedule/' . $id));
    }
}