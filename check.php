<?php

// The info for the remote url trying to update
$array = array(
    'version' => '0.4',
    'path' => 'http://remote.dev/0.4.zip'
);

// Json the array
echo json_encode($array);

// Optional Security feature checkupdate.php?key=1234567890
if($_GET['key'] == '1234567890') {
	// The info for the remote url trying to update
	$array = array(
	    'version' => '0.4',
	    'path' => 'http://remote.dev/0.4.zip'
	);

	// Json the array
	echo json_encode($array);
}