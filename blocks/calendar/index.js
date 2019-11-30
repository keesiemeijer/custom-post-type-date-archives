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

registerBlockType('cptda/calendar', {
	title: __( 'Custom Post Type Calendar', 'custom-post-type-date-archives' ),
	description: __( 'A calendar of your site’s custom post type posts.', 'custom-post-type-date-archives' ),
	icon,
	category: 'widgets',
	keywords: [ __( 'posts', 'custom-post-type-date-archives' ), __( 'archive', 'custom-post-type-date-archives' ) ],
	supports: {
		align: true,
	},
	edit,
	save() {
			// Rendering in PHP
			return null;
		}
} );
