<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_103 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        // Add options for schedules
        add_option('delete_only_on_last_schedule', 1);
        add_option('schedule_prefix', 'EST-');
        add_option('next_schedule_number', 1);
        add_option('schedule_number_decrement_on_delete', 1);
        add_option('schedule_number_format', 1);
        add_option('schedule_year', date('Y'));
        add_option('schedule_auto_convert_to_invoice_on_client_accept', 1);
        add_option('exclude_schedule_from_client_area_with_draft_status', 1);

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblscheduleitems` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `scheduleid` int(11) NOT NULL,
                    `itemid` int(11) NOT NULL,
                    `qty` int(11) NOT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblscheduleactivity` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `scheduleid` int(11) NOT NULL,
                    `description` text NOT NULL,
                    `staffid` varchar(11) DEFAULT NULL,
                    `date` datetime NOT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        // add terms to invoices
        $this->db->query('ALTER TABLE `tblinvoices` ADD `terms` TEXT NULL AFTER `last_recurring_date`;');
        // add translator permission
        $this->db->query("INSERT INTO `tblpermissions` (`permissionid`, `name`, `shortname`) VALUES (NULL, 'Translate', 'isTranslator');");

        // New feature admin client notifications
        $this->db->query('CREATE TABLE IF NOT EXISTS `tbladminclientreminders` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `description` text,
                      `date` date NOT NULL,
                      `isnotified` int(11) NOT NULL DEFAULT "0",
                      `clientid` int(11) NOT NULL,
                      `staff` int(11) NOT NULL,
                      `notify_by_email` int(11) NOT NULL DEFAULT "1",
                      `creator` int(11) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;');

        // Add 3 new email templates
        $this->db->query("INSERT INTO `tblemailtemplates` (`emailtemplateid`, `type`, `slug`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
            (10, 'schedule', 'schedule-send-to-client', 'When sending schedule to client', '{schedule_number} - {companyname}', '<p>Dear {client_firstname}&nbsp;{client_lastname}<br /><br />Find the schedule with number {schedule_number} on attach.<br />This schedule&nbsp;is with status:&nbsp;<strong>{schedule_status}</strong><br /><br />We look forward to doing more business with you.<br />Best Regards</p>\r\n<p>{email_signature}</p>', 'Company', 'company@test.com', 0, 1, 0),
            (11, 'schedule', 'schedule-already-send', 'Estimate Already Send to Client', 'On your command here is the schedule', '<p>On your command here is the schedule you asked for.<br />{schedule_number}<br />{email_signature}</p>', 'Company', 'sales@test.com', 0, 1, 0),
            (12, 'ticket', 'ticket-reply-to-admin', 'Ticket Reply (To admin)', 'New Ticket Reply', '{signature}', 'Company', 'info@test.com', 0, 1, 0);");

    }

}
