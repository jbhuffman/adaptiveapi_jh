<?php
/*
 * example script for using the Adaptive API
 */
require_once 'adaptive.php';

use JHuffman\API;

$user = 'adaptiveuser';
$pass = 'adaptivepassword';

// usage
$levels = JHuffman\API\Adaptive::exportLevels($user, $pass);
print_r($levels, false);