<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Schedule_accepted_to_staff extends App_mail_template
{
    protected $for = 'staff';

    protected $schedule;

    protected $staff_email;

    protected $contact_id;

    public $slug = 'schedule-accepted-to-staff';

    public $rel_type = 'schedule';

    public function __construct($schedule, $staff_email, $contact_id)
    {
        parent::__construct();

        $this->schedule    = $schedule;
        $this->staff_email = $staff_email;
        $this->contact_id  = $contact_id;
    }

    public function build()
    {

        $this->to($this->staff_email)
        ->set_rel_id($this->schedule->id)
        ->set_merge_fields('client_merge_fields', $this->schedule->clientid, $this->contact_id)
        ->set_merge_fields('schedule_merge_fields', $this->schedule->id);
    }
}
