<?php if (!function_exists('curl_init')) {
    die("You must have the PHP cURL extension!");
} ?><!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Instagram / Flickr Challenge</title>
    <link rel="stylesheet" href="css/foundation.css"/>
    <script src="js/vendor/modernizr.js"></script>
</head>
<body>

<div class="row">
    <div class="large-12 columns">
        <dl class="sub-nav">
            <dt>Filter:</dt>
            <dd id="filter_status">Disabled Until All Images Downloaded</dd>
            <dd id="filter_all" class="active hide"><a href="#">All</a></dd>
            <dd id="filter_instagram" class="hide"><a href="#">
                    Instagram (<span id="instagram_count"></span>)</a></dd>
            <dd id="filter_flickr" class="hide"><a href="#">
                    Flickr (<span id="flickr_count"></span>)</a></dd>
            <dt id="status_label">Status:</dt>
            <dd id="status">Gathering Information</dd>
        </dl>
    </div>
</div>

<div class="row">
    <div class="large-12 columns">
        <div class="panel" id="response">
            <ul id="photostream" class="large-block-grid-4 medium-block-grid-3
            small-block-grid-2"></ul>
        </div>
    </div>
</div>

<script src="js/vendor/jquery.js"></script>
<script src="js/foundation.min.js"></script>
<script>

// We get this list of photos from our server-side calls.
var flickr_photos = [<?php require('flickr.php'); ?>];
var instagram_photos = <?php require('instagram.php'); ?>;

