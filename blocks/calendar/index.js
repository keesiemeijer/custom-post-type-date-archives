/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;


/**
 * Internal dependencies
 */
import edit from './edit';
import icon from './icon';


registerBlockType('cptda/calendar', {
	title: __( 'Custom Post Type Calendar' ),
	description: __( 'A calendar of your site’s custom post type posts.' ),
	//icon,
	category: 'widgets',
	keywords: [ __( 'posts' ), __( 'archive' ) ],
	supports: {
		align: true,
	},
	edit,
	save() {
			// Rendering in PHP
			return null;
		}
} );
