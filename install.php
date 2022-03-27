<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'schedules')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "schedules` (
      `id` int(11) NOT NULL,
      `subject` varchar(191) NOT NULL,
      `description` text NOT NULL,
      `start_date` date NOT NULL,
      `end_date` date NOT NULL,
      `schedule_type` int(11) NOT NULL,
      `contract_type` int(11) NOT NULL DEFAULT '0',
      `achievement` int(11) NOT NULL,
      `notify_when_fail` tinyint(1) NOT NULL DEFAULT '1',
      `notify_when_achieve` tinyint(1) NOT NULL DEFAULT '1',
      `notified` int(11) NOT NULL DEFAULT '0',
      `staff_id` int(11) NOT NULL DEFAULT '0'
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'schedules`
      ADD PRIMARY KEY (`id`),
      ADD UNIQUE KEY `subject` (`subject`),
      ADD KEY `staff_id` (`staff_id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}


add_option('delete_only_on_last_schedule', 1);
add_option('schedule_prefix',  'EST-', 1);
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
add_option('schedule_number_format', 1);
