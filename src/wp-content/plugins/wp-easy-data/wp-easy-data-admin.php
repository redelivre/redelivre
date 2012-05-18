<?php 

do_action('wp-easy-data-admin-init');

if ($_POST['crud_submit']) {
	//echo '<pre>';
	//die(var_dump($_POST));
	$item = !empty($_POST['id']) ? $this->get_item((int) $_POST['id']) : $this->get_item();;    
	
	foreach($this->belongsTo as $bt) {
		$item->info->{$bt->obj->external_name} = $_POST[$bt->obj->external_name];
	}
	
    if (!empty($_POST['id'])){
        //$item = $this->get_item((int) $_POST['id']);        
        foreach ($this->fields as $field) {
        	if($field['type'] == 'checkboxes')
        		$item->info->$field['name'] = serialize($_POST[$field['name']]);
            elseif ($field['type'] != 'file')
               $item->info->$field['name'] = $_POST[$field['name']];
        }
        
        do_action('wp-easy-data-post', $item);
        $item = apply_filters('wp-easy-data-post-filter', $item);
        $item->save();
        $this->message = __('Item Updated', 'wp-easy-data');
    } else {
        //$item = $this->get_item();
        foreach ($this->fields as $field) {
            if($field['type'] == 'checkboxes')
        		$item->info->$field['name'] = serialize($_POST[$field['name']]);
            else
            	$item->info->$field['name'] = $_POST[$field['name']];
        }
        do_action('wp-easy-data-post', $item);
        $item = apply_filters('wp-easy-data-post-filter', $item);
        $item->save();
        $this->message = __('Item Created', 'wp-easy-data');
        
    }
    
    do_action('wp-easy-data-after-save', $item);
}

if ($_POST['delete_selected'] && $_POST['delete']) {
    foreach($_POST['delete'] as $id){
        $item = $this->get_item((int) $id);
        $item->delete();
    }
    $this->message = __('Items Removed', 'wp-easy-data');
}

$titleForm = 'Novo Item';

if ($_GET['action'] == 'edit' && $_GET['id']) {
    $editMode = true;
    $itemEdit = $this->get_item((int) $_GET['id']);
    $titleForm = __('Edit Item', 'wp-easy-data');    
}

if ($_GET['action'] == 'delete' && $_GET['id']) {
    $item = $this->get_item((int) $_GET['id']);
    do_action('wp-easy-data-delete', $item);
    $item->delete();
    $this->message = __('Item Removed', 'wp-easy-data');
}

if ($_GET['action'] == 'deletefile' && $_GET['id'] && $_GET['filetype']) {
    $item = $this->get_item((int) $_GET['id']);
    do_action('wp-easy-data-delete-file', $item);
    $item->delete_file($_GET['filetype']);
    $this->message = __('File Deleted', 'wp-easy-data');    
}

$belongsTo_index = array();
foreach($this->belongsTo as $bt) {
	if(!isset($belongsTo_index[$bt->form_position]))
		$belongsTo_index[$bt->form_position] = array();            		
	$belongsTo_index[$bt->form_position][] = $bt->obj->name;
}

$hasManyAndBelongsTo_index = array();
foreach($this->hasManyAndBelongsTo as $hmbt) {
	if(!isset($hasManyAndBelongsTo_index[$hmbt->form_position]))
		$hasManyAndBelongsTo_index[$hmbt->form_position] = array();            		
	$hasManyAndBelongsTo_index[$hmbt->form_position][] = $hmbt->obj->name;
}

?>

