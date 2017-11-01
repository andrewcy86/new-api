<?php
/**
 * Plugin Name: Create DB Tables
 * Plugin URI:  http://jppreusdev.com/development/wordpress-plugins/create-db-tables/
 * Description: Extremely simple way for developers to create new tables inside the existing WordPress database. Forget the annoying process of opening phpMyAdmin, logging in, then typing out the full SQL command for your new table. With this plugin, everything you need to do is located on one simple to use page, and you don't have to type out any SQL queries! This plugin also keeps record of the tables you've created. It is perfect for the developer who wants to quickly and easily add new database tables in a quick and effective manner.
 * Version:     1.2.1
 * Author:      James Preus | @JPPreusDev
 * Author URI:  http://jppreusdev.com/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
				
/**
 * Current Plugin Version
 */
if ( ! defined( 'CDBT_VERSION' ) ) {
	/**
	 *
	 */
	define( 'CDBT_VERSION', '1.2.1' );
}


require 'create-new-table.php';
require 'pages/table-edit.php';
require 'pages/view-table-data.php';
require_once(ABSPATH . 'wp-config.php');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

function cdbt_create_db_tables_create_menu() {

	//create new top-level menu
	add_menu_page('API Dashboard', 'API Dashboard', 'administrator', 'create-db-tables', 'cdbt_create_db_tables_settings_page' , 'dashicons-editor-table', 81 );
	
	add_submenu_page( 'create-db-tables', 'Add New API', 'Add New API', 'administrator', 'add-new-table', 'cdbt_add_new_table_page' );

}
add_action('admin_menu', 'cdbt_create_db_tables_create_menu');



