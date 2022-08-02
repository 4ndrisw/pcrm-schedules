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
               <div class="col-md-3">
                  <h3 class="bold no-mtop schedule-html-number no-mbot">
                     <span class="sticky-visible hide">
                     <?php echo format_schedule_number($schedule->id); ?>
                     </span>
                  </h3>
                  <h4 class="schedule-html-status mtop7">
                     <?php echo format_schedule_status($schedule->status,'',true); ?>
                  </h4>
               </div>
               <div class="col-md-9">
                  <?php echo form_open(site_url('schedules/office_pdf/'.$schedule->id), array('class'=>'pull-right action-button')); ?>
                  <button type="submit" name="schedulepdf" class="btn btn-default action-button download mright5 mtop7" value="schedulepdf">
                  <i class="fa fa-file-pdf-o"></i>
                  <?php echo _l('clients_invoice_html_btn_download'); ?>
                  </button>
                  <?php echo form_close(); ?>
                  <?php if(is_client_logged_in() || is_staff_member()){ ?>
                  <a href="<?php echo site_url('clients/schedules/'); ?>" class="btn btn-default pull-right mright5 mtop7 action-button go-to-portal">
                  <?php echo _l('client_go_to_dashboard'); ?>
                  </a>
                  <?php } ?>
               </div>
            </div>
            <div class="clearfix"></div>
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
               <span class="bold schedule_to"><?php echo _l('schedule_office_to'); ?>:</span>
               <address class="schedule-html-customer-billing-info">
                  <?php echo format_office_info($schedule->office, 'office', 'billing'); ?>
               </address>
               <!-- shipping details -->
               <?php if($schedule->include_shipping == 1 && $schedule->show_shipping_on_schedule == 1){ ?>
               <span class="bold schedule_ship_to"><?php echo _l('ship_to'); ?>:</span>
               <address class="schedule-html-customer-shipping-info">
                  <?php echo format_office_info($schedule->office, 'office', 'shipping'); ?>
               </address>
               <?php } ?>
            </div>
         </div>
         <div class="row">

            <div class="col-sm-12 text-left transaction-html-info-col-left">
               <p class="schedule_to"><?php echo _l('schedule_opening'); ?>:</p>
               <span class="schedule_to"><?php echo _l('schedule_client'); ?>:</span>
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



            <div class="col-md-6">
               <div class="container-fluid">
                  <?php if(!empty($schedule_members)){ ?>
                     <strong><?= _l('schedule_members') ?></strong>
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


            <div class="row mtop25">
               <div class="col-md-12">
                  <div class="col-md-6 text-center">
                     <div class="bold"><?php echo get_option('invoice_company_name'); ?></div>
                     <div class="qrcode text-center">
                        <img src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path(get_schedule_upload_path('schedule').$schedule->id.'/assigned-'.$schedule_number.'.png')); ?>" class="img-responsive center-block schedule-assigned" alt="schedule-<?= $schedule->id ?>">
                     </div>
                     <div class="assigned">
                     <?php if($schedule->assigned != 0 && get_option('show_assigned_on_schedules') == 1){ ?>
                        <?php echo get_staff_full_name($schedule->assigned); ?>
                     <?php } ?>

                     </div>
                  </div>
                     <div class="col-md-6 text-center">
                       <div class="bold"><?php echo $client_company; ?></div>
                       <?php if(!empty($schedule->signature)) { ?>
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
                           <div class="customer_signature text-center">
                              <img src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path(get_schedule_upload_path('schedule').$schedule->id.'/'.$schedule->signature)); ?>" class="img-responsive center-block schedule-signature" alt="schedule-<?= $schedule->id ?>">
                           </div>
                       <?php } ?>
                     </div>
               </div>
            </div>

         </div>
      </div>
   </div>
</div>

