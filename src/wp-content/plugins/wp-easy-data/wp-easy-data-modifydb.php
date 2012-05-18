
<?php

?>

<div class="wrap">
    <h2>Gerenciar <?php echo $this->adminName; ?></h2>
    <form method="post" action="?page=<?php echo  ($this->parent_menu == false ? $this->relative_path : $this->menu_name); ?>" class="updated">        
        <p>
	        <?php _e('<b>Alert:</b> Your model is different from your database. In order to continue you have to allow the changes to be made automatically. You can also change either your model or your database manually if you prefer.', 'wp-easy-data'); ?>
	        <br><br>
	        <?php _e('Changes that have to be made to the database:', 'wp-easy-data'); ?>
	        <?php if ($this->changedModel->add) : ?>
	            <b><?php _e('Add fields:', 'wp-easy-data'); ?></b><br>
	            <ul>
	            <?php foreach ($this->changedModel->add as $field) : ?>
	                <li><?php echo $field['name']; ?></li>
	            <?php endforeach; ?>
	            </ul>
	            <br><br>
	        <?php endif; ?>
	        <?php if ($this->changedModel->remove) : ?>
	            <b><?php _e('Remove fields:', 'wp-easy-data'); ?></b><br>
	            <ul>
	            <?php foreach ($this->changedModel->remove as $field) : ?>
	                <li><?php echo $field; ?></li>
	            <?php endforeach; ?>
	            </ul>
	            <?php _e('<b>WARNING: If you remove this fields you will loose any data on them.</b>', 'wp-easy-data'); ?>
	            <br>
	            <?php _e('Tip: If you want to disable a field, but you dont want to delete it from the database table, set it to either "hiddenInt" or "hiddenText" on your model.', 'wp-easy-data'); ?>
	            <br><br>
	        <?php endif; ?>
        </p>
                        
        <p class="submit">
            <input type="submit" class="button-primary" name="wp-easy-data-confirm-db-mod" value=" <?php _e('Confirm changes to the database', 'wp-easy-data'); ?> ">
        </p>
    
    </form>
</div>


