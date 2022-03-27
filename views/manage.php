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
                        _l('staff_member'),
                        _l('schedule_achievement'),
                        _l('schedule_start_date'),
                        _l('schedule_end_date'),
                        _l('schedule_type'),
                        _l('schedule_progress'),
                        ),'schedules'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function(){
        initDataTable('.table-schedules', window.location.href, [6], [6]);
        $('.table-schedules').DataTable().on('draw', function() {
            var rows = $('.table-schedules').find('tr');
            $.each(rows, function() {
                var td = $(this).find('td').eq(6);
                var percent = $(td).find('input[name="percent"]').val();
                $(td).find('.schedule-progress').circleProgress({
                    value: percent,
                    size: 45,
                    animation: false,
                    fill: {
                        gradient: ["#28b8da", "#059DC1"]
                    }
                })
            })
        })
    });
</script>
</body>
</html>
