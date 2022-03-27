<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Get Schedule short_url
 * @since  Version 2.7.3
 * @param  object $schedule
 * @return string Url
 */
function get_schedule_shortlink($schedule)
{
    $long_url = site_url("schedule/{$schedule->id}/{$schedule->hash}");
    if (!get_option('bitly_access_token')) {
        return $long_url;
    }

    // Check if schedule has short link, if yes return short link
    if (!empty($schedule->short_link)) {
        return $schedule->short_link;
    }

    // Create short link and return the newly created short link
    $short_link = app_generate_short_link([
        'long_url'  => $long_url,
        'title'     => format_schedule_number($schedule->id)
    ]);

    if ($short_link) {
        $CI = &get_instance();
        $CI->db->where('id', $schedule->id);
        $CI->db->update(db_prefix() . 'schedules', [
            'short_link' => $short_link
        ]);
        return $short_link;
    }
    return $long_url;
}

/**
 * Check schedule restrictions - hash, clientid
 * @param  mixed $id   schedule id
 * @param  string $hash schedule hash
 */
function check_schedule_restrictions($id, $hash)
{
    $CI = &get_instance();
    $CI->load->model('schedules_model');
    if (!$hash || !$id) {
        show_404();
    }
    if (!is_client_logged_in() && !is_staff_logged_in()) {
        if (get_option('view_schedule_only_logged_in') == 1) {
            redirect_after_login_to_current_url();
            redirect(site_url('authentication/login'));
        }
    }
    $schedule = $CI->schedules_model->get($id);
    if (!$schedule || ($schedule->hash != $hash)) {
        show_404();
    }
    // Do one more check
    if (!is_staff_logged_in()) {
        if (get_option('view_schedule_only_logged_in') == 1) {
            if ($schedule->clientid != get_client_user_id()) {
                show_404();
            }
        }
    }
}

/**
 * Check if schedule email template for expiry reminders is enabled
 * @return boolean
 */
function is_schedules_email_expiry_reminder_enabled()
{
    return total_rows(db_prefix() . 'emailtemplates', ['slug' => 'schedule-expiry-reminder', 'active' => 1]) > 0;
}

/**
 * Check if there are sources for sending schedule expiry reminders
 * Will be either email or SMS
 * @return boolean
 */
function is_schedules_expiry_reminders_enabled()
{
    return is_schedules_email_expiry_reminder_enabled() || is_sms_trigger_active(SMS_TRIGGER_SCHEDULES_EXP_REMINDER);
}

/**
 * Return RGBa schedule status color for PDF documents
 * @param  mixed $status_id current schedule status
 * @return string
 */
function schedule_status_color_pdf($status_id)
{
    if ($status_id == 1) {
        $statusColor = '119, 119, 119';
    } elseif ($status_id == 2) {
        // Sent
        $statusColor = '3, 169, 244';
    } elseif ($status_id == 3) {
        //Declines
        $statusColor = '252, 45, 66';
    } elseif ($status_id == 4) {
        //Accepted
        $statusColor = '0, 191, 54';
    } else {
        // Expired
        $statusColor = '255, 111, 0';
    }

    return hooks()->apply_filters('schedule_status_pdf_color', $statusColor, $status_id);
}

/**
 * Format schedule status
 * @param  integer  $status
 * @param  string  $classes additional classes
 * @param  boolean $label   To include in html label or not
 * @return mixed
 */
function format_schedule_status($status, $classes = '', $label = true)
{
    $id          = $status;
    $label_class = schedule_status_color_class($status);
    $status      = schedule_status_by_id($status);
    if ($label == true) {
        return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status schedule-status-' . $id . ' schedule-status-' . $label_class . '">' . $status . '</span>';
    }

    return $status;
}

/**
 * Return schedule status translated by passed status id
 * @param  mixed $id schedule status id
 * @return string
 */