function cdbt_create_db_tables_settings_page() {
global $conn;
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


    $query_string = $_SERVER['QUERY_STRING'];

    // $table is equal to the table name
    parse_str($query_string);
    
    if(!empty($table)) {
    
        cdbt_edit_existing_created_table();
        
    } elseif(!empty($view_table)) {
        
        cdbt_view_table_data();
        
    } else {
    
    cdbt_plugin_main_settings_page_styles(); ?>

<div class="wrap">
	<h2>API Management Dashboard 
		<?php if( current_user_can('administrator') ) { ?>
		<a href="<?php echo admin_url('admin.php?page=add-new-table'); ?>" class="page-title-action">
			Add New API
		</a>
		<?php } ?>
	</h2>
	
	<?php
	$query = $_SERVER['QUERY_STRING'];
    // $table_name
    parse_str($query);
	?>
	
	<?php
	/**
	 * Alert: New Table Created
	 */
	if ($_SERVER['QUERY_STRING'] == 'page=create-db-tables&create_new_table_success=true&table_name=' . $table_name) { ?>
	<div class="updated">
		<p><strong>Success!</strong> The following table has been added to the database: "<span style="font-weight: 800;"><?php echo $table_name; ?></span>"</p>
	</div>
	<?php } ?>


	<?php
	/**
	 * Alert: Error Creating Table
	 */
	if ($_SERVER['QUERY_STRING'] == 'page=create-db-tables&create_new_table_success=false') { ?>
	<div class="error">
		<p><?php _e( '<strong>Error:</strong> Your table could not be created. Check your input and please try again.' ); ?></p>
	</div>
	<?php } ?>

   
    <?php
    /**
	 * Alert: Duplicate id Row
	 */
    $query = $_SERVER['QUERY_STRING'];
    // $create_new_table_success
    parse_str($query);  
    if($create_new_table_success == 'id') { ?>
        <div class="error">
            <p><?php _e( '<strong>Error:</strong> The row named "id" is created by default. Do not create one manually. Please try again without creating a row named "id".' ); ?></p>
        </div>
    <?php } ?>

	
    <?php
	/**
	 * Alert: No Data Submitted
	 */		
	if ($_SERVER['QUERY_STRING'] == 'page=create-db-tables&create_new_table_success=null') { ?>
	<div class="error">
		<p><?php _e( '<strong>Error:</strong> You did not submit any data. Fill out the form and try again.' ); ?></p>
	</div>
	<?php } ?>
	
		<?php
	/**
	 * Alert: Update Submiteed Succesfully
	 */
	if ($_SERVER['QUERY_STRING'] == 'page=create-db-tables&update_table_success=true') { ?>
	<div class="updated">
		<p><strong>Success!</strong> The API Information has succesfully been updated.</p>
	</div>
	<?php } ?>

	
	
	<section style="margin-top: 30px;">
	
        <h3>APIs Created</h3>
        
		<table id="db-tables-list">
            <colgroup>
                <col span="1" style="width: 75%;">
                <?php // <col span="1" style="width: 20%;"> ?>
                <col span="1" style="width: 25%;">
            </colgroup>
            <tbody class="db-list-body">
                <tr class="db-list-header">
                    <th><h4>API Name</h4></th>
                    <?php // was column header for edit column <th></th> ?>
                    <?php if( current_user_can('administrator') ) { ?><th></th><?php } ?>
                </tr>
                <?php

$check_api_q = "SELECT api_name FROM api_data";

$check_api = mysqli_query($conn,$check_api_q); 

if(mysqli_num_rows($check_api) == 0 ){
echo '<tr><td>You have not created any APIs yet...</td></tr>';
} else {
  while ($row = mysqli_fetch_assoc($check_api)) {
                $edit_table_url = 'admin.php?page=create-db-tables&table=' . $row["api_name"];
                $view_table_url = 'admin.php?page=create-db-tables&view_table=' . $row["api_name"];
                ?>
                <tr class="table-row">
                    <td>
                        <?php echo $row["api_name"] ?> [<a class="table-links" href="<?php echo admin_url($view_table_url) ?>" title="<?php echo $row["api_name"] ?>">Edit</a>]
                    </td>
                    <?php /*
                    <td class="edit-col">
                        <a class="table-links-edit" href="<?php echo admin_url($edit_table_url) ?>" title="<?php echo $row["api_name"] ?>">Edit</a>
                    </td>
                    */ ?>
                    <?php if( current_user_can('administrator') ) { ?>
			<td class="delete-col">
                        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                            <input type="hidden" name="action" value="delete_db_table">
                            <input type="hidden" name="db_table" value="<?php echo $row["api_name"] ?>">
                            <button onclick="return confirm('Are you sure you want to delete this table? All the data inside the table will be permanently deleted. You will not be able to recover the deleted data.')" type="submit" class="table-links-delete">Delete</button>
                        </form>
                    </td>
		   <?php } ?>
                </tr>
<?php } }?>

            </tbody>
		</table>
		
		
	</section>
	
	
</div>

<?php 
    } // END else
    
} // END cdbt_create_db_tables_settings_page

