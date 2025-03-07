/**
 * TinyMCE plugin for adding heading permalinks
 */
(function() {
    tinymce.create('tinymce.plugins.WikiHeadingPermalink', {
        init: function(editor, url) {
            // Add button that opens a window
            editor.addButton('wiki_heading_permalink', {
                title: 'Insert Heading Permalink',
                icon: 'link',
                onclick: function() {
                    // Open window
                    editor.windowManager.open({
                        title: 'Insert Heading Permalink',
                        body: [
                            {
                                type: 'textbox',
                                name: 'heading_text',
                                label: 'Heading Text',
                                tooltip: 'Enter the exact heading text to link to'
                            },
                            {
                                type: 'textbox',
                                name: 'link_text',
                                label: 'Link Text',
                                value: 'Link to section'
                            },
                            {
                                type: 'textbox',
                                name: 'class',
                                label: 'CSS Class (optional)'
                            }
                        ],
                        onsubmit: function(e) {
                            // Insert content when the window form is submitted
                            var shortcode = '[heading_permalink';
                            
                            if (e.data.heading_text) {
                                shortcode += ' text="' + e.data.heading_text + '"';
                            }
                            
                            if (e.data.link_text) {
                                shortcode += ' link_text="' + e.data.link_text + '"';
                            }
                            
                            if (e.data.class) {
                                shortcode += ' class="' + e.data.class + '"';
                            }
                            
                            shortcode += ']';
                            
                            editor.insertContent(shortcode);
                        }
                    });
                }
            });
        },
        
        createControl: function(n, cm) {
            return null;
        },
        
        getInfo: function() {
            return {
                longname: 'Wiki Heading Permalink',
                author: 'Wiki Dynamic Heading Anchors',
                authorurl: '',
                infourl: '',
                version: '1.0'
            };
        }
    });
    
    // Register plugin
    tinymce.PluginManager.add('wiki_heading_permalink', tinymce.plugins.WikiHeadingPermalink);
})();
