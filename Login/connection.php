<?php

    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbName = "school_db";

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try{
        $conn = mysqli_connect($host,$user,$pass,$dbName);
        
    }
    catch (mysqli_sql_exception $e){
        echo "Could not connect: " . $e->getMessage();
    }

?>