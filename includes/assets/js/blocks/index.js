!function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=16)}([function(e,t){!function(){e.exports=this.wp.element}()},function(e,t){!function(){e.exports=this.wp.components}()},function(e,t){e.exports=function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}},function(e,t){function n(e,t){for(var n=0;n<t.length;n++){var r=t[n];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(e,r.key,r)}}e.exports=function(e,t,r){return t&&n(e.prototype,t),r&&n(e,r),e}},function(e,t,n){var r=n(17),o=n(9);e.exports=function(e,t){return!t||"object"!==r(t)&&"function"!=typeof t?o(e):t}},function(e,t){function n(t){return e.exports=n=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)},n(t)}e.exports=n},function(e,t,n){var r=n(18);e.exports=function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&r(e,t)}},function(e,t,n){"use strict";n.d(t,"a",(function(){return i}));var r=n(0),o=n(8),s=wp.i18n.__,a=wp.components.SelectControl;function c(){var e=[{label:s("Post"),value:"post"}],t=Object(o.get)(window.cptda_data,"post_type",{});for(var n in t)t.hasOwnProperty(n)&&e.push({label:t[n],value:n});return e}function i(e){var t=e.postType,n=e.onPostTypeChange;return[n&&Object(r.createElement)(a,{key:"cptda-select-post-type",label:s("Post Type","custom-post-type-date-archives"),value:"".concat(t),options:c(),onChange:function(e){n(e)}})]}},function(e,t){!function(){e.exports=this.lodash}()},function(e,t){e.exports=function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}},function(e,t){!function(){e.exports=this.React}()},function(e,t,n){"use strict";var r=n(13),o=n.n(r),s=n(2),a=n.n(s),c=n(3),i=n.n(c),l=n(4),u=n.n(l),p=n(5),h=n.n(p),f=n(6),b=n.n(f),y=n(12),d=n.n(y),m=n(0),v=n(8);n(7);function g(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}var O=wp.element,w=O.Component,j=O.RawHTML,E=wp.i18n,P=E.__,C=E.sprintf,_=wp.apiFetch,x=wp.url.addQueryArgs,S=wp.components,k=S.Placeholder,M=S.Spinner;function T(e){var t=e.block,n=e.defaultClass,r=e.attributes,o=void 0===r?null:r,s=e.urlQueryArgs,a=void 0===s?{}:s,c=o.post_type,i=Object.assign({},o);return i.class=n,delete i.post_type,x("/custom_post_type_date_archives/v1/".concat(c,"/").concat(t),function(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?g(Object(n),!0).forEach((function(t){d()(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):g(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}({},a,{},i))}var D=function(e){function t(e){var n;return a()(this,t),(n=u()(this,h()(t).call(this,e))).state={response:null},n}return b()(t,e),i()(t,[{key:"componentDidMount",value:function(){this.isStillMounted=!0,this.fetch(this.props),this.fetch=Object(v.debounce)(this.fetch,500)}},{key:"componentWillUnmount",value:function(){this.isStillMounted=!1}},{key:"componentDidUpdate",value:function(e){Object(v.isEqual)(e,this.props)||this.fetch(this.props)}},{key:"fetch",value:function(e){var t=this;if(this.isStillMounted){null!==this.state.response&&this.setState({response:null});e.block,e.attributes,e.urlQueryArgs;var n=T(e),r=this.currentFetchRequest=_({path:n}).then((function(e){t.isStillMounted&&r===t.currentFetchRequest&&e&&t.setState({response:e.rendered})})).catch((function(e){t.isStillMounted&&r===t.currentFetchRequest&&t.setState({response:{error:!0,errorMsg:e.message}})}));return r}}},{key:"render",value:function(){var e=this.state.response,t=this.props,n=t.className,r=t.EmptyResponsePlaceholder,s=t.ErrorResponsePlaceholder,a=t.LoadingResponsePlaceholder;return""===e?Object(m.createElement)(r,o()({response:e},this.props,{label:this.props.title})):e?e.error?Object(m.createElement)(s,o()({response:e},this.props,{label:this.props.title})):Object(m.createElement)(j,{key:"html",className:n},e):Object(m.createElement)(a,o()({response:e},this.props,{label:this.props.title}))}}]),t}(w);D.defaultProps={EmptyResponsePlaceholder:function(e){var t=e.className,n=e.label,r=P("No posts found with the current block settings","custom-post-type-date-archives");return Object(m.createElement)(k,{className:t,label:n},r)},ErrorResponsePlaceholder:function(e){var t=e.response,n=e.className,r=e.label,o=C(P("Error loading block: %s","custom-post-type-date-archives"),t.errorMsg);return Object(m.createElement)(k,{className:n,label:r},o)},LoadingResponsePlaceholder:function(e){var t=e.className,n=e.label;return Object(m.createElement)(k,{className:t,label:n},Object(m.createElement)(M,null))}},t.a=D},function(e,t){e.exports=function(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}},function(e,t){function n(){return e.exports=n=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var r in n)Object.prototype.hasOwnProperty.call(n,r)&&(e[r]=n[r])}return e},n.apply(this,arguments)}e.exports=n},function(e,t,n){e.exports=function(e,t){var n,r,o,s=0;function a(){var t,a,c=r,i=arguments.length;e:for(;c;){if(c.args.length===arguments.length){for(a=0;a<i;a++)if(c.args[a]!==arguments[a]){c=c.next;continue e}return c!==r&&(c===o&&(o=c.prev),c.prev.next=c.next,c.next&&(c.next.prev=c.prev),c.next=r,c.prev=null,r.prev=c,r=c),c.val}c=c.next}for(t=new Array(i),a=0;a<i;a++)t[a]=arguments[a];return c={args:t,val:e.apply(null,t)},r?(r.prev=c,c.next=r):o=c,s===n?(o=o.prev).next=null:s++,r=c,c.val}return t&&t.maxSize&&(n=t.maxSize),a.clear=function(){r=null,o=null,s=0},a}},function(e,t){!function(){e.exports=this.moment}()},function(e,t,n){n(21),n(20),e.exports=n(19)},function(e,t){function n(e){return(n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function r(t){return"function"==typeof Symbol&&"symbol"===n(Symbol.iterator)?e.exports=r=function(e){return n(e)}:e.exports=r=function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":n(e)},r(t)}e.exports=r},function(e,t){function n(t,r){return e.exports=n=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e},n(t,r)}e.exports=n},function(e,t,n){"use strict";n.r(t);var r=n(2),o=n.n(r),s=n(3),a=n.n(s),c=n(4),i=n.n(c),l=n(5),u=n.n(l),p=n(6),h=n.n(p),f=n(0),b=n(10),y=n(11),d=n(7),m=wp.i18n.__,v=wp.components,g=v.SelectControl,O=v.RangeControl;function w(e){var t=e.limit,n=e.onLimitChange,r=e.type,o=e.onTypeChange,s=e.order,a=e.onOrderChange;return[o&&Object(f.createElement)(g,{key:"cptda-select-order",label:m("Type of archives","custom-post-type-date-archives"),value:"".concat(r),options:E,onChange:function(e){o(e)}}),n&&Object(f.createElement)(O,{key:"cptda-range-limit",label:m("Limit","custom-post-type-date-archives"),value:t,onChange:function(e){n(e)},min:1,max:100}),a&&Object(f.createElement)(g,{key:"cptda-select-order",label:m("Order","custom-post-type-date-archives"),value:"".concat(s),options:j,onChange:function(e){a(e)}})]}var j=[{value:"ASC",label:m("Ascending")},{value:"DESC",label:m("Descending")}],E=[{value:"alpha",label:m("Alphabetical")},{value:"daily",label:m("Daily")},{value:"monthly",label:m("Monthly")},{value:"postbypost",label:m("Post By Post")},{value:"weekly",label:m("Weekly")},{value:"yearly",label:m("Yearly")}],P=wp.i18n.__,C=wp.components,_=C.Disabled,x=C.PanelBody,S=C.ToggleControl,k=wp.element.Component,M=wp.data.withSelect,T=wp.blockEditor.InspectorControls,D=function(e){function t(){return o()(this,t),i()(this,u()(t).apply(this,arguments))}return h()(t,e),a()(t,[{key:"componentDidMount",value:function(){var e=this.props,t=e.postType,n=e.setAttributes;e.attributes.post_type||n({post_type:t})}},{key:"render",value:function(){var e=this.props,t=e.setAttributes,n=e.attributes,r=n.post_type,o=n.type,s=(n.format,n.order),a=n.limit,c=n.show_post_count,i=n.displayAsDropdown,l=Object.assign({},n);if(delete l.displayAsDropdown,!r)return null;var u=Object(f.createElement)(T,null,Object(f.createElement)(x,{title:P("Archives Settings","custom-post-type-date-archives")},Object(f.createElement)(d.a,{postType:r,onPostTypeChange:function(e){return t({post_type:e})}}),Object(f.createElement)(S,{label:P("Display as Dropdown"),checked:i,onChange:function(){return t({displayAsDropdown:!i,format:i?"html":"option"})}}),Object(f.createElement)(S,{label:P("Show post count","custom-post-type-date-archives"),checked:c,onChange:function(e){return t({show_post_count:e})}}),Object(f.createElement)(w,{limit:a,onLimitChange:function(e){return t({limit:e})},type:o,onTypeChange:function(e){return t({type:e})},order:s,onOrderChange:function(e){return t({order:e})}})));return Object(f.createElement)(b.Fragment,null,u,Object(f.createElement)(_,null,Object(f.createElement)(y.a,{block:"archives",title:"Custom Post Type Archives",defaultClass:"wp-block-archives",attributes:l})))}}]),t}(k),V=M((function(e){var t=e("core/editor");if(t)return{postType:(0,t.getEditedPostAttribute)("type")}}))(D),A=n(1),H=Object(f.createElement)(A.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},Object(f.createElement)(A.Path,{d:"M21 6V20C21 21.1 20.1 22 19 22H5C3.89 22 3 21.1 3 20L3.01 6C3.01 4.9 3.89 4 5 4H6V2H8V4H16V2H18V4H19C20.1 4 21 4.9 21 6ZM5 8H19V6H5V8ZM19 20V10H5V20H19ZM11 12H17V14H11V12ZM17 16H11V18H17V16ZM7 12H9V14H7V12ZM9 18V16H7V18H9Z"})),R=wp.i18n.__;(0,wp.blocks.registerBlockType)("cptda/archives",{title:R("Custom Post Type Archives","custom-post-type-date-archives"),description:R("Display a monthly archive of your posts.","custom-post-type-date-archives"),icon:H,category:"widgets",supports:{align:!0,html:!1},edit:V,save:function(){return null}})},function(e,t,n){"use strict";n.r(t);var r=n(2),o=n.n(r),s=n(3),a=n.n(s),c=n(4),i=n.n(c),l=n(5),u=n.n(l),p=n(9),h=n.n(p),f=n(6),b=n.n(f),y=n(0),d=n(10),m=n(8),v=n(11),g=n(7),O=wp.i18n.__,w=wp.components.SelectControl,j=[{label:O("all posts"),value:"all"},{label:O("posts with future dates only"),value:"future"},{label:O("posts from the current year"),value:"year"},{label:O("posts from the current month"),value:"month"},{label:O("posts from today"),value:"day"}];function E(e){var t=e.include,n=e.onIncludeChange;return[n&&Object(y.createElement)(w,{key:"cptda-select-post-type",label:O("Include Posts","custom-post-type-date-archives"),value:"".concat(t),options:j,onChange:function(e){n(e)}})]}var P=wp.i18n.__,C=wp.components,_=C.Disabled,x=C.PanelBody,S=C.ToggleControl,k=C.RangeControl,M=(C.TextareaControl,C.BaseControl),T=wp.element.Component,D=wp.data.withSelect,V=wp.blockEditor.InspectorControls,A=0,H=function(e){function t(){var e;return o()(this,t),(e=i()(this,u()(t).apply(this,arguments))).onMessageChange=e.onMessageChange.bind(h()(e)),e.messageDebounced=Object(m.debounce)(e.updateMessage,1e3),e.instanceId=A++,e}return b()(t,e),a()(t,[{key:"componentDidMount",value:function(){var e=this.props,t=e.postType,n=e.setAttributes;e.attributes.post_type||n({post_type:t})}},{key:"componentWillUnmount",value:function(){this.messageDebounced.cancel()}},{key:"onMessageChange",value:function(e){this.messageDebounced(e.target.value)}},{key:"updateMessage",value:function(e){(0,this.props.setAttributes)({message:e})}},{key:"render",value:function(){var e=this.props,t=e.setAttributes,n=e.attributes,r="inspector-textarea-control-"+this.instanceId,o=n.post_type,s=n.number,a=n.show_date,c=n.include,i=n.message;if(!o)return null;var l=Object(y.createElement)(V,null,Object(y.createElement)(x,{title:P("Latest Posts Settings","custom-post-type-date-archives")},Object(y.createElement)(g.a,{postType:o,onPostTypeChange:function(e){return t({post_type:e})}}),Object(y.createElement)(k,{label:P("Number of posts","custom-post-type-date-archives"),value:s,onChange:function(e){return t({number:e})},min:1,max:100}),Object(y.createElement)(E,{include:c,onIncludeChange:function(e){return t({include:e})}}),Object(y.createElement)(S,{label:P("Display post date","custom-post-type-date-archives"),checked:a,onChange:function(e){return t({show_date:e})}}),Object(y.createElement)(M,{label:P("Message when no posts are found","custom-post-type-date-archives"),id:r},Object(y.createElement)("textarea",{className:"components-textarea-control__input",id:r,rows:"4",onChange:this.onMessageChange,defaultValue:i}))));return Object(y.createElement)(d.Fragment,null,l,Object(y.createElement)(_,null,Object(y.createElement)(v.a,{block:"recent-posts",title:"Custom Post Type Latest Posts",defaultClass:"wp-block-latest-posts",attributes:this.props.attributes})))}}]),t}(T),R=D((function(e){var t=e("core/editor");if(t)return{postType:(0,t.getEditedPostAttribute)("type")}}))(H),z=n(1),B=Object(y.createElement)(z.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},Object(y.createElement)(z.Path,{d:"M0,0h24v24H0V0z",fill:"none"}),Object(y.createElement)(z.Rect,{x:"11",y:"7",width:"6",height:"2"}),Object(y.createElement)(z.Rect,{x:"11",y:"11",width:"6",height:"2"}),Object(y.createElement)(z.Rect,{x:"11",y:"15",width:"6",height:"2"}),Object(y.createElement)(z.Rect,{x:"7",y:"7",width:"2",height:"2"}),Object(y.createElement)(z.Rect,{x:"7",y:"11",width:"2",height:"2"}),Object(y.createElement)(z.Rect,{x:"7",y:"15",width:"2",height:"2"}),Object(y.createElement)(z.Path,{d:"M20.1,3H3.9C3.4,3,3,3.4,3,3.9v16.2C3,20.5,3.4,21,3.9,21h16.2c0.4,0,0.9-0.5,0.9-0.9V3.9C21,3.4,20.5,3,20.1,3z M19,19H5V5h14V19z"})),N=wp.i18n.__;(0,wp.blocks.registerBlockType)("cptda/latest-posts",{title:N("Custom Post Type latest Posts","custom-post-type-date-archives"),description:N("Display a list of your most recent posts.","custom-post-type-date-archives"),icon:B,category:"widgets",keywords:[N("recent posts","custom-post-type-date-archives")],supports:{align:!0,html:!1},edit:R,save:function(){return null}})},function(e,t,n){"use strict";n.r(t);var r=n(12),o=n.n(r),s=n(2),a=n.n(s),c=n(3),i=n.n(c),l=n(4),u=n.n(l),p=n(5),h=n.n(p),f=n(9),b=n.n(f),y=n(6),d=n.n(y),m=n(0),v=n(10),g=n(15),O=n.n(g),w=n(14),j=n.n(w),E=n(11),P=n(7);function C(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}var _=wp.i18n.__,x=wp.components,S=x.Disabled,k=x.PanelBody,M=wp.element.Component,T=wp.data.withSelect,D=wp.blockEditor.InspectorControls,V=function(e){function t(){var e;return a()(this,t),(e=u()(this,h()(t).apply(this,arguments))).getYearMonth=j()(e.getYearMonth.bind(b()(e)),{maxSize:1}),e.getServerSideAttributes=j()(e.getServerSideAttributes.bind(b()(e)),{maxSize:1}),e}return d()(t,e),i()(t,[{key:"componentDidMount",value:function(){var e=this.props,t=e.postType,n=e.setAttributes;e.attributes.post_type||n({post_type:t})}},{key:"getYearMonth",value:function(e){if(!e)return{};var t=O()(e);return{year:t.year(),month:t.month()+1}}},{key:"getServerSideAttributes",value:function(e,t){return function(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?C(Object(n),!0).forEach((function(t){o()(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):C(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}({},e,{},this.getYearMonth(t))}},{key:"render",value:function(){var e=this.props,t=e.setAttributes,n=e.attributes.post_type;if(!n)return null;var r=Object(m.createElement)(D,null,Object(m.createElement)(k,{title:_("Calendar Settings","custom-post-type-date-archives")},Object(m.createElement)(P.a,{postType:n,onPostTypeChange:function(e){return t({post_type:e})}})));return Object(m.createElement)(v.Fragment,null,r,Object(m.createElement)(S,null,Object(m.createElement)(E.a,{block:"calendar",title:"Custom Post Type Calendar",defaultClass:"wp-block-calendar",attributes:this.getServerSideAttributes(this.props.attributes,this.props.date)})))}}]),t}(M),A=T((function(e){var t=e("core/editor");if(t){var n=t.getEditedPostAttribute;return{date:n("date"),postType:n("type")}}}))(V),H=n(1),R=Object(m.createElement)(H.SVG,{viewBox:"0 0 24 24",xmlns:"http://www.w3.org/2000/svg"},Object(m.createElement)(H.Path,{fill:"none",d:"M0 0h24v24H0V0z"}),Object(m.createElement)(H.G,null,Object(m.createElement)(H.Path,{d:"M7 11h2v2H7v-2zm14-5v14c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2l.01-14c0-1.1.88-2 1.99-2h1V2h2v2h8V2h2v2h1c1.1 0 2 .9 2 2zM5 8h14V6H5v2zm14 12V10H5v10h14zm-4-7h2v-2h-2v2zm-4 0h2v-2h-2v2z"}))),z=wp.i18n.__;(0,wp.blocks.registerBlockType)("cptda/calendar",{title:z("Custom Post Type Calendar","custom-post-type-date-archives"),description:z("A calendar of your site’s custom post type posts.","custom-post-type-date-archives"),icon:R,category:"widgets",keywords:[z("posts","custom-post-type-date-archives"),z("archive","custom-post-type-date-archives")],supports:{align:!0},edit:A,save:function(){return null}})}]);