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
$query = "SELECT id, api_name, api_description, api_version, api_table, api_operations, api_users FROM api_data WHERE api_name = '$safe_table_name'";

$result = $conn->query($query);

if ($result->num_rows > 0) {
  
    while($row = $result->fetch_assoc()) {
$api_name = $row["api_name"];
$api_description = $row["api_description"];
$api_version = $row["api_version"];
$api_table = $row["api_table"];
$api_operations = $row["api_operations"];
$api_users = $row["api_users"];
     }

}  
        ?>

        
        <section style="margin-top: 30px;">
	
            <h3>API - <?php echo $view_table ?></h3>

<?php
$table = 'api_'.$safe_table_name;
$t_query = "SHOW TABLES LIKE '$table'";
$t_result = $conn->query($t_query);

if (mysqli_num_rows($t_result) > 0) {

?>
<form action="<?php echo admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">

			<fieldset class="row-fieldset" id="table-name">
				<label id="table-name">Name:</label>
				<!-- EPA Added API Prefix -->
<span style="position: relative; top: 2px;">
				
api_<?php echo $api_name; ?>
</span>
			</fieldset>
<div class="clear"></div>
		
		<fieldset class="row-fieldset" id="table-endpoint">
				<label id="table-endpoint">Endpoint:</label>
				<!-- EPA Added API Prefix -->
				<span style="position: relative; top: 2px;">
<a href="https://developer.epa.gov/api/index.php/records/api_<?php echo $api_name; ?>">https://developer.epa.gov/api/index.php/records/api_<?php echo $api_name; ?></a><br />
Swagger Specification: <a href="https://developer.epa.gov/api/index.php/openapi/">https://developer.epa.gov/api/index.php/openapi/</a><br />

				 </span>
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

<?php if( current_user_can('administrator') ) { ?>
<fieldset class="row-fieldset" id="api-operation">
<label id="api-operation">Operation(s):</label>
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

<?php if ($api_operations == 'read, update') { ?>
  <option value="read, update" selected>Write/Update</option>
<?php } else { ?>
  <option value="read, update">Write/Update</option>
<?php } ?>


</select>
</fieldset>
<?php } ?>

<div class="clear"></div>	
<?php if( current_user_can('administrator') ) { ?>
<fieldset class="row-fieldset" id="api-users">
<label id="api-version">User(s):</label>
<select class="api-users" name="api_users[]" id="api-users" multiple>
  <?php
    $blogusers = get_users('blog_id=1&orderby=nicename&role=subscriber');
    $user_selected = explode(',',$api_users);
    foreach ($blogusers as $user) {
if (in_array($user->ID, $user_selected)) {
  echo '<option value="' . $user->ID . '" selected="selected">'. $user->user_email .'</option>';
} else {
  echo '<option value="' . $user->ID . '">'. $user->user_email .'</option>';
}
    }
?>

</select>

</fieldset>
<?php } ?>

<div class="clear"></div>

<fieldset class="row-fieldset" id="csv_upload">
<label id="upload-csv">Upload CSV</label>: <input type='file' name='csv_data' />
</fieldset>

<div class="clear"></div>
	    
  <input type="hidden" name="action" value="update_db_table">
  <input type="hidden" name="data" value="<?php echo $api_name ?>">
		
<fieldset class="row-fieldset" id="api-submit">
<button type="submit" >Update API</button>
</fieldset>



</form>	
	    
<?php
} else {
echo '<strong>The table associated with this API does not exisit. Please contact an administrator to resolve this issue.</strong>';
}
?>
	    
    </div>

<?php } // END cdbt_view_table_data

function cdbt_view_table_data_page_styles() {
	?>
<style>
/* EPA API name and Description Added */
	.api-field, #table-name, #table-endpoint,
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
	#upload-csv {
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
$api_users= sanitize_text_field(implode(",",$_POST['api_users']));

			if($_FILES['csv_data']['name']){
			$api_table_name = 'api_'.$api_name;
// Begin Update Process by Truncating Table
			$truncate="TRUNCATE TABLE $api_table_name";
			mysqli_query($conn,$truncate);
				
			$arrFileName = explode('.',$_FILES['csv_data']['name']);

// Determine Column Names
$get_column_name = "SHOW COLUMNS FROM $api_table_name";
$col_name = mysqli_query($conn, $get_column_name);

while($row = $col_name->fetch_assoc()){
    $columns[] = $row['Field'];
}
$column_name_final = implode(',', array_slice($columns, 1));
				
// Determine Number of Columns
$get_column_count = mysqli_query($conn, "SELECT * FROM $api_table_name");
$col_count = mysqli_num_fields($get_column_count)-1;


			if($arrFileName[1] == 'csv'){
				$handle = fopen($_FILES['csv_data']['tmp_name'], "r");
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

// Set Item Variables based on Data in Spreadsheet
$s = 0;
$i = -1;
for ($k = 0 ; $k <= $col_count; $k++){ 
$i++;
$s++;
$item[$s] = mysqli_real_escape_string($conn,$data[$i]);
}			

// Set values to Item Variables
$myvars = '';
for ($j=1; $j<=$col_count; $j++)
{
   $myvars .= "'".$item[$j]."',";   
}
$myvars = substr($myvars,0,-1);		
$myvars = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $myvars);
					
$import = "INSERT INTO $api_table_name($column_name_final) VALUES (".$myvars.")";
					

					mysqli_query($conn,$import);
				}
				fclose($handle);
			}
		}

// Update API Metadata
if( current_user_can('administrator') ) {
$update_api_q = "UPDATE api_data SET api_description = '$api_description', api_version = '$api_version', api_operations = '$api_operations', api_users = '$api_users' WHERE api_name = '$api_name'";
mysqli_query($conn, $update_api_q);
} else {
$update_api_q = "UPDATE api_data SET api_description = '$api_description', api_version = '$api_version' WHERE api_name = '$api_name'";
mysqli_query($conn, $update_api_q);	
}



//echo "<script type='text/javascript'>alert('$values');</script>";
	
        $succuss_url_redirect = admin_url( "admin.php?page=create-db-tables&update_table_success=true" );
        wp_redirect( $succuss_url_redirect );

}
?>
