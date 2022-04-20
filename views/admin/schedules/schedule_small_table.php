<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="panel_s">
    <div class="panel-body">
     <?php if(has_permission('schedules','','create')){ ?>
     <div class="_buttons">
        <a href="<?php echo admin_url('schedules/create'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_schedule'); ?></a>
     </div>
     <?php } ?>
     <?php if(has_permission('schedules','','create')){ ?>
     <div class="_buttons">
        <a href="<?php echo admin_url('schedules'); ?>" class="btn btn-primary pull-right display-block"><?php echo _l('schedule'); ?></a>
     </div>
     <?php } ?>
     <div class="clearfix"></div>
     <hr class="hr-panel-heading" />
     <div class="table-responsive">
        <?php render_datatable(array(
            _l('schedule_subject'),
            _l('schedule_company'),
            _l('schedule_start_date'),
            //_l('schedule_end_date'),
            ),'schedules'); ?>
     </div>
    </div>
</div>
