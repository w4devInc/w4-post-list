import {
	registerBlockType
} from '@wordpress/blocks';

import {
	Disabled,
	PanelBody,
	SelectControl
} from '@wordpress/components';

import {
	RichText,
	InspectorControls
} from '@wordpress/block-editor';

import {
	withSelect
} from '@wordpress/data';

import ServerSideRender from '@wordpress/server-side-render';

import { __ } from '@wordpress/i18n';

registerBlockType( 'w4-post-list/postlist', {
	title: __( 'W4 Post List', 'w4pl' ),
	description: __( 'Display a list from w4 post list plugin.', 'w4pl' ),
	supports: {
		align: false,
		html: false,
	},
	icon: 'list-view',
	category: 'widgets',
	attributes: {
		listId: {
			type: 'string',
			default: '0'
		}
	},
	edit: withSelect( ( select ) => {
        return {
            posts: select( 'core' ).getEntityRecords( 'postType', 'w4pl' ),
        };
    } )( ( { posts, className, attributes, setAttributes } ) => {
		// console.log ( attributes );
        if ( ! posts ) {
            return __( 'Loading...' );
        }

        if ( posts && posts.length === 0 ) {
            return __( 'No list found', 'w4pl' );
        }

		const { listId } = attributes;

		const listOptions = [{
			value: 0,
			label: __( 'Select a list', 'w4pl' )
		}];

		for (var i =0; i < posts.length; i++ ) {
			listOptions.push({
				value: posts[i].id,
				label: posts[i].title.rendered
			});
		}

		function onChangeList( newValue ) {
			setAttributes( {
				listId: newValue
			} );
		}

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Post List Settings', 'w4pl' ) }>
						<SelectControl
							label={ __( 'Select a list', 'w4pl' ) }
							value={ listId }
							options={ listOptions }
							onChange={ onChangeList }
						/>
						<p>{ __( 'Or', 'w4pl' ) }<br/> <a target="_blank" href="post-new.php?post_type=w4pl">{ __( 'Create a new one', 'w4pl' ) }</a></p>
					</PanelBody>
				</InspectorControls>
				<Disabled>
					<ServerSideRender
						block="w4-post-list/postlist"
						attributes={ attributes }
					/>
				</Disabled>
			</>
		);
    } )
} );
