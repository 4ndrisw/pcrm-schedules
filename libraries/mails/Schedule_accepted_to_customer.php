<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Schedule_accepted_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $schedule;

    protected $contact;

    public $slug = 'schedule-thank-you-to-customer';

    public $rel_type = 'schedule';

    public function __construct($schedule, $contact)
    {
        parent::__construct();

        $this->schedule = $schedule;
        $this->contact  = $contact;
    }

    public function build()
    {
        $this->to($this->contact['email'])
        ->set_rel_id($this->schedule->id)
        ->set_merge_fields('client_merge_fields', $this->schedule->clientid, $this->contact['id'])
        ->set_merge_fields('schedule_merge_fields', $this->schedule->id);
    }
}
