<?php defined('BASEPATH') or exit('No direct script access allowed');
   if ($schedule['status'] == $status) { ?>
<li data-schedule-id="<?php echo $schedule['id']; ?>" class="<?php if($schedule['invoiceid'] != NULL){echo 'not-sortable';} ?>">
   <div class="panel-body">
      <div class="row">
         <div class="col-md-12">
            <h4 class="bold pipeline-heading"><a href="<?php echo admin_url('schedules/list_schedules/'.$schedule['id']); ?>" onclick="schedule_pipeline_open(<?php echo $schedule['id']; ?>); return false;"><?php echo format_schedule_number($schedule['id']); ?></a>
               <?php if(has_permission('schedules','','edit')){ ?>
               <a href="<?php echo admin_url('schedules/schedule/'.$schedule['id']); ?>" target="_blank" class="pull-right"><small><i class="fa fa-pencil-square-o" aria-hidden="true"></i></small></a>
               <?php } ?>
            </h4>
            <span class="inline-block full-width mbot10">
            <a href="<?php echo admin_url('clients/client/'.$schedule['clientid']); ?>" target="_blank">
            <?php echo $schedule['company']; ?>
            </a>
            </span>
         </div>
         <div class="col-md-12">
            <div class="row">
               <div class="col-md-8">
                  <span class="bold">
                  <?php echo _l('schedule_total') . ':' . app_format_money($schedule['total'], $schedule['currency_name']); ?>
                  </span>
                  <br />
                  <?php echo _l('schedule_data_date') . ': ' . _d($schedule['date']); ?>
                  <?php if(is_date($schedule['expirydate']) || !empty($schedule['expirydate'])){
                     echo '<br />';
                     echo _l('schedule_data_expiry_date') . ': ' . _d($schedule['expirydate']);
                     } ?>
               </div>
               <div class="col-md-4 text-right">
                  <small><i class="fa fa-paperclip"></i> <?php echo _l('schedule_notes'); ?>: <?php echo total_rows(db_prefix().'notes', array(
                     'rel_id' => $schedule['id'],
                     'rel_type' => 'schedule',
                     )); ?></small>
               </div>
               <?php $tags = get_tags_in($schedule['id'],'schedule');
                  if(count($tags) > 0){ ?>
               <div class="col-md-12">
                  <div class="mtop5 kanban-tags">
                     <?php echo render_tags($tags); ?>
                  </div>
               </div>
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</li>
<?php } ?>
