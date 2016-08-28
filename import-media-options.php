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
                                <p><?php printf(__('<a href="%s">Add from Server</a> lets you import media files that are on your server but not part of the WordPress media library.', 'bw-import-media'), 'http://wordpress.org/extend/plugins/add-from-server/'); ?></p>
                                <p><?php printf(__('<a href="%s">Add Linked Images to Gallery</a> is helpful if you have imported data using other plugins and you would like to import linked images. However, it handles only images that are referenced with complete URLs; relative paths will not work.', 'bw-import-media'), 'http://wordpress.org/extend/plugins/add-linked-images-to-gallery-v01/'); ?></p>
                                <p><?php printf(__('<a href="%s">Broken Link Checker</a> finds broken links and references to missing media files. Since the importer does not handle links or media files other than images, you should run this to see what else needs to be copied or updated from your old site.', 'bw-import-media'), 'http://wordpress.org/extend/plugins/broken-link-checker/'); ?></p>
                                <p><?php printf(__('<a href="%s">Media Deduper</a> was built to help you find and eliminate duplicate images and attachments from your WordPress media library.', 'bw-import-media'), 'https://wordpress.org/plugins/media-deduper/'); ?></p>
                                <p><?php printf(__('<a href="%s">Redirection</a> provides a nice admin interface for managing redirects. If you would rather not edit your <kbd>.htaccess</kbd> file, or if you just want to redirect one or two of your old pages, you can ignore the redirects generated by the importer. Instead, copy the post\'s old URL from the custom fields and paste it into Redirection\'s options.', 'bw-import-media'), 'http://wordpress.org/extend/plugins/redirection/'); ?></p>
                                <p><?php printf(__('<a href="%s">Search and Replace</a> helps you fix many broken links at once, if you have many links to the same files or if there is a pattern ( like <kbd>&lt;a href="../../files"&gt;</kbd> ) to your broken links.', 'bw-import-media'), 'http://wordpress.org/extend/plugins/search-and-replace/'); ?></p>
                            </td>
                        </tr>
                        <!--<tr>
                            <th><?php _e('Donate', 'bw-import-media') ?></th>
                            <td>
                                <p><?php printf(__('If this importer has saved you hours of copying and pasting, a <a href="%s">donation toward future development</a> would be much appreciated!', 'bw-import-media'), 'http://genieblog.ch/'); ?></p>
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

    if (!in_array($input['status'], get_post_stati())) {
        $input['status'] = 'publish';
    }

    $post_types = get_post_types(array('public' => true), 'names');
    if (!in_array($input['type'], $post_types)) {
        $input['type'] = 'page';
    }

    if (!in_array($input['timestamp'], array('now', 'filemtime', 'customfield'))) {
        $input['timestamp'] = 'filemtime';
    }

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
        if (empty($linkstructure)) {
            $linkmsg = sprintf(__('If you intend to <a href="%s">set a permalink structure</a>, you should do it 
				before importing so the <kbd>.htaccess</kbd> redirects will be accurate.', 'bw-import-media'), 'options-permalink.php');
        }

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
    if (false !== strpos($file, '..')) {
        return 1;
    }

    if (false !== strpos($file, './')) {
        return 1;
    }

    if (!empty($allowed_files) && (!in_array($file, $allowed_files) )) {
        return 3;
    }
    /*
      if ( ':' == substr( $file, 1, 1 ) )
      return 2;
     */
    return 0;
}
