<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-<?php if(!isset($schedule)){echo '8 col-md-offset-2';} else {echo '6';} ?>">
                <div class="panel_s">
                    <div class="panel-body">

                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />


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
                                        ),'schedules'); ?>
                                    </div>
                                </div>
                            </div>


                    </div>
                </div>
            </div>
            <?php if(isset($schedule)){ ?>
                <div class="col-md-6">
                    <div class="panel_s">
                        <div class="panel-body">
                            <?php 
                            echo $schedule->subject .'<br />'; 
                            echo $schedule->start_date .'<br />'; 
                            echo $schedule->schedule_type .'<br />'; 
                            echo '================= br />';
                            ?>
                        </div>
                   </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>


<script>
    $(function(){
        initDataTable('.table-schedules', window.location.href, [3], [3]);
    });
</script>

</body>
</html>