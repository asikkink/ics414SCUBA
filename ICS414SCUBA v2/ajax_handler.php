<?php
//initialize the database connection
$db = mysqli_connect('localhost', 'bigoli', 'pasta', 'dive_table');
if ($db->connect_errno) echo'Could not open database connection.';
if(isset($_POST['action'])){
$action = $_POST['action'];
switch($action){
case 'initialize': initialize($db);break;
case 'select_max_depth': selectDepth($db); break;
case 'select_bottom_time': selectBottomTime($db); break;
case 'addingDive': addDive($db); break;
default: echo "No POST data sent to server."; break;
}
}else{
echo "No POST data sent to server.";
}
//set the initial values for the max depth field
function initialize($db){
if(isset($_POST['init'])){
//set sql statement
$sql = "SELECT DISTINCT `depth` from `bottom_time` ORDER BY `depth` ASC";
//see if there is a result when we run the query
if(!$result = mysqli_query($db, $sql)){
//if no result, there was an error
echo "MySQL error:".mysqli_error($db);
}
//if there is no mysql error, see if any rows were returned from the query
else if(mysqli_num_rows($result)){
$response = "";
for($i=0;$i<mysqli_num_rows($result);$i++){
$data = mysqli_fetch_assoc($result);
$response .= "<option value='{$data['depth']}'>{$data['depth']}</option>";
}
echo $response;
}
//send error
}else echo "No rows returned by database.";
}
//if a specific depth has been selected, retrieve the bottom times for that depth
function selectDepth($db){
if(isset($_POST['depth'])){
//set sql statement
$sql = "SELECT `time` from `bottom_time` WHERE `depth` = '{$_POST['depth']}' ORDER BY `time` ASC";
//see if there is a result when we run the query
if(!$result = mysqli_query($db, $sql)){
//if no result, there was an error
echo "MySQL error:".mysqli_error();
}
//if there is no mysql error, see if any rows were returned from the query
else if(mysqli_num_rows($result)){
$response = "";
for($i=0;$i<mysqli_num_rows($result);$i++){
$data = mysqli_fetch_assoc($result);
$response .= "<option value='{$data['time']}'>{$data['time']}</option>";
}
echo $response;
}
//send error
}else echo "No rows returned by database.";
}
//if a specific bottom time has been selected, retrieve the surface intervals for that depth
function selectBottomTime($db){
if(isset($_POST['bottom_time'])){
//set sql statement
$sql = "SELECT `end_time` from `surface_interval` WHERE `init_pressure_group` IN ( SELECT `pressure_group` FROM `bottom_time` WHERE `depth` = '{$_POST['depth_selected']}' AND `time` = '{$_POST['bottom_time']}') ORDER BY `end_time` ASC";
//see if there is a result when we run the query
if(!$result = mysqli_query($db, $sql)){
//if no result, there was an error
echo "MySQL error:".mysqli_error();
}
//if there is no mysql error, see if any rows were returned from the query
else if(mysqli_num_rows($result)){
$response = "";
for($i=0;$i<mysqli_num_rows($result);$i++){
$data = mysqli_fetch_assoc($result);
$response .= "<option value='{$data['end_time']}'>{$data['end_time']}</option>";
}
echo $response;
}
//send error
}else echo "No rows returned by database.";
}
//When submit button is pressed, manage current input
function addDive($db){
if(isset($_POST['depth'])){
$message = "wrong answer";
echo "<script type='text/javascript'>console.log(".$message.");</script>";
}
}
/*
//set the initial values for the max depth field
if(isset($_POST['init']) && !isset($_POST['addingDive'])){
//set sql statement
$sql = "SELECT DISTINCT `depth` from `bottom_time` ORDER BY `depth` ASC";
//see if there is a result when we run the query
if(!$result = mysqli_query($db, $sql)){
//if no result, there was an error
echo "MySQL error:".mysqli_error();
}
//if there is no mysql error, see if any rows were returned from the query
else if(mysqli_num_rows($result)){
$response = "";
for($i=0;$i<mysqli_num_rows($result);$i++){
$data = mysqli_fetch_assoc($result);
$response .= "<option value='{$data['depth']}'>{$data['depth']}</option>";
}
echo $response;
}
//send error
else echo "No rows returned by database.";
}
//if a specific depth has been selected, retrieve the bottom times for that depth
else if(isset($_POST['depth']) && !isset($_POST['addingDive']) ){
//set sql statement
$sql = "SELECT `time` from `bottom_time` WHERE `depth` = '{$_POST['depth']}' ORDER BY `time` ASC";
//see if there is a result when we run the query
if(!$result = mysqli_query($db, $sql)){
//if no result, there was an error
echo "MySQL error:".mysqli_error();
}
//if there is no mysql error, see if any rows were returned from the query
else if(mysqli_num_rows($result)){
$response = "";
for($i=0;$i<mysqli_num_rows($result);$i++){
$data = mysqli_fetch_assoc($result);
$response .= "<option value='{$data['time']}'>{$data['time']}</option>";
}
echo $response;
}
//send error
else echo "No rows returned by database.";
}
//if a specific bottom time has been selected, retrieve the surface intervals for that depth
else if(isset($_POST['bottom_time']) && !isset($_POST['addingDive']) ){
//set sql statement
$sql = "SELECT `end_time` from `surface_interval` WHERE `init_pressure_group` IN ( SELECT `pressure_group` FROM `bottom_time` WHERE `depth` = '{$_POST['depth_selected']}' AND `time` = '{$_POST['bottom_time']}') ORDER BY `end_time` ASC";
//see if there is a result when we run the query
if(!$result = mysqli_query($db, $sql)){
//if no result, there was an error
echo "MySQL error:".mysqli_error();
}
//if there is no mysql error, see if any rows were returned from the query
else if(mysqli_num_rows($result)){
$response = "";
for($i=0;$i<mysqli_num_rows($result);$i++){
$data = mysqli_fetch_assoc($result);
$response .= "<option value='{$data['end_time']}'>{$data['end_time']}</option>";
}
echo $response;
}
//send error
else echo "No rows returned by database.";
}
//send error
else echo "No POST data sent to server.";
*/
?>