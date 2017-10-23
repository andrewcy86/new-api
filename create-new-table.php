<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function cdbt_create_new_table() {

	global $conn;
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
	global $wpdb;

	/**
	 * Sanitize User Input
	 */
	
	/** Table Name Text Input EPA Add API Description, API Version, API Operations*/
	$safe_table_name = sanitize_text_field($_POST['table_name']);
	
	$safe_api_description = sanitize_text_field($_POST['api_description']);
	$safe_api_version = sanitize_text_field($_POST['api_version']);
	$safe_api_operations = sanitize_text_field($_POST['api_operations']);
			
	/** Row Name Text Input */
	$row_names = $_POST['name'];
	$row_types = $_POST['type'];
	$row_defaults = $_POST['default'];
	$row_nulls = $_POST['null'];
	$row_uniques = $_POST['unique'];
	$items_amount = $_POST['items'];
	$sql = '';
	for($i = 0; $i < $items_amount; $i++) {
        
		$safe_row_name = sanitize_text_field($row_names[$i]);
		$safe_row_type = sanitize_text_field($row_types[$i]);
		$safe_row_default = sanitize_text_field($row_defaults[$i]);
		$safe_row_null = $row_nulls[$i];
		$safe_row_unique = $row_uniques[$i];
		
        /**
         * Exit & Prompt Error if a duplicate
         * id row is created.
         */
        if($safe_row_name == 'id') {
            
            $duplicate_url_redirect = admin_url( "admin.php?page=create-db-tables&create_new_table_success=id" );
			wp_redirect( $duplicate_url_redirect );
            
        }
        
		$sql .= $safe_row_name . ' ' . $safe_row_type . ' ';
		if($safe_row_null == true) { $sql = $sql . 'NULL'; } else { $sql = $sql . 'NOT NULL';}
		if($safe_row_default != false) { $sql = $sql . " DEFAULT '" . $safe_row_default . "'"; }
		if($safe_row_unique == true) { $sql = $sql . ' UNIQUE'; }
		$sql = $sql . ', ';
	}
	
	/**
	 * Prepare Table Data
	 */

	// EPA Added API Prefix
	$table_name = $wpdb->prefix . "api_" . $safe_table_name;
	$charset_collate = $wpdb->get_charset_collate();

	if($safe_table_name != null) {

		/**
		 * Create SQL Query From Post Values
		 */
	
		$completed_sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT, ";
		$completed_sql = $completed_sql . $sql;
		$completed_sql = $completed_sql . "UNIQUE KEY id (id) ) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $completed_sql );		
// EPA insert a record into api_data



$insert_api_q = "INSERT INTO api_data (api_name, api_description, api_version, api_table, api_operations)
VALUES ('$safe_table_name', '$safe_api_description', '$safe_api_version', '$table_name', '$safe_api_operations')";

$check_api_q = "SELECT 'id' FROM 'api_data' WHERE 'api_table' = '$table_name'";

$check_api = mysqli_query($conn,$check_api_q); 

if(mysqli_num_rows($check_api) > 0 ){
$error_url_redirect = admin_url( 'admin.php?page=create-db-tables&create_new_table_success=false' );
wp_redirect( $error_url_redirect );
} else {
 mysqli_query($conn, $insert_api_q);	
}


/**
//EPA Create the coressponding PHP CRUD index.php file and folder

	$dirname = $_SERVER['DOCUMENT_ROOT']."/api/".$safe_table_name;  
    $filename = $_SERVER['DOCUMENT_ROOT']."/api/".$safe_table_name."/index.php";  
	  
    if (!file_exists($dirname)) {  
        //echo "The directory {$dirname} exists";  
    //} else {  
        mkdir($dirname, 0755);  
//$content = file_get_contents($_SERVER['DOCUMENT_ROOT']."/api/"."api_example.php");
	
	
$file = 'api_example.php';  
$appendBefore = '<?php 
require_once($_SERVER["DOCUMENT_ROOT"]."/api/"."db_login.php");
header("Access-Control-Allow-Origin: *");
$con = mysqli_connect($db_host,$db_username,$db_password,$db_name);

//MOD ID

$base_dir = basename(__DIR__);

$query = "SELECT id, api_name, api_description, api_version, api_table, api_operations FROM api_data WHERE api_name = \'$base_dir\'";

$result = $con->query($query);

if ($result->num_rows > 0) {
  
    while($row = $result->fetch_assoc()) {
$api_name = $row["api_name"];
$api_description = $row["api_description"];
$api_version = $row["api_version"];
$api_table = $row["api_table"];
$api_operations = $row["api_operations"];
     }

}
$con->close();

$ops_array = explode(", ", $api_operations);';
 
$temp = file_get_contents($_SERVER['DOCUMENT_ROOT']."/api/".$file);
$content = $appendBefore.$temp;
file_put_contents($file, $content);


	$fp = fopen($filename,"wb"); 
	fwrite($fp,$content); 
	fclose($fp); 
       // echo "The directory {$dirname} was successfully created.";  
    }  
*/	
	
	$succuss_url_redirect = admin_url( "admin.php?page=create-db-tables&create_new_table_success=true&table_name=$table_name" );
    wp_redirect( $succuss_url_redirect );

	} else {

		$null_url_redirect = admin_url( 'admin.php?page=create-db-tables&create_new_table_success=null' );
		wp_redirect( $null_url_redirect );

	}
	
}

// EPA Close Connection
mysqli_close($conn);


?>
