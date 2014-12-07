<?php
//File that includes all of the functions for our dive planner app!


//set the initial values for the max depth field
function initialize($db,$POST){
	if(isset($POST['init'])){
		ob_start();
		refreshVals($db);
		$depth_html = ob_get_clean();

		//latest profile ID
		$sql = "SELECT `profile_id` FROM `dives` ORDER BY `profile_id` DESC LIMIT 1";

		if(!$result = mysqli_query($db, $sql)){
			//if no result, there was an error
			echo "MySQL error:".mysqli_error();
		}
		//if there is no mysql error, see if any rows were returned from the query
		else if(mysqli_num_rows($result) == 0){
			// no previous profiles ids
			$pid = 1;
		}
		else {
			// most recent profile id
			$profid = mysqli_fetch_assoc($result);
			$pid = $profid['profile_id'] + 1;
		}

		$data['html'] = $depth_html;
		$data['pid'] = $pid;
		echo json_encode($data, true);

	//send error
	} else echo "No rows returned by database.";
}

function refreshVals($db) {
	//set sql statement
	$sql = "SELECT DISTINCT `depth` from `bottom_time` ORDER BY `depth` ASC";
	//see if there is a result when we run the query
	if(!$result = mysqli_query($db, $sql)){
		//if no result, there was an error
		echo "MySQL error:".mysqli_error();
	}
	//if there is no mysql error, see if any rows were returned from the query
	else if(mysqli_num_rows($result)){
		$response = "<option value='' selected disabled>Choose...</option>";
		for($i=0;$i<mysqli_num_rows($result);$i++){
			$data = mysqli_fetch_assoc($result);
			$response .= "<option value='{$data['depth']}'>{$data['depth']}</option>";
		}
		echo $response;
	}
}


