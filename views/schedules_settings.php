<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('schedules_settings'); ?>
<div class="horizontal-scrollable-tabs mbot15">
   <div role="tabpanel" class="tab-pane" id="schedules">
      <div class="form-group">
         <label class="control-label" for="schedule_prefix"><?php echo _l('schedule_prefix'); ?></label>
         <input type="text" name="settings[schedule_prefix]" class="form-control" value="<?php echo get_option('schedule_prefix'); ?>">
      </div>
      <hr />
      <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('next_schedule_number_tooltip'); ?>"></i>
      <?php echo render_input('settings[next_schedule_number]','next_schedule_number',get_option('next_schedule_number'), 'number', ['min'=>1]); ?>
      <hr />
      <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('due_after_help'); ?>"></i>
      <?php echo render_input('settings[schedule_qrcode_size]', 'schedule_qrcode_size', get_option('schedule_qrcode_size')); ?>
      <hr />
      <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('due_after_help'); ?>"></i>
      <?php echo render_input('settings[schedule_due_after]','schedule_due_after',get_option('schedule_due_after')); ?>
      <hr />
      <i class="fa fa-question-circle pull-left" data-toggle="tooltip" data-title="<?php echo _l('schedule_number_of_date_tooltip'); ?>"></i>
      <?php echo render_input('settings[schedule_number_of_date]','schedule_number_of_date',get_option('schedule_number_of_date'), 'number', ['min'=>0]); ?>
      <hr />
      <?php render_yes_no_option('schedule_send_telegram_message','schedule_send_telegram_message'); ?>
      <hr />
      <?php render_yes_no_option('delete_only_on_last_schedule','delete_only_on_last_schedule'); ?>
      <hr />
      <?php render_yes_no_option('schedule_number_decrement_on_delete','decrement_schedule_number_on_delete','decrement_schedule_number_on_delete_tooltip'); ?>
      <hr />
      <?php echo render_yes_no_option('allow_staff_view_schedules_assigned','allow_staff_view_schedules_assigned'); ?>
      <hr />
      <?php render_yes_no_option('view_schedule_only_logged_in','require_client_logged_in_to_view_schedule'); ?>
      <hr />
      <?php render_yes_no_option('show_assigned_on_schedules','show_assigned_on_schedules'); ?>
      <hr />
      <?php render_yes_no_option('show_project_on_schedule','show_project_on_schedule'); ?>
      <hr />

      <?php
      $staff = $this->staff_model->get('', ['active' => 1]);
      $selected = get_option('default_schedule_assigned');
      foreach($staff as $member){
       
         if($selected == $member['staffid']) {
           $selected = $member['staffid'];
         
       }
      }
      echo render_select('settings[default_schedule_assigned]',$staff,array('staffid',array('firstname','lastname')),'default_schedule_assigned_string',$selected);
      ?>
      <hr />
      <?php render_yes_no_option('exclude_schedule_from_client_area_with_draft_status','exclude_schedule_from_client_area_with_draft_status'); ?>
      <hr />   
      <?php render_yes_no_option('schedule_accept_identity_confirmation','schedule_accept_identity_confirmation'); ?>
      <hr />
      <?php echo render_input('settings[schedule_year]','schedule_year',get_option('schedule_year'), 'number', ['min'=>2020]); ?>
      <hr />
      
      <div class="form-group">
         <label for="schedule_number_format" class="control-label clearfix"><?php echo _l('schedule_number_format'); ?></label>
         <div class="radio radio-primary radio-inline">
            <input type="radio" name="settings[schedule_number_format]" value="1" id="e_number_based" <?php if(get_option('schedule_number_format') == '1'){echo 'checked';} ?>>
            <label for="e_number_based"><?php echo _l('schedule_number_format_number_based'); ?></label>
         </div>
         <div class="radio radio-primary radio-inline">
            <input type="radio" name="settings[schedule_number_format]" value="2" id="e_year_based" <?php if(get_option('schedule_number_format') == '2'){echo 'checked';} ?>>
            <label for="e_year_based"><?php echo _l('schedule_number_format_year_based'); ?> (YYYY.000001)</label>
         </div>
         <div class="radio radio-primary radio-inline">
            <input type="radio" name="settings[schedule_number_format]" value="3" id="e_short_year_based" <?php if(get_option('schedule_number_format') == '3'){echo 'checked';} ?>>
            <label for="e_short_year_based">000001-YY</label>
         </div>
         <div class="radio radio-primary radio-inline">
            <input type="radio" name="settings[schedule_number_format]" value="4" id="e_year_month_based" <?php if(get_option('schedule_number_format') == '4'){echo 'checked';} ?>>
            <label for="e_year_month_based">000001.MM.YYYY</label>
         </div>
         <hr />
      </div>
      <div class="row">
         <div class="col-md-12">
            <?php echo render_input('settings[schedules_pipeline_limit]','pipeline_limit_status',get_option('schedules_pipeline_limit')); ?>
         </div>
         <div class="col-md-7">
            <label for="default_proposals_pipeline_sort" class="control-label"><?php echo _l('default_pipeline_sort'); ?></label>
            <select name="settings[default_schedules_pipeline_sort]" id="default_schedules_pipeline_sort" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
               <option value="datecreated" <?php if(get_option('default_schedules_pipeline_sort') == 'datecreated'){echo 'selected'; }?>><?php echo _l('schedules_sort_datecreated'); ?></option>
               <option value="date" <?php if(get_option('default_schedules_pipeline_sort') == 'date'){echo 'selected'; }?>><?php echo _l('schedules_sort_schedule_date'); ?></option>
               <option value="pipeline_order" <?php if(get_option('default_schedules_pipeline_sort') == 'pipeline_order'){echo 'selected'; }?>><?php echo _l('schedules_sort_pipeline'); ?></option>
               <option value="expirydate" <?php if(get_option('default_schedules_pipeline_sort') == 'expirydate'){echo 'selected'; }?>><?php echo _l('schedules_sort_expiry_date'); ?></option>
            </select>
         </div>
         <div class="col-md-5">
            <div class="mtop30 text-right">
               <div class="radio radio-inline radio-primary">
                  <input type="radio" id="k_desc_schedule" name="settings[default_schedules_pipeline_sort_type]" value="asc" <?php if(get_option('default_schedules_pipeline_sort_type') == 'asc'){echo 'checked';} ?>>
                  <label for="k_desc_schedule"><?php echo _l('order_ascending'); ?></label>
               </div>
               <div class="radio radio-inline radio-primary">
                  <input type="radio" id="k_asc_schedule" name="settings[default_schedules_pipeline_sort_type]" value="desc" <?php if(get_option('default_schedules_pipeline_sort_type') == 'desc'){echo 'checked';} ?>>
                  <label for="k_asc_schedule"><?php echo _l('order_descending'); ?></label>
               </div>
            </div>
         </div>
         <div class="clearfix"></div>
      </div>
      <hr  />
      <?php echo render_textarea('settings[predefined_clientnote_schedule]','predefined_clientnote',get_option('predefined_clientnote_schedule'),array('rows'=>6)); ?>
      <?php echo render_textarea('settings[predefined_terms_schedule]','predefined_terms',get_option('predefined_terms_schedule'),array('rows'=>6)); ?>
   </div>
 <?php hooks()->do_action('after_schedules_tabs_content'); ?>
</div>
