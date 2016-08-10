<?php
//TODO: I REALLY NEED MYSQL SANITATION

//how this works php script works- on ajax posts from either view.js/report.js/register.js it will do mysql queries

//creates mysqli connection object...   
$mysqli = new mysqli("localhost", "root", "Applez255", "GPSCOORDS");

//dependency for the getid3 library
require_once("getID3/getid3/getid3.php");

//if error kill urself
if($mysqli->connect_errno) {
   printf("Connect failed: %s\n", $mysqli->connect_error);
   exit;
}


//NEW THING TO HANDLE REPORT SAVING TO MYSQL DATABASE AND PICTURE SAVING
//does the report text and the picture in one go!
//the trigger to this ajax POST response is only after validation!
//stores the text/coords into mysql, then gets the primary key of that new mysql to
//use as the name of the uploaded photo
//file extension is also saved in the mysql db so we can figure out the the the corresponding photo/video
// i.e. for mysql record with a primary key of "1" and an extension of "png" the corresponding file is "1.png"
//makes the files simple to track
//also by default gps coordinates will be attempted to be extracted from the meta data of the pictures/videos. 
//If none are present the gps coordinates from the report post will be stored. those "backup" gps coordinates are the location of the phone when the report was submitted.

// DO i need further validation? *scratches head
//TODO: oh yeah, mysql sanitation kek
else if(isset($_REQUEST['name'])){
   $text =filter_input(INPUT_POST,'text', FILTER_SANITIZE_STRING);
   $name = filter_input(INPUT_POST,'name', FILTER_SANITIZE_STRING);
   $ext = end(explode(".",$_FILES['pic']['name']));
   $date;
   $coords;

   $timestamp_flag = 0;
  // $coords = $_POST['coords'];

  //uses id3 library to get metadata about video/photos 
   $getID3 = new getID3;

   $metaData = $getID3->analyze($_FILES['pic']['tmp_name']);
   getid3_lib::CopyTagsToComments($metaData);
 

//   var_dump($metaData['quicktime']['moov']['subatoms'][0]['creation_time_unix']);
//this part is a little dirty, checks for the existense of tags to use in the mysql store

    //if the file has video gps metadata....
   if(isset($metaData['tags_html']['quicktime']['gps_latitude'])) {
      $coords = array( 0 => $metaData['tags']['quicktime']['gps_latitude'][0], 1 => $metaData['tags']['quicktime']['gps_longitude'][0]);
      //echo "found gps video";
      //if the video file also has iphone timestamp....
      if(isset($metaData['tags_html']['quicktime']['creationdate'])) {
        //echo "found iphone timestamp";
        // $date = date('Y-m-d H:i:s', $metaData['tags_html']['quicktime']['creationdate']); 
        $exp = explode("T", $metaData['tags_html']['quicktime']['creationdate'][0]);
        $date = $exp[0] ." ". substr($exp[1],0,-5);
         $sql_q = "INSERT INTO GPSCOORDS_TB1 ".
           "(gps_lat, gps_long, gps_text, gps_ext, gps_name, gps_timestamp) ".
           "VALUES ".
           "('$coords[0]', '$coords[1]','$text','$ext','$name', '$date')";

      }
      //or a android style timestamp
      else if(isset($metaData['quicktime']['moov']['subatoms'][0]['creation_time_unix'])) {
         //echo "found android timestamp";
         $date = date('Y-m-d H:i:s', $metaData['quicktime']['moov']['subatoms'][0]['creation_time_unix']); 
        
         $sql_q = "INSERT INTO GPSCOORDS_TB1 ".
           "(gps_lat, gps_long, gps_text, gps_ext, gps_name, gps_timestamp) ".
           "VALUES ".
           "('$coords[0]', '$coords[1]','$text','$ext','$name', '$date')";
      }
      //or no timestamp
      else {
          //echo "found no timestamp";
        // var_dump($metaData['tags_html']['quicktime']);       
         $sql_q = "INSERT INTO GPSCOORDS_TB1 ".
          "(gps_lat, gps_long, gps_text, gps_ext, gps_name) ".
          "VALUES ".
          "('$coords[0]', '$coords[1]','$text','$ext','$name')";
      }
   }
   //if its a picture file with gps coords...
   else if(isset($metaData['jpg']['exif']['GPS']['computed'])) {
     // echo "found picture with gps coordinates";
      $coords = array(0 => $metaData['jpg']['exif']['GPS']['computed']['latitude'], 1 => $metaData['jpg']['exif']['GPS']['computed']['longitude']);
       //if picture also has timestamp...i
      if(isset($metaData['jpg']['exif']['IFD0']['DateTime'])) {
        //echo "found timestamp";
         $date = $metaData['jpg']['exif']['IFD0']['DateTime'];
         $sql_q = "INSERT INTO GPSCOORDS_TB1 ".
           "(gps_lat, gps_long, gps_text, gps_ext, gps_name, gps_timestamp) ".
           "VALUES ".
           "('$coords[0]', '$coords[1]','$text','$ext','$name', '$date')";

      }
      //or no timestamp
      else {
         //echo "didnt find timestamp";
         $sql_q = "INSERT INTO GPSCOORDS_TB1 ".
          "(gps_lat, gps_long, gps_text, gps_ext, gps_name) ".
          "VALUES ".
          "('$coords[0]', '$coords[1]','$text','$ext','$name')";
      }
   }
   //if it has no gps coordinates...
   else {
       //echo "couldnt find any gps coordinates";
      $coords = $_POST['coords'];
      //but it may still have an iphone timestamp
      if(isset($metaData['tags_html']['quicktime']['creationdate'])) {
       //echo "found iphone timestamp"; 
        // $date = date('Y-m-d H:i:s', $metaData['tags_html']['quicktime']['creationdate']); 
        $exp = explode("T", $metaData['tags_html']['quicktime']['creationdate'][0]);
        $date = $exp[0] ." ". substr($exp[1],0,-5);
         $sql_q = "INSERT INTO GPSCOORDS_TB1 ".
           "(gps_lat, gps_long, gps_text, gps_ext, gps_name, gps_timestamp) ".
           "VALUES ".
           "('$coords[0]', '$coords[1]','$text','$ext','$name', '$date')";

      }

      //but it may still have an android video timestamp
      else if(isset($metaData['quicktime']['moov']['subatoms'][0]['creation_time_unix'])) {
         //echo "found android video timestamp";
         $date = date('Y-m-d H:i:s', $metaData['quicktime']['moov']['subatoms'][0]['creation_time_unix']); 
         $sql_q = "INSERT INTO GPSCOORDS_TB1 ".
           "(gps_lat, gps_long, gps_text, gps_ext, gps_name, gps_timestamp) ".
           "VALUES ".
           "('$coords[0]', '$coords[1]','$text','$ext','$name', '$date')";
      }
      //or a photo timestamp...
      else if(isset($metaData['jpg']['exif']['IFD0']['DateTime'])) {
        //echo "found photo timestamp";
         $date = $metaData['jpg']['exif']['IFD0']['DateTime'];
         $sql_q = "INSERT INTO GPSCOORDS_TB1 ".
           "(gps_lat, gps_long, gps_text, gps_ext, gps_name, gps_timestamp) ".
           "VALUES ".
           "('$coords[0]', '$coords[1]','$text','$ext','$name', '$date')";

      }
     //or absolutely nothing at all
      else {
         //echo "found no timestamps at all";
         $sql_q = "INSERT INTO GPSCOORDS_TB1 ".
          "(gps_lat, gps_long, gps_text, gps_ext, gps_name) ".
          "VALUES ".
          "('$coords[0]', '$coords[1]','$text','$ext','$name')";
      }
   }

   $result = mysqli_query($mysqli,$sql_q);
   if(!$result) {
      printf("report insert error\n");
      exit;
   }
   $sql_q2 = "SELECT * FROM GPSCOORDS_TB1
     ORDER BY gps_id DESC
      LIMIT 0,1";
   $result2 = mysqli_query($mysqli,$sql_q2);
   
   if(!$result2) {
      printf("report select primary key error\n");
      exit;
   }
   
   $record = mysqli_fetch_array($result2, MYSQL_ASSOC);

   mysqli_free_result($result);
   mysqli_free_result($result2);

   $file = $_FILES['pic']; 
   $fileContent = file_get_contents($file['tmp_name']);

   $test = fopen("../pic/".$record['gps_id'].".".$ext,"x");
   if(!$test) {
      echo "couldnt open";
      exit;
   }
   fwrite($test, $fileContent);
   fclose($test);
   echo "Report Saved Succesfully";

}