function cdbt_add_new_table_page() {
	global $wpdb;
	$prefix = $wpdb->prefix;
	cdbt_add_page_styles();
	?>
<div class="wrap">
	<h2>Add New API
		<a href="<?php echo admin_url('admin.php?page=create-db-tables'); ?>" class="page-title-action">
			Cancel
		</a>
	</h2>
	
	<section style="margin-top: 15px;">
		
		<div style="width: 700px;margin-bottom: 30px;">

			<span><strong>Important Notes:</strong></span>
			<ul>
				<li style="padding-left:15px;">• Your table will automatically include a row named "id" with the type set to "bigint(20)" and includes the auto_increment setting as the first row in the table.</li>
				<li style="padding-left:15px;">• The table's charset is automatically set to the WordPress standard "utf8mb4_unicode_ci".</li>
			</ul>

		</div>
		
		<form id="create-table" method="post" action="<?php echo admin_url('admin-post.php'); ?>">


		
			<input type="hidden" name="action" value="add_table">

			<fieldset class="row-fieldset" id="table-name">
				<label id="table-name">Name:</label>
				<!-- EPA Added API Prefix -->
				<span style="position: relative; top: 2px;"><?php echo $prefix; ?>api_</span><input type="text" class="api-field" name="table_name" size="30" id="table-name">
				<span>(Alphanumeric only, no special charaters.)</span>
			</fieldset>
<div class="clear"></div>
			
			<!-- EPA Added API Description -->
			<fieldset class="row-fieldset" id="api-description">
				<label id="api-description">Drescription:</label>
			    <textarea type="text" class="api-field" name="api_description" rows="3" cols="30" maxlength="255" id="api-description"></textarea>
			</fieldset>
<div class="clear"></div>
			<!-- EPA Added API Version -->
			<fieldset class="row-fieldset" id="api-version">
				<label id="api-version">Version:</label>
			    <input type="text" class="api-field" name="api_version" size="10" id="api-version">
			</fieldset>
			<!-- EPA Added API Operations -->
			<fieldset class="row-fieldset" id="api-operations">
				<label id="api-operations">Operation(s):</label>
<select class="api-operations" name="api_operations" id="api-operations">
  <option value="list">List</option>
  <option value="read">Read</option>
  <option value="read, update">Write/Update</option>
</select>

			</fieldset>
					<!-- EPA Added API Users -->	
					<div class="clear"></div>
			<fieldset class="row-fieldset" id="api-users">
<label id="api-version">User(s):</label>
<select class="api-users" name="api_users" id="api-users" multiple>
  <?php
    $blogusers = get_users('blog_id=1&orderby=nicename&role=subscriber');
    foreach ($blogusers as $user) {
        echo '<option value="' . $user->user_email . '">'. $user->user_email .'</option>';
    }
?>

</select>

</fieldset>
			
			
			<div id="rows">
				
				<fieldset class="row-fieldset" id="1"><label id="row-label">Column: </label><input type="text" class="name-input" name="name[]" placeholder="Name" size="20"><select name="type[]" class="type-input"><optgroup label="Numbers"><option>tinyint</option><option>smallint</option><option>mediumint</option><option selected="">int</option><option>bigint</option><option>decimal</option><option>float</option><option>double</option></optgroup><optgroup label="Date and time"><option>date</option><option>datetime</option><option>timestamp</option><option>time</option><option>year</option></optgroup><optgroup label="Strings"><option>char</option><option>varchar</option><option>tinytext</option><option>text</option><option>mediumtext</option><option>longtext</option></optgroup><optgroup label="Lists"><option>enum</option><option>set</option></optgroup><optgroup label="Binary"><option>bit</option><option>binary</option><option>varbinary</option><option>tinyblob</option><option>blob</option><option>mediumblob</option><option>longblob</option></optgroup><optgroup label="Geometry"><option>geometry</option><option>point</option><option>linestring</option><option>polygon</option><option>multipoint</option><option>multilinestring</option><option>multipolygon</option><option>geometrycollection</option></optgroup></select>&nbsp;&nbsp;(&nbsp;&nbsp;<input type="text" class="count-input" name="count[]" size="5">)&nbsp;&nbsp;<span id="null-label">Null</span><input type="checkbox" class="null-input" name="null[]"><input type="text" class="default-input" name="default[]" placeholder="Default Value" size="20"><span id="unique-label">Unique</span><input type="checkbox" class="unique-input" name="unique[]"></fieldset>
				
			</div>

			<div id="add-row">
				<button type="button" class="add-row button-secondary">Add Column</button>
			</div>
            
            <div id="delete-row">
				<button type="button" class="delete-row button-secondary">Delete Column</button>
			</div>
            
            <div class="clear"></div>
			
			<fieldset>
				<input type="hidden" id="items" name="items" value="1" />
			</fieldset>

			<fieldset>
				<button type="submit" class="button button-primary button-large">Create API</button>
			</fieldset>

		</form>

		<script>
			jQuery(function($) {
				$('.add-row').click(function () {
					$('#items').val(function(i, val) { return +val+1 });
                    var rowNumber = $('#items').val();
					var rowHTML = '<fieldset class="row-fieldset" id="' + rowNumber + '"><label id="row-label">Column: </label><input type="text" class="name-input" name="name[]" placeholder="Name" size="20"><select name="type[]" class="type-input"><optgroup label="Numbers"><option>tinyint</option><option>smallint</option><option>mediumint</option><option selected="">int</option><option>bigint</option><option>decimal</option><option>float</option><option>double</option></optgroup><optgroup label="Date and time"><option>date</option><option>datetime</option><option>timestamp</option><option>time</option><option>year</option></optgroup><optgroup label="Strings"><option>char</option><option>varchar</option><option>tinytext</option><option>text</option><option>mediumtext</option><option>longtext</option></optgroup><optgroup label="Lists"><option>enum</option><option>set</option></optgroup><optgroup label="Binary"><option>bit</option><option>binary</option><option>varbinary</option><option>tinyblob</option><option>blob</option><option>mediumblob</option><option>longblob</option></optgroup><optgroup label="Geometry"><option>geometry</option><option>point</option><option>linestring</option><option>polygon</option><option>multipoint</option><option>multilinestring</option><option>multipolygon</option><option>geometrycollection</option></optgroup></select>&nbsp;&nbsp;(&nbsp;&nbsp;<input type="text" class="count-input" name="count[]" size="5">)&nbsp;&nbsp;<span id="null-label">Null</span><input type="checkbox" class="null-input" name="null[]"><input type="text" class="default-input" name="default[]" placeholder="Default Value" size="20"><span id="unique-label">Unique</span><input type="checkbox" class="unique-input" name="unique[]"></fieldset>';
					$('#rows').append(rowHTML);
				});
                $('.delete-row').click(function () {
                    var rowNumber = $('#items').val();
					$('#items').val(function(i, val) { return +val-1 });
					var rowHTML = '<fieldset class="row-fieldset" id="' + rowNumber + '"><label id="row-label">Column: </label><input type="text" class="name-input" name="name[]" placeholder="Name" size="20"><select name="type[]" class="type-input"><optgroup label="Numbers"><option>tinyint</option><option>smallint</option><option>mediumint</option><option selected="">int</option><option>bigint</option><option>decimal</option><option>float</option><option>double</option></optgroup><optgroup label="Date and time"><option>date</option><option>datetime</option><option>timestamp</option><option>time</option><option>year</option></optgroup><optgroup label="Strings"><option>char</option><option>varchar</option><option>tinytext</option><option>text</option><option>mediumtext</option><option>longtext</option></optgroup><optgroup label="Lists"><option>enum</option><option>set</option></optgroup><optgroup label="Binary"><option>bit</option><option>binary</option><option>varbinary</option><option>tinyblob</option><option>blob</option><option>mediumblob</option><option>longblob</option></optgroup><optgroup label="Geometry"><option>geometry</option><option>point</option><option>linestring</option><option>polygon</option><option>multipoint</option><option>multilinestring</option><option>multipolygon</option><option>geometrycollection</option></optgroup></select>&nbsp;&nbsp;(&nbsp;&nbsp;<input type="text" class="count-input" name="count[]" size="5">)&nbsp;&nbsp;<span id="null-label">Null</span><input type="checkbox" class="null-input" name="null[]"><input type="text" class="default-input" name="default[]" placeholder="Default Value" size="20"><span id="unique-label">Unique</span><input type="checkbox" class="unique-input" name="unique[]"></fieldset>';
                    var rowID = '#' + rowNumber;
					$(rowID).remove();
				});
				$("input.name-input").on({
				  keydown: function(e) {
					if (e.which === 32)
					  return false;
				  },
				  change: function() {
					this.value = this.value.replace(/\s/g, "");
				  }
				});
				$("input.table-name").on({
				  keydown: function(e) {
					if (e.which === 32)
					  return false;
				  },
				  change: function() {
					this.value = this.value.replace(/\s/g, "");
				  }
				});
								$("input.api-description").on({
				  keydown: function(e) {
					if (e.which === 32)
					  return false;
				  },
				  change: function() {
					this.value = this.value.replace(/\s/g, "");
				  }
				});
								$("input.api-version").on({
				  keydown: function(e) {
					if (e.which === 32)
					  return false;
				  },
				  change: function() {
					this.value = this.value.replace(/\s/g, "");
				  }
				});
								$("input.api-operations").on({
				  keydown: function(e) {
					if (e.which === 32)
					  return false;
				  },
				  change: function() {
					this.value = this.value.replace(/\s/g, "");
				  }
				});
			});
		</script>

	</section>
	
</div>

<?php 
}

