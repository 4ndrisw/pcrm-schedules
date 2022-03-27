<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s accounting-template schedule">
   <div class="panel-body">
      <?php if(isset($schedule)){ ?>
      <?php echo format_schedule_status($schedule->status); ?>
      <hr class="hr-panel-heading" />
      <?php } ?>
      <div class="row">
          <?php if (isset($schedule_request_id) && $schedule_request_id != '') {
              echo form_hidden('schedule_request_id',$schedule_request_id);
          }
          ?>
         <div class="col-md-6">
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
            <div class="row">
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

            <div class="row">
               <div class="col-md-6">
                  <?php $value = (isset($schedule) ? _d($schedule->date) : _d(date('Y-m-d'))); ?>
                  <?php echo render_date_input('date','schedule_add_edit_date',$value); ?>
               </div>
               <div class="col-md-6">
                  <?php
                  $value = '';
                  if(isset($schedule)){
                    $value = _d($schedule->expirydate);
                  } else {
                      if(get_option('schedule_due_after') != 0){
                          $value = _d(date('Y-m-d', strtotime('+' . get_option('schedule_due_after') . ' DAY', strtotime(date('Y-m-d')))));
                      }
                  }
                  echo render_date_input('expirydate','schedule_add_edit_expirydate',$value); ?>
               </div>
            </div>
            <div class="clearfix mbot15"></div>
            <?php $rel_id = (isset($schedule) ? $schedule->id : false); ?>
            <?php
                  if(isset($custom_fields_rel_transfer)) {
                      $rel_id = $custom_fields_rel_transfer;
                  }
             ?>
            <?php echo render_custom_fields('schedule',$rel_id); ?>
         </div>
         <div class="col-md-6">
            <div class="panel_s no-shadow">
               <div class="form-group">
                  <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                  <input type="text" class="tagsinput" id="tags" name="tags" value="<?php echo (isset($schedule) ? prep_tags_input(get_tags_in($schedule->id,'schedule')) : ''); ?>" data-role="tagsinput">
               </div>
               <div class="row">
                  <div class="col-md-6">
                     <?php

                        $currency_attr = array('disabled'=>true,'data-show-subtext'=>true);
                        $currency_attr = apply_filters_deprecated('schedule_currency_disabled', [$currency_attr], '2.3.0', 'schedule_currency_attributes');
                        foreach($currencies as $currency){
                          if($currency['isdefault'] == 1){
                            $currency_attr['data-base'] = $currency['id'];
                          }
                          if(isset($schedule)){
                            if($currency['id'] == $schedule->currency){
                              $selected = $currency['id'];
                            }
                          } else{
                           if($currency['isdefault'] == 1){
                            $selected = $currency['id'];
                          }
                        }
                        }
                        $currency_attr = hooks()->apply_filters('schedule_currency_attributes',$currency_attr);
                        ?>
                     <?php echo render_select('currency', $currencies, array('id','name','symbol'), 'schedule_add_edit_currency', $selected, $currency_attr); ?>
                  </div>
                   <div class="col-md-6">
                     <div class="form-group select-placeholder">
                        <label class="control-label"><?php echo _l('schedule_status'); ?></label>
                        <select class="selectpicker display-block mbot15" name="status" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <?php foreach($schedule_statuses as $status){ ?>
                           <option value="<?php echo $status; ?>" <?php if(isset($schedule) && $schedule->status == $status){echo 'selected';} ?>><?php echo format_schedule_status($status,'',false); ?></option>
                           <?php } ?>
                        </select>
                     </div>
                  </div>
                  <div class="col-md-12">
                    <?php $value = (isset($schedule) ? $schedule->reference_no : ''); ?>
                    <?php echo render_input('reference_no','reference_no',$value); ?>
                  </div>
                  <div class="col-md-6">
                         <?php
                        $selected = '';
                        foreach($staff as $member){
                         if(isset($schedule)){
                           if($schedule->sale_agent == $member['staffid']) {
                             $selected = $member['staffid'];
                           }
                         }
                        }
                        echo render_select('sale_agent',$staff,array('staffid',array('firstname','lastname')),'sale_agent_string',$selected);
                        ?>
                  </div>
                  <div class="col-md-6">
                       <div class="form-group select-placeholder">
                        <label for="discount_type" class="control-label"><?php echo _l('discount_type'); ?></label>
                        <select name="discount_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <option value="" selected><?php echo _l('no_discount'); ?></option>
                           <option value="before_tax" <?php
                              if(isset($schedule)){ if($schedule->discount_type == 'before_tax'){ echo 'selected'; }}?>><?php echo _l('discount_type_before_tax'); ?></option>
                           <option value="after_tax" <?php if(isset($schedule)){if($schedule->discount_type == 'after_tax'){echo 'selected';}} ?>><?php echo _l('discount_type_after_tax'); ?></option>
                        </select>
                     </div>
                  </div>
               </div>
               <?php $value = (isset($schedule) ? $schedule->adminnote : ''); ?>
               <?php echo render_textarea('adminnote','schedule_add_edit_admin_note',$value); ?>

            </div>
         </div>
      </div>
   </div>
   <?php $this->load->view('admin/schedules/_add_edit_items'); ?>
   <div class="row">
    <div class="col-md-12 mtop15">
      <div class="panel-body bottom-transaction">
        <?php $value = (isset($schedule) ? $schedule->clientnote : get_option('predefined_clientnote_schedule')); ?>
        <?php echo render_textarea('clientnote','schedule_add_edit_client_note',$value,array(),array(),'mtop15'); ?>
        <?php $value = (isset($schedule) ? $schedule->terms : get_option('predefined_terms_schedule')); ?>
        <?php echo render_textarea('terms','terms_and_conditions',$value,array(),array(),'mtop15'); ?>
        <div class="btn-bottom-toolbar text-right">
          <div class="btn-group dropup">
            <button type="button" class="btn-tr btn btn-info schedule-form-submit transaction-submit">
              <?php echo _l('submit'); ?>
            </button>
          <button type="button"
            class="btn btn-info dropdown-toggle"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false">
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-right width200">
            <li>
              <a href="#" class="schedule-form-submit save-and-send transaction-submit">
                <?php echo _l('save_and_send'); ?>
              </a>
            </li>
            <?php if(!isset($schedule)) { ?>
              <li>
                <a href="#" class="schedule-form-submit save-and-send-later transaction-submit">
                  <?php echo _l('save_and_send_later'); ?>
                </a>
              </li>
            <?php } ?>
          </ul>
        </div>
      </div>
    </div>
    <div class="btn-bottom-pusher"></div>
  </div>
</div>
</div>
