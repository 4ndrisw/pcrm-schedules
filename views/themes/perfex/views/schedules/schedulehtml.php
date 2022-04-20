<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="mtop15 preview-top-wrapper">
   <div class="row">
      <div class="col-md-3">
         <div class="mbot30">
            <div class="schedule-html-logo">
               <?php echo get_dark_company_logo(); ?>
            </div>
         </div>
      </div>
      <div class="clearfix"></div>
   </div>
   <div class="top" data-sticky data-sticky-class="preview-sticky-header">
      <div class="container preview-sticky-container">
         <div class="row">
            <div class="col-md-12">
               <div class="pull-left">
                  <h3 class="bold no-mtop schedule-html-number no-mbot">
                     <span class="sticky-visible hide">
                     <?php echo format_schedule_number($schedule->id); ?>
                     </span>
                  </h3>
                  <h4 class="schedule-html-status mtop7">
                     <?php echo format_schedule_status($schedule->status,'',true); ?>
                  </h4>
               </div>
               <div class="visible-xs">
                  <div class="clearfix"></div>
               </div>
               <?php
                  // Is not accepted, declined and expired
                  if ($schedule->status != 4 && $schedule->status != 3 && $schedule->status != 5) {
                    $can_be_accepted = true;
                    if($identity_confirmation_enabled == '0'){
                      echo form_open($this->uri->uri_string(), array('class'=>'pull-right mtop7 action-button'));
                      echo form_hidden('schedule_action', 4);
                      echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_schedule').'</button>';
                      echo form_close();
                    } else {
                      echo '<button type="button" id="accept_action" class="btn btn-success mright5 mtop7 pull-right action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_schedule').'</button>';
                    }
                  } else if($schedule->status == 3){
                    if (($schedule->expirydate >= date('Y-m-d') || !$schedule->expirydate) && $schedule->status != 5) {
                      $can_be_accepted = true;
                      if($identity_confirmation_enabled == '0'){
                        echo form_open($this->uri->uri_string(),array('class'=>'pull-right mtop7 action-button'));
                        echo form_hidden('schedule_action', 4);
                        echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_schedule').'</button>';
                        echo form_close();
                      } else {
                        echo '<button type="button" id="accept_action" class="btn btn-success mright5 mtop7 pull-right action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_schedule').'</button>';
                      }
                    }
                  }
                  // Is not accepted, declined and expired
                  if ($schedule->status != 4 && $schedule->status != 3 && $schedule->status != 5) {
                    echo form_open($this->uri->uri_string(), array('class'=>'pull-right action-button mright5 mtop7'));
                    echo form_hidden('schedule_action', 3);
                    echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-default action-button accept"><i class="fa fa-remove"></i> '._l('clients_decline_schedule').'</button>';
                    echo form_close();
                  }
                  ?>
               <?php echo form_open($this->uri->uri_string(), array('class'=>'pull-right action-button')); ?>
               <button type="submit" name="schedulepdf" class="btn btn-default action-button download mright5 mtop7" value="schedulepdf">
               <i class="fa fa-file-pdf-o"></i>
               <?php echo _l('clients_invoice_html_btn_download'); ?>
               </button>
               <?php echo form_close(); ?>
               <?php if(is_client_logged_in() && has_contact_permission('schedules')){ ?>
               <a href="<?php echo site_url('clients/schedules/'); ?>" class="btn btn-default pull-right mright5 mtop7 action-button go-to-portal">
               <?php echo _l('client_go_to_dashboard'); ?>
               </a>
               <?php } ?>
               <div class="clearfix"></div>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="clearfix"></div>
