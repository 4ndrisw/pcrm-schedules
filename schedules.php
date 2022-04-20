<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Schedules
Description: Default module for defining schedules
Version: 2.3.0
Requires at least: 2.3.*
*/

define('SCHEDULES_MODULE_NAME', 'schedules');
define('SCHEDULE_ATTACHMENTS_FOLDER', 'uploads/schedules/');

hooks()->add_filter('before_schedule_updated', '_format_data_schedule_feature');
hooks()->add_filter('before_schedule_added', '_format_data_schedule_feature');

hooks()->add_action('after_cron_run', 'schedules_notification');
hooks()->add_action('admin_init', 'schedules_module_init_menu_items');
hooks()->add_action('clients_init', 'schedules_clients_area_menu_items');

hooks()->add_action('staff_member_deleted', 'schedules_staff_member_deleted');
hooks()->add_action('admin_init', 'schedules_permissions');

hooks()->add_filter('migration_tables_to_replace_old_links', 'schedules_migration_tables_to_replace_old_links');
hooks()->add_filter('global_search_result_query', 'schedules_global_search_result_query', 10, 3);
hooks()->add_filter('global_search_result_output', 'schedules_global_search_result_output', 10, 2);
hooks()->add_filter('get_dashboard_widgets', 'schedules_add_dashboard_widget');


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
                'slug'     => 'schedules-tracking',
                'name'     => _l('schedules'),
                'icon'     => 'fa fa-map-marker',
                'href'     => admin_url('schedules'),
                'position' => 12,
        ]);
    }
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
 * Get schedule types for the schedules feature
 *
 * @return array
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
            'lang_key'  => 'schedule_type_total_schedules_converted',
            'subtext'   => 'schedule_type_total_schedules_converted_subtext',
            'dashboard' => has_permission('schedules', 'view'),
        ],
    ];

    return hooks()->apply_filters('get_schedule_types', $types);
}

 */
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



/**
 * Remove and format some common used data for the schedule feature eq invoice,schedules etc..
 * @param  array $data $_POST data
 * @return array
 */
function _format_data_schedule_feature($data)
{
    foreach (_get_schedule_feature_unused_names() as $u) {
        if (isset($data['data'][$u])) {
            unset($data['data'][$u]);
        }
    }

    if (isset($data['data']['date'])) {
        $data['data']['date'] = to_sql_date($data['data']['date']);
    }

    if (isset($data['data']['open_till'])) {
        $data['data']['open_till'] = to_sql_date($data['data']['open_till']);
    }

    if (isset($data['data']['expirydate'])) {
        $data['data']['expirydate'] = to_sql_date($data['data']['expirydate']);
    }

    if (isset($data['data']['duedate'])) {
        $data['data']['duedate'] = to_sql_date($data['data']['duedate']);
    }

    if (isset($data['data']['clientnote'])) {
        $data['data']['clientnote'] = nl2br_save_html($data['data']['clientnote']);
    }

    if (isset($data['data']['terms'])) {
        $data['data']['terms'] = nl2br_save_html($data['data']['terms']);
    }

    if (isset($data['data']['adminnote'])) {
        $data['data']['adminnote'] = nl2br($data['data']['adminnote']);
    }

    if ((isset($data['data']['adjustment']) && !is_numeric($data['data']['adjustment'])) || !isset($data['data']['adjustment'])) {
        $data['data']['adjustment'] = 0;
    } elseif (isset($data['data']['adjustment']) && is_numeric($data['data']['adjustment'])) {
        $data['data']['adjustment'] = number_format($data['data']['adjustment'], get_decimal_places(), '.', '');
    }

    if (isset($data['data']['discount_total']) && $data['data']['discount_total'] == 0) {
        $data['data']['discount_type'] = '';
    }

    foreach (['country', 'billing_country', 'shipping_country', 'project_id', 'assigned'] as $should_be_zero) {
        if (isset($data['data'][$should_be_zero]) && $data['data'][$should_be_zero] == '') {
            $data['data'][$should_be_zero] = 0;
        }
    }

    return $data;
}

function __format_data_client($data, $id = null)
{
    foreach (__get_client_unused_names() as $u) {
        if (isset($data[$u])) {
            unset($data[$u]);
        }
    }

    if (isset($data['address'])) {
        $data['address'] = trim($data['address']);
        $data['address'] = nl2br($data['address']);
    }

    if (isset($data['billing_street'])) {
        $data['billing_street'] = trim($data['billing_street']);
        $data['billing_street'] = nl2br($data['billing_street']);
    }

    if (isset($data['shipping_street'])) {
        $data['shipping_street'] = trim($data['shipping_street']);
        $data['shipping_street'] = nl2br($data['shipping_street']);
    }

    return $data;
}
/**
 * Unsed $_POST request names, mostly they are used as helper inputs in the form
 * The top function will check all of them and unset from the $data
 * @return array
 */
function _get_schedule_feature_unused_names()
{
    return [
        'taxname', 'description',
        'currency_symbol', 'price',
        'isedit', 'taxid',
        'long_description', 'unit',
        'rate', 'quantity',
        'item_select', 'tax',
        'billed_tasks', 'billed_expenses',
        'task_select', 'task_id',
        'expense_id', 'repeat_every_custom',
        'repeat_type_custom', 'bill_expenses',
        'save_and_send', 'merge_current_invoice',
        'cancel_merged_invoices', 'invoices_to_merge',
        'tags', 's_prefix', 'save_and_record_payment',
    ];
}

function __get_client_unused_names()
{
    return [
        'fakeusernameremembered', 'fakepasswordremembered',
        'DataTables_Table_0_length', 'DataTables_Table_1_length',
        'onoffswitch', 'passwordr', 'permissions', 'send_set_password_email',
        'donotsendwelcomeemail',
    ];
}


// Add options for schedules
add_option('delete_only_on_last_schedule', 1);
add_option('schedule_prefix', 'EST-');
add_option('next_schedule_number', 1);
add_option('schedule_number_decrement_on_delete', 1);
add_option('schedule_number_format', 1);
add_option('schedule_year', date('Y'));
add_option('schedule_auto_convert_to_invoice_on_client_accept', 1);
add_option('exclude_schedule_from_client_area_with_draft_status', 1);

add_option('predefined_clientnote_schedule', '- Staf diatas untuk melakukan riksa uji pada peralatan tersebut.<br />
- Staf diatas untuk membuat dokumentasi riksa uji sesuai kebutuhan.');

add_option('predefined_terms_schedule', '- Pelaksanaan riksa uji harus mengikuti prosedur yang ditetapkan perusahaan pemilik alat.<br />
- Dilarang membuat dokumentasi tanpa seizin perusahaan pemilik alat.');
/**
* Load the module helper
*/


$CI = &get_instance();
$CI->load->helper(SCHEDULES_MODULE_NAME . '/schedules');
//$CI->load->helper('datatables');


//$CI->load->library(SCHEDULES_MODULE_NAME . '/Schedule_send_to_customer_already_sent');

module_libs_path(SCHEDULES_MODULE_NAME, 'libraries');

//$CI->load->library(SCHEDULES_MODULE_NAME . '/Schedule_send_to_customer');
//$CI->load->library(SCHEDULES_MODULE_NAME . '/Schedule_expiration_reminder');
//$CI->load->library(SCHEDULES_MODULE_NAME . '/Schedule_declined_to_staff');
//$CI->load->library(SCHEDULES_MODULE_NAME . '/Schedule_accepted_to_staff');
//$CI->load->library(SCHEDULES_MODULE_NAME . '/Schedule_accepted_to_customer');


