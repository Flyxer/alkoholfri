import domReady from '@roots/sage/client/dom-ready';
import { registerBlockStyle, unregisterBlockStyle } from '@wordpress/blocks';

import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

/**
 * Editor entrypoint
 */
domReady(() => {
  unregisterBlockStyle('core/button', 'outline');

  registerBlockStyle('core/button', {
    name: 'outline',
    label: 'Outline',
  });

  registerBlockStyle('core/paragraph', {
    name: 'highlight',
    label: 'Highlight',
  });

  registerBlockStyle('core/paragraph', {
    name: 'large',
    label: 'Large',
  });


  registerBlockType( 'pfc/quote-slider', {
    title: "Quote-Slider",
    icon: "quote",
    edit: () => {
      const blockProps = useBlockProps();

      return (
        <div { ...blockProps }>
          <InnerBlocks />
        </div>
      );
    },

    save: () => {
      const blockProps = useBlockProps.save();

      return (
        <div { ...blockProps }>
          <InnerBlocks.Content />
        </div>
      );
    },
  } );
});

/**
 * @see {@link https://webpack.js.org/api/hot-module-replacement/}
 */
import.meta.webpackHot?.accept(console.error);
