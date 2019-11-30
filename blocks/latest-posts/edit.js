/**
 * External dependencies
 */
import { Fragment } from 'react';
import { isUndefined, debounce } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Disabled, PanelBody, ToggleControl, RangeControl, TextareaControl, BaseControl } from '@wordpress/components';
import { Component } from '@wordpress/element';
import { withSelect } from '@wordpress/data';
import { InspectorControls } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import { hasDateArchive } from '../components/post-types.js';
import CPTDA_ServerSideRender from '../components/server-side-render';
import PostTypeSelect from '../components/post-types.js';
import IncludePosts from '../components/include-posts.js';

let instances = 0;

class LatestPostsEdit extends Component {
	constructor() {
		super(...arguments);

		// The title is updated 1 second after a change.
		// This allows the user more time to type.
		this.onMessageChange = this.onMessageChange.bind(this);
		this.messageDebounced = debounce(this.updateMessage, 1000);

		this.instanceId = instances++;
	}

	componentDidMount() {
		const { postType, setAttributes, attributes } = this.props;
		let { post_type } = attributes;

		if (!post_type) {
			const current = hasDateArchive(postType) ? postType : 'post';

			// Default to current post type
			setAttributes({ post_type: current })
		}
	}

	componentWillUnmount() {
		this.messageDebounced.cancel();
	}

	onMessageChange(e) {
		// React pools events, so we read the value before debounce.
		// Alternately we could call `event.persist()` and pass the entire event.
		// For more info see reactjs.org/docs/events.html#event-pooling
		this.messageDebounced(e.target.value);
	}

	updateMessage(value) {
		const { setAttributes } = this.props;
		setAttributes({ message: value });
	}

	render() {
		const { setAttributes, attributes } = this.props;
		const textareaID = 'inspector-textarea-control-' + this.instanceId;
		let { post_type, number, show_date, include, message } = attributes;

		// Return if post type has not been set yet
		if (!post_type) {
			return null;
		}

		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={ __( 'Latest Posts Settings', 'custom-post-type-date-archives' ) }>
					<PostTypeSelect
						postType={post_type}
						onPostTypeChange={ ( value ) => setAttributes( { post_type: value } ) }
						dateArchives={false}
					/>
					<RangeControl
							label={ __( 'Number of posts', 'custom-post-type-date-archives' ) }
							value={ number }
							onChange={ ( value ) => setAttributes( { number: value } ) }
							min={ 1 }
							max={ 100 }
					/>
					<IncludePosts
						include={include}
						onIncludeChange={ ( value ) => setAttributes( { include: value } ) }
					/>
					<ToggleControl
						label={ __( 'Display post date', 'custom-post-type-date-archives' ) }
						checked={ show_date }
						onChange={ ( value ) => setAttributes( { show_date: value } ) }
					/>
					<BaseControl label={ __( 'Message when no posts are found', 'custom-post-type-date-archives' ) } id={textareaID}>
						<textarea
							className="components-textarea-control__input"
							id={textareaID}
							rows='4'
							onChange={ this.onMessageChange }
							defaultValue={message}
						/>
					</BaseControl>				
				</PanelBody>
			</InspectorControls>
		);

		return (
			<Fragment>
				{inspectorControls}
				<Disabled>
					<CPTDA_ServerSideRender
						block='recent-posts'
						title='Custom Post Type Latest Posts'
						defaultClass='wp-block-latest-posts'
						dateArchives={false}
						attributes={ this.props.attributes }
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
	return {
		postType: getEditedPostAttribute('type')
	};
})(LatestPostsEdit);