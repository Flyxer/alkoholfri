const { __ } = wp.i18n;
const { PluginDocumentSettingPanel } = wp.editPost;
const { PanelRow } = wp.components;

const Flyxer_Post_Fields = () => {
    return(
        <PluginDocumentSettingPanel title={ __( 'My Custom Post meta', 'txtdomain') } initialOpen="true">
            <PanelRow>
                Hello there.
            </PanelRow>
        </PluginDocumentSettingPanel>
    );
}

export default Flyxer_Post_Fields;