<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_202 extends App_module_migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {

        // Add RTL option
        add_option('delete_only_on_last_schedule', 1);
        add_option('schedule_prefix',  'SCH-', 1);
        add_option('next_schedule_number', 1);
        add_option('schedule_number_decrement_on_delete', 1);
        add_option('schedule_number_format', 1);
        add_option('schedule_auto_convert_to_invoice_on_client_accept', 1);
        add_option('exclude_schedule_from_client_area_with_draft_status', 1);
        add_option('show_sale_agent_on_schedules', 1);
        add_option('predefined_terms_schedule', 1);
        add_option('predefined_clientnote_schedule', 1);
        add_option('show_schedules_on_calendar', 1);
        add_option('send_schedule_expiry_reminder_before', '4', 1);
        add_option('view_schedule_only_logged_in', 1);
        add_option('calendar_schedule_color', '#fd6100', 1);
        add_option('show_schedule_reminders_on_calendar', 1);
        add_option('default_schedules_pipeline_sort', 'pipeline_order', 1);
        add_option('default_schedules_pipeline_sort_type', 'asc', 1);
        add_option('pdf_format_schedule', 'A4-PORTRAIT', 1);
        add_option('schedules_pipeline_limit', '50', 1);
        add_option('schedule_due_after', '7', 1);
        add_option('show_pdf_signature_schedule', '1', 0);
        add_option('schedule_accept_identity_confirmation', '1', 0);
        add_option('schedules_auto_operations_hour', '21', 1);
        add_option('gdpr_on_forgotten_remove_schedules', '0', 1);
        add_option('allow_staff_view_schedules_assigned', '1', 1);


        // Schedules - new feature
        /*
        $this->db->query("CREATE TABLE IF NOT EXISTS `tblschedules` (
              `id` int(11) NOT NULL,
              `sent` tinyint(1) NOT NULL DEFAULT 0,
              `datesend` datetime DEFAULT NULL,
              `clientid` int(11) NOT NULL,
              `deleted_customer_name` varchar(100) DEFAULT NULL,
              `project_id` int(11) NOT NULL DEFAULT 0,
              `number` int(11) NOT NULL,
              `prefix` varchar(50) DEFAULT NULL,
              `number_format` int(11) NOT NULL DEFAULT 0,
              `hash` varchar(32) DEFAULT NULL,
              `datecreated` datetime NOT NULL,
              `date` date NOT NULL,
              `expirydate` date DEFAULT NULL,
              `currency` int(11) NOT NULL,
              `subtotal` decimal(15,2) NOT NULL,
              `total_tax` decimal(15,2) NOT NULL DEFAULT 0.00,
              `total` decimal(15,2) NOT NULL,
              `adjustment` decimal(15,2) DEFAULT NULL,
              `addedfrom` int(11) NOT NULL,
              `status` int(11) NOT NULL DEFAULT 1,
              `clientnote` text DEFAULT NULL,
              `adminnote` text DEFAULT NULL,
              `discount_percent` decimal(15,2) DEFAULT 0.00,
              `discount_total` decimal(15,2) DEFAULT 0.00,
              `discount_type` varchar(30) DEFAULT NULL,
              `invoiceid` int(11) DEFAULT NULL,
              `invoiced_date` datetime DEFAULT NULL,
              `terms` text DEFAULT NULL,
              `reference_no` varchar(100) DEFAULT NULL,
              `sale_agent` int(11) NOT NULL DEFAULT 0,
              `billing_street` varchar(200) DEFAULT NULL,
              `billing_city` varchar(100) DEFAULT NULL,
              `billing_state` varchar(100) DEFAULT NULL,
              `billing_zip` varchar(100) DEFAULT NULL,
              `billing_country` int(11) DEFAULT NULL,
              `shipping_street` varchar(200) DEFAULT NULL,
              `shipping_city` varchar(100) DEFAULT NULL,
              `shipping_state` varchar(100) DEFAULT NULL,
              `shipping_zip` varchar(100) DEFAULT NULL,
              `shipping_country` int(11) DEFAULT NULL,
              `include_shipping` tinyint(1) NOT NULL,
              `show_shipping_on_schedule` tinyint(1) NOT NULL DEFAULT 1,
              `show_quantity_as` int(11) NOT NULL DEFAULT 1,
              `pipeline_order` int(11) DEFAULT 1,
              `is_expiry_notified` int(11) NOT NULL DEFAULT 0,
              `acceptance_firstname` varchar(50) DEFAULT NULL,
              `acceptance_lastname` varchar(50) DEFAULT NULL,
              `acceptance_email` varchar(100) DEFAULT NULL,
              `acceptance_date` datetime DEFAULT NULL,
              `acceptance_ip` varchar(40) DEFAULT NULL,
              `signature` varchar(40) DEFAULT NULL,
              `short_link` varchar(100) DEFAULT NULL
                    PRIMARY KEY (`id`),
                    ADD KEY `clientid` (`clientid`),
                    ADD KEY `project_id` (`project_id`),
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        */
        $this->db->query("CREATE TABLE IF NOT EXISTS `tblscheduleitems` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `scheduleid` int(11) NOT NULL,
                    `itemid` int(11) NOT NULL,
                    `qty` int(11) NOT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        // Add schedule email templates
        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
            ('schedule', 'schedule-send-to-client', 'english', 'Send Schedule to Customer', 'Schedule # {schedule_number} created', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">Please find the attached schedule <strong># {schedule_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\"><strong>Schedule status:</strong> {schedule_status}</span><br /><br /><span style=\"font-size: 12pt;\">You can view the schedule on the following link: <a href=\"{schedule_link}\">{schedule_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">We look forward to your communication.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}<br /></span>', '{companyname} | CRM', '', 0, 1, 0),
            ('schedule', 'schedule-already-send', 'english', 'Schedule Already Sent to Customer', 'Schedule # {schedule_number} ', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank you for your schedule request.</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the schedule on the following link: <a href=\"{schedule_link}\">{schedule_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
            ('schedule', 'schedule-declined-to-staff', 'english', 'Schedule Declined (Sent to Staff)', 'Customer Declined Schedule', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) declined schedule with number <strong># {schedule_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the schedule on the following link: <a href=\"{schedule_link}\">{schedule_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
            ('schedule', 'schedule-accepted-to-staff', 'english', 'Schedule Accepted (Sent to Staff)', 'Customer Accepted Schedule', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted schedule with number <strong># {schedule_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the schedule on the following link: <a href=\"{schedule_link}\">{schedule_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
            ('schedule', 'schedule-thank-you-to-customer', 'english', 'Thank You Email (Sent to Customer After Accept)', 'Thank for you accepting schedule', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank for for accepting the schedule.</span><br /> <br /><span style=\"font-size: 12pt;\">We look forward to doing business with you.</span><br /> <br /><span style=\"font-size: 12pt;\">We will contact you as soon as possible.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
            ('schedule', 'schedule-expiry-reminder', 'english', 'Schedule Expiration Reminder', 'Schedule Expiration Reminder', '<p><span style=\"font-size: 12pt;\">Hello {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">The schedule with <strong># {schedule_number}</strong> will expire on <strong>{schedule_expirydate}</strong></span><br /><br /><span style=\"font-size: 12pt;\">You can view the schedule on the following link: <a href=\"{schedule_link}\">{schedule_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span></p>', '{companyname} | CRM', '', 0, 1, 0),
            ");

    }

}
