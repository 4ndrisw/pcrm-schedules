<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: schedules
Description: Default module for defining schedules
Version: 1.0.1
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

hooks()->add_action('after_schedule_updated', 'schedule_create_assigned_qrcode');

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
    $widgets[] = [
        'path'      => 'schedules/widgets/project_not_scheduled',
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
        $output = '<a href="' . admin_url('schedules/schedule/' . $data['result']['id']) . '">' . format_schedule_number($data['result']['id']) . '</a>';
    }

    return $output;
}

function schedules_global_search_result_query($result, $q, $limit)
{
    $CI = &get_instance();
    if (has_permission('schedules', '', 'view')) {

        // schedules
        $CI->db->select()
           ->from(db_prefix() . 'schedules')
           ->like(db_prefix() . 'schedules.formatted_number', $q)->limit($limit);
        
        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'schedules',
                'search_heading' => _l('schedules'),
            ];
        
        if(isset($result[0]['result'][0]['id'])){
            return $result;
        }

        // schedules
        $CI->db->select()->from(db_prefix() . 'schedules')->like(db_prefix() . 'clients.company', $q)->or_like(db_prefix() . 'schedules.formatted_number', $q)->limit($limit);
        $CI->db->join(db_prefix() . 'clients',db_prefix() . 'schedules.clientid='.db_prefix() .'clients.userid', 'left');
        $CI->db->order_by(db_prefix() . 'clients.company', 'ASC');

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
* Register deactivation module hook
*/
register_deactivation_hook(SCHEDULES_MODULE_NAME, 'schedules_module_deactivation_hook');

function schedules_module_deactivation_hook()
{

     log_activity( 'Hello, world! . schedules_module_deactivation_hook ' );
}

//hooks()->add_action('deactivate_' . $module . '_module', $function);

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
            'position'   => 57,
            ]);

    if (has_permission('schedules', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('schedules', [
                'slug'     => 'schedules-tracking',
                'name'     => _l('schedules'),
                'icon'     => 'fa fa-calendar',
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

/**
 * [perfex_dark_theme_settings_tab net menu item in setup->settings]
 * @return void
 */
function schedules_settings_tab()
{
    $CI = &get_instance();
    $CI->app_tabs->add_settings_tab('schedules', [
        'name'     => _l('settings_group_schedules'),
        //'view'     => module_views_path(SCHEDULES_MODULE_NAME, 'admin/settings/includes/schedules'),
        'view'     => 'schedules/schedules_settings',
        'position' => 51,
    ]);
}

$CI = &get_instance();
$CI->load->helper(SCHEDULES_MODULE_NAME . '/schedules');

if(($CI->uri->segment(0)=='admin' && $CI->uri->segment(1)=='schedules') || $CI->uri->segment(1)=='schedules'){
    $CI->app_css->add(SCHEDULES_MODULE_NAME.'-css', base_url('modules/'.SCHEDULES_MODULE_NAME.'/assets/css/'.SCHEDULES_MODULE_NAME.'.css'));
    $CI->app_scripts->add(SCHEDULES_MODULE_NAME.'-js', base_url('modules/'.SCHEDULES_MODULE_NAME.'/assets/js/'.SCHEDULES_MODULE_NAME.'.js'));
}

