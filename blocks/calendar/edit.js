/**
 * External dependencies
 */
import { Fragment } from 'react';
import moment from 'moment';
import memoize from 'memize';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { Disabled, PanelBody } = wp.components;
const { Component } = wp.element;
const { withSelect } = wp.data;
const { InspectorControls } = wp.blockEditor;

import CPTDA_ServerSideRender from '../components/server-side-render';
import PostTypePanel from '../components/post-types.js';

class CalendarEdit extends Component {
	constructor() {
		super(...arguments);

		this.getYearMonth = memoize(
			this.getYearMonth.bind(this), { maxSize: 1 }
		);
		this.getServerSideAttributes = memoize(
			this.getServerSideAttributes.bind(this), { maxSize: 1 }
		);
	}

	componentDidMount() {
		const { postType, setAttributes, attributes } = this.props;
		let { post_type } = attributes;

		if (!post_type) {
			// Default to current post type
			setAttributes({ post_type: postType })
		}
	}

	getYearMonth(date) {
		if (!date) {
			return {};
		}
		const momentDate = moment(date);
		return {
			year: momentDate.year(),
			month: momentDate.month() + 1,
		};
	}

	getServerSideAttributes(attributes, date) {
		return {
			...attributes,
			...this.getYearMonth(date),
		};
	}

	render() {
		const { setAttributes, attributes } = this.props;
		let { post_type } = attributes;

		// Return if post type has not been set yet
		if (!post_type) {
			return null;
		}

		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={ __( 'Calendar Settings', 'custom-post-type-date-archives' ) }>
					<PostTypePanel
						postType={post_type}
						onPostTypeChange={ ( value ) => setAttributes( { post_type: value } ) }
					/>
				</PanelBody>
			</InspectorControls>
		);

		return (
			<Fragment>
				{inspectorControls}
				<Disabled>
					<CPTDA_ServerSideRender
						block='calendar'
						title='Custom Post Type Calendar'
						defaultClass='wp-block-calendar'
						attributes={
							this.getServerSideAttributes(
							this.props.attributes,
							this.props.date
						) }
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

	return {
		date: getEditedPostAttribute('date'),
		postType: getEditedPostAttribute('type')
	};
})(CalendarEdit);