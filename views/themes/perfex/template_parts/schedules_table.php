<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<table class="table dt-table table-schedules" data-order-col="1" data-order-type="desc">
    <thead>
        <tr>
            <th class="th-schedule-number"><?php echo _l('clients_schedule_dt_number'); ?></th>
            <th class="th-schedule-date"><?php echo _l('clients_schedule_dt_date'); ?></th>
            <th class="th-schedule-duedate"><?php echo _l('clients_schedule_dt_duedate'); ?></th>
            <th class="th-schedule-amount"><?php echo _l('clients_schedule_dt_amount'); ?></th>
            <th class="th-schedule-reference-number"><?php echo _l('reference_no'); ?></th>
            <th class="th-schedule-status"><?php echo _l('clients_schedule_dt_status'); ?></th>

        </tr>
    </thead>
    <tbody>
        <?php foreach($schedules as $schedule){ ?>
            <tr>
                <td data-order="<?php echo $schedule['number']; ?>"><a href="<?php echo site_url('schedule/' . $schedule['id'] . '/' . $schedule['hash']); ?>" class="schedule-number"><?php echo format_schedule_number($schedule['id']); ?></a>
                    <?php
                    if($schedule['invoiceid']){
                        echo '<br /><span class="text-success">' . _l('schedule_invoiced') . '</span>';
                    }
                    ?>
                </td>
                <td data-order="<?php echo $schedule['date']; ?>"><?php echo _d($schedule['date']); ?></td>
                <td data-order="<?php echo $schedule['expirydate']; ?>"><?php echo _d($schedule['expirydate']); ?></td>
                <td data-order="<?php echo $schedule['total']; ?>"><?php echo app_format_money($schedule['total'], $schedule['currency_name']);; ?></td>
                <td><?php echo $schedule['reference_no']; ?></td>
                <td><?php echo format_schedule_status($schedule['status'], 'inline-block', true); ?></td>
                
            </tr>
        <?php } ?>
    </tbody>
</table>
