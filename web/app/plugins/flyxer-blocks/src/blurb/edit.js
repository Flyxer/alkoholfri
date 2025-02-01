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


export default function Edit({attributes, setAttributes}) {
	const onSelect = (items) => {
		setAttributes({
			images: items.map(item => {
				return {
					mediaID: parseInt(item.id, 10),
					mediaURL: item.url,
					thumbnail: item.url,
				};
			}),
		});
	};
	const removeImages = () => {
		setAttributes({
			images: []
		});
	}

	const onSelectMask = (media) => {
		setAttributes({
			maskID: parseInt(media.id, 10),
			mask: media.url
		});
	}
	const removeMask = () => {
		setAttributes({
			maskID: 0,
			mask: ''
		});
	}

	const blockProps = useBlockProps();

	const size = 300;
	const number = attributes.images.length;

	const r = size;

	const deg = ((360 / number));
	const x = r * Math.cos(rad(deg)) + 150;
	const y = r * Math.sin(rad(deg)) + 150;

	const deg2 = 0;
	const x2 = r * Math.cos(rad(deg2)) + 150;
	const y2 = r * Math.sin(rad(deg2)) + 150;


	function rad(angle) {
		return angle * Math.PI / 180;
	}


	return (
		<div {...blockProps}>
			{
				attributes.images.length >= 1 &&
				(
					!attributes.maskID ?
					<MediaUploadCheck>
						<MediaUpload
							allowedTypes={ ['image'] }
							onSelect={onSelectMask}
							render={({open}) => (
								<Button
									className={attributes.maskID == 0 ? '' : ''}
									onClick={open}
								>
									Add Mask
								</Button>
							)}
						/>
					</MediaUploadCheck> :
					<MediaUploadCheck>
						<Button onClick={removeMask} isDestructive>{__('Remove Mask', 'awp')}</Button>
					</MediaUploadCheck>
				)
			}
			<div className={"swap mask"} style={ attributes.mask ? {maskImage:"url("+attributes.mask+")"} : {} }>
			{attributes.images.length >= 1 ? (
				<svg width={size} height={size} viewbox={"-" + (size / 2) + " -" + (size / 2) + " " + size + " " + size}
					 xmlns="http://www.w3.org/2000/svg"
				>
					{attributes.images.map((item, index) => (
						<>
							<mask id={"mask-" + index}>
								<path
									d={"M " + x + " " + y + " A " + r + " " + r + " 0 0 0 " + x2 + " " + y2 + "L " + (size / 2) + " " + (size / 2)}
									style={{
										fill: "white",
										transform: "rotate(" + (360 / number * index) + "deg)",
										transformOrigin: "50% 50%"
									}}

								/>
							</mask>
							<image mask={"url(#mask-" + index + ")"} href={item.thumbnail || item.mediaURL}
								   height={size} width={size}
								   preserveAspectRatio="xMinYMin slice"
							/>
						</>
					))}
				</svg>
			) : <p>Click the button and add some images to your slider! :)</p>}
			</div>
			<MyMediaUploader
				mediaIDs={attributes.images.map(item => item.mediaID)}
				onSelect={onSelect}
			/>
			{attributes.images.length >= 1 &&
				<MediaUploadCheck>
					<Button onClick={removeImages} isDestructive>{__('Remove Images', 'awp')}</Button>
				</MediaUploadCheck>
			}
		</div>
	);
}
