<?php
/**
To turn off Testing mode look in the ajax.js file.

*/

//require functions file
require 'functions.php';

//initialize the database connection
$db = mysqli_connect('localhost', 'bigoli', 'pasta', 'dive_table');
if ($db->connect_errno) echo'Could not open database connection.';
if(isset($_POST['action'])){
	$action = $_POST['action'];
	switch($action){
	case 'initialize': initialize($db, $_POST);break;
	case 'select_max_depth': selectDepth($db,$_POST); break;
	case 'select_bottom_time': selectBottomTime($db,$_POST); break;
	case 'addingDive': addDive($db, $_POST); break;
	case 'debugMode': testFunctions($db,$_POST);break;
	default: echo "No POST data sent to server."; break;
}

	
	}
else{
	echo "No POST data sent to server.";
}

///this will handle everything. Create all variable 
function testFunctions($db, $POST){
//Case 1: Adding 3 Dives
	//Add first dive of 60ft for 11 min and surfint of 47
	echo "Test case 1 \n";
	dbsAddDive($db, $POST, 60,11,47);// look at console for values
	dbsAddDive($db, $POST, 70,15,38);
	dbsAddDive($db, $POST, 40,64,72);
	echo "\n";
	//Results of row (post_dive & post_surf pgroups): BB, KE, TF
	
	truncateDives($db);
	
//Case 2: Pressure group increases each dive due to residual time adding to bottom time
	echo "Test case 2 \n";
	dbsAddDive($db, $POST, 60,11,47); 
	/**
	Residual time: 0  + 11 = 11
	60 for 11 -> B
	B -> 47 -> B
	*/
	dbsAddDive($db, $POST, 60,11,47); 
	/*
	Residual time: 11 + 11 = 22
	60 for 22 -> H
	H -> 47 -> C
	*/
	dbsAddDive($db, $POST, 60,11,47); 
	/*
	Residual time: 14 + 11 = 25
	60 for 25 -> I
	I -> 47 -> C
	*/
	//Results of row: BB, HC, IC
	
	truncateDives($db);
	
/**Test whether or not we are preventing users from 
* selecting past the max actual time so they don't die.
*/
}

function dbsAddDive($db, $POST, $depth, $bottomTime, $surfaceInt){
	$POST['depth_select'] = $depth;
	$POST['bottom_time_select'] = $bottomTime;
	$POST['surface_int_select'] = $surfaceInt;
	addDive($db,$POST); 

}

//Empties table "dives" for testing purposes
function truncateDives($db){
	$sql = "TRUNCATE TABLE dives";
	mysqli_query($db, $sql);
}

?>