import {
	Disabled,
	PanelBody,
	PanelRow,
	ComboboxControl
} from '@wordpress/components';

import {
	InspectorControls
} from '@wordpress/block-editor';

import apiFetch from '@wordpress/api-fetch';
import { Component } from '@wordpress/element';

import ServerSideRender from '@wordpress/server-side-render';

import { __ } from '@wordpress/i18n';

export default class Edit extends Component {
	constructor(props) {
		super(props)
		this.state = {
			choices: []
		}

		this.onListSearch = this.onListSearch.bind(this);
		this.onListSelect = this.onListSelect.bind(this);
	}

	componentDidMount() {
		const { listId } = this.props.attributes;
		if (parseInt(listId, 10) > 0) {
			this.loadChoices({ include: listId })
		}
	}

	loadChoices({ search, include }) {
		let path = '/wp/v2/w4pl?_fields=id,title&status=publish,pending,draft&per_page=10&_locale=user'
		if (search) {
			path += '&search=' + search
		}
		if (include) {
			path += '&include[]=' + include
		}

		apiFetch({ path: path })
			.then((posts) => {
				const choices = this.formatChoices(posts)
				this.setState({ choices })
			});
	}

	formatChoices(posts) {
		if (!posts) {
			return [];
		}

		return posts.map((post) => {
			return {
				value: post.id.toString(),
				label: `${post.title.rendered} (#${post.id})`
			}
		})
	}

	onListSearch(search) {
		this.loadChoices({ search })
	}

	onListSelect(inputValue) {
		this.props.setAttributes({
			listId: inputValue
		});
	}

	render() {
		const { attributes } = this.props;
		const { choices } = this.state

		return (
			<>
				<InspectorControls>
					<PanelBody title={__('Settings', 'w4-post-list')}>
						<PanelRow>
							<ComboboxControl
								label="Select a list"
								value={attributes.listId}
								options={choices}
								onChange={this.onListSelect}
								onFilterValueChange={this.onListSearch}
							/>
						</PanelRow>
						<PanelRow>
							<p>{__('Or', 'w4-post-list')} <a target="_blank" href="post-new.php?post_type=w4pl">{__('Create a new list', 'w4-post-list')}</a></p>
						</PanelRow>
					</PanelBody>
				</InspectorControls>
				<Disabled>
					<ServerSideRender
						block="w4-post-list/postlist"
						attributes={attributes}
					/>
				</Disabled>
			</>
		);
	}
}