function cdbt_add_page_styles() {
	?>
<style>
/* EPA API name and Description Added */
	.api-field, #table-name,
	#row-label {
		padding-right:25px;
		font-weight: 600;
	}
	
	#api-description {
		padding-right:0px;
		font-weight: 600;
	}
	
	#api-version {
		padding-right:0px;
		font-weight: 600;
	}
	
	#api-operations {
		padding-right:0px;
		font-weight: 600;
	}
	
	input[type="text"], input[type="checkbox"] {
		margin-right: 10px!important;
	}
	#null-label,
	#unique-label {
		padding-right: 5px;
	}
	.row-fieldset {
		margin-bottom: 15px;
		display: inline-table;
	}
	#rows {
		margin-bottom: 15px;
	}
	#add-row {
		margin-bottom: 20px;
        float: left;
        margin-right: 20px;
	}
</style>
<?php
}


function cdbt_plugin_main_settings_page_styles() {
	?>
<style>
    .table-links {
        text-decoration: none;
    }
    #db-tables-list {
        width: 45%;
        min-width: 300px;
        border: 1px solid #e1e1e1;
        box-shadow: 0 1px 2px #cecece;
    }
    .db-list-header {
        background: #F9F9F9;
        text-align: left;
    }
    .db-list-header th {
        border: 1px solid #FDFDFD;
        padding-left: 15px;
    }
    .db-list-header th h4 {
        margin: 10px 0;
    }
    .table-row td {
        padding: 10px 0 10px 15px;
    }
    .edit-col, .delete-col {
        text-align: center;
        padding-left: 0px!important;
    }
    .table-links-edit, .table-links-delete {
        text-decoration: none;
        letter-spacing: -0.5px;
        transition: 0.4s;
        font-weight: 600;
    }
    .table-links-delete {
        padding: 0px;
        background: none;
        border: 0px;
        color: #0073aa;
    }
    .table-links-delete:hover {
        color: #dd1010;
    }
    .table-links-edit:hover {
        color: #147d00;
    }
    .db-list-body {
        background-color: #fff;
    }
