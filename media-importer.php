<?php
// main php file containing the import logic


// in case this server doesn't have php_mbstring enabled in php.ini...
if (!function_exists('mb_strlen')) {

    function mb_strlen($string) {
        return strlen(utf8_decode($string));
    }

}
if (!function_exists('mb_strrpos')) {

    function mb_strrpos($haystack, $needle, $offset = 0) {
        return strrpos(utf8_decode($haystack), $needle, $offset);
    }

}

defined('ABSPATH') or die('No script kiddies please!');
if (!defined('WP_LOAD_IMPORTERS')) {
    return;
}

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

if (!class_exists('WP_Importer')) {
    $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
    if (file_exists($class_wp_importer)) {
        require_once $class_wp_importer;
    }
}

if (class_exists('WP_Importer')) {

    class Media_Importer extends WP_Importer {

        private $importedFiles = [[]];
        private $filearr = [];

        function getAllPosts() {
            $args = ['numberposts' => -1];
            $posts = get_posts($args);
            return $posts;
        }

        function getPostContent($post) {
            $data = setup_postdata($post);
            return the_content();
        }

        function getAllPages() {
            $pages = get_pages();
            return $pages;
        }

        function getAllPs() {
            $p = array_merge($this->getAllPages(), $this->getAllPosts());
            return $p;
        }

        function header() {
            echo '<div class="wrap">';
            screen_icon();
            echo '<h2>' . __('Media Importer', 'import-media-pages') . '</h2>';
        }

        function footer() {
            echo '</div>';
        }

        function greet() {
            $options = get_option('import_media');
            ?>
            <div class="narrow">
                <?php
                if ($options['firstrun'] === true) {
                    echo '<p>' . sprintf(__('It looks like you have not yet visited the <a href="%s">Media Import options page</a>. Please do so now! You need to specify which media from which site should be imported before you proceed.', 'import-media-pages'), 'options-general.php?page=import-media.php') . '</p>';
                } else {
                    ?>
                    <h4><?php _e('What page do you eliminate today?'); ?></h4>
                    <form enctype="multipart/form-data" method="post" action="admin.php?import=media&amp;step=1">

                        <p id="directory">
                            <?php
                            printf(__('Your media will be imported from <kbd>%s</kbd>. <a href="%s">Change url</a>.', 'import-media-pages'), esc_html($options['old_url']), 'options-general.php?page=import-media.php');
                            ?>
                        </p>

                        <input type="hidden" name="action" value="save" />

                        <p class="submit">
                            <input type="submit" name="submit" class="button" value="<?php echo esc_attr(__('Submit', 'import-media-pages')); ?>" />
                        </p>
                        <?php wp_nonce_field('import-media'); ?>
                    </form>
                </div>
                <?php
            } // else
        }

// main function to loop over all posts
        function import() {
            echo '<p>Import started</p>';
            $options = get_option('import_media');
            $posts = $this->getAllPs();
            echo 'Got ' . count($posts) . ' to loop through.';
            if ($options['documents']) {
                echo '<br />I will look for documents. ';
            }
            if ($options['ímages']) {
                echo '<br />And Images will be imported for sure!';
            }
            for ($index = 0; $index < count($posts); $index++) {
                $post = $posts[$index];
                 if ($options['images']) {
                    $this->import_images($post->ID);
                }
                
                if ($options['documents']) {
                    $this->import_documents($post->ID);
                }               
            }
            echo '<p>Congratulations! This plugin has come to an end now.</p>';
            flush();
        }

//Handle an individual file import. Borrowed almost entirely from dd32's Add From Server plugin
        function handle_import_media_file($file, $post_id = 0) {
// see if the attachment already exists
            $id = array_search($file, $this->filearr);
            if ($id === false) {
                echo '<br />Handling file ' . $file;

                set_time_limit(120);
                $post = get_post($post_id);
                $time = $post->post_date_gmt;

// A writable uploads dir will pass this test. Again, there's no point overriding this one.
                if (!( ( $uploads = wp_upload_dir($time) ) && false === $uploads['error'] )) {
                    return new WP_Error('upload_error', $uploads['error']);
                }

                $filename = wp_unique_filename($uploads['path'], basename($file));

// copy the file to the uploads dir
                $new_file = $uploads['path'] . '/' . $filename;
                if (false === @copy($file, $new_file)) {
                    return new WP_Error('upload_error', sprintf(__('Could not find the right path to %s ( tried %s ). It could not be imported. Please upload it manually.', 'import-media-pages'), basename($file), $file));
                }
//  DEBUG
                else {
                    printf(__('<br /><em>%s</em> is being copied to the uploads directory as <em>%s</em>.', 'import-media-pages'), $file, $new_file);
                }
// Set correct file permissions
                $stat = stat(dirname($new_file));
                $perms = $stat['mode'] & 0000666;
                @chmod($new_file, $perms);
// Compute the URL
                $url = $uploads['url'] . '/' . $filename;

//Apply upload filters
                $return = apply_filters('wp_handle_upload', array('file' => $new_file, 'url' => $url, 'type' => wp_check_filetype($file, null)));
                $new_file = $return['file'];
                $url = $return['url'];
                $type = $return['type'];

                $title = preg_replace('!\.[^.]+$!', '', basename($file));
                $content = '';

// use image exif/iptc data for title and caption defaults if possible
                if ($image_meta = @wp_read_image_metadata($new_file)) {
                    if ('' != trim($image_meta['title'])) {
                        $title = trim($image_meta['title']);
                    }
                    if ('' != trim($image_meta['caption'])) {
                        $content = trim($image_meta['caption']);
                    }
                }

                if ($time) {
                    $post_date_gmt = $time;
                    $post_date = $time;
                } else {
                    $post_date = current_time('mysql');
                    $post_date_gmt = current_time('mysql', 1);
                }

// Construct the attachment array
                $wp_filetype = wp_check_filetype(basename($filename), null);
                $attachment = array(
                    'post_mime_type' => $wp_filetype['type'],
                    'guid' => $url,
                    'post_parent' => $post_id,
                    'post_title' => $title,
                    'post_name' => $title,
                    'post_content' => $content,
                    'post_date' => $post_date,
                    'post_date_gmt' => $post_date_gmt
                );

//Win32 fix:
                $new_file = str_replace(strtolower(str_replace('\\', '/', $uploads['basedir'])), $uploads['basedir'], $new_file);

// Insert attachment
                $id = wp_insert_attachment($attachment, $new_file, $post_id);
                if (!is_wp_error($id)) {
                    $data = wp_generate_attachment_metadata($id, $new_file);
                    wp_update_attachment_metadata($id, $data);
                    $this->filearr[$id] = $file; // $file contains the original, absolute path to the file
                }
            } // if attachment already exists
            flush();
            return $id;
        }

// largely borrowed from the Add Linked Images to Gallery plugin, except we do a simple str_replace at the end
        function import_images($id) {
            $post = get_post($id);
            $options = get_option('import_media');
            $result = array();
            $srcs = array();
            $content = $post->post_content;
            $title = $post->post_title;
            if (empty($title)) {
                $title = __('( no title )', 'import-media');
            }
            $update = false;

            $bad_domain = $options['old_url'];

// find all src attributes
            preg_match_all('@(<img.*?)(' . $bad_domain . ')(.*?[^>]*>)@i', $content, $matches);
            for ($i = 0; $i < count($matches[0]); $i++) {
                preg_match('/src=["\']?([^"\'>]+)["\']?/', $matches[0][$i], $srcs[]);
            }

            if (!empty($srcs)) {
                $count = count($srcs);

                echo "<p>";
                printf(_n('<br />Found %d image in <a href="%s">%s</a>. Importing... ', '<br />Found %d images in <a href="%s">%s</a>. Importing... ', $count, 'import-media-pages'), $count, get_permalink($post->ID), $title);
                foreach ($srcs as $src) {
                    $src = $src[1];
// src="http://foo.com/images/foo"
                    if (preg_match('/^http:\/\//', $src) || preg_match('/^https:\/\//', $src)) {
                        $imgpath = $src;
                    }
// intersect base path and src, or just clean up junk
                    $imgpath = $this->remove_dot_segments($imgpath);

                    if (isset($this->importedFiles[$imgpath])) {
// set image URL to already imported one
                        $imgid = $this->importedFiles[$imgpath];
                    } else {
//  load the image from $imgpath
                        $imgid = $this->handle_import_media_file($imgpath, $id);
                        $this->importedFiles[$imgpath] = $imgid;
                    }


                    if (is_wp_error($imgid)) {
                        echo '<br /><span class="attachment_error">' . $imgid->get_error_message() . '</span>';
                    } else {
                        $imgpath = wp_get_attachment_url($imgid);

//  replace paths in the content
                        if (!is_wp_error($imgpath) && $imgpath !== '') {
                            $content = str_replace($src, $imgpath, $content);
                            $custom = str_replace($src, $imgpath, $custom);
                            $update = true;
                        }
                    } // is_wp_error else
                } // foreach
// update the post only once
                if ($update == true) {
                    $my_post = array();
                    $my_post['ID'] = $id;
                    $my_post['post_content'] = $content;
                    wp_update_post($my_post);
                }

                _e('done.', 'import-media-images');
                echo '</p>';
                flush();
            } // if empty
        }

        function import_documents($id) {
            $post = get_post($id);
            $options = get_option('import_media');
            $oldUrl = $options['old_url'];
            $content = $post->post_content;
            $title = $post->post_title;
            if (empty($title)) {
                $title = __('( no title )', 'import-media');
            }
            $update = false;
            $mimes = explode(',', $options['document_mimes']);

            // find all href attributes with the old URL
            // echo ('<br />@(<a.*?)(' . preg_quote($oldUrl) . ')(.*?[^>]*>)@i'); WTF does really come out with this echo?
            preg_match_all('@(<a.*?)(' . preg_quote($oldUrl) . ')(.*?[^>]*>)@i', $content, $matches);
            for ($i = 0; $i < count($matches[0]); $i++) {
                preg_match('/href=["\']?([^"\'>]+)["\']?/', $matches[0][$i], $hrefs[]); // extract the whole URL
            }

            if (!empty($hrefs)) {
                $count = count($hrefs);

                echo "<p>";
                printf(_n('Found %d link in <a href="%s">%s</a>. Checking file types... <br />', 'Found %d links in <a href="%s">%s</a>. Checking file types... <br />', $count, 'import-media-pages'), $count, get_permalink($post->ID), $title);

                //echo '<p>Looking in '.get_permalink( $id ).'</p>';
                foreach ($hrefs as $href) {
                    $href = $href[1];
                    if ('#' != substr($href, 0, 1) && 'mailto:' != substr($href, 0, 7)) { // skip anchors and mailtos
// check wether this file is already been imported 
                        if (isset($this->importedFiles[$imgpath])) {
// set image id to already imported one
                            $fileid = $this->importedFiles[$imgpath];

// file already switched. jast change URL
                        } else {
// we found a file to import
                            $linkpath = rtrim($href, '/');
                                        // DEBUG
                            echo '<p>Old link: '.$href.' Full path: '.$linkpath;
                            // check file extension
                            $filename_parts = explode(".", $linkpath);
                            $ext = strtolower($filename_parts[count($filename_parts) - 1]);

                            if (in_array($ext, $mimes)) {  // allowed upload types only
                                echo '<br />Importing ' . ltrim(strrchr($linkpath, '/'), '/') . '... ';
//  load the file from $linkpath
                                $fileid = $this->handle_import_media_file($linkpath, $id);

                                if (is_wp_error($fileid)) {
                                    echo '<br /><span class="attachment_error">' . $fileid->get_error_message() . '</span>';
                                } // is_wp_error $fileid
                                else {
                                    $this->importedFiles[$imgpath] = $fileid;
                                }
                            } // if in array mimes
                            else {
                                echo '<br />Found link ' . $linkpath . ' which we are not going to update. Sorry.';
                                return;
                            }
                        } // if empty linkpath
                        $filepath = wp_get_attachment_url($fileid);

//  replace paths in the content
                        if (!is_wp_error($filepath) && $filepath !== '') {
                            $content = str_replace($href, $filepath, $content);
                            $update = true;
                        }
                    } // if #/mailto
                } // foreach
// update the post only once
                if ($update == true) {
                    $my_post = array();
                    $my_post['ID'] = $id;
                    $my_post['post_content'] = $content;
                    wp_update_post($my_post);
                }

                _e('done.', 'import-media-images');
                echo '</p>';
                flush();
            } // if empty $hrefs
        }

        function importer_styles() {
            ?>
            <style type="text/css">
                textarea#import-result { height: 12em; width: 100%; }
                #importing th { width: 32% } 
                #importing th#id { width: 4% }
                #importing tbody tr:nth-child( odd ) { background: #f9f9f9; }
                span.attachment_error { display: block; padding-left: 2em; color: #d54e21; /* WP orange */ }
            </style>
            <?php
        }

        // decide about what to do
        function dispatch() {
            if (empty($_GET['step'])) {
                $step = 0;
            } else {
                $step = (int) $_GET['step'];
            }

            $this->header();

            switch ($step) {
                case 0 :
                    $this->greet();
                    break;
                case 1 :
                    check_admin_referer('import-media');
                    $result = $this->import();
                    if (is_wp_error($result)) {
                        echo $result->get_error_message();
                    }
                    break;
                case 2 :
                    $this->regenerate_redirects();
                    break;
            }

            $this->footer();
        }

        // improve URL
        function remove_dot_segments($path) {
            $inSegs = preg_split('!/!u', $path);
            $outSegs = array();
            foreach ($inSegs as $seg) {
                if (empty($seg) || $seg == '.') {
                    continue;
                }
                if ($seg == '..') {
                    array_pop($outSegs);
                } else {
                    array_push($outSegs, $seg);
                }
            }
            $outPath = implode('/', $outSegs);
            if (isset($path[0]) && $path[0] == '/') {
                $outPath = '/' . $outPath;
            }
            if ($outPath != '/' &&
                    ( mb_strlen($path) - 1 ) == mb_strrpos($path, '/', 'UTF-8')) {
                $outPath .= '/';
            }
            $outPath = str_replace('http:/', 'http://', $outPath);
            $outPath = str_replace('https:/', 'https://', $outPath);
            $outPath = str_replace(':///', '://', $outPath);
            return rawurldecode($outPath);
        }

        // register itself on creation
        function __construct() {
            add_action('admin_head', array(&$this, 'importer_styles'));
        }

    } // media importer
} // WP Importer exists


global $media_import;
$media_import = new Media_Importer();

register_importer('media', 'media importer', sprintf(__('Import media linked to an old site in your posts, pages, or any custom post type. Visit <a href="%s">the options page</a> first to select which portions of your documents should be imported.', 'media-import-pages'), 'options-general.php?page=import-media.php'), array($media_import, 'dispatch'));