<div class="wrap">
    <h2><?php _e('Manage', 'wp-easy-data'); ?> <?php echo $this->adminName; ?></h2><BR>
    <a href="<?php echo add_query_arg('id', ''); ?>#form_title" class="button-primary"><?php _e('New Item', 'wp-easy-data'); ?></a>
    <?php do_action('wp-easy-data-after-newitem'); ?>
    <br/><br/>
    <?php if ($this->message) { ?>
        <div id="message" class="updated fade"><p><strong><?php echo $this->message; ?></strong></p></div>
    <?php } ?>
    <form id="wp-easy-data-list-form" name="wp-easy-data-list-form" method="post">
        <?php $this->print_bulk_actions(); ?>
        
        <table id="wp-easy-data-table" class="widefat fixed">
            <thead>
                <tr class="thead">
                    <th class="check-column"></th>
                    <?php do_action('wp-easy-data-table-newColLeft-title'); ?>
                    
                    <?php foreach ($this->fields as $field) : ?>
                        <?php if ($field['list_display']) : ?>
                            <th><?php echo $field['display_name']; ?></th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    
                    <?php 
                    foreach($this->belongsTo as $bt) {
                        if($bt->list_display)
                            echo '<th>', $bt->label, '</th>';
                    }
                    
                    foreach($this->hasManyAndBelongsTo as $hmbt) {
                        if($hmbt->list_display)
                            echo '<th>', $hmbt->label, '</th>';
                    }
                    
                    ?>
                    
                    <?php do_action('wp-easy-data-table-newCol-title'); ?>
                    <th></th>
                </tr>
            </thead>
            <?php 
            
            $q_search = $this->build_search_clause($_GET['search']);
            
            $items = $this->get_items(apply_filters('wp-easy-data-list',$q_search));
            
            foreach ($items as $iss) {
                do_action('wp-easy-data-table-veforeRow', $iss); 
                ?>
                <tr class="sortable" id="wp-easy-data-record-<?php echo $iss->ID; ?>">
                    <th class="check-column" scope="row">
                        <input type="checkbox" name="delete[]" value="<?php echo $iss->ID; ?>">
                    </th>
                    <?php do_action('wp-easy-data-table-newColLeft-value', $iss); ?>
                    <?php foreach ($this->fields as $field) : 
                    

                        if ($field['list_display']) : 
                            ?>
                            <td class="<?php echo $field['name']; ?>">
                            <?php 
                            if (isset($iss->info->$field['name'])) {

                                if ($field['type'] == 'file' && wp_attachment_is_image($iss->info->$field['name'])) {
                                    echo wp_get_attachment_image($iss->info->$field['name']);
                                } elseif ($field['type'] == 'bool') {
                                    $img = $iss->info->$field['name'] ? 'true' : 'false';
                                    echo "<img src='" . H_CRUD_URLPATH . "img/$img.png' />";
                                } else {
                                	if($field['type'] == 'checkboxes' AND is_array($iss->info->$field['name']))
                                    	$display = apply_filters('wp-easy-data-table-cells', array(implode(', ', array_keys($iss->info->$field['name'])), $iss->info));
                                    else
                                    	$display = apply_filters('wp-easy-data-table-cells', array($iss->info->$field['name'], $iss->info));
                                    	
                                    $display = apply_filters('wp-easy-data-table-col-' . $field['name'], array($display[0], $iss->info));
                                    
                                    echo $display[0];
                                }
                            }
                            
                            ?>
                            </td>
                        <?php 
                        endif;
                        ?>
                        
                    <?php endforeach; ?>
                    
                    <?php 
                    foreach($this->belongsTo as $bt) {
                        $field_index = $bt->obj->get_external_label_index($bt->obj->name);
                        if($bt->list_display)
                            echo '<td>', $iss->info->{$bt->obj->name}->info->{$bt->obj->fields[$field_index]['name']}, '</td>';
                        
                    }
                    ?>
                    
                    <?php do_action('wp-easy-data-table-newCol-value', $iss->info); ?>
                    <td>
                        <a href="<?php echo add_query_arg(array('action' => 'edit', 'id' => $iss->ID)); ?>#form_title" class="deletesubmit"><?php _e('Edit'); ?></a> |
                        <a href="javascript: if(confirm('<?php _e('Are you sure you want to remove this item?', 'wp-easy-data'); ?>')) location.href='<?php echo add_query_arg(array('action' => 'delete', 'id' => $iss->ID)); ?>';" class="deletesubmit"><?php _e('Remove', 'wp-easy-data'); ?></a>
                        <?php do_action('wp-easy-data-table-actions', $iss->info); ?>
                    </td>
                </tr>
                <?php 
                do_action('wp-easy-data-table-afterRow', $iss); 
            }
            ?>
        </table>
    <?php $this->print_bulk_actions(); ?>
    </form>
