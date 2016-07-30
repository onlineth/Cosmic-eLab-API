<?php
# This file will not be visible to the public

# Data Location
$data_location = 'D:/Fermilab/Detector Files/';

# Credentials for Database
$database_host = 'localhost';
$database_port = '5432';
$database_name = 'I2U2_Cosmic_Ray';
$database_user = 'postgres';
$database_password = 'password';

# Accepted file types (should probably not change)
# Note: raw does not mean .raw but like "6179.2014.0429.0"
$allowed_filetypes = array('raw', 'thresh', 'analyze', 'bless', 'geo');
