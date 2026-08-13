<?php

$host = "localhost";
$dbname = "food_ordering_system";
$username = "root";
$dbPassword = "";

if (!defined("BASE_URL")) {
    $scriptDir = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"] ?? ""));

   
    $appFolders = ["admin", "auth", "user", "includes", "config"];

    $segments = array_values(array_filter(explode("/", $scriptDir)));

    $cutAt = null;

    foreach ($segments as $index => $segment) {
        if (in_array($segment, $appFolders, true)) {
            $cutAt = $index;
            break;
        }
    }

    if ($cutAt === null) {
        $baseUrl = $scriptDir;
    } else {
        $baseUrl = "/" . implode("/", array_slice($segments, 0, $cutAt));
    }

    if ($baseUrl === "/") {
        $baseUrl = "";
    }

    define("BASE_URL", $baseUrl);
}

try{

    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $dbPassword
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

}catch(PDOException $e){

    die("Database Error : ".$e->getMessage());

}

?>