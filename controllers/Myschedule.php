<?php defined('BASEPATH') or exit('No direct script access allowed');

class Myschedule extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('schedules_model');
        $this->load->model('clients_model');
    }

    /* Get all schedules in case user go on index page */
    public function list($id = '')
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('schedules', 'admin/tables/table'));
        }
        $contact_id = get_contact_user_id();
        $user_id = get_user_id_by_contact_id($contact_id);
        $client = $this->clients_model->get($user_id);
        $data['schedules'] = $this->schedules_model->get_client_schedules($client);
        $data['scheduleid']            = $id;
        $data['title']                 = _l('schedules_tracking');

        $data['bodyclass'] = 'schedules';
        $this->data($data);
        $this->view('themes/'. active_clients_theme() .'/views/schedules/schedules');
        $this->layout();
    }

    public function show($id, $hash)
    {
        check_schedule_restrictions($id, $hash);
        $schedule = $this->schedules_model->get($id);

        if (!is_client_logged_in()) {
            load_client_language($schedule->clientid);
        }

        $identity_confirmation_enabled = get_option('schedule_accept_identity_confirmation');

        if ($this->input->post('schedule_action')) {
            $action = $this->input->post('schedule_action');

            // Only decline and accept allowed
            if ($action == 4 || $action == 3) {
                $success = $this->schedules_model->mark_action_status($action, $id, true);

                $redURL   = $this->uri->uri_string();
                $accepted = false;

                if (is_array($success)) {
                    if ($action == 4) {
                        $accepted = true;
                        set_alert('success', _l('clients_schedule_accepted_not_invoiced'));
                    } else {
                        set_alert('success', _l('clients_schedule_declined'));
                    }
                } else {
                    set_alert('warning', _l('clients_schedule_failed_action'));
                }
                if ($action == 4 && $accepted = true) {
                    process_digital_signature_image($this->input->post('signature', false), SCHEDULE_ATTACHMENTS_FOLDER . $id);

                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'schedules', get_acceptance_info_array());
                }
            }
            redirect($redURL);
        }
        // Handle Schedule PDF generator

        $schedule_number = format_schedule_number($schedule->id);
        if ($this->input->post('schedulepdf')) {
            try {
                $pdf = schedule_pdf($schedule);
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }

            //$schedule_number = format_schedule_number($schedule->id);
            $companyname     = get_option('company_name');
            if ($companyname != '') {
                $schedule_number .= '-' . mb_strtoupper(slug_it($companyname), 'UTF-8');
            }

            $filename = hooks()->apply_filters('customers_area_download_schedule_filename', mb_strtoupper(slug_it($schedule_number), 'UTF-8') . '.pdf', $schedule);

            $pdf->Output($filename, 'D');
            die();
        }
        $this->load->library('app_number_to_word', [
            'clientid' => $schedule->clientid,
        ], 'numberword');

        $this->app_scripts->theme('sticky-js', 'assets/plugins/sticky/sticky.js');


        $data['title'] = $schedule_number;
        $this->disableNavigation();
        $this->disableSubMenu();

        $data['schedule_number']              = $schedule_number;
        $data['hash']                          = $hash;
        $data['can_be_accepted']               = false;
        $data['schedule']                     = hooks()->apply_filters('schedule_html_pdf_data', $schedule);
        $data['bodyclass']                     = 'viewschedule';
        $data['client_company']                = $this->clients_model->get($schedule->clientid)->company;

        $data['identity_confirmation_enabled'] = $identity_confirmation_enabled;
        if ($identity_confirmation_enabled == '1') {
            $data['bodyclass'] .= ' identity-confirmation';
        }
        $data['schedule_members']  = $this->schedules_model->get_schedule_members($schedule->id,true);

        $qrcode_data  = '';
        $qrcode_data .= _l('schedule_number') . ' : ' . $schedule_number ."\r\n";
        $qrcode_data .= _l('schedule_date') . ' : ' . $schedule->date ."\r\n";
        $qrcode_data .= _l('schedule_datesend') . ' : ' . $schedule->datesend ."\r\n";
        $qrcode_data .= _l('schedule_assigned_string') . ' : ' . get_staff_full_name($schedule->assigned) ."\r\n";
        $qrcode_data .= _l('schedule_url') . ' : ' . site_url('schedules/show/'. $schedule->id .'/'.$schedule->hash) ."\r\n";


        $schedule_path = get_upload_path_by_type('schedules') . $schedule->id . '/';
        _maybe_create_upload_path('uploads/schedules');
        _maybe_create_upload_path('uploads/schedules/'.$schedule_path);

        $params['data'] = $qrcode_data;
        $params['writer'] = 'png';
        $params['setSize'] = 160;
        $params['encoding'] = 'UTF-8';
        $params['setMargin'] = 0;
        $params['setForegroundColor'] = ['r'=>0,'g'=>0,'b'=>0];
        $params['setBackgroundColor'] = ['r'=>255,'g'=>255,'b'=>255];

        $params['crateLogo'] = true;
        $params['logo'] = './uploads/company/favicon.png';
        $params['setResizeToWidth'] = 60;

        $params['crateLabel'] = false;
        $params['label'] = $schedule_number;
        $params['setTextColor'] = ['r'=>255,'g'=>0,'b'=>0];
        $params['ErrorCorrectionLevel'] = 'hight';

        $params['saveToFile'] = FCPATH.'uploads/schedules/'.$schedule_path .'assigned-'.$schedule_number.'.'.$params['writer'];

        $this->load->library('endroid_qrcode');
        $this->endroid_qrcode->generate($params);

        $this->data($data);
        //$this->view('schedulehtml');
        $this->view('themes/'. active_clients_theme() .'/views/schedules/schedulehtml');
        add_views_tracking('schedule', $id);
        hooks()->do_action('schedule_html_viewed', $id);
        no_index_customers_area();
        $this->layout();
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
