<?php

const DEBUG = true;
const PASSPORT_ENABLE = true;

if (DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}
