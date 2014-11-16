<?php

/**
 * This script takes the ID of a photo on flickr and prints some information
 * about it: when it was posted, it's flickr link and how many comments. Since
 * no dctech images in the past two months have any comments it is also
 * possible to randomize this value by passing 'randomize_flickr_comments as
 * a URL parameter.
 */

require_once("phpflickr/phpFlickr.php");

// Create a new flickr object using my API key.
$flickr = new phpFlickr("94515977712a6228a4a03d7944dbebaa");

// Get photo information from the API
$info = $flickr->photos_getInfo($_GET['id']);

// We'll create a new array to encode rather than removing information that we
// don't want from the returned info, as that's eaiser, and we aren't worried
// about such little overhead.
$return_info = array();

// If we're using the random ID then generate an integer from 0 to 10,
// otherwise save the real value.
if (isset($_GET['randomize_flickr_comments'])) {
    $return_info['comments'] = rand(0, 10);
} else {
    $return_info['comments'] = $info['photo']['comments']['_content'];
}

// Get the timestamp that the photo was posted.
$return_info['posted'] = $info['photo']['dates']['posted'];

// We need to loop through the url object to find the photopage link which is
// what we're actually interested in.
foreach ($info['photo']['urls']['url'] as $url) {
    if (!strcasecmp($url['type'], 'photopage')) {
        $return_info['link'] = $url['_content'];
        break;
    }
}

// Make sure we at least have a value for "link"
if (!isset($return_info['link'])){
    $return_info['link'] = "";
}

// Print the data as a JSON object so we can use it client-side!
echo json_encode($return_info);
