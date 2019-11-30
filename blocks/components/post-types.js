/**
 * External dependencies
 */
import {get } from 'lodash';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { SelectControl } from '@wordpress/components';

export function getDateArchivePostTypes() {
	return get(window, 'cptda_data.post_type', {});
}

export function getPublicPostTypes() {
	return get(window, 'cptda_data.public', {});
}

export function hasDateArchive(postType) {
	const postTypes = Object.keys(getDateArchivePostTypes());
	if (('post' === postType) || (-1 !== postTypes.indexOf(postType))) {
		return true;
	}

	return false;
}

export function is_public_post_type(postType) {
	const postTypes = Object.keys(getPublicPostTypes());
	if (-1 !== postTypes.indexOf(postType)) {
		return true;
	}

	return false;

}

export function getPostTypeOptions(invalidPostType = '', dateArchives = true) {
	const options = [];

	// invalid post type
	if (invalidPostType.length) {
		options.push({
			label: invalidPostType,
			value: invalidPostType,
		})
	}

	let postTypes;
	if (dateArchives) {
		postTypes = getDateArchivePostTypes();

		// Default option.
		options.push({
			label: __('Post'),
			value: 'post',
		});
	} else {
		postTypes = getPublicPostTypes();
	}

	for (var key in postTypes) {
		if (postTypes.hasOwnProperty(key)) {
			options.push({
				label: postTypes[key],
				value: key,
			});
		}
	}

	return options;
}

export default function PostTypeSelect({
	postType,
	onPostTypeChange,
	dateArchives
}) {
	let error = '';
	let help = '';
	let invalidPostType = '';

	if (dateArchives && !hasDateArchive(postType)) {
		error = 'cptda-select-error';
		help = sprintf(__("The post type %s doesn't exist or doesn't have date archives.", 'custom-post-type-date-archives'), postType);
		help += ' ' + __('Please select another post type', 'custom-post-type-date-archives');
		invalidPostType = postType;
	} else if (!dateArchives && !is_public_post_type(postType)) {
		error = 'cptda-select-error';
		help = sprintf(__("The post type %s doesn't exist.", 'custom-post-type-date-archives'), postType);
		help += ' ' + __('Please select another post type', 'custom-post-type-date-archives');
		invalidPostType = postType;
	}

	let options = getPostTypeOptions(invalidPostType, dateArchives);

	return [
		onPostTypeChange && (
			<SelectControl
			key="cptda-select-post-type"
			help={help}
			label={ __( 'Post Type', 'custom-post-type-date-archives' ) }
			value={ `${ postType}` }
			options={ options }
			onChange={ ( value ) => { onPostTypeChange( value ); } }
			className={error}
		/>)
	]
}