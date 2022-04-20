<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel-body mtop10">
   <div class="row">
   </div>
   <div class="table-responsive s_table">
      <table class="table schedule-items-table items table-main-schedule-edit has-calculations no-mtop">
         <thead>
            <tr>
               <th></th>
               <th width="30%" align="left"><i class="fa fa-exclamation-circle" aria-hidden="true" data-toggle="tooltip" data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i> <?php echo _l('schedule_table_item_heading'); ?></th>
               <th width="45%" align="left"><?php echo _l('schedule_table_item_description'); ?></th>
               <?php
                  $custom_fields = get_custom_fields('items');
                  foreach($custom_fields as $cf){
                   echo '<th width="15%" align="left" class="custom_field">' . $cf['name'] . '</th>';
                  }

                  $qty_heading = _l('schedule_table_quantity_heading');
                  if(isset($schedule) && $schedule->show_quantity_as == 2){
                  $qty_heading = _l('schedule_table_hours_heading');
                  } else if(isset($schedule) && $schedule->show_quantity_as == 3){
                  $qty_heading = _l('schedule_table_quantity_heading') . '/' . _l('schedule_table_hours_heading');
                  }
                  ?>
               <th width="10%" class="qty" align="right"><?php echo $qty_heading; ?></th>
               <th align="center"><i class="fa fa-cog"></i></th>
            </tr>
         </thead>
         <tbody>
            <tr class="main">
               <td></td>
               <td>
                  <textarea name="description" rows="2" class="form-control" placeholder="<?php echo _l('item_description_placeholder'); ?>"></textarea>
               </td>
               <td>
                  <textarea name="long_description" rows="2" class="form-control" placeholder="<?php echo _l('item_long_description_placeholder'); ?>"></textarea>
               </td>
               <?php echo render_custom_fields_items_table_add_edit_preview(); ?>
               <td>
                  <input type="number" name="quantity" min="0" value="1" class="form-control" placeholder="<?php echo _l('item_quantity_placeholder'); ?>">
                  <input type="text" placeholder="<?php echo _l('unit'); ?>" name="unit" class="form-control input-transparent text-right">
               </td>
               <td>
                  <?php
                     $new_item = 'undefined';
                     if(isset($schedule)){
                       $new_item = true;
                     }
                     ?>
                  <button type="button" onclick="add_schedule_item_to_table('undefined','undefined',<?php echo $new_item; ?>); return false;" class="btn text-center btn-info"><i class="fa fa-check"></i></button>
               </td>
            </tr>
            <?php if (isset($schedule) || isset($add_items)) {
               $i               = 1;
               $items_indicator = 'newitems';
               if (isset($schedule)) {
                 $add_items       = $schedule->items;
                 $items_indicator = 'items';
               }

               foreach ($add_items as $item) {
                 $manual    = false;
                 $table_row = '<tr class="sortable item">';
                 $table_row .= '<td class="dragger">';
                 if ($item['qty'] == '' || $item['qty'] == 0) {
                   $item['qty'] = 1;
                 }
               $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][itemid]', $item['id']);
               // order input
               $table_row .= '<input type="hidden" class="order" name="' . $items_indicator . '[' . $i . '][order]">';
               $table_row .= '</td>';
               $table_row .= '<td class="bold description"><textarea name="' . $items_indicator . '[' . $i . '][description]" class="form-control" rows="2">' . clear_textarea_breaks($item['description']) . '</textarea></td>';
               $table_row .= '<td><textarea name="' . $items_indicator . '[' . $i . '][long_description]" class="form-control" rows="2">' . clear_textarea_breaks($item['long_description']) . '</textarea></td>';
               $table_row .= '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="' . $items_indicator . '[' . $i . '][qty]" value="' . $item['qty'] . '" class="form-control">';
               $unit_placeholder = '';
               if(!$item['unit']){
                 $unit_placeholder = _l('unit');
                 $item['unit'] = '';
               }
               $table_row .= '<input type="text" placeholder="'.$unit_placeholder.'" name="'.$items_indicator.'['.$i.'][unit]" class="form-control input-transparent text-right" value="'.$item['unit'].'">';
               $table_row .= '</td>';
               $table_row .= '<td><a href="#" class="btn btn-danger text-center" onclick="delete_item(this,' . $item['id'] . '); return false;"><i class="fa fa-times"></i></a></td>';
               $table_row .= '</tr>';
               echo $table_row;
               $i++;
               }
               }
               ?>
         </tbody>
      </table>
   </div>
   <div id="removed-items"></div>
</div>