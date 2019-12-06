/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';

const options = [{
	label: __('all posts'),
	value: 'all',
}, {
	label: __('posts with future dates only'),
	value: 'future',
}, {
	/* translators: label for ordering posts by title in ascending order */
	label: __('posts from the current year'),
	value: 'year',
}, {
	/* translators: label for ordering posts by title in descending order */
	label: __('posts from the current month'),
	value: 'month',
}, {
	/* translators: label for ordering posts by title in descending order */
	label: __('posts from today'),
	value: 'day',
}, ];

export default function IncludePosts({
	include,
	onIncludeChange
}) {

	return [
		onIncludeChange && (
			<SelectControl
			key="cptda-select-post-type"
			label={ __( 'Include Posts', 'custom-post-type-date-archives' ) }
			value={ `${ include}` }
			options={ options }
			onChange={ ( value ) => { onIncludeChange( value ); } }
		/>)
	]
}