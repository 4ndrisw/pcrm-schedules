<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
/*
Module Name: Schedules
Description: Default module for defining schedules
Version: 2.0.2
Requires at least: 2.3.*
*/

define('SCHEDULES_MODULE_NAME', 'schedules');

hooks()->add_action('after_cron_run', 'schedules_notification');
hooks()->add_action('admin_init', 'schedules_module_init_menu_items');
hooks()->add_action('staff_member_deleted', 'schedules_staff_member_deleted');
hooks()->add_action('admin_init', 'schedules_permissions');

hooks()->add_filter('migration_tables_to_replace_old_links', 'schedules_migration_tables_to_replace_old_links');
hooks()->add_filter('global_search_result_query', 'schedules_global_search_result_query', 10, 3);
hooks()->add_filter('global_search_result_output', 'schedules_global_search_result_output', 10, 2);
hooks()->add_filter('get_dashboard_widgets', 'schedules_add_dashboard_widget');
hooks()->add_action('admin_init', 'schedules_add_settings_tab');


//hooks()->add_filter('calendar_data', 'schedules_register_show_schedules_on_calendar', 10, 2);

/*
 * Loads the module function helper
 */
$CI->load->helper(SCHEDULES_MODULE_NAME . '/schedules');


function schedules_add_dashboard_widget($widgets)
{
    $widgets[] = [
        'path'      => 'schedules/widget',
        'container' => 'right-4',
    ];

    return $widgets;
}

/**
 * Register schedules on staff and clients calendar.
 *
 * @param $data
 * @param $config
 *
 * @return mixed
 */
/*
function schedules_register_show_schedules_on_calendar($data, $config)
{
    $CI = &get_instance();
    $CI->load->model('schedules/schedules_model');

    $data = $CI->sch->getCalendarData($config['start'], $config['end'], $data);
    return $data;
}
*/

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
        $output = '<a href="' . admin_url('schedules/schedule/' . $data['result']['id']) . '">' . $data['result']['subject'] . '</a>';
    }

    return $output;
}

function schedules_global_search_result_query($result, $q, $limit)
{
    $CI = &get_instance();
    if (has_permission('schedules', '', 'view')) {
        // Schedules
        $CI->db->select()->from(db_prefix() . 'schedules')->like('description', $q)->or_like('subject', $q)->limit($limit);

        $CI->db->order_by('subject', 'ASC');

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
            'url'        => 'schedules/schedule',
            'permission' => 'schedules',
            'position'   => 56,
            ]);

    if (has_permission('schedules', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('schedules', [
            'slug'     => 'schedules',
            'name'     => _l('schedules'),
            'position' => 5,
            'icon'     => 'fa fa-calendar',
            'href'     => admin_url('schedules')
        ]);
    }
}


/**
 * Get schedule types for the schedules feature
 *
 * @return array
 */
function get_schedule_types()
{
    $types = [
        [
            'key'       => 1,
            'lang_key'  => 'schedule_type_total_income',
            'subtext'   => 'schedule_type_income_subtext',
            'dashboard' => has_permission('invoices', 'view'),
        ],
        [
            'key'       => 8,
            'lang_key'  => 'schedule_type_invoiced_amount',
            'subtext'   => '',
            'dashboard' => has_permission('invoices', 'view'),
        ],
        [
            'key'       => 2,
            'lang_key'  => 'schedule_type_convert_leads',
            'dashboard' => is_staff_member(),
        ],
        [
            'key'       => 3,
            'lang_key'  => 'schedule_type_increase_customers_without_leads_conversions',
            'subtext'   => 'schedule_type_increase_customers_without_leads_conversions_subtext',
            'dashboard' => has_permission('customers', 'view'),
        ],
        [
            'key'        => 4,
            'lang_key'   => 'schedule_type_increase_customers_with_leads_conversions',
            'subtext'    => 'schedule_type_increase_customers_with_leads_conversions_subtext',
             'dashboard' => has_permission('customers', 'view'),

        ],
        [
            'key'       => 5,
            'lang_key'  => 'schedule_type_make_contracts_by_type_calc_database',
            'subtext'   => 'schedule_type_make_contracts_by_type_calc_database_subtext',
            'dashboard' => has_permission('contracts', 'view'),
        ],
        [
            'key'       => 7,
            'lang_key'  => 'schedule_type_make_contracts_by_type_calc_date',
            'subtext'   => 'schedule_type_make_contracts_by_type_calc_date_subtext',
            'dashboard' => has_permission('contracts', 'view'),
        ],
        [
            'key'       => 6,
            'lang_key'  => 'schedule_type_total_estimates_converted',
            'subtext'   => 'schedule_type_total_estimates_converted_subtext',
            'dashboard' => has_permission('estimates', 'view'),
        ],
    ];

    return hooks()->apply_filters('get_schedule_types', $types);
}

/**
 * Get schedule type by given key
 *
 * @param  int $key
 *
 * @return array
 */
function get_schedule_type($key)
{
    foreach (get_schedule_types() as $type) {
        if ($type['key'] == $key) {
            return $type;
        }
    }
}

/**
 * Translate schedule type based on passed key
 *
 * @param  mixed $key
 *
 * @return string
 */
function format_schedule_type($key)
{
    foreach (get_schedule_types() as $type) {
        if ($type['key'] == $key) {
            return _l($type['lang_key']);
        }
    }

    return $type;
}


/*
 * Check if can have permissions then apply new tab in settings
 */
//if (staff_can('view', 'settings')) {
//    hooks()->add_action('admin_init', 'schedules_add_settings_tab');
//}

function schedules_add_settings_tab()
{
    $CI = &get_instance();
    $CI->app_tabs->add_settings_tab('schedules-settings', [
        'name'     => _l('schedules'),
        'view'     => 'schedules/settings',
        'position' => 36,
    ]);
}


/**
 * [perfex_dark_theme_settings_tab net menu item in setup->settings]
 * @return void
 */
function perfex_dark_theme_settings_tab()
{
    $CI = &get_instance();
    $CI->app_tabs->add_settings_tab('perfex-theme-dark-settings', [
        'name'     => '' . _l('perfex_dark_theme_settings_first') . '',
        'view'     => 'perfex_dark_theme/perfex_dark_theme_settings',
        'position' => 50,
    ]);
}