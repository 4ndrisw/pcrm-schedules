<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Schedule_staff_added_as_member extends App_mail_template
{
    protected $for = 'staff';

    protected $schedule_id;

    protected $client_id;

    protected $project_id;

    protected $staff;

    public $slug = 'staff-added-as-schedule-member';

    public $rel_type = 'schedule';

    public function __construct($staff, $schedule_id, $project_id, $client_id)
    {
        parent::__construct();

        $this->staff      = $staff;
        $this->schedule_id = $schedule_id;
        $this->client_id  = $client_id;
        $this->project_id  = $project_id;
    }

    public function build()
    {
        $this->to($this->staff['email'])
        ->set_rel_id($this->schedule_id)
        ->set_merge_fields('client_merge_fields', $this->client_id)
        ->set_merge_fields('project_merge_fields', $this->project_id)
        ->set_merge_fields('staff_merge_fields', $this->staff['staff_id'])
        ->set_merge_fields('schedules_merge_fields', $this->schedule_id);
    }
}
