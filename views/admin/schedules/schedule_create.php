<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content schedule-add">
		<div class="row">
			<?php
			echo form_open($this->uri->uri_string(),array('id'=>'schedule-form','class'=>'_transaction_form'));
			if(isset($schedule)){
				echo form_hidden('isedit');
			}
			?>
			<div class="col-md-12">
				<?php $this->load->view('admin/schedules/schedule_template'); ?>
			</div>
			<?php echo form_close(); ?>
			<?php $this->load->view('admin/invoice_items/item'); ?>
		</div>
	</div>
</div>
</div>
<?php init_tail(); ?>
<script type="text/javascript" src="/modules/schedules/assets/js/schedules.js"></script>
<script type="text/javascript">
	$(function(){
		validate_schedule_form();
		// Project ajax search
		init_ajax_project_search_by_customer_id();
		// Maybe items ajax search
	    init_ajax_search('items','#item_select.ajax-search',undefined,admin_url+'items/search');
	});
</script>
</body>
</html>
