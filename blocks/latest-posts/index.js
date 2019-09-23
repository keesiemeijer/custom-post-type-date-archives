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

registerBlockType('cptda/latest-posts', {
	title: __( 'Custom Post Type latest Posts', 'custom-post-type-date-archives' ),
	description: __( 'Display a list of your most recent posts.', 'custom-post-type-date-archives' ),
	icon,
	category: 'widgets',
	keywords: [ __( 'recent posts', 'custom-post-type-date-archives' ) ],
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

