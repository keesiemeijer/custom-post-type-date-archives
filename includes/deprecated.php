<?php

function cptda_get_admin_post_types( $type = 'names' ) {
	_deprecated_function( __FUNCTION__, '2.4.1', 'cptda_get_post_types' );

	return cptda_get_post_types( $type, 'admin' );
}
