/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import {__} from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {useBlockProps} from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */

const {Button} = wp.components;
const {MediaUpload, MediaUploadCheck} = wp.blockEditor;

const ALLOWED_MEDIA_TYPES = ['image'];

function MyMediaUploader({mediaIDs, onSelect}) {
	return (
		<MediaUploadCheck>
			<MediaUpload
				onSelect={onSelect}
				allowedTypes={ALLOWED_MEDIA_TYPES}
				value={mediaIDs}
				render={({open}) => (
					<Button
						onClick={open}
						className="button button-large"
					>{mediaIDs.length < 1 ? 'Upload/Select Images' : 'Edit'}</Button>
				)}
				gallery
				multiple
			/>
		</MediaUploadCheck>
	);
}


export default function Edit({attributes, setAttributes})

	return (
		<div class={"share_post"}>
			Share Links
		</div>
	);
}
