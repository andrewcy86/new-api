<?php
/**
 * Edit Tables Page
 * Since: 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function cdbt_edit_existing_created_table() { 

    $query_string = $_SERVER['QUERY_STRING'];

    // $table is equal to the table name
    parse_str($query_string); ?>
    
    <div class="wrap">
	<h2>Edit Table - <?php echo $table ?>
		<a href="<?php echo admin_url('admin.php?page=create-db-tables'); ?>" class="page-title-action">
			Back
		</a>
	</h2>
    
<?php } // END cdbt_edit_existing_created_table ?>