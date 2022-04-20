<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Schedule_send_to_customer_already_sent extends App_mail_template
{
    protected $for = 'customer';

    protected $schedule;

    protected $contact;

    public $slug = 'schedule-already-send';

    public $rel_type = 'schedule';

    public function __construct($schedule, $contact = false, $cc = '')
    {
        parent::__construct();

        $this->schedule = $schedule;
        $this->contact = $contact;
        $this->cc      = $cc;
    }

    public function build()
    {
        if ($this->ci->input->post('email_attachments')) {
            $_other_attachments = $this->ci->input->post('email_attachments');
            foreach ($_other_attachments as $attachment) {
                $_attachment = $this->ci->schedules_model->get_attachments($this->schedule->id, $attachment);
                $this->add_attachment([
                                'attachment' => get_upload_path_by_type('schedule') . $this->schedule->id . '/' . $_attachment->file_name,
                                'filename'   => $_attachment->file_name,
                                'type'       => $_attachment->filetype,
                                'read'       => true,
                            ]);
            }
        }

        $this->to($this->contact->email)
        ->set_rel_id($this->schedule->id)
        ->set_merge_fields('client_merge_fields', $this->schedule->clientid, $this->contact->id)
        ->set_merge_fields('schedule_merge_fields', $this->schedule->id);
    }
}
