<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
    $CI = &get_instance();
    $CI->load->model('schedules/schedules_model');
    $schedules = $CI->schedules_model->get_schedules_this_week(get_staff_user_id());

?>

<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('schedule_this_week'); ?>">
    <?php if(staff_can('view', 'schedules') || staff_can('view_own', 'schedules')) { ?>
    <div class="panel_s schedules-expiring">
        <div class="panel-body padding-10">
            <p class="padding-5"><?php echo _l('schedule_this_week'); ?></p>
            <hr class="hr-panel-heading-dashboard">
            <?php if (!empty($schedules)) { ?>
                <div class="table-vertical-scroll">
                    <a href="<?php echo admin_url('schedules'); ?>" class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
                    <table id="widget-<?php echo create_widget_id(); ?>" class="table dt-table" data-order-col="3" data-order-type="desc">
                        <thead>
                            <tr>
                                <th><?php echo _l('schedule_number'); ?> #</th>
                                <th class="<?php echo (isset($client) ? 'not_visible' : ''); ?>"><?php echo _l('schedule_list_client'); ?></th>
                                <th><?php echo _l('schedule_list_project'); ?></th>
                                <th><?php echo _l('schedule_list_date'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $schedule) { ?>
                                <tr class="<?= 'schedule_status_' . $schedule['status']?>">
                                    <td>
                                        <?php echo '<a href="' . admin_url("schedules/schedule/" . $schedule["id"]) . '">' . format_schedule_number($schedule["id"]) . '</a>'; ?>
                                    </td>
                                    <td>
                                        <?php echo '<a href="' . admin_url("clients/client/" . $schedule["userid"]) . '">' . $schedule["company"] . '</a>'; ?>
                                    </td>
                                    <td>
                                        <?php echo '<a href="' . admin_url("projects/view/" . $schedule["projects_id"]) . '">' . $schedule['name'] . '</a>'; ?>
                                    </td>
                                    <td>
                                        <?php echo _d($schedule['date']); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="text-center padding-5">
                    <i class="fa fa-check fa-5x" aria-hidden="true"></i>
                    <h4><?php echo _l('no_schedule_this_week',["7"]) ; ?> </h4>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>
