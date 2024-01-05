<?php

defined('APP') OR exit('No direct script access allowed');

function site_url($uri = '', $protocol = NULL) {
	return rtrim( base_url($protocol), '/' ) .'/'. ltrim( $uri, '/' );
}


function base_url($protocol = NULL) {
	$base_url = '';

	// HTTP Protocol
	if( $protocol ) {
		$base_url .= $protocol.'//';
	} elseif ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
		$base_url .= 'https://';
	} else {
		$base_url .= 'http://';
	}

	// HTTP Host
	$base_url .= $_SERVER['HTTP_HOST'];

	// Base Directory
	$base_url .= str_replace( $_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME'] );
	
	// Remove index.php
	if ( !$base_url == '/index.php' ) $base_url = rtrim( $base_url, 'index.php' );
	$base_url = rtrim( $base_url, 'index.php' );

	return $base_url;
}


function current_url($protocol = NULL) {
	$current_url = '';
	// HTTP Protocol
	if( $protocol ) {
		$current_url .= $protocol.'//';
	} elseif ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
		$current_url .= 'https://';
	} else {
		$current_url .= 'http://';
	}

	// HTTP Host
	$current_url .= $_SERVER['HTTP_HOST'];

	// Current URL
	$current_url .= $_SERVER['REQUEST_URI']; 

	return $current_url;
}