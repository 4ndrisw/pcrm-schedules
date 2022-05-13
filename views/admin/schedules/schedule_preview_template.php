<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_hidden('_attachment_sale_id',$schedule->id); ?>
<?php echo form_hidden('_attachment_sale_type','schedule'); ?>
<div class="col-md-12 no-padding">
   <div class="panel_s">
      <div class="panel-body">
         <div class="horizontal-scrollable-tabs preview-tabs-top">
            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
            <div class="horizontal-tabs">
               <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                  <li role="presentation" class="active">
                     <a href="#tab_schedule" aria-controls="tab_schedule" role="tab" data-toggle="tab">
                     <?php echo _l('schedule'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_activity" aria-controls="tab_activity" role="tab" data-toggle="tab">
                     <?php echo _l('schedule_view_activity_tooltip'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_reminders" onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $schedule->id ;?> + '/' + 'schedule', undefined, undefined, undefined,[1,'asc']); return false;" aria-controls="tab_reminders" role="tab" data-toggle="tab">
                     <?php echo _l('schedule_reminders'); ?>
                     <?php
                        $total_reminders = total_rows(db_prefix().'reminders',
                          array(
                           'isnotified'=>0,
                           'staff'=>get_staff_user_id(),
                           'rel_type'=>'schedule',
                           'rel_id'=>$schedule->id
                           )
                          );
                        if($total_reminders > 0){
                          echo '<span class="badge">'.$total_reminders.'</span>';
                        }
                        ?>
                     </a>
                  </li>
                  <li role="presentation" class="tab-separator">
                     <a href="#tab_notes" onclick="get_sales_notes(<?php echo $schedule->id; ?>,'schedules'); return false" aria-controls="tab_notes" role="tab" data-toggle="tab">
                     <?php echo _l('schedule_notes'); ?>
                     <span class="notes-total">
                        <?php if($totalNotes > 0){ ?>
                           <span class="badge"><?php echo $totalNotes; ?></span>
                        <?php } ?>
                     </span>
                     </a>
                  </li>
                  <li role="presentation" data-toggle="tooltip" title="<?php echo _l('emails_tracking'); ?>" class="tab-separator">
                     <a href="#tab_emails_tracking" aria-controls="tab_emails_tracking" role="tab" data-toggle="tab">
                     <?php if(!is_mobile()){ ?>
                     <i class="fa fa-envelope-open-o" aria-hidden="true"></i>
                     <?php } else { ?>
                     <?php echo _l('emails_tracking'); ?>
                     <?php } ?>
                     </a>
                  </li>
                  <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('view_tracking'); ?>" class="tab-separator">
                     <a href="#tab_views" aria-controls="tab_views" role="tab" data-toggle="tab">
                     <?php if(!is_mobile()){ ?>
                     <i class="fa fa-eye"></i>
                     <?php } else { ?>
                     <?php echo _l('view_tracking'); ?>
                     <?php } ?>
                     </a>
                  </li>
                  <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>" class="tab-separator toggle_view">
                     <a href="#" onclick="small_table_full_view(); return false;">
                     <i class="fa fa-expand"></i></a>
                  </li>
               </ul>
            </div>
         </div>
         <div class="row mtop10">
            <div class="col-md-3 no-padding">
               <?php echo format_schedule_status($schedule->status,'mtop5');  ?>
            </div>
            <div class="col-md-9 no-padding">
               <div class="visible-xs">
                  <div class="mtop10"></div>
               </div>
               <div class="pull-right _buttons">
                  <?php if((staff_can('edit', 'schedules') && $schedule->status != 4) || is_admin()){ ?>                     
                     <a href="<?php echo admin_url('schedules/update/'.$schedule->id); ?>" class="btn btn-default btn-with-tooltip" data-toggle="tooltip" title="<?php echo _l('edit_schedule_tooltip'); ?>" data-placement="bottom"><i class="fa fa-pencil-square-o"></i></a>
                  
                  <?php } ?>
                  <div class="btn-group">
                     <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf-o"></i><?php if(is_mobile()){echo ' PDF';} ?> <span class="caret"></span></a>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <li class="hidden-xs"><a href="<?php echo site_url('schedules/pdf/'.$schedule->id.'?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a></li>
                        <li class="hidden-xs"><a href="<?php echo site_url('schedules/pdf/'.$schedule->id.'?output_type=I'); ?>" target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                        <li><a href="<?php echo site_url('schedules/pdf/'.$schedule->id); ?>"><?php echo _l('download'); ?></a></li>
                        <li>
                           <a href="<?php echo site_url('schedules/pdf/'.$schedule->id.'?print=true'); ?>" target="_blank">
                           <?php echo _l('print'); ?>
                           </a>
                        </li>
                     </ul>
                  </div>
                  <?php
                     $_tooltip = _l('schedule_sent_to_email_tooltip');
                     $_tooltip_already_send = '';
                     if($schedule->sent == 1){
                        $_tooltip_already_send = _l('schedule_already_send_to_client_tooltip', time_ago($schedule->datesend));
                     }
                     ?>
                  <?php if(!empty($schedule->clientid)){ ?>
                  <a href="#" class="schedule-send-to-client btn btn-default btn-with-tooltip" data-toggle="tooltip" title="<?php echo $_tooltip; ?>" data-placement="bottom"><span data-toggle="tooltip" data-title="<?php echo $_tooltip_already_send; ?>"><i class="fa fa-envelope"></i></span></a>
                  <?php } ?>
                  <div class="btn-group">
                     <button type="button" class="btn btn-default pull-left dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo _l('more'); ?> <span class="caret"></span>
                     </button>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <li>
                           <a href="<?php echo site_url('schedules/show/' . $schedule->id . '/' .  $schedule->hash) ?>" target="_blank">
                           <?php echo _l('view_schedule_as_client'); ?>
                           </a>
                        </li>
                        <?php hooks()->do_action('after_schedule_view_as_client_link', $schedule); ?>
                        <?php if((!empty($schedule->expirydate) && date('Y-m-d') < $schedule->expirydate && ($schedule->status == 2 || $schedule->status == 5)) && is_schedules_expiry_reminders_enabled()){ ?>
                        <li>
                           <a href="<?php echo admin_url('schedules/send_expiry_reminder/'.$schedule->id); ?>">
                           <?php echo _l('send_expiry_reminder'); ?>
                           </a>
                        </li>
                        <?php } ?>
                        <li>
                           <a href="#" data-toggle="modal" data-target="#sales_attach_file"><?php echo _l('attach_file'); ?></a>
                        </li>
                        <?php if (staff_can('create', 'projects') && $schedule->project_id == 0) { ?>
                           <li>
                              <a href="<?php echo admin_url("projects/project?via_schedule_id={$schedule->id}&customer_id={$schedule->clientid}") ?>">
                                 <?php echo _l('schedule_convert_to_project'); ?>
                              </a>
                           </li>
                        <?php } ?>
                           <?php
                           if(staff_can('edit', 'schedules')){
                             foreach($schedule_statuses as $status){
                               if($schedule->status != $status){ ?>
                                 <li>
                                    <a href="<?php echo admin_url() . 'schedules/mark_action_status/'.$status.'/'.$schedule->id; ?>">
                                    <?php echo _l('schedule_mark_as',format_schedule_status($status,'',false)); ?></a>
                                 </li>
                              <?php }
                             }
                             ?>
                           <?php } ?>

                        <?php if(staff_can('create', 'schedules')){ ?>
                        <li>
                           <a href="<?php echo admin_url('schedules/copy/'.$schedule->id); ?>">
                           <?php echo _l('copy_schedule'); ?>
                           </a>
                        </li>
                        <?php } ?>
                        <?php if(!empty($schedule->signature) && staff_can('delete', 'schedules')){ ?>
                        <li>
                           <a href="<?php echo admin_url('schedules/clear_signature/'.$schedule->id); ?>" class="_delete">
                           <?php echo _l('clear_signature'); ?>
                           </a>
                        </li>
                        <?php } ?>
                        <?php if(staff_can('delete', 'schedules')){ ?>
                        <?php
                           if((get_option('delete_only_on_last_schedule') == 1 && is_last_schedule($schedule->id)) || (get_option('delete_only_on_last_schedule') == 0)){ ?>
                        <li>
                           <a href="<?php echo admin_url('schedules/delete/'.$schedule->id); ?>" class="text-danger delete-text _delete"><?php echo _l('delete_schedule_tooltip'); ?></a>
                        </li>
                        <?php
                           }
                           }
                           ?>
                     </ul>
                  </div>


                  <?php if($schedule->jobreportid == NULL){ ?>
                  <?php if(staff_can('create', 'schedules') && !empty($schedule->clientid)){ ?>
                  <div class="btn-group pull-right mleft5">
                     <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <?php echo _l('schedule_convert_to_jobreport'); ?> <span class="caret"></span>
                     </button>
                     <ul class="dropdown-menu">
                        <li><a href="<?php echo admin_url('schedules/convert_to_jobreport/'.$schedule->id.'?save_as_draft=true'); ?>"><?php echo _l('convert_and_save_as_draft'); ?></a></li>
                        <li class="divider">
                        <li><a href="<?php echo admin_url('schedules/convert_to_jobreport/'.$schedule->id); ?>"><?php echo _l('convert'); ?></a></li>
                        </li>
                     </ul>
                  </div>
                  <?php } ?>
                  <?php } else { ?>
                  <a href="<?php echo admin_url('jobreports/jobreport/'.$schedule->jobreportid); ?>" data-placement="bottom" data-toggle="tooltip" title="<?php echo _l('schedule_jobreportd_date',_dt($schedule->jobreport_date)); ?>"class="btn mleft10 btn-info"><?php echo format_jobreport_number($schedule->jobreportid); ?></a>
                  <?php } ?>



               </div>
            </div>
         </div>
         <div class="clearfix"></div>
         <hr class="hr-panel-heading" />
         <div class="tab-content">
            <div role="tabpanel" class="tab-pane ptop10 active" id="tab_schedule">
               <?php if(isset($schedule->scheduled_email) && $schedule->scheduled_email) { ?>
                     <div class="alert alert-warning">
                        <?php echo _l('will_be_sent_at', _dt($schedule->scheduled_email->scheduled_at)); ?>
                        <?php if(staff_can('edit', 'schedules') || $schedule->addedfrom == get_staff_user_id()) { ?>
                           <a href="#"
                           onclick="edit_schedule_scheduled_email(<?php echo $schedule->scheduled_email->id; ?>); return false;">
                           <?php echo _l('edit'); ?>
                        </a>
                     <?php } ?>
                  </div>
               <?php } ?>
               <div id="schedule-preview">
                  <div class="row">
                     <?php if($schedule->status == 4 && !empty($schedule->acceptance_firstname) && !empty($schedule->acceptance_lastname) && !empty($schedule->acceptance_email)){ ?>
                     <div class="col-md-12">
                        <div class="alert alert-info mbot15">
                           <?php echo _l('accepted_identity_info',array(
                              _l('schedule_lowercase'),
                              '<b>'.$schedule->acceptance_firstname . ' ' . $schedule->acceptance_lastname . '</b> (<a href="mailto:'.$schedule->acceptance_email.'">'.$schedule->acceptance_email.'</a>)',
                              '<b>'. _dt($schedule->acceptance_date).'</b>',
                              '<b>'.$schedule->acceptance_ip.'</b>'.(is_admin() ? '&nbsp;<a href="'.admin_url('schedules/clear_signature/'.$schedule->id).'" class="_delete text-muted" data-toggle="tooltip" data-title="'._l('clear_this_information').'"><i class="fa fa-remove"></i></a>' : '')
                              )); ?>
                        </div>
                     </div>
                     <?php } ?>
                     <?php if($schedule->project_id != 0){ ?>
                     <div class="col-md-12">
                        <h4 class="font-medium mbot15"><?php echo _l('related_to_project',array(
                           _l('schedule_lowercase'),
                           _l('project_lowercase'),
                           '<a href="'.admin_url('projects/view/'.$schedule->project_id).'" target="_blank">' . $schedule->project_data->name . '</a>',
                           )); ?></h4>
                     </div>
                     <?php } ?>
                     <div class="col-md-6 col-sm-6">
                        <h4 class="bold">
                           <?php
                              $tags = get_tags_in($schedule->id,'schedule');
                              if(count($tags) > 0){
                                echo '<i class="fa fa-tag" aria-hidden="true" data-toggle="tooltip" data-title="'.html_escape(implode(', ',$tags)).'"></i>';
                              }
                              ?>
                           <a href="<?php echo site_url('schedules/show/'.$schedule->id.'/'.$schedule->hash); ?>">
                           <span id="schedule-number">
                           <?php echo format_schedule_number($schedule->id); ?>
                           </span>
                           </a>
                        </h4>
                        <address>
                           <?php echo format_organization_info(); ?>
                        </address>
                     </div>
                     <div class="col-sm-6 text-right">
                        <span class="bold"><?php echo _l('schedule_to'); ?>:</span>
                        <address>
                           <?php echo format_customer_info($schedule, 'schedule', 'billing', true); ?>
                        </address>
                        <?php if($schedule->include_shipping == 1 && $schedule->show_shipping_on_schedule == 1){ ?>
                        <span class="bold"><?php echo _l('ship_to'); ?>:</span>
                        <address>
                           <?php echo format_customer_info($schedule, 'schedule', 'shipping'); ?>
                        </address>
                        <?php } ?>
                     </div>
                  </div>
                  <div class="row">
                     <div class="container-fluid">
                        <div class="col-md-6">
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
                        <div class="col-md-6 text-right">
                           <p class="no-mbot">
                              <span class="bold">
                              <?php echo _l('schedule_data_date'); ?>:
                              </span>
                              <?php echo $schedule->date; ?>
                           </p>
                           <?php if(!empty($schedule->reference_no)){ ?>
                           <p class="no-mbot">
                              <span class="bold"><?php echo _l('reference_no'); ?>:</span>
                              <?php echo $schedule->reference_no; ?>
                           </p>
                           <?php } ?>

                           <?php if($schedule->project_id != 0 && get_option('show_project_on_schedule') == 1){ ?>
                           <p class="no-mbot">
                              <span class="bold"><?php echo _l('project'); ?>:</span>
                              <?php echo get_project_name_by_id($schedule->project_id); ?>
                           </p>
                           <?php } ?>
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col-md-12 schedule-items">
                        <div class="table-responsive">
                              <?php
                                 $items = get_schedule_items_table_data($schedule, 'schedule', 'html', true);
                                 echo $items->table();
                              ?>
                        </div>
                     </div>
                     <?php if(count($schedule->attachments) > 0){ ?>
                        <div class="clearfix"></div>
                        <hr />
                        <div class="col-md-12">
                           <p class="bold text-muted"><?php echo _l('schedule_files'); ?></p>
                        </div>
                        <?php foreach($schedule->attachments as $attachment){
                           $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
                           if(!empty($attachment['external'])){
                             $attachment_url = $attachment['external_link'];
                           }
                           ?>
                           <div class="mbot15 row col-md-12" data-attachment-id="<?php echo $attachment['id']; ?>">
                              <div class="col-md-8">
                                 <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
                                 <a href="<?php echo $attachment_url; ?>" target="_blank"><?php echo $attachment['file_name']; ?></a>
                                 <br />
                                 <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
                              </div>
                              <div class="col-md-4 text-right">
                                 <?php if($attachment['visible_to_customer'] == 0){
                                    $icon = 'fa fa-toggle-off';
                                    $tooltip = _l('show_to_customer');
                                    } else {
                                    $icon = 'fa fa-toggle-on';
                                    $tooltip = _l('hide_from_customer');
                                    }
                                    ?>
                                 <a href="#" data-toggle="tooltip" onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $schedule->id; ?>,this); return false;" data-title="<?php echo $tooltip; ?>"><i class="<?php echo $icon; ?>" aria-hidden="true"></i></a>
                                 <?php if($attachment['staffid'] == get_staff_user_id() || is_admin()){ ?>
                                 <a href="#" class="text-danger" onclick="delete_schedule_attachment(<?php echo $attachment['id']; ?>); return false;"><i class="fa fa-times"></i></a>
                                 <?php } ?>
                              </div>
                           </div>
                        <?php } ?>
                     <?php } ?>

                     <?php if($schedule->assigned != 0 && get_option('show_assigned_on_schedules') == 1){ ?>
                     <div class="col-md-12 no-mbot">
                        <div class="bold"><?php echo _l('schedule_staff_string'); ?>:</div>
                        <?php echo get_staff_full_name($schedule->assigned); ?>
                     </div>
                     <?php } ?>                     
                     <?php if($schedule->clientnote != ''){ ?>
                     <div class="col-md-12 mtop15">
                        <p class="bold text-muted"><?php echo _l('schedule_order'); ?></p>
                        <p><?php echo $schedule->clientnote; ?></p>
                     </div>
                     <?php } ?>
                     <?php if($schedule->terms != ''){ ?>
                     <div class="col-md-12 mtop15">
                        <p class="bold text-muted"><?php echo _l('terms_and_conditions'); ?></p>
                        <p><?php echo $schedule->terms; ?></p>
                     </div>
                     <?php } ?>
                  </div>
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_reminders">
               <a href="#" data-toggle="modal" class="btn btn-info" data-target=".reminder-modal-schedule-<?php echo $schedule->id; ?>"><i class="fa fa-bell-o"></i> <?php echo _l('schedule_set_reminder_title'); ?></a>
               <hr />
               <?php render_datatable(array( _l( 'reminder_description'), _l( 'reminder_date'), _l( 'reminder_staff'), _l( 'reminder_is_notified')), 'reminders'); ?>
               <?php $this->load->view('admin/includes/modals/reminder',array('id'=>$schedule->id,'name'=>'schedule','members'=>$members,'reminder_title'=>_l('schedule_set_reminder_title'))); ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_emails_tracking">
               <?php
                  $this->load->view('admin/includes/emails_tracking',array(
                     'tracked_emails'=>
                     get_tracked_emails($schedule->id, 'schedule'))
                  );
                  ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_notes">
               <?php echo form_open(admin_url('schedules/add_note/'.$schedule->id),array('id'=>'sales-notes','class'=>'schedule-notes-form')); ?>
               <?php echo render_textarea('description'); ?>
               <div class="text-right">
                  <button type="submit" class="btn btn-info mtop15 mbot15"><?php echo _l('schedule_add_note'); ?></button>
               </div>
               <?php echo form_close(); ?>
               <hr />
               <div class="panel_s mtop20 no-shadow" id="sales_notes_area">
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_activity">
               <div class="row">
                  <div class="col-md-12">
                     <div class="activity-feed">
                        <?php foreach($activity as $activity){
                           $_custom_data = false;
                           ?>
                        <div class="feed-item" data-sale-activity-id="<?php echo $activity['id']; ?>">
                           <div class="date">
                              <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($activity['date']); ?>">
                              <?php echo time_ago($activity['date']); ?>
                              </span>
                           </div>
                           <div class="text">
                              <?php if(is_numeric($activity['staffid']) && $activity['staffid'] != 0){ ?>
                              <a href="<?php echo admin_url('profile/'.$activity["staffid"]); ?>">
                              <?php echo staff_profile_image($activity['staffid'],array('staff-profile-xs-image pull-left mright5'));
                                 ?>
                              </a>
                              <?php } ?>
                              <?php
                                 $additional_data = '';
                                 if(!empty($activity['additional_data'])){
                                  $additional_data = unserialize($activity['additional_data']);
                                  
                                  $i = 0;
                                  foreach($additional_data as $data){
                                    if(strpos($data,'<original_status>') !== false){
                                      $original_status = get_string_between($data, '<original_status>', '</original_status>');
                                      $additional_data[$i] = format_schedule_status($original_status,'',false);
                                    } else if(strpos($data,'<new_status>') !== false){
                                      $new_status = get_string_between($data, '<new_status>', '</new_status>');
                                      $additional_data[$i] = format_schedule_status($new_status,'',false);
                                    } else if(strpos($data,'<status>') !== false){
                                      $status = get_string_between($data, '<status>', '</status>');
                                      $additional_data[$i] = format_schedule_status($status,'',false);
                                    } else if(strpos($data,'<custom_data>') !== false){
                                      $_custom_data = get_string_between($data, '<custom_data>', '</custom_data>');
                                      unset($additional_data[$i]);
                                    }
                                    $i++;
                                  }
                                 }
                                 $_formatted_activity = _l($activity['description'],$additional_data);
                                 if($_custom_data !== false){
                                 $_formatted_activity .= ' - ' .$_custom_data;
                                 }
                                 if(!empty($activity['full_name'])){
                                 $_formatted_activity = $activity['full_name'] . ' - ' . $_formatted_activity;
                                 }
                                 echo $_formatted_activity;
                                 if(is_admin()){
                                 echo '<a href="#" class="pull-right text-danger" onclick="delete_sale_activity('.$activity['id'].'); return false;"><i class="fa fa-remove"></i></a>';
                                 }
                                 ?>
                           </div>
                        </div>
                        <?php } ?>
                     </div>
                  </div>
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_views">
               <?php
                  $views_activity = get_views_tracking('schedule',$schedule->id);
                  if(count($views_activity) === 0) {
                     echo '<h4 class="no-mbot">'._l('not_viewed_yet',_l('schedule_lowercase')).'</h4>';
                  }
                  foreach($views_activity as $activity){ ?>
               <p class="text-success no-margin">
                  <?php echo _l('view_date') . ': ' . _dt($activity['date']); ?>
               </p>
               <p class="text-muted">
                  <?php echo _l('view_ip') . ': ' . $activity['view_ip']; ?>
               </p>
               <hr />
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</div>
<?php $this->load->view('admin/schedules/schedule_send_to_client'); ?>
