<?php
//require functions file
require 'functions.php';

//initialize the database connection
$db = mysqli_connect('localhost', 'bigoli', 'pasta', 'dive_table');
if ($db->connect_errno) echo'Could not open database connection.';
if(isset($_POST['action'])){
	$action = $_POST['action'];
	switch($action){
	case 'initialize': initialize($db,$_POST);break;
	case 'select_max_depth': selectDepth($db,$_POST); break;
	case 'select_bottom_time': selectBottomTime($db,$_POST); break;
	case 'addingDive': addDive($db, $_POST); break;
	case 'select_dive_to_edit': showDive($db, $_POST); break;
	case 'get_dive_data': getDiveData($db, $_POST); break;
	case 'delete_lastest_dive': deleteDive($db, $_POST); break;
	case 'refresh': refreshVals($db); break;
	
	default: echo "No POST data sent to server."; break;
	}

}else{
	echo "No POST data sent to server.";
}

?>