/**If a specific depth has been selected, retrieve the bottom times for that depth
*  Field affected: Bottom Time(minutes)
====================================================================================
*  [Need Fix] Condition: Based on initial PG and depth, limit selection to be less than actual_bottom_time
====================================================================================
*/
function selectDepth($db, $POST){
	if(isset($POST['depth'])){
		//set sql statement
		$sql = "SELECT `time` from `bottom_time` WHERE `depth` = '{$POST['depth']}' ORDER BY `time` ASC";
		//see if there is a result when we run the query
		if(!$result = mysqli_query($db, $sql)){
			//if no result, there was an error
			echo "MySQL error:".mysqli_error();
		}
		//if there is no mysql error, see if any rows were returned from the query
		else if(mysqli_num_rows($result)){
			$response = "<option value='' selected disabled>Choose...</option>";
			for($i=0;$i<mysqli_num_rows($result);$i++){
				$data = mysqli_fetch_assoc($result);
				$response .= "<option value='{$data['time']}'>{$data['time']}</option>";
			}
			echo $response;
		}
		//send error
	}else echo "No rows returned by database.";
}
/**If a specific bottom time has been selected, retrieve the surface intervals for that depth
*  Field affected: Surface Interval (minutes)
*/
function selectBottomTime($db, $POST){
	
	if(isset($_POST['bottom_time'])){
		//set sql statement
		$sql = "SELECT `start_time`, `end_time` from `surface_interval` WHERE `init_pressure_group` IN ( SELECT `pressure_group` FROM `bottom_time` WHERE `depth` = '{$POST['depth_selected']}' AND `time` = '{$POST['bottom_time']}') ORDER BY `end_time` ASC";

		//see if there is a result when we run the query
		if(!$result = mysqli_query($db, $sql)){
			//if no result, there was an error
			echo "MySQL error:".mysqli_error();
		}
		//if there is no mysql error, see if any rows were returned from the query
		else if(mysqli_num_rows($result)){
			$response = "<option value='' selected disabled>Choose...</option>";
			for($i=0;$i<mysqli_num_rows($result);$i++){
				$data = mysqli_fetch_assoc($result);
				$response .= "<option value='{$data['end_time']}'>{$data['start_time']} - {$data['end_time']}</option>";
			}
			echo $response;
		}
		//send error
	}else echo "No rows returned by database.";
}
/**When submit button is pressed, manage add dive to dive table
*  Test: echo's dive row back to client console
*  Everything goes on here for testing
*  VICTOR: Story time!
*/
function addDive($db, $POST){
	//references
	$profileID = $POST['pid'];
	$depth = $POST['depth_select'];
	$time = $POST['bottom_time_select'];
	$surfInt = $POST['surface_int_select'];
	$diveNum = $POST['diveNumber'];
	$safetyDepth = $POST['safety_depth_select'];
	if ($safetyDepth != 0) {
		$safetyTime = $POST['safety_time_select'];
	}
	else {
		$safetyTime = 0;
	}
	
	//Check if "New dive" was selected
	if ($diveNum == 0) {
		// NEW DIVE
		//Lets check our previous dive... and add one
		$diveNum = getDiveNum($db, $profileID);
		//Okay so the post surface interval pg of my previous dive will now be my current initial pg. 
		$initialPG = getInitialPG($db, $profileID, $diveNum-1);
		
		//in order to get an accurate pg, need to calculate the residual(from last dive) + time and send that to the getPostDivePG function
		//VICTOR: You can still calculate the residual time by getting the initial PG of current dive (which is also the postsurfpg of previous dive) with the next dive depth
		//RESIDUAL NITROGEN IS AFFECTED BY CURRENT INITIAL GROUP and the change of depth. 
		
		//Get residual time
		//I'm gonna have to be careful how deep im gonna go this time... My residual time will increase the deeper I go. Fortunately, I know my initial pg
		$residualTime = getResidualTime($db, $initialPG, $depth);
		$totalTime = $residualTime + $time;
		
		//After diving with RTN, you come up with a new post dive pressure group
		$postDivePG = getPostDivePG($db, $depth, $totalTime);
		
		//Chillin out on the boat while your postDivePG calms down. Now you have a new one!
		$postSurfacePG = getPostSurfaceIntPG($db, $postDivePG, $surfInt);	
		
		//insert values into table
		$sql = "INSERT INTO `dives` VALUES ('$profileID', '$diveNum', '$initialPG', '$depth', 
		'$time', '$postDivePG', '$surfInt', '$postSurfacePG', '$residualTime') ";
		
		//Wait... what is the point of this? Oh wells, yolo!
		storeStops($db, $profileID, $diveNum, $safetyDepth, $safetyTime);

	}
	else {
		// EDIT EXISTING DIVE
		$diveNum = $POST['diveNumber'];
		$sql = "UPDATE `dives` SET `depth` = '$depth', `time` = '$time', `surf_int` = '$surfInt' WHERE `profile_id` = '$profileID' AND `dive_num` = '$diveNum'";
	}
	//Adds or updates dive
	mysqli_query($db, $sql);
	
	// NEED TO CALL A FUNCTION TO UPDATE PRESSURE GROUPS HERE
	updateDatabase($db, $profileID);

	$diveInfo = getDives($db, $diveNum, $profileID);

	echo $diveInfo;
}
/** Update Everything!
============================================ Start
* What: goes through dives in database and updates pressure groups due to modified depths, times, and surface intervals
* Why: could reduce code regarding calculation
*/
function updateDatabase($db, $profileID){
	//Select every single row
	$sql = "SELECT * FROM `dives` WHERE `profile_id` = '$profileID'";
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	$num_dives = mysqli_num_rows($result);
	if($num_dives == 0) return;
	$prevDive = 0;
	
	for($i=1;$i<=$num_dives;$i++){
	
			$data = mysqli_fetch_assoc($result);
			//Get important values: dive_num, initialpg, depth, time, surface int
			$diveNum = $data['dive_num'];
			$depth = $data['depth'];
			$time = $data['time'];
			$surfInt = $data['surf_int'];
		//If dive num is not consecutive, organize divenums.
		//Ex. $diveNum = 2 $prevDive = 0
		// $diveNum row 2 needs to change diveNum to 1
		if($diveNum != ($prevDive +1)){
			updateDiveNum($db, $diveNum, $prevDive +1);
			$diveNum = $prevDive +1;
		}
		
		//If first row
		if($i == 1){
			
			//get postDivePG. Time has no residual effect due to this being the first dive
			$postDivePG = getPostDivePG($db, $depth, $time);
			$postSurfPG = getPostSurfaceIntPG($db, $postDivePG, $surfInt);
			//Begin to update database row
			$updateSql = "UPDATE `dives` SET `post_dive_pg` = '$postDivePG', `post_surf_int_pg` = '$postSurfPG' WHERE `profile_id` = '$profileID' AND `dive_num` = '$diveNum'";
			if(!$result2 = mysqli_query($db, $updateSql)) return "MySQL error: ".mysqli_error($db);
			
		}else{//Every row
			$data = mysqli_fetch_assoc($result);
			updateOthers($db, $prevDive, $data);

		}
		$prevDive++;
	}
	
}
//Update dive num if user deleted a dive
function updateDiveNums($db, $diveNum, $updateNum){
	$updateNum = "UPDATE `dives` SET `dive_num` = '$updateNum' WHERE `profile_id` = '$profileID' AND `dive_num` = '$diveNum'";
	if(!$result2 = mysqli_query($db, $updateNum)) return "MySQL error: ".mysqli_error($db);
}

