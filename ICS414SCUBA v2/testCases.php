<?php
//require functions file
require 'functions.php';

//initialize the database connection
$db = mysqli_connect('localhost', 'bigoli', 'pasta', 'dive_table');
if ($db->connect_errno) echo'Could not open database connection.';
if(isset($_POST['action'])){
	$action = $_POST['action'];
	switch($action){
	case 'initialize': initialize($db);break;
	case 'select_max_depth': selectDepth($db); break;
	case 'select_bottom_time': selectBottomTime($db); break;
	case 'addingDive': addDive($db, $_POST); break;
	case 'debugMode': testFunctions($db, $_POST);break;
	default: echo "No POST data sent to server."; break;
}

	
	}
else{
	echo "No POST data sent to server.";
}

function testFunctions($db, $POST){
	
}

?>