</div>



<h2 id="form_title"><?php echo $titleForm; ?></h2>

<form id="wp-easy-data_form" enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <input type="hidden" name="id" value="<?php if (isset($itemEdit->info->ID)) echo $itemEdit->info->ID; ?>">
    <p class="submit">
        <input type="submit" class="button-primary" name="crud_submit" value=" <?php _e('Save', 'wp-easy-data'); ?> ">
    </p>
    <table class="form-table">
        <tbody>
            
            <?php 
            if (isset($itemEdit)) $passItem = $itemEdit;
            do_action('wp-easy-data-form-beggining', $passItem); 
            ?>
            <?php      
            $i = 1;
            foreach ($this->fields as $field) :
                
                $value = "";
                if (isset($itemEdit)) {
                    if (isset($itemEdit->info->$field['name']))
                        $value = $itemEdit->info->$field['name'];
                }
                if ($field['type'] == 'hasManyAndBelongsTo')
                    $value = $itemEdit->info->ID;
                
                if(is_array($belongsTo_index[$i])) {
					
                	foreach($belongsTo_index[$i] as $belongsTo_name) {
						$this->print_row_belongsTo($belongsTo_name, $itemEdit);
                	}
                }
				
				
				if(is_array( $hasManyAndBelongsTo_index[$i] )) {
					foreach ($hasManyAndBelongsTo_index[$i] as $hasManyAndBelongsTo_name) {
						$this->print_row_hasManyAndBelongsTo($hasManyAndBelongsTo_name, $itemEdit);
					}
				}
                
                if ($field['type'] != 'hiddenInt' && $field['type'] != 'hiddenText') : 
                ?>
                
                    <tr class="form-field">
                        
                        <th scope="row"><label for="<?php echo $field['name']; ?>"><?php echo $field['display_name']; ?></label></th>
                        
                        <td>
                        
							<?php                         
							
							if ($field['type'] == 'file') : 
							
								if (empty($itemEdit->info->$field['name']) && $editMode) {
									_e('No file uploaded yet. Choose one using the field bellow.', 'wp-easy-data');
								} 
								if (!empty($itemEdit->info->$field['name']) && $editMode) {
									if (wp_attachment_is_image($itemEdit->info->$field['name'])) {
										echo wp_get_attachment_image($itemEdit->info->$field['name']);
									} else {
										echo "<a href='" . wp_get_attachment_url($itemEdit->info->$field['name']) . "'>";
										echo "<img src='".wp_mime_type_icon( 'document' )."' >";
										echo "</a>";
									}
									
									?>
									
									<a href="<?php echo add_query_arg(array('action' => 'deletefile', 'filetype' => $field['name'], 'id' => $itemEdit->info->ID)); ?>">
									<?php _e('Delete File', 'wp-easy-data'); ?>
									</a>
									<br><br>
									<?php 
								}
													
							endif;
		
							if ($field['type'] == 'textfield')
								$value = htmlspecialchars($value);
							
							$this->print_field($field, $value);
		
							?>
                        
							<small>
								<?php echo $field['description']; ?>
							</small>
                       	</td>
                    </tr>
                <?php 
                else : #hidden fields
                   $this->print_field($field, $value);
                endif;
                
             $i++ ;
            endforeach;
            
            if(is_array($belongsTo_index[0])) {
				foreach($belongsTo_index[0] as $belongsTo_name) {
					$this->print_row_belongsTo($belongsTo_name, $itemEdit);
				}
			} 
			
			if(is_array( $hasManyAndBelongsTo_index[0] )) {
				foreach ($hasManyAndBelongsTo_index[0] as $hasManyAndBelongsTo_name) {
					$this->print_row_hasManyAndBelongsTo($hasManyAndBelongsTo_name, $itemEdit);
				}
			}
            
            
            ?>
            <?php do_action('wp-easy-data-form-end', $itemEdit); ?>
            
        </tbody>
    </table>
    <p class="submit">
        <input type="submit" class="button-primary" name="crud_submit" value=" <?php _e('Save', 'wp-easy-data'); ?> ">
    </p>
</form>
