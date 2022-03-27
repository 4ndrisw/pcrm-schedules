<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
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


            </div>
        </div>
    </div>
</div>

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
    </script>
</body>
</html>