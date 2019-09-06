/**
 * External dependencies
 */
import { isEqual, debounce, find } from 'lodash';

import { getPostTypes } from './post-types.js';

/**
 * WordPress dependencies
 */
const { Component, RawHTML } = wp.element;
const { __, sprintf } = wp.i18n;
const apiFetch = wp.apiFetch;
const { addQueryArgs } = wp.url;
const { Placeholder, Spinner } = wp.components;

export function rendererPath(block, attributes = null, urlQueryArgs = {} ) {
	const {post_type} = attributes;

	let attributesClone = Object.assign({}, attributes);
	delete attributesClone.post_type;

	return addQueryArgs( `/custom_post_type_date_archives/v1/${ post_type }/${ block }`, {
		...urlQueryArgs,
		...attributesClone
	} );
}

export class CPTDA_ServerSideRender extends Component {
	constructor( props ) {
		super( props );
		this.postTypes = getPostTypes();
		this.state = {
			response: null,
		};
	}

	componentDidMount() {
		this.isStillMounted = true;
		this.fetch( this.props );
		// Only debounce once the initial fetch occurs to ensure that the first
		// renders show data as soon as possible.
		this.fetch = debounce( this.fetch, 500 );
	}

	componentWillUnmount() {
		this.isStillMounted = false;
	}

	componentDidUpdate( prevProps ) {
		if ( ! isEqual( prevProps, this.props ) ) {
			this.fetch( this.props );
		}
	}

	fetch( props ) {
		if ( ! this.isStillMounted ) {
			return;
		}
		if ( null !== this.state.response ) {
			this.setState( { response: null } );
		}
		const { block, attributes = null, urlQueryArgs = {} } = props;

		const path = rendererPath( block, attributes, urlQueryArgs );
		// Store the latest fetch request so that when we process it, we can
		// check if it is the current request, to avoid race conditions on slow networks.
		const fetchRequest = this.currentFetchRequest = apiFetch( { path } )
			.then( ( response ) => {
				if ( this.isStillMounted && fetchRequest === this.currentFetchRequest && response ) {
					this.setState( { response: response.rendered } );
				}
			} )
			.catch( ( error ) => {
				if ( this.isStillMounted && fetchRequest === this.currentFetchRequest ) {
					this.setState( { response: {
						error: true,
						errorMsg: error.message,
					} } );
				}
			} );
		return fetchRequest;
	}

	render() {
		const response = this.state.response;
		const { className, EmptyResponsePlaceholder, ErrorResponsePlaceholder, LoadingResponsePlaceholder } = this.props;

		if ( response === '' ) {
			return (
				<EmptyResponsePlaceholder response={ response } { ...this.props } postTypes={this.postTypes} />
			);
		} else if ( ! response ) {
			return (
				<LoadingResponsePlaceholder response={ response } { ...this.props } />
			);
		} else if ( response.error ) {
			return (
				<ErrorResponsePlaceholder response={ response } { ...this.props } />
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
	EmptyResponsePlaceholder: ( { className, attributes, postTypes } ) => {

	let emptyResponseMessage = __( 'No posts found' );
	const {post_type} = attributes;
	if(post_type) {
		const postTypeObj = find( postTypes, {'value': post_type});
		if(postTypeObj) {
			emptyResponseMessage = __( 'No posts found for post type ' ) + postTypeObj.label;
		}
	}

	return (
		<Placeholder
			className={ className }
		>
			{ emptyResponseMessage }
		</Placeholder>
	)
},
	ErrorResponsePlaceholder: ( { response, className } ) => {
		// translators: %s: error message describing the problem
		const errorMessage = sprintf( __( 'Error loading block: %s' ), response.errorMsg );
		return (
			<Placeholder
				className={ className }
			>
				{ errorMessage }
			</Placeholder>
		);
	},
	LoadingResponsePlaceholder: ( { className } ) => {
		return (
			<Placeholder
				className={ className }
			>
				<Spinner />
			</Placeholder>
		);
	},
};

export default CPTDA_ServerSideRender;
