<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s section-heading section-schedules">
    <div class="panel-body">
        <h4 class="no-margin section-text"><?php echo _l('clients_my_schedules'); ?></h4>
    </div>
</div>
<div class="panel_s">
    <div class="panel-body">
        <div class="client-schedules" id="schedules-client-<?php echo $client->userid; ?>" data-name="<?php echo _l('schedule_this_week'); ?>">
            <div class="panel_s schedules-expiring">
                <div class="panel-body padding-10">
                    <div class="schedules-dragger"></div>
                    <p class="padding-5"><?php echo $client->company; ?></p>
                    <hr class="hr-panel-heading-dashboard">
                    <?php if (!empty($schedules)) { ?>
                        <div class="table-vertical-scroll">
                            <table id="schedules-<?php echo $client->userid; ?>" class="table dt-table" data-order-col="2" data-order-type="desc">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('schedule_number'); ?> #</th>
                                        <th><?php echo _l('schedule_list_project'); ?></th>
                                        <th><?php echo _l('schedule_list_date'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($schedules as $schedule) { ?>
                                        <tr>
                                            <td>
                                                <?php echo '<a href="' . site_url("schedules/show/" . $schedule["id"] .'/'. $schedule["hash"]) . '">' . format_schedule_number($schedule["id"]) . '</a>'; ?>
                                            </td>
                                            <td>
                                                <?php echo $schedule['name']; ?>
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
                            <h4><?php echo _l('no_schedules_found') ; ?> </h4>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>









    </div>
</div>
