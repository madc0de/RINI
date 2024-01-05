<?php

defined('APP') OR exit('No direct script access allowed');

if( !function_exists( 'view' ) ) {
    function view($location = '') {
        if( !$location ) return;
        if( !file_exists( APP . 'View/' . $location ) ) return;
        require APP . 'View/' . $location;
    }
}