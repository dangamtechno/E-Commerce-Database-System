<?php
//session_start();
require '../include/database_config.php';
//header('Access-Control-Allow-Origin: *');
if($_SERVER['REQUEST_METHOD']=="GET"){
   $stmt = "SELECT * FROM product  WHERE status = 1 order by rand() limit 3;";
    if($result = $conn->query($stmt)){
        $arr = array();
        while($rowArray = $result->fetch_assoc()){
            array_push($arr,$rowArray);
        }
        echo json_encode(['featured_products'=> $arr]);
    }
    else{
       echo json_encode(['error'=> 'something went wrong']);
    }
   exit();
}