$(document).foundation().ready(function () {

    // Update the parenthesis in the filter bar with the photo counts.
    $('#instagram_count').html(instagram_photos.length);
    $('#flickr_count').html(flickr_photos.length);

    // Set up what happens when clicking the filter links. Basically, for
    // a service filter we want to hide the other photos and make sure the
    // others are showing. For all we want to make sure they're all
    // visible.
    $('#filter_all a').click(function () {
        $('#filter_all').addClass('active');
        $('#filter_instagram').removeClass('active');
        $('#filter_flickr').removeClass('active');
        $(".instagram_img").show();
        $('.flickr_img').show();
        return false;
    });
    $('#filter_instagram a').click(function () {
        $('#filter_instagram').addClass('active');
        $('#filter_all').removeClass('active');
        $('#filter_flickr').removeClass('active');
        $('.instagram_img').show();
        $('.flickr_img').hide();
        return false;
    });
    $('#filter_flickr a').click(function () {
        $('#filter_flickr').addClass('active');
        $('#filter_all').removeClass('active');
        $('#filter_instagram').removeClass('active');
        $('.instagram_img').hide();
        $('.flickr_img').show();
        return false;
    });

    // Start Instagram. We do this first, because we already have all of
    // the information about the photos (thanks Instagram API!) and so all
    // we have to do is manipulate the DOM. This is pretty much instant,
    // even with a large number of photos. This way the user can a least
    // see something while they wait for all of the flickr API calls to
    // complete.

    // Since images are sorted by date, but we want them sorted by number
    // of comments we will basically keep a marker for each actual number
    // of comments. Then when we process an image we can see how many
    // comments it has and search for that marker. If we find the marker
    // then just add the image after it. If it doesn't exist then we'll
    // loop from the lowest number that we've inserted up to the number of
    // comments on the image, keeping track of the last marker we hit when
    // we finished the loop. Then, we just add the image as a new marker
    // before that last marker and we've kept the images in order by
    // number of comments. Here we set that we haven't inserted any images
    // yet.
    var lowest_inserted = null;

    instagram_photos.forEach(function (photo) {

        // Let's save some variables about the photo for easier access.
        var comments = photo.comments.count;
        var link = photo.link;
        var url = photo.images.standard_resolution.url;
        var posted = new Date(photo.created_time * 1000);

        // This is the actual image that we'll place in the li.
        var img_div = '<a href="' + link + '" class="th">' +
            '<img src=' + url + '></a>' +
            'Date Posted: ' + posted.toLocaleDateString("en-US", {
                day: "numeric",
                month: "short",
                year: "2-digit"
            }) + '<br/>Comments: ' + comments;

        // If we haven't inserted any images yet, or the number of
        // comments for images is lower than the lowest, just add the new
        // image to the end of the list.
        if (lowest_inserted == null || comments < lowest_inserted) {

            $('#photostream').append(
                '<li id="comments-' + comments +
                '" class="instagram_img" data-comments="' + comments +
                '">' + img_div + '</li>');
            lowest_inserted = comments;

        } else {

            // If the marker we're looking for doesn't exist then we need
            // to find the closest one and add a new marker before it.
            // That's all this loop does: start from the lowest to this
            // number of comments and keep track of the marker with the
            // most comments (top of the list) before this number of
            // comments. Then it just adds the new  marker before it
            // (becoming the new top of the list).
            if ($('#comments-' + comments).length === 0) {
                var last = "#comments-" + lowest_inserted;
                for (var i = lowest_inserted; i < comments; i++) {
                    if ($("#comments-" + i).length !== 0) {
                        last = "#comments-" + i;
                    }
                }
                $(last).before(
                    '<li id="comments-' + comments +
                    '" class="instagram_img" data-comments="' + comments +
                    '">' + img_div + '</li>');

            } else {

                // Else the marker with this number of comments already
                // exists. Just add this element after it to keep it in
                // order.
                $('#comments-' + comments).after(
                    '<li class="instagram_img" data-comments="' + comments
                    + '">' + img_div + '</li>');

            }

        }

    });

    // Start Flickr
    //TODO write about flickr process

    // We use this variable to update the status bar.
    var remaining = flickr_photos.length;

    flickr_photos.forEach(function (photo) {

        // Construct the URL to load an image from flickr.
        var flickr_url = 'https://farm' + photo.farm +
            '.staticflickr.com/' + photo.server + '/' + photo.id + '_' +
            photo.secret + '.jpg';

        // This is the actual image that we load into the li.
        var img_div = '<a href="' + flickr_url + '" class="th">' +
            '<img src=' + flickr_url + '></a>';

        // This is the flickr image ID that we want to get info about.
        var options = {id: photo.id};

        // If we set 'randomize_flickr_comments' on this page then we need
        // to pass it to the info script as well.
        if (location.search.indexOf('randomize_flickr_comments') >= 0) {
            options.randomize_flickr_comments = true;
        }

        $.getJSON("comments.php", options, function (data) {

            // Set a few variables based on the info for easier access.
            var comments = data.comments;
            var posted = new Date(parseInt(data.posted) * 1000);
            var info = 'Date Posted: ' + posted.toLocaleDateString(
                    "en-US", {
                        day: "numeric",
                        month: "short",
                        year: "2-digit"
                    }) + '<br/>Comments: ' + comments;

            // This entire part works exactly the same as the instagram
            // section, but uses flickr_img instead of instagram_img.
            if (lowest_inserted == null || comments < lowest_inserted) {

                $('#photostream').append(
                    '<li id="comments-' + comments +
                    '" class="flickr_img" data-comments="' + comments +
                    '">' + img_div + '</li>');
                lowest_inserted = comments;

            } else {

                if ($('#comments-' + comments).length === 0) {
                    var last = "#comments-" + lowest_inserted;
                    for (var i = lowest_inserted; i < comments; i++) {
                        if ($("#comments-" + i).length !== 0) {
                            last = "#comments-" + i;
                        }
                    }
                    $(last).before(
                        '<li id="comments-' + comments +
                        '" class="flickr_img" data-comments="' + comments +
                        '">' + img_div + '</li>');

                } else {

                    $('#comments-' + comments).after(
                        '<li class="flickr_img" data-comments="' + comments +
                        '">' + img_div + info + '</li>');

                }

            }

            // Here's where we update the status bar. If we've finally reached
            // zero then unhide the filter buttons. Otherwise just update the
            // number of images remaining.
            if (--remaining == 0) {
                $('#status_label').hide();
                $('#status').hide();
                $('#filter_status').hide();
                $('#filter_all').removeClass("hide");
                $('#filter_instagram').removeClass("hide");
                $('#filter_flickr').removeClass("hide");
            } else {
                $('#status').html(
                    "Processing Flickr Photos (" + remaining +
                    " remaining).");
            }

        });

    });

});

</script>
</body>
</html>