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

export function isDateArchivePostType(postType) {
	const postTypes = Object.keys(getDateArchivePostTypes());
	if (('post' === postType) || (-1 !== postTypes.indexOf(postType))) {
		return true;
	}

	return false;
}

export function isPublicPostType(postType) {
	const postTypes = Object.keys(getPublicPostTypes());
	if (-1 !== postTypes.indexOf(postType)) {
		return true;
	}

	return false;
}

export function getPostTypeError(postType, dateArchives) {
	if (!postType.length) {
		return '';
	}

	let error = '';
	if (!isPublicPostType(postType)) {
		error = __("The post type for this block doesn't exist.", 'custom-post-type-date-archives');
	} else if (dateArchives && !isDateArchivePostType(postType)) {
		error = __("The post type for this block doesn't have date archives.", 'custom-post-type-date-archives');
	}

	return error;
}

export function getPostTypeOptions(invalidPostType = '', dateArchives = true) {
	const options = [];

	// Add invalid post type to options
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

	if (dateArchives && !isDateArchivePostType(postType)) {
		error = 'cptda-select-error';
		help = sprintf(__("The post type %s doesn't exist or doesn't have date archives.", 'custom-post-type-date-archives'), postType);
		help += ' ' + __('Please select another post type', 'custom-post-type-date-archives');
		invalidPostType = postType;
	} else if (!dateArchives && !isPublicPostType(postType)) {
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