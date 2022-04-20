<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Schedule_expiration_reminder extends App_mail_template
{
    protected $for = 'customer';

    protected $schedule;

    protected $contact;

    public $slug = 'schedule-expiry-reminder';

    public $rel_type = 'schedule';

    public function __construct($schedule, $contact)
    {
        parent::__construct();

        $this->schedule = $schedule;
        $this->contact  = $contact;

        // For SMS
        $this->set_merge_fields('client_merge_fields', $this->schedule->clientid, $this->contact['id']);
        $this->set_merge_fields('schedule_merge_fields', $this->schedule->id);
    }

    public function build()
    {
        $this->to($this->contact['email'])
        ->set_rel_id($this->schedule->id);
    }
}
