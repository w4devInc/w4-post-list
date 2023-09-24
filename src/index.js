import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit.js';

registerBlockType('w4-post-list/postlist', {
	title: __('W4 Post List', 'w4-post-list'),
	description: __('Display a list from w4 post list plugin.', 'w4-post-list'),
	supports: {
		align: false,
		html: false,
	},
	icon: 'list-view',
	category: 'widgets',
	attributes: {
		listId: {
			type: 'string',
			default: ''
		}
	},
	edit: Edit
});
