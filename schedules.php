<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Schedules
Description: Default module for defining schedules
Version: 1.0.2
Requires at least: 2.3.*
*/

define('SCHEDULES_MODULE_NAME', 'schedules');
define('SCHEDULE_ATTACHMENTS_FOLDER', 'uploads/schedules/');

hooks()->add_filter('before_schedule_updated', '_format_data_schedule_feature');
hooks()->add_filter('before_schedule_added', '_format_data_schedule_feature');

hooks()->add_action('after_cron_run', 'schedules_notification');
hooks()->add_action('admin_init', 'schedules_module_init_menu_items');
hooks()->add_action('admin_init', 'schedules_permissions');
hooks()->add_action('clients_init', 'schedules_clients_area_menu_items');

hooks()->add_action('staff_member_deleted', 'schedules_staff_member_deleted');

hooks()->add_filter('migration_tables_to_replace_old_links', 'schedules_migration_tables_to_replace_old_links');
hooks()->add_filter('global_search_result_query', 'schedules_global_search_result_query', 10, 3);
hooks()->add_filter('global_search_result_output', 'schedules_global_search_result_output', 10, 2);
hooks()->add_filter('get_dashboard_widgets', 'schedules_add_dashboard_widget');
hooks()->add_filter('module_schedules_action_links', 'module_schedules_action_links');


function schedules_add_dashboard_widget($widgets)
{
    $widgets[] = [
        'path'      => 'schedules/widgets/schedule_this_week',
        'container' => 'left-8',
    ];

    return $widgets;
}


function schedules_staff_member_deleted($data)
{
    $CI = &get_instance();
    $CI->db->where('staff_id', $data['id']);
    $CI->db->update(db_prefix() . 'schedules', [
            'staff_id' => $data['transfer_data_to'],
        ]);
}

function schedules_global_search_result_output($output, $data)
{
    if ($data['type'] == 'schedules') {
        $output = '<a href="' . admin_url('schedules/schedule/' . $data['result']['id']) . '">' . format_schedule_number($data['result']['number']) . '</a>';
    }

    return $output;
}

function schedules_global_search_result_query($result, $q, $limit)
{
    $CI = &get_instance();
    if (has_permission('schedules', '', 'view')) {
        // Schedules
        $CI->db->select()->from(db_prefix() . 'schedules')->like(db_prefix() . 'projects.name', $q)->or_like(db_prefix() . 'clients.company', $q)->limit($limit);
        $CI->db->join(db_prefix() . 'projects',db_prefix() . 'schedules.project_id='.db_prefix() .'projects.id', 'left');
        $CI->db->join(db_prefix() . 'clients',db_prefix() . 'schedules.clientid='.db_prefix() .'clients.userid', 'left');
        $CI->db->order_by(db_prefix() . 'projects.name', 'ASC');

        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'schedules',
                'search_heading' => _l('schedules'),
            ];
    }

    return $result;
}

function schedules_migration_tables_to_replace_old_links($tables)
{
    $tables[] = [
                'table' => db_prefix() . 'schedules',
                'field' => 'description',
            ];

    return $tables;
}

function schedules_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('schedules', $capabilities, _l('schedules'));
}

function schedules_notification()
{
    $CI = &get_instance();
    $CI->load->model('schedules/schedules_model');
    $schedules = $CI->schedules_model->get('', true);
    foreach ($schedules as $schedule) {
        $achievement = $CI->schedules_model->calculate_schedule_achievement($schedule['id']);

        if ($achievement['percent'] >= 100) {
            if (date('Y-m-d') >= $schedule['end_date']) {
                if ($schedule['notify_when_achieve'] == 1) {
                    $CI->schedules_model->notify_staff_members($schedule['id'], 'success', $achievement);
                } else {
                    $CI->schedules_model->mark_as_notified($schedule['id']);
                }
            }
        } else {
            // not yet achieved, check for end date
            if (date('Y-m-d') > $schedule['end_date']) {
                if ($schedule['notify_when_fail'] == 1) {
                    $CI->schedules_model->notify_staff_members($schedule['id'], 'failed', $achievement);
                } else {
                    $CI->schedules_model->mark_as_notified($schedule['id']);
                }
            }
        }
    }
}

/**
* Register activation module hook
*/
register_activation_hook(SCHEDULES_MODULE_NAME, 'schedules_module_activation_hook');

function schedules_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(SCHEDULES_MODULE_NAME, [SCHEDULES_MODULE_NAME]);

/**
 * Init schedules module menu items in setup in admin_init hook
 * @return null
 */
function schedules_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app->add_quick_actions_link([
            'name'       => _l('schedule'),
            'url'        => 'schedules',
            'permission' => 'schedules',
            'position'   => 56,
            ]);

    if (has_permission('schedules', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('schedules', [
                'slug'     => 'schedules-tracking',
                'name'     => _l('schedules'),
                'icon'     => 'fa fa-map-marker',
                'href'     => admin_url('schedules'),
                'position' => 12,
        ]);
    }
}

function module_schedules_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=schedules') . '">' . _l('settings') . '</a>';

    return $actions;
}

function schedules_clients_area_menu_items()
{   
    // Show menu item only if client is logged in
    if (is_client_logged_in()) {
        add_theme_menu_item('schedules', [
                    'name'     => _l('schedules'),
                    'href'     => site_url('schedules/list'),
                    'position' => 15,
        ]);
    }
}


$CI = &get_instance();
$CI->load->helper(SCHEDULES_MODULE_NAME . '/schedules');
