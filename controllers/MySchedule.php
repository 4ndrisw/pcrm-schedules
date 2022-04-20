<?php defined('BASEPATH') or exit('No direct script access allowed');

class MySchedule extends ClientsController
{
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
                if (is_array($success) && $success['invoiced'] == true) {
                    $accepted = true;
                    $invoice  = $this->invoices_model->get($success['invoiceid']);
                    set_alert('success', _l('clients_schedule_invoiced_successfully'));
                    $redURL = site_url('invoice/' . $invoice->id . '/' . $invoice->hash);
                } elseif (is_array($success) && $success['invoiced'] == false || $success === true) {
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
        if ($this->input->post('schedulepdf')) {
            try {
                $pdf = schedule_pdf($schedule);
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }

            $schedule_number = format_schedule_number($schedule->id);
            $companyname     = get_option('invoice_company_name');
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

        $data['title'] = format_schedule_number($schedule->id);
        $this->disableNavigation();
        $this->disableSubMenu();
        $data['hash']                          = $hash;
        $data['can_be_accepted']               = false;
        $data['schedule']                      = hooks()->apply_filters('schedule_html_pdf_data', $schedule);
        $data['bodyclass']                     = 'viewschedule';
        $data['identity_confirmation_enabled'] = $identity_confirmation_enabled;
        if ($identity_confirmation_enabled == '1') {
            $data['bodyclass'] .= ' identity-confirmation';
        }
        $data['schedule_members']  = $this->schedules_model->get_schedule_members($schedule->id,true);
        $this->data($data);
        //$this->view('schedulehtml');
        $this->view('themes/'. active_clients_theme() .'/views/schedulehtml');
        add_views_tracking('schedule', $id);
        hooks()->do_action('schedule_html_viewed', $id);
        no_index_customers_area();
        $this->layout();
    }
}