<div class="panel_s mtop20">
   <div class="panel-body">
      <div class="col-md-10 col-md-offset-1">
         <div class="row mtop20">
            <div class="col-md-6 col-sm-6 transaction-html-info-col-left">
               <h4 class="bold schedule-html-number"><?php echo format_schedule_number($schedule->id); ?></h4>
               <address class="schedule-html-company-info">
                  <?php echo format_organization_info(); ?>
               </address>
            </div>
            <div class="col-sm-6 text-right transaction-html-info-col-right">
               <span class="bold schedule_to"><?php echo _l('schedule_to'); ?>:</span>
               <address class="schedule-html-customer-billing-info">
                  <?php echo format_customer_info($schedule, 'schedule', 'billing'); ?>
               </address>
               <!-- shipping details -->
               <?php if($schedule->include_shipping == 1 && $schedule->show_shipping_on_schedule == 1){ ?>
               <span class="bold schedule_ship_to"><?php echo _l('ship_to'); ?>:</span>
               <address class="schedule-html-customer-shipping-info">
                  <?php echo format_customer_info($schedule, 'schedule', 'shipping'); ?>
               </address>
               <?php } ?>
            </div>
         </div>
         <div class="row">
            <div class="col-md-6">
               <div class="container-fluid">
                  <?php if(!empty($schedule_members)){ ?>
                     <strong><?= _l('schedule_members_name') ?></strong>
                     <ul class="schedule_members">
                     <?php 
                        foreach($schedule_members as $member){
                          echo ('<li style="list-style:auto" class="member">' . $member['firstname'] .' '. $member['lastname'] .'</li>');
                         }
                     ?>
                     </ul>
                  <?php } ?>
               </div>
            </div>
            <div class="col-md-6 text-right">
               <p class="no-mbot schedule-html-date">
                  <span class="bold">
                  <?php echo _l('schedule_data_date'); ?>:
                  </span>
                  <?php echo _d($schedule->date); ?>
               </p>
               <?php if(!empty($schedule->expirydate)){ ?>
               <p class="no-mbot schedule-html-expiry-date">
                  <span class="bold"><?php echo _l('schedule_data_expiry_date'); ?></span>:
                  <?php echo _d($schedule->expirydate); ?>
               </p>
               <?php } ?>
               <?php if(!empty($schedule->reference_no)){ ?>
               <p class="no-mbot schedule-html-reference-no">
                  <span class="bold"><?php echo _l('reference_no'); ?>:</span>
                  <?php echo $schedule->reference_no; ?>
               </p>
               <?php } ?>
               <?php if($schedule->project_id != 0 && get_option('show_project_on_schedule') == 1){ ?>
               <p class="no-mbot schedule-html-project">
                  <span class="bold"><?php echo _l('project'); ?>:</span>
                  <?php echo get_project_name_by_id($schedule->project_id); ?>
               </p>
               <?php } ?>
               <?php $pdf_custom_fields = get_custom_fields('schedule',array('show_on_pdf'=>1,'show_on_client_portal'=>1));
                  foreach($pdf_custom_fields as $field){
                    $value = get_custom_field_value($schedule->id,$field['id'],'schedule');
                    if($value == ''){continue;} ?>
               <p class="no-mbot">
                  <span class="bold"><?php echo $field['name']; ?>: </span>
                  <?php echo $value; ?>
               </p>
               <?php } ?>
            </div>
         </div>   
         <div class="row">
            <div class="col-md-12">
               <div class="table-responsive">
                  <?php
                     $items = get_schedule_items_table_data($schedule, 'schedule');
                     echo $items->table();
                     ?>
               </div>
            </div>

            <?php if($schedule->assigned != 0 && get_option('show_assigned_on_schedules') == 1){ ?>
            
            <div class="col-md-12">
               <div class="col-md-12 schedule-html-sale-agent">
                  <div class="bold"><?php echo _l('assigned_string'); ?>:</div>
                  <?php echo get_staff_full_name($schedule->assigned); ?>
               </div>
                <?php if(!empty($schedule->signature)) { ?>
                  <div class="row mtop25">
                     <div class="col-md-6 col-md-offset-6 text-right">
                        <div class="bold">
                           <p class="no-mbot"><?php echo _l('schedule_signed_by') . ": {$schedule->acceptance_firstname} {$schedule->acceptance_lastname}"?></p>
                           <p class="no-mbot"><?php echo _l('schedule_signed_date') . ': ' . _dt($schedule->acceptance_date) ?></p>
                           <p class="no-mbot"><?php echo _l('schedule_signed_ip') . ": {$schedule->acceptance_ip}"?></p>
                        </div>
                        <p class="bold"><?php echo _l('document_customer_signature_text'); ?>
                        <?php if($schedule->signed == 1 && has_permission('schedules','','delete')){ ?>
                           <a href="<?php echo admin_url('schedules/clear_signature/'.$schedule->id); ?>" data-toggle="tooltip" title="<?php echo _l('clear_signature'); ?>" class="_delete text-danger">
                              <i class="fa fa-remove"></i>
                           </a>
                        <?php } ?>
                        </p>
                        <div class="pull-right">
                           <img src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path(get_schedule_upload_path('schedule').$schedule->id.'/'.$schedule->signature)); ?>" class="img-responsive schedule-signature" alt="schedule-<?= $schedule->id ?>">
                        </div>
                     </div>
                  </div>
               <?php } ?>
            </div>




            <?php } ?>
            <?php if(!empty($schedule->clientnote)){ ?>
            <div class="col-md-12 schedule-html-note">
            <hr />
               <b><?php echo _l('schedule_order'); ?></b><br /><?php echo $schedule->clientnote; ?>
            </div>
            <?php } ?>
            <?php if(!empty($schedule->terms)){ ?>
            <div class="col-md-12 schedule-html-terms-and-conditions">
               <b><?php echo _l('terms_and_conditions'); ?>:</b><br /><?php echo $schedule->terms; ?>
            </div>
            <?php } ?>

         </div>
      </div>
   </div>
</div>
<?php
   if($identity_confirmation_enabled == '1' && $can_be_accepted){
    get_template_part('identity_confirmation_form',array('formData'=>form_hidden('schedule_action',4)));
   }
   ?>
<script>
   $(function(){
     new Sticky('[data-sticky]');
   })
</script>
