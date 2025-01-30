const { registerPlugin } = wp.plugins;

import Flyxer_Post_Fields from './flyxer-post-fields';

registerPlugin( 'flyxer-postmeta-plugin', {
    render() {
        return(<Flyxer_Post_Fields />);
    }
} );