<?php
// Save the post data to a CSV file
function awesome_posts_save_to_csv($post_id) {
    // Make sure it's the 'awesome_post' post type
    if (get_post_type($post_id) != 'awesome_post') {
        return;
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
