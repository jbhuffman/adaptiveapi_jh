<?php
/*
 * example script for using the Adaptive API
 */
use \JHuffman\API;

$user = 'adaptiveuser';
$pass = 'adaptivepassword';

$levels = Adaptive::exportLevels($user, $pass);