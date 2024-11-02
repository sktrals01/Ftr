<?php
require_once("config.inc.php");
if (isset($_GET['search'])) {
     
   $query = "SELECT * FROM clf_cities WHERE cityname LIKE '{$_GET['search']}%' LIMIT 25";
    $result = mysqli_query($conn, $query);
 
    if (mysqli_num_rows($result) > 0) {
     while ($user = mysqli_fetch_array($result)) {
      $res[] = $user['cityname'];
     }
    } else {
      $res = array();
    }
    //return json res
    echo json_encode($res);
}
?>