</style>
<?php
}

function cdbt_delete_db_table() {
global $conn;
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
    $db_table = sanitize_text_field($_POST['db_table']);

	if($db_table != null) {

// EPA Remove entry from api_data and PHP Crud File/Folder

		$rm_dirname = ABSPATH . 'api/'.$db_table;
		$remove_api_q = "DELETE FROM api_data WHERE api_name = '$db_table'";
		mysqli_query($conn, $remove_api_q);
		
		$delete_table = 'wp_api_'.$db_table;
		
        $delete_table_statement = "DROP TABLE IF EXISTS $delete_table";
		echo $delete_table_statement;
        mysqli_query($conn, $delete_table_statement);
		
		//array_map('unlink', glob("$rm_dirname/*.*"));
		//rmdir($rm_dirname);
		
        $succuss_url_redirect = admin_url( "admin.php?page=create-db-tables&delete_table_success=true" );
        wp_redirect( $succuss_url_redirect );
        
    } else {

		$null_url_redirect = admin_url( 'admin.php?page=create-db-tables&delete_table_success=null' );
		wp_redirect( $null_url_redirect );

	}
    
}

function cdbt_edit_db_table() {
    echo 'Table Edited...';
    
}

// Create Database Table
add_action( 'admin_post_add_table', 'cdbt_create_new_table' );

// Delete Database Table
add_action( 'admin_post_delete_db_table', 'cdbt_delete_db_table' );

// Edit Database Table
add_action( 'admin_post_edit_db_table', 'cdbt_edit_db_table' );

?>
