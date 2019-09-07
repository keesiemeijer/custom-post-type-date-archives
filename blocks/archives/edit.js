/**
 * External dependencies
 */
import { Fragment } from 'react';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { Disabled, PanelBody, ToggleControl } = wp.components;
const { Component } = wp.element;
const { withSelect } = wp.data;
const { InspectorControls } = wp.editor;

import CPTDA_ServerSideRender from '../components/server-side-render';
import PostTypePanel from '../components/post-types.js';
import QueryPanel from '../components/query-panel.js';

class CalendarEdit extends Component {
	constructor() {
		super(...arguments);
	}

	componentDidMount() {
		const { postType, setAttributes, attributes } = this.props;
		let { post_type } = attributes;

		if (!post_type) {
			// Default to current post type
			setAttributes({ post_type: postType })
		}
	}

	render() {
		const { setAttributes, attributes } = this.props;
		const { post_type, type, format, order, limit, show_post_count } = attributes;

		// Return if post type has not been set yet
		if (!post_type) {
			return null;
		}

		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={ __( 'Calendar Settings' ) }>
					<PostTypePanel
						postType={post_type}
						onPostTypeChange={ ( value ) => setAttributes( { post_type: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show post count' ) }
						checked={ show_post_count }
						onChange={ ( value ) => setAttributes( { show_post_count: value } ) }
					/>
					<QueryPanel
						limit={limit}
						onLimitChange={ ( value ) => setAttributes( { limit: value } ) }
						type={type}
						onTypeChange={ ( value ) => setAttributes( { type: value } ) }
						format={format}
						onFormatChange={ ( value ) => setAttributes( { format: value } ) }
						order={order}
						onOrderChange={ ( value ) => setAttributes( { order: value } ) }
					/>
				</PanelBody>
			</InspectorControls>
		);

		return (
			<Fragment>
				{inspectorControls}
				<Disabled>
					<CPTDA_ServerSideRender
						block='archives'
						title='Custom Post Type Archives'
						attributes={  this.props.attributes }
					/>
				</Disabled>
			</Fragment>
		);
	}
}

export default withSelect((select) => {
	const coreEditorSelect = select('core/editor');
	if (!coreEditorSelect) {
		return;
	}
	const {
		getEditedPostAttribute,
	} = coreEditorSelect;
	const postType = getEditedPostAttribute('type');
	// Dates are used to overwrite year and month used on the calendar.
	// This overwrite should only happen for 'post' post types.
	// For other post types the calendar always displays the current month.
	return {
		postType: postType
	};
})(CalendarEdit);