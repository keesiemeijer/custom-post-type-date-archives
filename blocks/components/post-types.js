import { get } from 'lodash';

const { __ } = wp.i18n;
const { SelectControl } = wp.components;

export function getPostTypes() {
	const postTypes = [{
		label: __('Post'),
		value: 'post',
	}, ];

	const cptdaPostType = get(window.cptda_data, 'post_type', {});

	for (var key in cptdaPostType) {
		if (cptdaPostType.hasOwnProperty(key)) {
			postTypes.push({
				label: cptdaPostType[key],
				value: key,
			});
		}
	}

	return postTypes;
}

export default function PostTypePanel({
	postType,
	onPostTypeChange,
}) {
	return [
		onPostTypeChange && (
			<SelectControl
			key="cptda-select-post-type"
			label={ __( 'Post Type', 'custom-post-type-date-archives' ) }
			value={ `${ postType}` }
			options={ getPostTypes() }
			onChange={ ( value ) => { onPostTypeChange( value ); } }
		/>)
	]
}