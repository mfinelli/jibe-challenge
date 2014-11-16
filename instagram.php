<?php

/**
 * This script will fetch all images from Instagram tagged with the dctech
 * hashtag. Since Instagram doesn't allow you to search specifically by tag
 * date we'll have to get all of the images. But, it does order them by the
 * tag date, so when we reach an image that's older than our cutoff we can
 * stop processing, knowing that we have all newer images.
 */

if (!defined('HASHTAG')) {
    define('HASHTAG', "dctech");
}

// Our default date cutoff is one month. However we can change that by passing
// the URL parameter instagram_modify and an integer value to go back that
// many months.
$now = new \DateTime("now");
if (isset($_GET['instagram_modify']) && $_GET['instagram_modify'] != 1) {
    $now->modify("-" . intval($_GET['instagram_modify']) . " months");
} else {
    $now->modify("-1 month");
}

$instagram_options = array(
    'client_id' => '6e75f225530744e0bb0a5a46e4bff2f8',
    'count' => 0,
);

// Instagram API tag endpoint:
// http://instagram.com/developer/endpoints/tags/#get_tags_media_recent
$url = "https://api.instagram.com/v1/tags/" . HASHTAG . "/media/recent?" .
    http_build_query($instagram_options);

// Array that will hold all of the pictures we get through all of our calls.
$instagram_photos = array();

// We want to run this loop while Instagram tells us there are more results.
// We probably won't ever actually reach this because we'll stop early once
// we've reached the date cutoff, but it should work if we wanted to fetch all
// images as well.
while (!is_null($url)) {

    try {

        // Use these cURL options: https://gist.github.com/maxparm/2294032
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Instagram returns JSON so we decode it so we can perform array
        // operations, then close the cURL connection.
        $data = json_decode(curl_exec($ch), true);
        curl_close($ch);

        // Here is where we determine if there are more results, as described
        // at the beginning of the while loop.
        $url = (isset($data['pagination']['next_url'])) ?
            $data['pagination']['next_url'] : null;

        // We'll use this variable to track if we've reached images older than
        // our cutoff date.
        $stopped = false;

        // Loop through all images returned in the dataset and check if they
        // are older than our cutoff.
        foreach ($data['data'] as $i => $photo) {

            // If we don't have an image (e.g., video) then throw it away.
            if (strcasecmp(strtolower(trim($photo['type'])), 'image') != 0) {
                unset($data['data'][$i]);
            } else {

                // Unset some information that we aren't interested in.
                unset(
                    $data['data'][$i]['attribution'],
                    $data['data'][$i]['tags'],
                    $data['data'][$i]['location'],
                    $data['data'][$i]['comments']['data'],
                    $data['data'][$i]['filter'],
                    $data['data'][$i]['likes'],
                    $data['data'][$i]['users_in_photo'],
                    $data['data'][$i]['caption'],
                    $data['data'][$i]['user']
                );

                if ($photo['created_time'] < $now->getTimestamp()) {

                    // Unset this from the resultset so it doesn't get
                    // included in our final array.
                    unset($data['data'][$i]);

                    $stopped = true;

                }

            }
        }

        // Add the resultset to our final array of images.
        $instagram_photos = array_merge($instagram_photos, $data['data']);

        // We have reached images that are older than we care about, so let's
        // break the loop.
        if ($stopped) {
            $url = null;
        }

    } catch (Exception $e) {
        // Since this is just a demo we aren't doing any real error checking,
        // but ideally we would let the script know there was some kind of
        // error and then alert the user.
    }
}

// Print the final array of images as JSON so we can use it client-side.
echo json_encode($instagram_photos);
