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

registerBlockType('cptda/recent-posts', {
	title: __( 'Custom post type recent Posts' ),
	description: __( 'Display a list of your most recent posts.' ),
	icon,
	category: 'widgets',
	keywords: [ __( 'recent posts' ) ],
	supports: {
		align: true,
		html: false,
	},
	edit,
	save() {
			// Rendering in PHP
			return null;
		}
} );