function schedule_status_by_id($id)
{
    $status = '';
    if ($id == 1) {
        $status = _l('schedule_status_draft');
    } elseif ($id == 2) {
        $status = _l('schedule_status_sent');
    } elseif ($id == 3) {
        $status = _l('schedule_status_declined');
    } elseif ($id == 4) {
        $status = _l('schedule_status_accepted');
    } elseif ($id == 5) {
        // status 5
        $status = _l('schedule_status_expired');
    } else {
        if (!is_numeric($id)) {
            if ($id == 'not_sent') {
                $status = _l('not_sent_indicator');
            }
        }
    }

    return hooks()->apply_filters('schedule_status_label', $status, $id);
}

/**
 * Return schedule status color class based on twitter bootstrap
 * @param  mixed  $id
 * @param  boolean $replace_default_by_muted
 * @return string
 */
function schedule_status_color_class($id, $replace_default_by_muted = false)
{
    $class = '';
    if ($id == 1) {
        $class = 'default';
        if ($replace_default_by_muted == true) {
            $class = 'muted';
        }
    } elseif ($id == 2) {
        $class = 'info';
    } elseif ($id == 3) {
        $class = 'danger';
    } elseif ($id == 4) {
        $class = 'success';
    } elseif ($id == 5) {
        // status 5
        $class = 'warning';
    } else {
        if (!is_numeric($id)) {
            if ($id == 'not_sent') {
                $class = 'default';
                if ($replace_default_by_muted == true) {
                    $class = 'muted';
                }
            }
        }
    }

    return hooks()->apply_filters('schedule_status_color_class', $class, $id);
}

/**
 * Check if the schedule id is last invoice
 * @param  mixed  $id scheduleid
 * @return boolean
 */
function is_last_schedule($id)
{
    $CI = &get_instance();
    $CI->db->select('id')->from(db_prefix() . 'schedules')->order_by('id', 'desc')->limit(1);
    $query            = $CI->db->get();
    $last_schedule_id = $query->row()->id;
    if ($last_schedule_id == $id) {
        return true;
    }

    return false;
}

/**
 * Format schedule number based on description
 * @param  mixed $id
 * @return string
 */
function format_schedule_number($id)
{
    $CI = &get_instance();
    $CI->db->select('date,number,prefix,number_format')->from(db_prefix() . 'schedules')->where('id', $id);
    $schedule = $CI->db->get()->row();

    if (!$schedule) {
        return '';
    }

    $number = sales_number_format($schedule->number, $schedule->number_format, $schedule->prefix, $schedule->date);

    return hooks()->apply_filters('format_schedule_number', $number, [
        'id'       => $id,
        'schedule' => $schedule,
    ]);
}


/**
 * Function that return schedule item taxes based on passed item id
 * @param  mixed $itemid
 * @return array
 */
function get_schedule_item_taxes($itemid)
{
    $CI = &get_instance();
    $CI->db->where('itemid', $itemid);
    $CI->db->where('rel_type', 'schedule');
    $taxes = $CI->db->get(db_prefix() . 'item_tax')->result_array();
    $i     = 0;
    foreach ($taxes as $tax) {
        $taxes[$i]['taxname'] = $tax['taxname'] . '|' . $tax['taxrate'];
        $i++;
    }

    return $taxes;
}

/**
 * Calculate schedules percent by status
 * @param  mixed $status          schedule status
 * @return array
 */
