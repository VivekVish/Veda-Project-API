<?php

# Includes
require_once("../includes/main.inc.php");

# Query, replace with dynamic data
$result = pg_query("SELECT * FROM lessons");
$row = pg_fetch_assoc($result);

/*
# Setup XML document
$xmlObj = new DOMDocument;
$xmlObj->loadXML($row['content']);

# Get list of ilo's and length of list
$ilos = $xmlObj->getElementsByTagName("iloPh");
$length = $ilos->length;

# Loop through list
for ($i = 0; $i < $length; $i++)
{
	# Get ilo tag
	$ilo = $ilos->item($i);

	# Get id from tag
	$id = $ilo->getAttribute("id");

	# Get content for ilo
	$sql = "SELECT content FROM lesson_ilos WHERE id = $id";
	$result = pg_query($sql);
	$row = pg_fetch_assoc($result);

	# Construct node object
	$htmlContent = "<div id='ilo_$id' class='jsmath'>{$row['content']}</div>";
	$htmlObj = new DOMDocument;
	$htmlObj->loadXML($htmlContent);
	$htmlNode = $xmlObj->importNode($htmlObj->getElementsByTagName("div")->item(0));
	$ilo->parentNode->replaceChild($htmlNode, $ilo);	
}
*/

header("Content-Type: text/xml");
print $row['content'];

?>
