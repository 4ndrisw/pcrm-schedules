<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$schedules = [];
if (is_staff_member()) {
   $this->load->model('schedules/schedules_model');
   $schedules = $this->schedules_model->get_all_schedules();
}
?>
<div class="widget<?php if(count($schedules) == 0 || !is_staff_member()){echo ' hide';} ?>" id="widget-<?php echo create_widget_id('schedules'); ?>">
   <?php if(is_staff_member()){ ?>
      <div class="row">
         <div class="col-md-12">
            <div class="panel_s">
               <div class="panel-body padding-10">
                  <div class="widget-dragger"></div>
                  <p class="padding-5">
                     <?php echo _l('schedules'); ?>
                  </p>
                  <hr class="hr-panel-heading-dashboard">
                  <?php foreach($schedules as $schedule){
                     ?>
                     <div class="schedule padding-5 no-padding-top">
                        <h4 class="pull-left font-medium no-mtop">
                           <?php echo $schedule['schedule_type_name']; ?>
                           <br />
                           <small><?php echo $schedule['subject']; ?></small>
                        </h4>
                        <h4 class="pull-right bold no-mtop text-success text-right">
                           <?php echo $schedule['achievement']['total']; ?>
                           <br />
                           <small><?php echo _l('schedule_achievement'); ?></small>
                        </h4>
                        <div class="clearfix"></div>
                        <div class="progress no-margin progress-bar-mini">
                           <div class="progress-bar progress-bar-danger no-percent-text not-dynamic" role="progressbar" aria-valuenow="<?php echo $schedule['achievement']['percent']; ?>" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-percent="<?php echo $schedule['achievement']['percent']; ?>">
                           </div>
                        </div>
                        <p class="text-muted pull-left mtop5"><?php echo _l('schedule_progress'); ?></p>
                        <p class="text-muted pull-right mtop5"><?php echo $schedule['achievement']['percent']; ?>%</p>
                     </div>
                  <?php } ?>
               </div>
            </div>
         </div>
      </div>
   <?php } ?>
</div>
