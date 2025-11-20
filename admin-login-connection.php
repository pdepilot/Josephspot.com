<?php 

$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "josephs_pot";


$connect = mysqli_connect($hostname, $username, $password, $dbname);

if(!$connect){
    echo "connected";
}