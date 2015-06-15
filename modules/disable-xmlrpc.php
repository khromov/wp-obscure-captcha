<?php
/* Borrowed from: https://wordpress.org/plugins/disable-xml-rpc/ */
add_filter( 'xmlrpc_enabled', '__return_false' );