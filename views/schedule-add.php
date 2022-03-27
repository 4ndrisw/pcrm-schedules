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
                        <?php echo form_open($this->uri->uri_string()); ?>
                        <?php $attrs = (isset($schedule) ? array() : array('autofocus'=>true)); ?>
                        <?php $value = (isset($schedule) ? $schedule->subject : ''); ?>
                        <?php echo render_input('subject','schedule_subject',$value,'text',$attrs); ?>
                        <div class="form-group select-placeholder">
                            <label for="schedule_type" class="control-label"><?php echo _l('schedule_type'); ?></label>
                            <select name="schedule_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <?php foreach(get_schedule_types() as $type){ ?>
                                <option value="<?php echo $type['key']; ?>" data-subtext="<?php if(isset($type['subtext'])){echo _l($type['subtext']);} ?>" <?php if(isset($schedule) && $schedule->schedule_type == $type['key']){echo 'selected';} ?>><?php echo _l($type['lang_key']); ?></option>
                                <?php } ?>
                            </select>
                        </div>

<!---------------------------------------------->

        <div class="row">
         <div class="col-md-12">
            <div class="f_client_id">
             <div class="form-group select-placeholder">
                <label for="clientid" class="control-label"><?php echo _l('schedule_select_customer'); ?></label>
                <select id="clientid" name="clientid" data-live-search="true" data-width="100%" class="ajax-search<?php if(isset($schedule) && empty($schedule->clientid)){echo ' customer-removed';} ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
               <?php $selected = (isset($schedule) ? $schedule->clientid : '');
                 if($selected == ''){
                   $selected = (isset($customer_id) ? $customer_id: '');
                 }
                 if($selected != ''){
                    $rel_data = get_relation_data('customer',$selected);
                    $rel_val = get_relation_values($rel_data,'customer');
                    echo '<option value="'.$rel_val['id'].'" selected>'.$rel_val['name'].'</option>';
                 } ?>
                </select>
              </div>
            </div>
            <div class="form-group select-placeholder projects-wrapper<?php if((!isset($schedule)) || (isset($schedule) && !customer_has_projects($schedule->clientid))){ echo ' hide';} ?>">
             <label for="project_id"><?php echo _l('project'); ?></label>
             <div id="project_ajax_search_wrapper">
               <select name="project_id" id="project_id" class="projects ajax-search" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                <?php
                  if(isset($schedule) && $schedule->project_id != 0){
                    echo '<option value="'.$schedule->project_id.'" selected>'.get_project_name_by_id($schedule->project_id).'</option>';
                  }
                ?>
              </select>
            </div>
         </div>
        </div>
        
        <div class="row">
         <div class="col-md-12">
           <div class="col-md-12">
              <a href="#" class="edit_shipping_billing_info" data-toggle="modal" data-target="#billing_and_shipping_details"><i class="fa fa-pencil-square-o"></i></a>
              <?php include_once(APPPATH .'views/admin/schedules/billing_and_shipping_template.php'); ?>
           </div>
           <div class="col-md-6">
              <p class="bold"><?php echo _l('invoice_bill_to'); ?></p>
              <address>
                 <span class="billing_street">
                 <?php $billing_street = (isset($schedule) ? $schedule->billing_street : '--'); ?>
                 <?php $billing_street = ($billing_street == '' ? '--' :$billing_street); ?>
                 <?php echo $billing_street; ?></span><br>
                 <span class="billing_city">
                 <?php $billing_city = (isset($schedule) ? $schedule->billing_city : '--'); ?>
                 <?php $billing_city = ($billing_city == '' ? '--' :$billing_city); ?>
                 <?php echo $billing_city; ?></span>,
                 <span class="billing_state">
                 <?php $billing_state = (isset($schedule) ? $schedule->billing_state : '--'); ?>
                 <?php $billing_state = ($billing_state == '' ? '--' :$billing_state); ?>
                 <?php echo $billing_state; ?></span>
                 <br/>
                 <span class="billing_country">
                 <?php $billing_country = (isset($schedule) ? get_country_short_name($schedule->billing_country) : '--'); ?>
                 <?php $billing_country = ($billing_country == '' ? '--' :$billing_country); ?>
                 <?php echo $billing_country; ?></span>,
                 <span class="billing_zip">
                 <?php $billing_zip = (isset($schedule) ? $schedule->billing_zip : '--'); ?>
                 <?php $billing_zip = ($billing_zip == '' ? '--' :$billing_zip); ?>
                 <?php echo $billing_zip; ?></span>
              </address>
           </div>
           <div class="col-md-6">
              <p class="bold"><?php echo _l('ship_to'); ?></p>
              <address>
                 <span class="shipping_street">
                 <?php $shipping_street = (isset($schedule) ? $schedule->shipping_street : '--'); ?>
                 <?php $shipping_street = ($shipping_street == '' ? '--' :$shipping_street); ?>
                 <?php echo $shipping_street; ?></span><br>
                 <span class="shipping_city">
                 <?php $shipping_city = (isset($schedule) ? $schedule->shipping_city : '--'); ?>
                 <?php $shipping_city = ($shipping_city == '' ? '--' :$shipping_city); ?>
                 <?php echo $shipping_city; ?></span>,
                 <span class="shipping_state">
                 <?php $shipping_state = (isset($schedule) ? $schedule->shipping_state : '--'); ?>
                 <?php $shipping_state = ($shipping_state == '' ? '--' :$shipping_state); ?>
                 <?php echo $shipping_state; ?></span>
                 <br/>
                 <span class="shipping_country">
                 <?php $shipping_country = (isset($schedule) ? get_country_short_name($schedule->shipping_country) : '--'); ?>
                 <?php $shipping_country = ($shipping_country == '' ? '--' :$shipping_country); ?>
                 <?php echo $shipping_country; ?></span>,
                 <span class="shipping_zip">
                 <?php $shipping_zip = (isset($schedule) ? $schedule->shipping_zip : '--'); ?>
                 <?php $shipping_zip = ($shipping_zip == '' ? '--' :$shipping_zip); ?>
                 <?php echo $shipping_zip; ?></span>
              </address>
           </div>
        
         </div>
        </div>
        
            <?php
               $next_schedule_number = get_option('next_schedule_number');
               $format = get_option('schedule_number_format');

                if(isset($schedule)){
                  $format = $schedule->number_format;
                }

               $prefix = get_option('schedule_prefix');

               if ($format == 1) {
                 $__number = $next_schedule_number;
                 if(isset($schedule)){
                   $__number = $schedule->number;
                   $prefix = '<span id="prefix">' . $schedule->prefix . '</span>';
                 }
               } else if($format == 2) {
                 if(isset($schedule)){
                   $__number = $schedule->number;
                   $prefix = $schedule->prefix;
                   $prefix = '<span id="prefix">'. $prefix . '</span><span id="prefix_year">' . date('Y',strtotime($schedule->date)).'</span>/';
                 } else {
                   $__number = $next_schedule_number;
                   $prefix = $prefix.'<span id="prefix_year">'.date('Y').'</span>/';
                 }
               } else if($format == 3) {
                  if(isset($schedule)){
                   $yy = date('y',strtotime($schedule->date));
                   $__number = $schedule->number;
                   $prefix = '<span id="prefix">'. $schedule->prefix . '</span>';
                 } else {
                  $yy = date('y');
                  $__number = $next_schedule_number;
                }
               } else if($format == 4) {
                  if(isset($schedule)){
                   $yyyy = date('Y',strtotime($schedule->date));
                   $mm = date('m',strtotime($schedule->date));
                   $__number = $schedule->number;
                   $prefix = '<span id="prefix">'. $schedule->prefix . '</span>';
                 } else {
                  $yyyy = date('Y');
                  $mm = date('m');
                  $__number = $next_schedule_number;
                }
               }

               $_schedule_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
               $isedit = isset($schedule) ? 'true' : 'false';
               $data_original_number = isset($schedule) ? $schedule->number : 'false';
               ?>

            <div></div>   
            <div class="form-group">
               <label for="number"><?php echo _l('schedule_add_edit_number'); ?></label>
               <div class="input-group">
                  <span class="input-group-addon">
                  <?php if(isset($schedule)){ ?>
                  <a href="#" onclick="return false;" data-toggle="popover" data-container='._transaction_form' data-html="true" data-content="<label class='control-label'><?php echo _l('settings_sales_schedule_prefix'); ?></label><div class='input-group'><input name='s_prefix' type='text' class='form-control' value='<?php echo $schedule->prefix; ?>'></div><button type='button' onclick='save_sales_number_settings(this); return false;' data-url='<?php echo admin_url('schedules/update_number_settings/'.$schedule->id); ?>' class='btn btn-info btn-block mtop15'><?php echo _l('submit'); ?></button>"><i class="fa fa-cog"></i></a>
                   <?php }
                    echo $prefix;
                  ?>
                 </span>
                  <input type="text" name="number" class="form-control" value="<?php echo $_schedule_number; ?>" data-isedit="<?php echo $isedit; ?>" data-original-number="<?php echo $data_original_number; ?>">
                  <?php if($format == 3) { ?>
                  <span class="input-group-addon">
                     <span id="prefix_year" class="format-n-yy"><?php echo $yy; ?></span>
                  </span>
                  <?php } else if($format == 4) { ?>
                   <span class="input-group-addon">
                     <span id="prefix_month" class="format-mm-yyyy"><?php echo $mm; ?></span>
                     /
                     <span id="prefix_year" class="format-mm-yyyy"><?php echo $yyyy; ?></span>
                  </span>
                  <?php } ?>
               </div>
            </div>







