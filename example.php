<?php
/*
 * example script for using the Adaptive API
 */
require_once 'adaptive.php';

use \JHuffman\API;

$user = 'adaptiveuser';
$pass = 'adaptivepassword';

// usage
$levels = Adaptive::exportLevels($user, $pass);