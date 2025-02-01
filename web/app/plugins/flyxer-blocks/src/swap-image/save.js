/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */
export default function save({ attributes }) {
	if ( attributes.images.length < 1 ) {
		return null;
	}

	const blockProps = useBlockProps.save( { className: 'swap' } );

	return (
		<div { ...blockProps }>
			{ attributes.maskID > 0 &&
				<img className="mask" src={ attributes.mask} data-id={ attributes.maskID}/>
			}
			{ attributes.images.map( item => (
				<div className="swap-image" key={ 'image-' + item.mediaID } data-id={ item.mediaID }>
					<img src={ item.mediaURL } data-id={ item.mediaID } data-thumb={ item.thumbnail } />
				</div>
			) ) }
		</div>
	);
}

