/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Internal dependencies
 */
import Edit from './edit';
import save from './save';
import metadata from './block.json';


/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( metadata.name, {
	/**
	 * @see ./edit.js
	 */
	edit: Edit,

	/**
	 * @see ./save.js
	 */
	save,
	supports: {
		// Remove the support for wide alignment.
		alignWide: true
	},
	attributes: {
		size: {
			type: 'string',
			default: "",
		},
		title: {
			type: 'string',
			default: "",
		},
		rows: {
			type: 'number',
			default: 1,
		},
		excerpt_length: {
			type: 'number',
			default: 15,
		},
		categories_selected: {
			type: 'array',
			default: [],
			selector: '.category',
			query: {
				value: {
					type: 'number',
					source: 'attribute',
					attribute: 'data-id',
				},
			}
		},
		tags_selected: {
			type: 'array',
			default: [],
			selector: '.tag',
			query: {
				value: {
					type: 'number',
					source: 'attribute',
					attribute: 'data-id',
				},
			}
		}
	},
} );
