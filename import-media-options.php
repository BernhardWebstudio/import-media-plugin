<?php

function import_media_get_options() {
    $defaults = array(
        'root_directory' => ABSPATH . __('media-files-to-import', 'bw-import-media'),
        'old_url' => '',
        'skipdirs' => '',
        'import_post' => true,
        'import_page' => true,
        'images' => true,
        'documents' => true,
        'timestamp' => 'filemtime',
        'file_extensions' => 'rtf,doc,docx,xls,xlsx,csv,ppt,pps,pptx,ppsx,pdf,zip,wmv,avi,flv,mov,mpeg,mp3,m4a,wav',
        'firstrun' => true,
        'import_date' => 0,
        'date_region' => '',
    );
    $options = get_option('import_media');
    if (!is_array($options))
        $options = array();
    return array_merge($defaults, $options);
}

function import_media_options_page() {
    ?>
    <div class="wrap">

        <form method="post" id="import_media" action="options.php">
            <?php
            settings_fields('import_media');
            get_settings_errors('import_media');
            $options = import_media_get_options();
            //$msg .= '<pre>'. print_r( $options, true ) .'</pre>';
            //echo esc_html( $msg );
            ?>

            <div class="ui-tabs">

                <h2><?php _e('Media Import Settings', 'bw-import-media'); ?></h2>
                <?php
                if ($options['firstrun'] === true) {
                    echo '<p>' . sprintf(__('Welcome to Media Import! This is a complicated importer with many options. Please look through all the tabs on this page before running your import.', 'bw-import-media'), 'options-general.php?page=import-media.php') . '</p>';
                }
                ?>
                <h2 class="nav-tab-wrapper">
                    <ul class="ui-tabs-nav">
                        <li><a class="nav-tab" href="#tabs-1"><?php _e("Files", 'bw-import-media'); ?></a></li>
                        <li><a class="nav-tab" href="#tabs-2"><?php _e("Content", 'bw-import-media'); ?></a></li>
                        <li><a class="nav-tab" href="#tabs-6"><?php _e("Tools", 'bw-import-media'); ?></a></li>
                    </ul>
                </h2>

                <!-- FILES -->
                <div id="tabs-1">
                    <h3><?php _e("Files", 'bw-import-media'); ?></h3>				
                    <table class="form-table ui-tabs-panel" id="files">

                        <tr valign="top">
                            <th scope="row"><?php _e("Old site URL", 'bw-import-media'); ?></th>
                            <td><p><label><input type="text" name="import_media[old_url]" id="old_url" 
                                                 value="<?php echo esc_attr($options['old_url']); ?>" class="widefloat" /> </label><br />
                                    <span class="description">
    <?php _e('This is the site, from which the files will be imported.', 'bw-import-media'); ?>
                                    </span>
                                </p></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php _e("Document extensions to include (if documents import is enabled)", 'bw-import-media'); ?></th>
                            <td><p><label><input type="text" name="import_media[file_extensions]" id="file_extensions" 
                                                 value="<?php echo esc_attr($options['file_extensions']); ?>" class="widefloat" /> </label><br />
                                    <span class="description">
    <?php _e("File extensions, without periods, separated by commas. All other file types, which are not images, will 
							be ignored.", 'bw-import-media'); ?>
                                    </span>
                                </p></td>
                        </tr>
                        
                        <tr valign="top">
                                        <th scope="row"><?php _e("Import...", 'bw-import-media'); ?></th>
                                        <td><p><label>
                                                    <input type="checkbox" name="import_media[images]" id="skipdirs" 
                                                        value="<?php echo esc_attr($options['images']); ?>" class="widefloat" />
                                                            <?php _e("Update images"); ?>
                                                </label><br />
                                                <label>
                                                <input type="checkbox" name="import_media[documents]" id="skipdirs" 
                                                        value="<?php echo esc_attr($options['documents']); ?>" class="widefloat" /> 
                                                            <?php _e("Update links"); ?> 
                                                </label><br />
                                                        <span class="description">
    <?php _e("Select, if you want to import documents and or images", 'bw-import-media'); ?>
                                                        </span>
                                                </p></td>
                                </tr>
                        <!--
                                        <tr valign="top">
                                        <th scope="row"><?php _e("Directories to exclude", 'bw-import-media'); ?></th>
                                        <td><p><label><input type="text" name="import_media[skipdirs]" id="skipdirs" 
                                                        value="<?php echo esc_attr($options['skipdirs']); ?>" class="widefloat" />  </label><br />
                                                        <span class="description">
    <?php _e("Directory names, without slashes, separated by commas. All files in these directories 
							will be ignored.", 'bw-import-media'); ?>
                                                        </span>
                                                </p></td>
                                </tr>
                        -->

                    </table>

                </div>

                <!-- CONTENT -->	
                <div id="tabs-2">
                <!--<h3><?php _e("Content", 'bw-import-media'); ?></h3>				
                        <table class="form-table ui-tabs-panel" id="content">
                                <tr valign="top" id="contentselect">
                                <th scope="row"><?php _e("Select content by", 'bw-import-media'); ?></th>
                                <td><p><label>
                                                <input type="radio" name="import_media[import_content]"
                                                        value="tag" <?php checked($options['import_content'], 'tag'); ?> class="showrow" title="content" />
    <?php _e('HTML tag', 'bw-import-media'); ?></label> 
                                                &nbsp;&nbsp;
                                                <label>
                                                <input type="radio" name="import_media[import_content]"
                                                        value="region" <?php checked($options['import_content'], 'region'); ?> class="showrow" title="content" />
    <?php _e('Dreamweaver template region', 'bw-import-media'); ?></label>
                                                &nbsp;&nbsp;
                                                <label>
                                                <input type="radio" name="import_media[import_content]"
                                                        value="file" <?php checked($options['import_content'], 'file'); ?> class="showrow" title="content" />
    <?php _e('Import entire file', 'bw-import-media'); ?></label>
                                        </p>
                                        
                                        
                                        <table>
                                                <tr id="content-tag" <?php if ($options['import_content'] != 'tag') echo 'style="display: none;"'; ?>>
                                        <td class="taginput">
                                            <label><?php _e("Tag", 'bw-import-media'); ?><br />
                                            <input type="text" name="import_media[content_tag]" id="content_tag" value="<?php echo esc_attr($options['content_tag']); ?>" />
                                            </label>
                                            <br />
                                            <span class="description"><?php _e("The HTML tag, without brackets", 'bw-import-media'); ?></span>
                                                </td>
                                                <td class="taginput">
                                            <label><?php _e("Attribute", 'bw-import-media'); ?><br />
                                            <input type="text" name="import_media[content_tagatt]" id="content_tagatt" value="<?php echo esc_attr($options['content_tagatt']); ?>" />
                                            </label>
                                            <br />
                                            <span class="description"><?php _e("Leave blank to use a tag without an attribute, or when the attributes don't matter, such as &lt;body&gt;", 'bw-import-media'); ?></span>
                                                </td>
                                                <td class="taginput">
                                            <label><?php _e("= Value", 'bw-import-media'); ?><br />
                                            <input type="text" name="import_media[content_attval]" id="content_attval" value="<?php echo esc_attr($options['content_attval']); ?>" />
                                            </label>
                                            <br />
                                            <span class="description"><?php _e("Enter the attribute's value ( such as width, ID, or class name ) without quotes", 'bw-import-media'); ?></span>
                                        </td>
                                </tr>
                                <tr id="content-region" <?php if ($options['import_content'] != 'region') echo 'style="display: none;"'; ?>>
                                        <td colspan="3">
                                                <label><?php _e("Dreamweaver template region", 'bw-import-media'); ?><br />
                                        <input type="text" name="import_media[content_region]" value="<?php echo esc_attr($options['content_region']); ?>" />  
                                        </label><br />
                                        <span class="description"><?php _e("The name of the editable region ( e.g. 'Main Content' )", 'bw-import-media'); ?></span>
                                        </td>
                                </tr>
                                </table>
                                
                                        </td>
                        </tr>

                                <tr>
                                <th><?php _e("More content options", 'bw-import-media'); ?></th>
                                <td>
                                        <label><input name="import_media[import_images]" id="import_images"  type="checkbox" value="1" 
    <?php checked($options['import_images'], '1'); ?> /> <?php _e("Import linked images", 'bw-import-media'); ?></label>
                                </td>
                                </tr>
                                <tr>
                                <th></th>
                                <td>
                                        <label><input name="import_media[import_documents]" id="import_documents" value="1" type="checkbox" <?php checked($options['import_documents']); ?> class="toggle" /> 
    <?php _e("Import linked documents", 'bw-import-media'); ?></label>
                                </td>
                                </tr>
                                <tr class="import_documents" 
    <?php if (isset($options['import_documents']) && !$options['import_documents']) echo 'style="display:none;"'; ?>>
                                <th><?php _e("Allowed file types", 'bw-import-media'); ?></th>
                            <td><label>
                                                <input type="text" name="import_media[document_mimes]" id="document_mimes" 
                                                        value="<?php echo esc_attr($options['document_mimes']); ?>" class="widefloat" />  </label><br />
                            <span class="description"><?php _e("Enter file extensions without periods, separated by commas. File types not listed here will not be imported to the media library. <br />
		Suggested: rtf, doc, docx, xls, xlsx, csv, ppt, pps, pptx, ppsx, pdf, zip, wmv, avi, flv, mov, mpeg, mp3, m4a, wav<br />", 'bw-import-media'); ?></span>
                            </td> 
                       </tr>
                                <tr>
                                <th></th>
                                <td>
                                        <label><input name="import_media[fix_links]" id="fix_links" value="1" type="checkbox" <?php checked($options['fix_links']); ?> /> 
    <?php _e("Update internal links", 'bw-import-media'); ?></label>
                                </td>
                                </tr>
                                <th></th>
                                <td>
                                        <label><input name="import_media[meta_desc]" id="meta_desc" value="1" type="checkbox" <?php checked($options['meta_desc']); ?> /> 
    <?php _e("Use meta description as excerpt", 'bw-import-media'); ?></label>
                                </td>
                                </tr>
                                <tr>
                                <th></th>
                                <td>
                                        <label><input name="import_media[encode]" id="encode"  type="checkbox" value="1" 
    <?php checked($options['encode'], '1'); ?> /> <?php _e("Convert special characters ( accents and symbols )", 'bw-import-media'); ?> </label>
                                </td>
                                </tr>
                                <tr>
                                <th></th>
                                <td>
                                        <label><input name="import_media[clean_html]" id="clean_html"  type="checkbox" value="1" 
    <?php checked($options['clean_html'], '1'); ?> class="toggle" />
    <?php _e("Clean up bad ( Word, Frontpage ) HTML", 'bw-import-media'); ?> </label>
                                </td>
                                </tr>
                                <tr class="clean_html" <?php if (!$options['clean_html']) echo 'style="display:none;"'; ?>>
                                 
                                        <th><?php _e("Allowed HTML", 'bw-import-media'); ?></th>
                                    <td>    <label>
                                        <input type="text" name="import_media[allow_tags]" id="allow_tags" 
                                                                value="<?php echo esc_attr($options['allow_tags']); ?>" class="widefloat" />  </label><br />
                                        <span class="description"><?php _e("Enter tags ( with brackets ) to be preserved. All tags not listed here will be removed. <br />Suggested: ", 'bw-import-media'); ?> 
                                        &lt;p&gt;
                                        &lt;br&gt;
                                        &lt;img&gt;
                                        &lt;a&gt;
                                        &lt;ul&gt;
                                        &lt;ol&gt;
                                        &lt;li&gt;
                                                        &lt;dl&gt;
                                                        &lt;dt&gt;
                                                        &lt;dd&gt;
                                        &lt;blockquote&gt;
                                        &lt;cite&gt;
                                        &lt;em&gt;
                                        &lt;i&gt;
                                        &lt;strong&gt;
                                        &lt;b&gt;
                                        &lt;h2&gt;
                                        &lt;h3&gt;
                                        &lt;h4&gt;
                                        &lt;h5&gt;
                                        &lt;h6&gt;
                                        &lt;hr&gt;
                                        <br />

                                        <em><?php _e("If you have data tables, also include:", 'bw-import-media'); ?></em> 
                                        &lt;table&gt;
                                        &lt;tbody&gt;
                                        &lt;thead&gt;
                                        &lt;tfoot&gt;
                                        &lt;tr&gt;
                                        &lt;td&gt;
                                        &lt;th&gt;
                                        &lt;caption&gt;
                                        &lt;colgroup&gt;
                                        </span>
                                    </td> 
                                        </tr>
                                        <tr class="clean_html" <?php if (!$options['clean_html']) echo 'style="display:none;"'; ?>>
                                        <th><?php _e("Allowed attributes", 'bw-import-media'); ?></th>
                                    <td><label>
                                                        <input type="text" name="import_media[allow_attributes]" id="allow_attributes" 
                                                                value="<?php echo esc_attr($options['allow_attributes']); ?>" class="widefloat" />  </label><br />
                                    <span class="description"><?php _e("Enter attributes separated by commas. All attributes not listed here will be removed. <br />Suggested: href, src, alt, title<br />
			    			<em>If you have data tables, also include:</em> summary, rowspan, colspan, span", 'bw-import-media'); ?></span>
                                    </td> 
                               </tr>
                        </table>-->

                </div>

                <!-- TOOLS -->
                <div id="tabs-6">
                    <h3><?php _e("Tools", 'bw-import-media'); ?></h3>				
                    <table class="form-table ui-tabs-panel" id="tools">
                        <tr valign="top">
                            <th scope="row"><?php _e("Regenerate <kbd>.htaccess</kbd> redirects", 'bw-import-media'); ?></th>
                            <td><p><?php printf(__('If you <a href="%s">changed your permalink structure</a> after you imported files, you can <a href="%s">regenerate the redirects</a>.', 'bw-import-media'), 'wp-admin/options-permalink.php', wp_nonce_url('admin.php?import=html&step=2', 'import_media_regenerate')) ?></p></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php _e("Other helpful plugins", 'bw-import-media'); ?></th>
                            <td>
                                <p><?php printf(__('<a href="%s">Broken Link Checker</a> finds broken links and references to missing media files. Since the importer does not handle links or media files other than images, you should run this to see what else needs to be copied or updated from your old site.', 'bw-import-media'), 'http://wordpress.org/extend/plugins/broken-link-checker/'); ?></p>
                                <p><?php printf(__('<a href="%s">Search and Replace</a> helps you fix many broken links at once, if you have many links to the same files or if there is a pattern ( like <kbd>&lt;a href="../../files"&gt;</kbd> ) to your broken links.', 'bw-import-media'), 'http://wordpress.org/extend/plugins/search-and-replace/'); ?></p>
                                <p><?php printf(__('<a href="%s">Redirection</a> provides a nice admin interface for managing redirects. If you would rather not edit your <kbd>.htaccess</kbd> file, or if you just want to redirect one or two of your old pages, you can ignore the redirects generated by the importer. Instead, copy the post\'s old URL from the custom fields and paste it into Redirection\'s options.', 'bw-import-media'), 'http://wordpress.org/extend/plugins/redirection/'); ?></p>
                                <p><?php printf(__('<a href="%s">Add from Server</a> lets you import media files that are on your server but not part of the WordPress media library.', 'bw-import-media'), 'http://wordpress.org/extend/plugins/add-from-server/'); ?></p>
                                <p><?php printf(__('<a href="%s">Add Linked Images to Gallery</a> is helpful if you have imported data using other plugins and you would like to import linked images. However, it handles only images that are referenced with complete URLs; relative paths will not work.', 'bw-import-media'), 'http://wordpress.org/extend/plugins/add-linked-images-to-gallery-v01/'); ?></p>
                            </td>
                        </tr>
                        <!--<tr>
                            <th><?php _e('Donate', 'bw-import-media') ?></th>
                            <td>
                                <p><?php printf(__('If this importer has saved you hours of copying and pasting, a <a href="%s">donation toward future development</a> would be much appreciated!', 'bw-import-media'), 'http://stephanieleary.com/code/wordpress/import-media/'); ?></p>
                            </td>
                        </tr>-->
                    </table>
                </div>		

            </div>	<!-- UI tabs wrapper -->	
            <p class="submit">
                <input type="submit" class="button-primary" value="<?php _e('Save settings', 'bw-import-media') ?>" />
                <?php if (!$options['firstrun']) { ?>
                    <a href="admin.php?import=media" class="button-secondary">Import</a>
    <?php } ?>
            </p>
        </form>

    </div> <!-- .wrap -->
    <?php
}

function import_media_validate_options($input) {
    // Validation/sanitization. Add errors to $msg[].
    $msg = array();
    $linkmsg = '';
    $msgtype = 'error';

    // sanitize path for Win32
    $input['root_directory'] = str_replace('\\', '/', $input['root_directory']);
    $input['root_directory'] = preg_replace('|/+|', '/', $input['root_directory']);

    if (validate_import_file($input['root_directory']) > 0) {
        $msg[] = __("The beginning directory you entered is not an absolute path. Relative paths are not allowed here.", 'bw-import-media');
        $input['root_directory'] = ABSPATH . __('html-files-to-import', 'bw-import-media');
    }

    $input['root_directory'] = rtrim($input['root_directory'], '/');
    $input['old_url'] = esc_url(rtrim($input['old_url'], '/'));

    // trim the extensions, skipped dirs, allowed attributes. Invalid ones will not cause problems.
    $input['file_extensions'] = str_replace('.', '', $input['file_extensions']);
    $input['file_extensions'] = str_replace(' ', '', $input['file_extensions']);
    $input['file_extensions'] = strtolower($input['file_extensions']);
    $input['document_mimes'] = str_replace('.', '', $input['document_mimes']);
    $input['document_mimes'] = str_replace(' ', '', $input['document_mimes']);
    $input['document_mimes'] = strtolower($input['document_mimes']);
    //$input['skipdirs'] = str_replace(' ', '', $input['skipdirs']);
   
    if (!in_array($input['status'], get_post_stati()))
        $input['status'] = 'publish';

    $post_types = get_post_types(array('public' => true), 'names');
    if (!in_array($input['type'], $post_types))
        $input['type'] = 'page';

    if (!in_array($input['timestamp'], array('now', 'filemtime', 'customfield')))
        $input['timestamp'] = 'filemtime';

    if (!in_array($input['import_content'], array('tag', 'region', 'file'))) {
        $input['import_content'] = 'tag';
    }
    if (!in_array($input['import_title'], array('tag', 'region', 'filename'))) {
        $input['import_title'] = 'tag';
    }

    // trim region/tag/attr/value
    if (!empty($input['content_region'])) {
        $input['content_region'] = sanitize_text_field($input['content_region']);
    }
    if (!empty($input['content_tag'])) {
        $input['content_tag'] = sanitize_text_field($input['content_tag']);
    }
    if (!empty($input['content_tagatt'])) {
        $input['content_tagatt'] = sanitize_text_field($input['content_tagatt']);
    }
    if (!empty($input['content_attval'])) {
        $input['content_attval'] = sanitize_text_field($input['content_attval']);
    }
    if (!empty($input['title_region'])) {
        $input['title_region'] = sanitize_text_field($input['title_region']);
    }
    if (!empty($input['title_tag'])) {
        $input['title_tag'] = sanitize_text_field($input['title_tag']);
    }
    if (!empty($input['title_tagatt'])) {
        $input['title_tagatt'] = sanitize_text_field($input['title_tagatt']);
    }
    if (!empty($input['title_attval'])) {
        $input['title_attval'] = sanitize_text_field($input['title_attval']);
    }
    if (!empty($input['date_region'])) {
        $input['date_region'] = sanitize_text_field($input['date_region']);
    }
    if (!empty($input['date_tag'])) {
        $input['date_tag'] = sanitize_text_field($input['date_tag']);
    }
    if (!empty($input['date_tagatt'])) {
        $input['date_tagatt'] = sanitize_text_field($input['date_tagatt']);
    }
    if (!empty($input['date_attval'])) {
        $input['date_attval'] = sanitize_text_field($input['date_attval']);
    }


    if (!isset($input['root_parent'])) {
        $input['root_parent'] = 0;
    }
    
    if (!isset($input['documents'])) {
        $input['documents'] = FALSE;
    } else {
        $input['documents'] = TRUE;
    }
    
    if (!isset($input['images'])) {
        $input['images'] = FALSE;
    } else {
        $input['images'] = TRUE;
    }

    // If settings have been saved at least once, we can turn this off.
    $input['firstrun'] = false;


    // Send custom updated message
    $msg = implode('<br />', $msg);

    if (empty($msg)) {

        $linkstructure = get_option('permalink_structure');
        if (empty($linkstructure))
            $linkmsg = sprintf(__('If you intend to <a href="%s">set a permalink structure</a>, you should do it 
				before importing so the <kbd>.htaccess</kbd> redirects will be accurate.', 'bw-import-media'), 'options-permalink.php');

        $msg = sprintf(__('Settings saved. %s <a href="%s">Ready to import files?</a>', 'bw-import-media'), $linkmsg, 'admin.php?import=media');
        // $msg .= '<pre>'. print_r( $input, false ) .'</pre>';
        $msgtype = 'updated';
    }

    add_settings_error('import_media', 'import_media', $msg, $msgtype);
    return $input;
}

// custom file validator to accommodate Win32 paths starting with drive letter
// based on WP's validate_file()
function validate_import_file($file, $allowed_files = '') {
    if (false !== strpos($file, '..'))
        return 1;

    if (false !== strpos($file, './'))
        return 1;

    if (!empty($allowed_files) && (!in_array($file, $allowed_files) ))
        return 3;
    /*
      if ( ':' == substr( $file, 1, 1 ) )
      return 2;
     */
    return 0;
}
