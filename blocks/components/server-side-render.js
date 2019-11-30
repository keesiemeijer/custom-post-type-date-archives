/**
 * External dependencies
 */
import { isEqual, debounce } from 'lodash';

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { Component, RawHTML } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';
import { Placeholder, Spinner } from '@wordpress/components';

export function rendererPath(props) {
	const { block, defaultClass, attributes = null, urlQueryArgs = {} } = props;
	const { post_type } = attributes;

	let serverAttributes = Object.assign({}, attributes);
	serverAttributes.class = defaultClass;
	delete serverAttributes.post_type;

	return addQueryArgs(`/custom_post_type_date_archives/v1/${ post_type }/${ block }`, {
		...urlQueryArgs,
		...serverAttributes
	});
}

export class CPTDA_ServerSideRender extends Component {
	constructor(props) {
		super(props);
		this.state = {
			response: null,
		};
	}

	componentDidMount() {
		this.isStillMounted = true;
		this.fetch(this.props);
		// Only debounce once the initial fetch occurs to ensure that the first
		// renders show data as soon as possible.
		this.fetch = debounce(this.fetch, 500);
	}

	componentWillUnmount() {
		this.isStillMounted = false;
	}

	componentDidUpdate(prevProps) {
		if (!isEqual(prevProps, this.props)) {
			this.fetch(this.props);
		}
	}

	fetch(props) {
		if (!this.isStillMounted) {
			return;
		}
		if (null !== this.state.response) {
			this.setState({ response: null });
		}
		const { block, attributes = null, urlQueryArgs = {} } = props;
		const path = rendererPath(props);

		// Store the latest fetch request so that when we process it, we can
		// check if it is the current request, to avoid race conditions on slow networks.
		const fetchRequest = this.currentFetchRequest = apiFetch({ path })
			.then((response) => {
				if (this.isStillMounted && fetchRequest === this.currentFetchRequest && response) {
					this.setState({ response: response.rendered });
				}
			})
			.catch((error) => {
				if (this.isStillMounted && fetchRequest === this.currentFetchRequest) {
					this.setState({
						response: {
							error: true,
							errorMsg: error.message,
						}
					});
				}
			});
		return fetchRequest;
	}

	render() {
		const response = this.state.response;
		const { className, EmptyResponsePlaceholder, ErrorResponsePlaceholder, LoadingResponsePlaceholder } = this.props;

		if (response === '') {
			return (
				<EmptyResponsePlaceholder response={ response } { ...this.props } label={this.props.title} />
			);
		} else if (!response) {
			return (
				<LoadingResponsePlaceholder response={ response } { ...this.props } label={this.props.title} />
			);
		} else if (response.error) {
			return (
				<ErrorResponsePlaceholder response={ response } { ...this.props } label={this.props.title} />
			);
		}

		return (
			<RawHTML
				key="html"
				className={ className }
			>
				{ response }
			</RawHTML>
		);
	}
}

CPTDA_ServerSideRender.defaultProps = {
	EmptyResponsePlaceholder: ({ className, label }) => {

		let emptyResponseMessage = __('No posts found with the current block settings', 'custom-post-type-date-archives');
		return (
			<Placeholder
			className={ className }
			label={label}
		>
			{ emptyResponseMessage }
		</Placeholder>
		)
	},
	ErrorResponsePlaceholder: ({ response, className, label, dateArchives }) => {
		// translators: %s: error message describing the problem
		let errorMessage = sprintf(__('Error loading block: %s', 'custom-post-type-date-archives'), response.errorMsg);
		if ('Invalid post type' === response.errorMsg) {
			const archiveError = __("The post type for this block doesn't exist or doesn't have date archives.", 'custom-post-type-date-archives');
			const typeError = __("The post type for this block doesn't exist.", 'custom-post-type-date-archives');
			const error = dateArchives ? archiveError : typeError;

			errorMessage = (<span>{errorMessage}<br/><br/>{error}</span>);
		}

		return (
			<Placeholder
				className={ className }
				label={label}
			>
				{ errorMessage }
			</Placeholder>
		);
	},
	LoadingResponsePlaceholder: ({ className, label }) => {
		return (
			<Placeholder
				className={ className }
				label={label}
			>
				<Spinner />
			</Placeholder>
		);
	},
};

export default CPTDA_ServerSideRender;