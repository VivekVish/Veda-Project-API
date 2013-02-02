<?php

print_r(apache_request_headers());
print_r($_SERVER);
print_r($_REQUEST);
print_r($GLOBALS);
print_R($_GLOBALS);
$putdata = fopen("php://input", "r");
while ($data = fread($putdata, 1024))
	echo $data;


?>
