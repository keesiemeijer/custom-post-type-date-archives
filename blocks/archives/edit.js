/**
 * External dependencies
 */
import { Fragment } from 'react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Disabled, PanelBody, ToggleControl } from '@wordpress/components';
import { Component } from '@wordpress/element';
import { withSelect } from '@wordpress/data';
import { InspectorControls } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import CPTDA_ServerSideRender from '../components/server-side-render';
import { hasDateArchive } from '../components/post-types.js';
import PostTypeSelect from '../components/post-types.js';
import QueryPanel from '../components/query-panel.js';

class ArchivesEdit extends Component {
	constructor() {
		super(...arguments);
	}

	componentDidMount() {
		const { postType, setAttributes, attributes } = this.props;
		let { post_type } = attributes;
		let current;

		if (!post_type) {
			current = hasDateArchive(postType) ? postType : 'post';

			// Default to current post type
			setAttributes({ post_type: current })
		}
	}

	render() {
		const { setAttributes, attributes } = this.props;
		const { post_type, type, format, order, limit, show_post_count, displayAsDropdown } = attributes;
		let serverAttributes = Object.assign({}, attributes);

		// Clean up attributes
		delete serverAttributes.displayAsDropdown;

		// Return if post type has not been set yet
		if (!post_type) {
			return null;
		}

		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={ __( 'Archives Settings', 'custom-post-type-date-archives' ) }>
					<PostTypeSelect
						postType={ post_type }
						onPostTypeChange={ ( value ) => setAttributes( { post_type: value } ) }
						dateArchives={true}
					/>
					<ToggleControl
						label={ __( 'Display as Dropdown' ) }
						checked={ displayAsDropdown }
						onChange={ () => setAttributes( {
							displayAsDropdown: ! displayAsDropdown,
							format: ! displayAsDropdown ? 'option' : 'html'
						} ) }
					/>
					<ToggleControl
						label={ __( 'Show post count', 'custom-post-type-date-archives' ) }
						checked={ show_post_count }
						onChange={ () => setAttributes( { show_post_count: ! show_post_count } ) }
					/>
					<QueryPanel
						limit={limit}
						onLimitChange={ ( value ) => setAttributes( { limit: value } ) }
						type={type}
						onTypeChange={ ( value ) => setAttributes( { type: value } ) }
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
						defaultClass='wp-block-archives'
						dateArchives={true}
						attributes={ serverAttributes }
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

	const { getEditedPostAttribute } = coreEditorSelect;

	return { postType: getEditedPostAttribute('type') };
})(ArchivesEdit);