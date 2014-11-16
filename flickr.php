<?php

/**
 * This script will search flickr for all images tagged with the dctech
 * hasgtag. Here we can specify the date range we are interested in, and
 * flickr tells us how many pages are in our resultset. We can use this
 * information to start on page one and make an API call for each page in the
 * set.
 */

if (!defined('HASHTAG')) {
    define('HASHTAG', "dctech");
}

// Include the phpFlickr library and create a new object with my API key.
require_once("phpflickr/phpFlickr.php");
$flickr = new phpFlickr("94515977712a6228a4a03d7944dbebaa");

// Our default date cutoff is one month. However we can change that by passing
// the URL parameter flickr_modify and an integer value to go back that many
// months. (Note that flickr doesn't have any #dctech images in the last
// month.
$now = new \DateTime("now");
if (isset($_GET['flickr_modify']) && $_GET['flickr_modify'] != 1) {
    $now->modify("-" . $_GET['flickr_modify'] . " months");
} else {
    $now->modify("-1 month");
}

// We want to get images in descening order, and get as many of them as
// possible at a time in order to lessen the number of calls we need to make.
$search_parameters = array(
    'tags' => HASHTAG,
    'min_upload_date' => $now->getTimestamp(),
    'sort' => 'date-posted-desc',
    'page' => 1,
    'per_page' => 500,
);

// If we want to limit the number of requests we have to make to flickr in
// order to speed up testing. Note, that setting this greater than 500 won't
// have any effect. (but how does that speed up testing, anyway? :))
if (isset($_GET['flickr_limit'])) {
    $search_parameters['per_page'] = $_GET['flickr_limit'];
}

// An array to store all of the photos that we get.
$flickr_photos = array();

// Flickr tells us how many pages of data there are when searching via their
// API, so we can use this information to make API calls while the page that
// we're on is less than the total number of pages for that query.
do {

    // Make the call and save all the results to a temporary variable.
    $photos = $flickr->photos_search($search_parameters);

    // Now loop through the results and unset some information that we aren't
    // particularly interested in. Then take convert it to JSON and add it to
    // our total array.
    foreach ($photos['photo'] as $photo) {

        unset($photo['ispublic'], $photo['isfriend'], $photo['isfamily']);
        $flickr_photos[] = json_encode($photo);

    }

    // Increment which page to use for the next call.
    $search_parameters['page']++;

    // If we've set the flickr_limit then break the loop.
    if (isset($_GET['flickr_limit'])) {
        break;
    }

} while ($photos['page'] < $photos['pages']);

// Combine the final array of photos into what will be an array of JSON
// objects.
echo implode(',', $flickr_photos);