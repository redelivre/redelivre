(function() {
    tinymce.create('tinymce.plugins.monarch', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {
            var t = this;

            ed.addButton('on_media', {
                title : 'Monarch On Media',
                cmd : 'on_media'
            });

            ed.addCommand('on_media', function() {
                var selected_text = ed.selection.getContent(),
                    content = ed.getContent(),
                    pattern = new RegExp( "\\[caption.*\\].*" + selected_text + ".*\\[\\/caption\\]" );
                    return_text = '',
                    final_content = '',
                    match = null;

                if ( pattern.test( content ) ) {
                    match = pattern.exec( content );
                    final_content = content.replace( match[0], '[et_social_share_media]' + match[0] + '[/et_social_share_media]' );

                    ed.selection.setContent( match[0] );
                } else {
                    return_text = '[et_social_share_media]' + selected_text + '[/et_social_share_media]';
                    final_content = content.replace( selected_text, return_text );
                }

                ed.setContent( final_content );
            });

        },


        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
               longname : "Elegant Monarch Button",
                author : 'Elegant Themes',
                authorurl : 'http://www.elegantthemes.com/',
                infourl : 'http://www.elegantthemes.com/',
                version : "1.0"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add( 'monarch', tinymce.plugins.monarch );
})();