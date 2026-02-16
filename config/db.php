<?php

$host = "yallegue-luishebertosuarezflores-2522.f.aivencloud.com";
$dbname = "defaultdb";
$user = "avnadmin";
$pass = "AVNS_g1CmAIgcRPKaMmAkN_I";
$port = 20421;

try {

    $db = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
        $user,
        $pass
    );

    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e){

    die("Error BD: " . $e->getMessage());

}