//updates rows greater than the first. used in updateDatabase else statement
//requires $prevRow to get previous postSurfIntPG to put into current initialPG 
function updateOthers($db, $prevRow, $data){
	$profileID = $data['profile_id'];
	$diveNum = $data['dive_num'];
	$depth = $data['depth'];
	$time = $data['time'];
	$surfaceInt = $data['surf_int'];
	
	//get initial pgroup
	$sql = "SELECT `post_surf_int_pg` FROM `dives` WHERE `profile_id` = '$profileID' AND `dive_num` = '$prevRow'";
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	$initialResult = mysqli_fetch_assoc($result);
	$initialPG = $initialResult['post_surf_int_pg'];
	
	//Get residual time
	$residualTime = getResidualTime($db, $initialPG, $depth);
	$totalTime = $residualTime + $time;
	$postDivePG = getPostDivePG($db, $depth, $totalTime);
	$postSurfIntPG = getPostSurfaceIntPG($db, $postDivePG, $surfaceInt);
	
	//Time to update this row
	$updateSql = "UPDATE `dives` SET `init_pg`= '$initialPG', `post_dive_pg` = '$postDivePG', `post_surf_int_pg` = '$postSurfIntPG' WHERE `profile_id` = '$profileID' AND `dive_num` = '$diveNum'";
	if(!$result2 = mysqli_query($db, $updateSql)) return "MySQL error: ".mysqli_error($db);
	
}
/**============================================================================END
*/


/**Gets the LASTEST dive number
*  Used: AddDive will insert the next incremented dive number to dives
*/
function getDiveNum($db, $profileID){
	$sql = "SELECT `dive_num` FROM `dives` WHERE `profile_id` = '$profileID'";
	
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	$num_dives = mysqli_num_rows($result);
	//if empty table returned, there is no dive yet
	if($num_dives == 0) $dive_num = 1; 
	else {
		$dive_num = $num_dives + 1;
	}
	return $dive_num;
}

/**Gets the post_surface_int_pg from previous dive
*  Used: AddDive sets the Initial Pressure group of current dive.
*/
function getInitialPG($db, $profileID, $prevDiveNum){
	//if no previous dives, initialize first one
	$sql = "SELECT `post_surf_int_pg` FROM `dives` WHERE `profile_id` = '$profileID' AND `dive_num` = '$prevDiveNum'";
	//error_log("getInitPG: $sql");
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	//if empty table returned, there is no dive yet
	if(mysqli_num_rows($result) == 0) $init_pressure_group = null; 
	//else we have the post_surf_int_pg
	else {
		//take last dive num and grab post_surf_int_pg from results
		$ipg = mysqli_fetch_assoc($result);
		$last_dive = end($ipg);
		$init_pressure_group = $last_dive;
	}
	return $init_pressure_group;	
}
/**Get the post dive pressure group
*  The $time will be the Total time 
*  
*/
function getPostDivePG($db, $depth, $time){

	$sql = "SELECT `pressure_group` FROM `bottom_time` WHERE `depth` = '$depth' AND `time` >= '$time'";
	//error_log("getPostDivePG: $sql");
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	if(mysqli_num_rows($result) == 0) return "broken";
	else {
		$pdpg = mysqli_fetch_assoc($result);
		return $pdpg['pressure_group'];
	}
}


/**Get residual time. Pressure group -> Depth = Residual time
*  Used: getPostDivePG will need to add residual time to $time to get total time.
* EDIT: Residual time is calculated with the PG after the surface interval...
* EDIT2: The initial pressure group IS the PG after the surface interval (of the previous dive)
*/
function getResidualTime($db, $initialPG, $depth){
	if($initialPG == null) return 0;
	$sql = "SELECT `residual_time` FROM `residual_time` WHERE `pressure_group` = '$initialPG' and `depth` = '$depth'";
	//error_log("getResidTime: $sql");
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	
	if(mysqli_num_rows($result) == 0) return 0;
	else {
		$residualRow = mysqli_fetch_assoc($result);
		return $residualRow['residual_time'];
	}
}