<!------------------------------->

                          <?php
                           $selected = (isset($schedule) ? $schedule->staff_id : '');
                           echo render_select('staff_id',$members,array('staffid',array('firstname','lastname')),'staff_member',$selected,array('data-none-selected-text'=>_l('all_staff_members'))); ?>
                        <?php $value = (isset($schedule) ? $schedule->achievement : ''); ?>
                        <?php echo render_input('achievement','schedule_achievement',$value,'number'); ?>
                        <?php $value = (isset($schedule) ? _d($schedule->start_date) : _d(date('Y-m-d'))); ?>
                        <?php echo render_date_input('start_date','schedule_start_date',$value); ?>
                        <?php $value = (isset($schedule) ? _d($schedule->end_date) : ''); ?>
                        <?php echo render_date_input('end_date','schedule_end_date',$value); ?>
                        <div class="hide" id="contract_types">
                            <?php $selected = (isset($schedule) ? $schedule->contract_type : ''); ?>
                            <?php echo render_select('contract_type',$contract_types,array('id','name'),'schedule_contract_type',$selected); ?>
                        </div>
                        <?php $value = (isset($schedule) ? $schedule->description : ''); ?>
                        <?php echo render_textarea('description','schedule_description',$value); ?>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="notify_when_achieve" id="notify_when_achieve" <?php if(isset($schedule)){if($schedule->notify_when_achieve == 1){echo 'checked';} } else {echo 'checked';} ?>>
                            <label for="notify_when_achieve"><?php echo _l('schedule_notify_when_achieve'); ?></label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="notify_when_fail" id="notify_when_fail" <?php if(isset($schedule)){if($schedule->notify_when_fail == 1){echo 'checked';} } else {echo 'checked';} ?>>
                            <label for="notify_when_fail"><?php echo _l('schedule_notify_when_fail'); ?></label>
                        </div>
                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
            <?php if(isset($schedule)){ ?>
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                    <h4 class="no-margin"><?php echo _l('schedule_achievement'); ?></h4>
                      <hr class="hr-panel-heading" />
                        <?php
                        $show_acchievement_ribbon = false;
                        $help_text = '';
                        if($schedule->end_date < date('Y-m-d')){
                          $achieve_indicator_class = 'danger';
                          $lang_key = 'schedule_failed';
                          $finished = true;
                          $notify_type = 'failed';

                          if($schedule->notified == 1){
                            $help_text = '<p class="text-muted text-center">'._l('schedule_staff_members_notified_about_failure').'</p>';
                        }

                        $show_acchievement_ribbon = true;
                    } else if($achievement['percent'] == 100){

                      $achieve_indicator_class = 'success';
                      $show_acchievement_ribbon = true;
                      if($schedule->notified == 1){
                        $help_text = '<p class="text-muted text-center">'._l('schedule_staff_members_notified_about_achievement').'</p>';
                    }

                    $notify_type = 'success';
                    $finished = true;
                    $lang_key = 'schedule_achieved';

                } else if($achievement['percent'] >= 80) {
                  $achieve_indicator_class = 'warning';
                  $show_acchievement_ribbon = true;
                  $lang_key = 'schedule_close';
              }
              if($show_acchievement_ribbon == true){
                  echo '<div class="ribbon '.$achieve_indicator_class.'"><span>'._l($lang_key).'</span></div>';
              }

              ?>
              <h3 class="text-center no-mtop"><?php echo _l('schedule_result_heading'); ?>
                  <small><?php echo _l('schedule_total',$achievement['total']); ?></small>
              </h3>
              <?php if($schedule->schedule_type == 1){
                echo '<p class="text-muted text-center no-mbot">' . _l('schedule_income_shown_in_base_currency') . '</p>';
            }
            if((isset($finished) && $schedule->notified == 0) && ($schedule->notify_when_achieve == 1 || $schedule->notify_when_fail == 1)){
                echo '<p class="text-center text-info">'._l('schedule_notify_when_end_date_arrives').'</p>';

                echo '<div class="text-center"><a href="'.admin_url('schedules/notify/'.$schedule->id . '/'.$notify_type).'" class="btn btn-default">'._l('schedule_notify_staff_manually').'</a></div>';
            }
            echo $help_text;
            ?>
            <div class="achievement mtop30" data-toggle="tooltip" title="<?php echo _l('schedule_total',$achievement['total']); ?>">
                <div class="schedule-progress" data-thickness="40" data-reverse="true">
                    <strong class="schedule-percent"></strong>
                </div>
            </div>
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
       appValidateForm($('form'), {
        subject: 'required',
        schedule_type: 'required',
        end_date: 'required',
        start_date: 'required',
        contract_type: {
            required: {
                depends:function(element) {
                    return $('select[name="schedule_type"]').val() == 5 || $('select[name="schedule_type"]').val() == 7;
                }
            }
        }
    });
        <?php if(isset($schedule)){ ?>
            var circle = $('.schedule-progress').circleProgress({
                value: '<?php echo $achievement['progress_bar_percent']; ?>',
                size: 250,
                fill: {
                    gradient: ["#28b8da", "#059DC1"]
                }
            }).on('circle-animation-progress', function(event, progress, stepValue) {
                $(this).find('strong.schedule-percent').html(parseInt(100 * stepValue) + '<i>%</i>');
            });
            <?php } ?>
            var schedule_type = $('select[name="schedule_type"]').val();
            if (schedule_type == 5 || schedule_type == 7) {
                $('#contract_types').removeClass('hide');
            }
            $('select[name="schedule_type"]').on('change', function() {
                var schedule_type = $(this).val();
                if (schedule_type == 5 || schedule_type == 7) {
                    $('#contract_types').removeClass('hide');
                } else {
                    $('#contract_types').addClass('hide');
                    $('#contract_type').selectpicker('val', '');
                }
            });
        });
    </script>
</body>
</html>