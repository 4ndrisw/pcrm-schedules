<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'schedules')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "schedules` (
      `id` int(11) NOT NULL,
      `staff_id` int(11) NOT NULL DEFAULT 0,
      `sent` tinyint(1) NOT NULL DEFAULT 0,
      `datesend` datetime DEFAULT NULL,
      `clientid` int(11) NOT NULL DEFAULT 0,
      `deleted_customer_name` varchar(100) DEFAULT NULL,
      `project_id` int(11) NOT NULL DEFAULT 0,
      `number` int(11) NOT NULL DEFAULT 0,
      `prefix` varchar(50) DEFAULT NULL,
      `number_format` int(11) NOT NULL DEFAULT 0,
      `hash` varchar(32) DEFAULT NULL,
      `datecreated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
      `date` date NOT NULL DEFAULT '0000-00-00',
      `expirydate` date DEFAULT NULL,
      `addedfrom` int(11) NOT NULL DEFAULT 0,
      `status` int(11) NOT NULL DEFAULT 1,
      `clientnote` text DEFAULT NULL,
      `adminnote` text DEFAULT NULL,
      `invoiceid` int(11) DEFAULT NULL,
      `invoiced_date` datetime DEFAULT NULL,
      `terms` text DEFAULT NULL,
      `reference_no` varchar(100) DEFAULT NULL,
      `assigned` int(11) NOT NULL DEFAULT 0,
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
      `include_shipping` tinyint(1) NOT NULL DEFAULT 0,
      `show_shipping_on_schedule` tinyint(1) NOT NULL DEFAULT 1,
      `show_quantity_as` int(11) NOT NULL DEFAULT 1,
      `pipeline_order` int(11) DEFAULT 1,
      `is_expiry_notified` int(11) NOT NULL DEFAULT 0,
      `signed` tinyint(1) NOT NULL DEFAULT 0,
      `acceptance_firstname` varchar(50) DEFAULT NULL,
      `acceptance_lastname` varchar(50) DEFAULT NULL,
      `acceptance_email` varchar(100) DEFAULT NULL,
      `acceptance_date` datetime DEFAULT NULL,
      `acceptance_ip` varchar(40) DEFAULT NULL,
      `signature` varchar(40) DEFAULT NULL,
      `short_link` varchar(100) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'schedules`
      ADD PRIMARY KEY (`id`),
      ADD KEY `signed` (`signed`),
      ADD KEY `status` (`status`),
      ADD KEY `clientid` (`clientid`),
      ADD KEY `project_id` (`project_id`),
      ADD KEY `date` (`date`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'schedules`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}


if (!$CI->db->table_exists(db_prefix() . 'schedule_members')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "schedule_members` (
      `id` int(11) NOT NULL,
      `schedule_id` int(11) NOT NULL DEFAULT 0,
      `staff_id` int(11) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'schedule_members`
      ADD PRIMARY KEY (`id`),
      ADD KEY `staff_id` (`staff_id`),
      ADD KEY `schedule_id` (`schedule_id`) USING BTREE;');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'schedule_members`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'schedule_activity')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "schedule_activity` (
  `id` int(11) NOT NULL,
  `rel_type` varchar(20) DEFAULT NULL,
  `rel_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `additional_data` text DEFAULT NULL,
  `staffid` varchar(11) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `date` datetime NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'schedule_activity`
        ADD PRIMARY KEY (`id`),
        ADD KEY `date` (`date`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'schedule_activity`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}


if (!$CI->db->table_exists(db_prefix() . 'schedule_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "schedule_items` (
      `id` int(11) NOT NULL,
      `rel_id` int(11) NOT NULL,
      `rel_type` varchar(15) NOT NULL,
      `description` mediumtext NOT NULL,
      `long_description` mediumtext DEFAULT NULL,
      `qty` decimal(15,2) NOT NULL,
      `unit` varchar(40) DEFAULT NULL,
      `item_order` int(11) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'schedule_items`
      ADD PRIMARY KEY (`id`),
      ADD KEY `rel_id` (`rel_id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'schedule_items`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}
