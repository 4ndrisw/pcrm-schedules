<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<table class="table dt-table table-schedules" data-order-col="1" data-order-type="desc">
    <thead>
        <tr>
            <th><?php echo _l('schedule_number'); ?> #</th>
            <th><?php echo _l('schedule_list_project'); ?></th>
            <th><?php echo _l('schedule_list_date'); ?></th>
            <th><?php echo _l('schedule_list_status'); ?></th>

        </tr>
    </thead>
    <tbody>
        <?php foreach($schedules as $schedule){ ?>
            <tr>
                <td><?php echo '<a href="' . admin_url("schedules/schedule/" . $schedule["id"]) . '">' . format_schedule_number($schedule["id"]) . '</a>'; ?></td>
                <td><?php echo $schedule['name']; ?></td>
                <td><?php echo _d($schedule['date']); ?></td>
                <td><?php echo format_schedule_status($schedule['status']); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
