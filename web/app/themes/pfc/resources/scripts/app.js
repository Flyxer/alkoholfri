import domReady from '@roots/sage/client/dom-ready';

/**
 * Application entrypoint
 */
domReady(async () => {
  // ...

  var acordions = document.getElementsByClassName("wp-block-gutena-accordion-panel");
  for(var index=0;index < acordions.length;index++){
    if(!acordions[index].querySelector(".wp-block-gutena-accordion-panel-content")){

      var element = acordions[index];
      element.classList.add("empty");
      element.querySelector(".trigger-plus-minus").remove();

      var old_element = element.querySelector(".gutena-accordion-block__panel-title");
      var new_element = old_element.cloneNode(true);
      old_element.parentNode.replaceChild(new_element, old_element);

    }
  }
});

/**
 * @see {@link https://webpack.js.org/api/hot-module-replacement/}
 */
import.meta.webpackHot?.accept(console.error);
