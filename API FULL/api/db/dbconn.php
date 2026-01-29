<?php
	$dsn = "pgsql:host=;port=;dbname=l;";
	$pdo = new PDO($dsn, "", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
	if ($pdo) {
		echo "Connected to the  database successfully!";
       
        }
	else{
		echo"no";
	}

?>