function get_schedules_percent_by_status($status, $project_id = null)
{
    $has_permission_view = has_permission('schedules', '', 'view');
    $where               = '';

    if (isset($project_id)) {
        $where .= 'project_id=' . get_instance()->db->escape_str($project_id) . ' AND ';
    }
    if (!$has_permission_view) {
        $where .= get_schedules_where_sql_for_staff(get_staff_user_id());
    }

    $where = trim($where);

    if (endsWith($where, ' AND')) {
        $where = substr_replace($where, '', -3);
    }

    $total_schedules = total_rows(db_prefix() . 'schedules', $where);

    $data            = [];
    $total_by_status = 0;

    if (!is_numeric($status)) {
        if ($status == 'not_sent') {
            $total_by_status = total_rows(db_prefix() . 'schedules', 'sent=0 AND status NOT IN(2,3,4)' . ($where != '' ? ' AND (' . $where . ')' : ''));
        }
    } else {
        $whereByStatus = 'status=' . $status;
        if ($where != '') {
            $whereByStatus .= ' AND (' . $where . ')';
        }
        $total_by_status = total_rows(db_prefix() . 'schedules', $whereByStatus);
    }

    $percent                 = ($total_schedules > 0 ? number_format(($total_by_status * 100) / $total_schedules, 2) : 0);
    $data['total_by_status'] = $total_by_status;
    $data['percent']         = $percent;
    $data['total']           = $total_schedules;

    return $data;
}

function get_schedules_where_sql_for_staff($staff_id)
{
    $CI = &get_instance();
    $has_permission_view_own             = has_permission('schedules', '', 'view_own');
    $allow_staff_view_schedules_assigned = get_option('allow_staff_view_schedules_assigned');
    $whereUser                           = '';
    if ($has_permission_view_own) {
        $whereUser = '((' . db_prefix() . 'schedules.addedfrom=' . $CI->db->escape_str($staff_id) . ' AND ' . db_prefix() . 'schedules.addedfrom IN (SELECT staff_id FROM ' . db_prefix() . 'staff_permissions WHERE feature = "schedules" AND capability="view_own"))';
        if ($allow_staff_view_schedules_assigned == 1) {
            $whereUser .= ' OR sale_agent=' . $CI->db->escape_str($staff_id);
        }
        $whereUser .= ')';
    } else {
        $whereUser .= 'sale_agent=' . $CI->db->escape_str($staff_id);
    }

    return $whereUser;
}
/**
 * Check if staff member have assigned schedules / added as sale agent
 * @param  mixed $staff_id staff id to check
 * @return boolean
 */
function staff_has_assigned_schedules($staff_id = '')
{
    $CI       = &get_instance();
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $cache    = $CI->app_object_cache->get('staff-total-assigned-schedules-' . $staff_id);

    if (is_numeric($cache)) {
        $result = $cache;
    } else {
        $result = total_rows(db_prefix() . 'schedules', ['sale_agent' => $staff_id]);
        $CI->app_object_cache->add('staff-total-assigned-schedules-' . $staff_id, $result);
    }

    return $result > 0 ? true : false;
}
/**
 * Check if staff member can view schedule
 * @param  mixed $id schedule id
 * @param  mixed $staff_id
 * @return boolean
 */
function user_can_view_schedule($id, $staff_id = false)
{
    $CI = &get_instance();

    $staff_id = $staff_id ? $staff_id : get_staff_user_id();

    if (has_permission('schedules', $staff_id, 'view')) {
        return true;
    }

    $CI->db->select('id, addedfrom, sale_agent');
    $CI->db->from(db_prefix() . 'schedules');
    $CI->db->where('id', $id);
    $schedule = $CI->db->get()->row();

    if ((has_permission('schedules', $staff_id, 'view_own') && $schedule->addedfrom == $staff_id)
        || ($schedule->sale_agent == $staff_id && get_option('allow_staff_view_schedules_assigned') == '1')
    ) {
        return true;
    }

    return false;
}


/**
 * Get module version.
 *
 * @return string
 */
if (!function_exists('get_schedules_version')) {

    function get_schedules_version()
    {
        get_instance()->db->where('module_name', 'schedules');
        $version = get_instance()->db->get(db_prefix() . 'modules');

        if ($version->num_rows() > 0) {
            return _l('schedules_current_version ') . $version->row('installed_version');
        }
        return 'Unknown';
    }
}