<?php
namespace Wpform;

if( ! defined('WF_DIR') ) {

	define( 'WF_DIR'				, dirname(__FILE__) . '/' );
	define( 'WF_VERSION'			, '1.1' );

	// abstract classes
	foreach( glob( WF_DIR . 'abstract/*.php') as $file ) {
		include_once( $file );
	}
	// forms
	foreach( glob( WF_DIR . 'form/*.php') as $file ) {
		include_once( $file );
	}
	// fields
	foreach( glob( WF_DIR . 'field/*.php') as $file ) {
		include_once( $file );
	}
}

?>