/**Get the post surface interval pressure group
*
*/
function getPostSurfaceIntPG($db, $postDivePG, $surfInt) {
	
	$sql = "SELECT `final_pressure_group` FROM `surface_interval` WHERE `init_pressure_group` = '$postDivePG' AND `end_time` >= '$surfInt'";
	//error_log("getPostSurfaceIntPG: $sql");
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	if(mysqli_num_rows($result) == 0) return "broken";
	else {
		$psipg = mysqli_fetch_assoc($result);
		return $psipg['final_pressure_group'];
	}
	
}

function deleteDive($db, $POST) {
	$sql = "DELETE FROM `dives` WHERE `profile_id` = '{$POST['profileID']}' ORDER BY `dive_num` DESC LIMIT 1";
	if(!$success = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);

	echo getDives($db, $POST['diveNum'] - 1, $POST['profileID']);
}

//Displays on the Planned dives column
function getDives($db, $diveNum, $profileID) {
	$sql = "SELECT `depth`, `time`, `dive_num` FROM `dives` WHERE `profile_id` = '$profileID' ORDER BY `dive_num` ASC";
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	$numRows = mysqli_num_rows($result);
	if($numRows == 0) return "";
	else if($numRows){
		$diveInfo = "";
		$checked = false;
		for($i=1;$i<=$numRows;$i++){
			$data = mysqli_fetch_assoc($result);
			$diveInfo .= "<input type='radio' name='diveRadio' id='$i' value='$i'";
			if (($data['dive_num'] == $diveNum || $i == $numRows) && !$checked) {
				$diveInfo .= " checked";
				$checked = true;
			}
			$diveInfo .= "> <strong>Dive $i:</strong> {$data['depth']} ft. for {$data['time']} min.<br>";
		}
		return $diveInfo;
	}
}

function showDive($db, $POST) {
	//$profileID = 1;
	
	$sql = "SELECT `depth`, `time`, `surf_int`, `dive_num` FROM `dives` WHERE `profile_id` = '{$POST['profileID']}' AND `dive_num` = '{$POST['diveNum']}'";

	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	if(mysqli_num_rows($result) == 0) echo 0;
	else {
		$test = mysqli_fetch_assoc($result);
		//call select Depth to correct bottom time field
		
		echo json_encode($test);
	}
	closeDB($db);

}

//Get dive data
function getDiveData($db, $POST){
	$profileID = $POST['profileID'];
	
	$sql = //"SELECT `dive_num`, `depth`, `time`, `surf_int`, `post_dive_pg`, `post_surf_int_pg`, `residual_time` FROM `dives` WHERE `profile_id` = '$profileID'";
	"SELECT d.dive_num AS dive_num, d.depth AS depth, d.time AS time, d.surf_int AS surf_int, d.post_dive_pg AS post_dive_pg, d.post_surf_int_pg AS post_surf_int_pg, d.residual_time AS residual_time, ss.ss_depth AS ss_depth, ss.ss_time AS ss_time
	FROM `dives` AS d INNER JOIN `safety_stops` AS ss
	WHERE d.profile_id = '$profileID' AND d.profile_id = ss.profile_id AND d.dive_num = ss.dive_num";
	
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	if(mysqli_num_rows($result) == 0) echo 0;
	else {
		$json = mysqli_fetch_all($result, MYSQLI_ASSOC);
		echo json_encode($json);
	}
	closeDB($db);

}

//Simple close db connection function
function closeDB($db){
	mysqli_close($db);
}

function getPrevDiveResidual($db, $profileID, $prevDiveNum){
	$sql = "SELECT `residual_time` FROM `dives` WHERE `profile_id` = '$profileID' AND `dive_num` = '$prevDiveNum'";
	//error_log("getPrev: $sql");
	if(!$result = mysqli_query($db, $sql)) return "MySQL error: ".mysqli_error($db);
	//if empty table returned, there is no dive yet
	if(mysqli_num_rows($result) == 0) $residual_time = 0; 
	//else we have the residual time from the previous dive
	else {
		//take last dive num and grab post_surf_int_pg from results
		$prevDive = mysqli_fetch_assoc($result);
		$residual_time = $prevDive['residual_time'];
		//error_log($residual_time);
	}
	return $residual_time;	
}
//Store safety stops
function storeStops($db, $profileID, $diveNum, $ssDepth, $ssTime){
	
	$sql = "INSERT INTO `safety_stops` VALUES ('$profileID', '$diveNum', $ssDepth, $ssTime)";
	mysqli_query($db, $sql);
	
}


?>