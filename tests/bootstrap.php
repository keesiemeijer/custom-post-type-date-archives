<?php
$_tests_dir = '/tmp/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';
define( 'CPTDA_TEST_THEMES_DIR', dirname( dirname( __FILE__ ) ) . '/tests' );



function _manually_load_plugin() {
	switch_theme( 'cptda-test-theme' );
	require dirname( dirname( __FILE__ ) ) . '/custom-post-type-date-archives.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
require dirname( dirname( __FILE__ ) ) . '/tests/testcase.php';
