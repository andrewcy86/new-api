<?php
/**
 * View Table Data Page
 * Since: 1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

function cdbt_view_table_data() { 

    $query_string = $_SERVER['QUERY_STRING'];

    // $table is equal to the table name
    parse_str($query_string);
    
    $safe_table_name = sanitize_text_field($view_table);

    cdbt_view_table_data_page_styles();
    
    ?>
    
    <div class="wrap">
        <h2>Edit API Information
           <a href="<?php echo admin_url('admin.php?page=create-db-tables'); ?>" class="page-title-action">
			 Back
           </a>
        </h2>
        
        <?php
    

	global $conn;
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
            
        /**
         * Select Table Names
         */
$query = "SELECT id, api_name, api_description, api_version, api_table, api_operations FROM api_data WHERE api_name = '$safe_table_name'";

$result = $conn->query($query);

if ($result->num_rows > 0) {
  
    while($row = $result->fetch_assoc()) {
$api_name = $row["api_name"];
$api_description = $row["api_description"];
$api_version = $row["api_version"];
$api_table = $row["api_table"];
$api_operations = $row["api_operations"];
     }

}
        

        ?>

        
        <section style="margin-top: 30px;">
	
            <h3>API - <?php echo $view_table ?></h3>

<form action="<?php echo admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">

			<fieldset class="row-fieldset" id="table-name">
				<label id="table-name">Name:</label>
				<!-- EPA Added API Prefix -->
				<span style="position: relative; top: 2px;">
				
<?php echo $prefix; ?>api_<?php echo $api_name; ?></span></span>
			</fieldset>
<div class="clear"></div>
			
			<!-- EPA Added API Description -->
			<fieldset class="row-fieldset" id="api-description">
				<label id="api-description">Drescription:</label>
			    <textarea type="text" class="api-field" name="api_description" rows="3" cols="30" maxlength="255" id="api_description"><?php echo $api_description; ?></textarea>
			</fieldset>
<div class="clear"></div>
			<!-- EPA Added API Version -->
			<fieldset class="row-fieldset" id="api-version">
				<label id="api-version">Version:</label>
			    <input type="text" class="api-field" name="api_version" size="10" id="api-version" value="<?php echo $api_version; ?>">
			</fieldset>
<div class="clear"></div>			
			
<fieldset class="row-fieldset" id="api-version">
<label id="api-version">Operation(s):</label>
<select class="api-field" name="api_operation" id="api-operation">

<?php if ($api_operations == 'list') { ?>
  <option value="list" selected>List</option>
<?php } else { ?>
  <option value="list">List</option>
<?php } ?>

<?php if ($api_operations == 'read') { ?>
  <option value="read" selected>Read</option>
<?php } else { ?>
  <option value="read">Read</option>
<?php } ?>

<?php if ($api_operations == 'update') { ?>
  <option value="read, update" selected>Write/Update</option>
<?php } else { ?>
  <option value="read, update">Write/Update</option>
<?php } ?>


</select>
</fieldset>

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

<div class="clear"></div>

<fieldset class="row-fieldset" id="csv_upload">
Upload CSV: <input type='file' name='csv_data' /> <input type='submit' name='submit' value='Upload CSV' />
</fieldset>

<div class="clear"></div>
	    
  <input type="hidden" name="action" value="update_db_table">
  <input type="hidden" name="data" value="<?php echo $api_name ?>">
<fieldset class="row-fieldset" id="api-submit">
<button type="submit" >Update API</button>
</fieldset>



</form>
										
						
    </div>

<?php } // END cdbt_view_table_data

function cdbt_view_table_data_page_styles() {
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
<?php } 

// Update API data Database Table
add_action( 'admin_post_update_db_table', 'cdbt_update_db_table' );

function cdbt_update_db_table() {

global $conn;
global $safe_table_name;


// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$api_name = sanitize_text_field($_POST['data']);
$api_description = sanitize_text_field($_POST['api_description']);
$api_version = sanitize_text_field($_POST['api_version']);
$api_operations = sanitize_text_field($_POST['api_operation']);

			if($_FILES['csv_data']['name']){
//echo $api_table_name;
			$api_table_name = 'wp_api_'.$api_name;
			$truncate="TRUNCATE TABLE $api_table_name";
			mysqli_query($conn,$truncate);
				
			$arrFileName = explode('.',$_FILES['csv_data']['name']);

			if($arrFileName[1] == 'csv'){
				$handle = fopen($_FILES['csv_data']['tmp_name'], "r");
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					$item1 = mysqli_real_escape_string($conn,$data[0]);
					$item2 = mysqli_real_escape_string($conn,$data[1]);
					
					//$import="INSERT into '$api_table_name'(name,email) values('$item1','$item2')";
					//mysqli_query($conn,$import);
				}
				fclose($handle);
			}
		}

$update_api_q = "UPDATE api_data SET api_description = '$api_description', api_version = '$api_version', api_operations = '$api_operations' WHERE api_name = '$api_name'";
mysqli_query($conn, $update_api_q);
	
        $succuss_url_redirect = admin_url( "admin.php?page=create-db-tables&update_table_success=true" );
        wp_redirect( $succuss_url_redirect );

}
?>