// on this POST, registers the name and team id in the mysql database table 2!
else if(isset($_REQUEST['namereg'])){
   $name = filter_input(INPUT_POST, 'namereg', FILTER_SANITIZE_STRING);

   $stmt1 = $mysqli->prepare("SELECT * FROM GPSCOORDS_TB2 WHERE
      gps_name = ? LIMIT 1");
   $stmt1->bind_param('s',$name);
   $stmt1->execute();
   $stmt1->store_result();
   

   if($stmt1->num_rows > 0) {
      echo "error";
   }
   else {
      $stmt2 = $mysqli->prepare("INSERT INTO GPSCOORDS_TB2 (gps_name) VALUES (?)");
      $stmt2->bind_param('s',$name);
      $stmt2->execute();
      
      if(!$stmt2->store_result()) {
         echo "error in registering name n stuff";
      }
      $stmt2->free_result();
      $stmt2->close();
   }
   $stmt1->free_result();
   $stmt1->close();
}
/*
// on this POST, saves the random report
else if(isset($_REQUEST['rand'])){
   $rand = $_POST['rand'];
   $sql_q = "INSERT INTO GPSCOORDS_TB1 ".
       "(gps_lat, gps_long, gps_text, gps_ext, gps_name) ".
       "VALUES ".
       "('$rand[0]', '$rand[1]','$rand[2]','$rand[3]','$rand[4]')";
   $result = mysqli_query($mysqli,$sql_q);
   if(!$result) {
      printf("report insert error\n");
      exit;
   }
   mysqli_free_result($result);
}*/

//on this post, gets all the names out of the mysql db table 2!

else if(isset($_REQUEST['getnames'])){

   $arr = array();
   $sql_q = 'SELECT gps_name 
        FROM GPSCOORDS_TB2';
   $stmt = $mysqli->prepare($sql_q);
   $stmt->bind_result($name);
   $stmt->execute();

   if(!$stmt->store_result()) {
      printf("genames error\n");
      exit;
   }

   while($stmt->fetch())  
      $arr[] = $name;

   echo json_encode($arr);
   $stmt->free_result();
   $stmt->close(); 
   unset($arr);
}

mysqli_close($mysqli);

?>