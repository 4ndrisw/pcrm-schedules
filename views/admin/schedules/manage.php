<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                     <?php if(has_permission('schedules','','create')){ ?> 

                     <div class="_buttons">
                        <a href="<?php echo admin_url('schedules/create'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_schedule'); ?></a>
                    </div>
                    <div class="clearfix"></div>
                    <hr class="hr-panel-heading" />
                    <?php } ?>
                    <?php render_datatable(array(
                        _l('schedule_subject'),
                        _l('schedule_company'),
                        _l('schedule_project'),
                        //_l('schedule_projects_name'),
                        _l('schedule_status'),
                        _l('schedule_start_date'),
                        _l('schedule_acceptance_firstname'),
                        _l('schedule_acceptance_date'),
                        //_l('schedule_end_date'),

                        ),'schedules'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script type="text/javascript" id="schedule-js" src="<?= base_url() ?>modules/schedules/assets/js/schedules.js?"></script>
<script>
    $(function(){
        initDataTable('.table-schedules', window.location.href, [3], [3]);
    });
</script>
</body>
</html>