/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import edit from './edit';
import icon from './icon';

registerBlockType('cptda/archives', {
	title: __( 'Custom Post Type Archives', 'custom-post-type-date-archives' ),
	description: __( 'Display a monthly archive of your posts.', 'custom-post-type-date-archives' ),
	icon,
	category: 'widgets',
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
