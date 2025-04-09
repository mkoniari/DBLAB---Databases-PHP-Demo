<?php
   
   function OpenCon(){ 
      $dbhost = getenv('MYSQL_HOST');
      $dbuser = getenv('MYSQL_USER');
      $dbpass = getenv('MYSQL_PASSWORD');
      $db = getenv('MYSQL_DATABASE');
      
      $conn = new mysqli($dbhost, $dbuser, $dbpass, $db) or die("Connect failed: %s\n". $conn->error);
      //echo "Connected successfully";
      return $conn;
  }
   
   function CloseCon($conn){
      $conn -> close();
   }  


?>