<?php

if( !function_exists( 'exec_time' ) ) {
    function exec_time() {
        return @round( microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 2 );
    }
}


if( !function_exists( 'mem_used' ) ) {
    function mem_used() {
        $mem_used = memory_get_usage();

        return @round( $mem_used/pow( 1024, 2 ) , 2 );
    }
}