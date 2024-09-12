<?php
/*
Plugin Name: Awesome Posts
Description: Adds a custom post type 'Awesome Posts' with specific fields and saves to a CSV file.
Version: 1.2
Author: Augusto Neto
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Register the 'Awesome Posts' Custom Post Type
function awesome_posts_custom_post_type() {
    $labels = array(
        'name' => 'Awesome Posts',
        'singular_name' => 'Awesome Post',
        'menu_name' => 'Awesome Posts',
        'add_new_item' => 'Add New Awesome Post',
        'edit_item' => 'Edit Awesome Post',
        'new_item' => 'New Awesome Post',
        'view_item' => 'View Awesome Post',
        'all_items' => 'All Awesome Posts',
        'search_items' => 'Search Awesome Posts',
        'not_found' => 'No Awesome Posts found',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title'),
        'menu_position' => 5,
        'show_in_rest' => true, // For Gutenberg support
    );

    register_post_type('awesome_post', $args);
}
add_action('init', 'awesome_posts_custom_post_type');

// Add custom meta fields
function awesome_posts_add_meta_boxes() {
    add_meta_box('awesome_post_fields', 'Awesome Post Fields', 'awesome_posts_fields_callback', 'awesome_post', 'normal', 'default');
}
add_action('add_meta_boxes', 'awesome_posts_add_meta_boxes');

// Enqueue media library scripts for image upload
function awesome_posts_enqueue_media_uploader() {
    global $typenow;
    if ($typenow == 'awesome_post') {
        wp_enqueue_media();
        wp_enqueue_script('awesome-posts-script', plugin_dir_url(__FILE__) . 'awesome-posts.js', array('jquery'), null, true);
    }
}
add_action('admin_enqueue_scripts', 'awesome_posts_enqueue_media_uploader');

// Meta box fields callback function
function awesome_posts_fields_callback($post) {
    wp_nonce_field('save_awesome_posts_meta', 'awesome_posts_nonce');

    $text1 = get_post_meta($post->ID, 'text_1', true);
    $text2 = get_post_meta($post->ID, 'text_2', true);
    $start = get_post_meta($post->ID, 'start_date', true);
    $end = get_post_meta($post->ID, 'end_date', true);
    $image = get_post_meta($post->ID, 'image', true);
    ?>
    <p>
        <label for="text_1">Text 1:</label>
        <input type="text" id="text_1" name="text_1" value="<?php echo esc_attr($text1); ?>" />
    </p>
    <p>
        <label for="text_2">Text 2:</label>
        <input type="text" id="text_2" name="text_2" value="<?php echo esc_attr($text2); ?>" />
    </p>
    <p>
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo esc_attr($start); ?>" />
    </p>
    <p>
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo esc_attr($end); ?>" />
    </p>
    <p>
        <label for="image">Upload Image:</label>
        <input type="text" id="image" name="image" value="<?php echo esc_url($image); ?>" />
        <input type="button" id="upload_image_button" class="button" value="Upload Image" />
    </p>
    <?php
}

// Save the custom fields
function awesome_posts_save_to_csv($post_id) {
    // Make sure it's the 'awesome_post' post type and check for autosave
    if (get_post_type($post_id) != 'awesome_post' || defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Verify the nonce
    if (!isset($_POST['awesome_posts_nonce']) || !wp_verify_nonce($_POST['awesome_posts_nonce'], 'save_awesome_posts_meta')) {
        return;
    }

    // Save custom fields to post meta
    if (isset($_POST['text_1'])) {
        update_post_meta($post_id, 'text_1', sanitize_text_field($_POST['text_1']));
    }
    if (isset($_POST['text_2'])) {
        update_post_meta($post_id, 'text_2', sanitize_text_field($_POST['text_2']));
    }
    if (isset($_POST['start_date'])) {
        update_post_meta($post_id, 'start_date', sanitize_text_field($_POST['start_date']));
    }
    if (isset($_POST['end_date'])) {
        update_post_meta($post_id, 'end_date', sanitize_text_field($_POST['end_date']));
    }
    if (isset($_POST['image'])) {
        update_post_meta($post_id, 'image', esc_url_raw($_POST['image']));
    }

    // Set CSV directory
    $csv_dir = WP_CONTENT_DIR . '/awesome-posts';

    // Check if the directory exists; if not, create it
    if (!is_dir($csv_dir)) {
        if (!mkdir($csv_dir, 0755, true)) {
            error_log('Failed to create CSV directory: ' . $csv_dir);
            return;
        }
    }

    // Set the file path for the CSV file
    $file = $csv_dir . '/awesome_posts.csv';

    // Check if the file exists
    $csv_exists = file_exists($file);
    
    // Open the file in append mode ('a')
    $csv = fopen($file, 'a');
    if (!$csv) {
        error_log('Failed to open CSV file: ' . $file);
        return;
    }

    // If the file does not exist, add column headers
    if (!$csv_exists) {
        fputcsv($csv, array('POST-ID', 'TITLE', 'IMAGE-URL', 'TEXT-1', 'TEXT-2', 'START', 'END', 'WEIGHT', 'HEIGHT', 'COLOR', 'MATERIAL'));
    }

    // Gather post data
    $post_title = get_the_title($post_id);
    $text1 = get_post_meta($post_id, 'text_1', true);
    $text2 = get_post_meta($post_id, 'text_2', true);
    $start_date = get_post_meta($post_id, 'start_date', true);
    $end_date = get_post_meta($post_id, 'end_date', true);
    $image = get_post_meta($post_id, 'image', true);

    // Prepare data to be written
    $data = array(
        $post_id,
        $post_title,
        $image,
        $text1,
        $text2,
        $start_date,
        $end_date,
        '10lbs.',
        '7FT',
        'Green',
        'Canvas'
    );

    // Write data to the CSV file
    fputcsv($csv, $data);

    // Close the CSV file
    fclose($csv);
}
add_action('save_post', 'awesome_posts_save_to_csv');

