Instagram/Flickr Challenge
==========================

Requirements
------------
This challenge has been implemented using PHP as the server backend in order to protect the API keys from being exposed to the public. The only requirement other than PHP itself if the cURL extension. On a Debian-based Linux distribution this can be downloaded with:

    # apt-get install php5-curl
    
Running the Project
-------------------
With PHP 5.4 or later it is possible to run a simple webserver using the PHP command line utility. That is all that is required for this challenge to run. (Although it is of course possible to also place this behind an apache or nginx webserver.) After changing into the main directory:

    $ php -S localhost:8000
    
You can now see the challenge in action by visiting `http://localhost:8000/` in your browser.

Options
-------

### Timeframe
Because there were no photos tagged with "#dctech" on flickr in the past month, an option to change the date range for both flickr and instagram was implemented. You can change the number of months to go back by passing either `flickr_modify=X` and/or `instagram_modify=X` as URL parameters, where X is the number of months you would like to go back for that service. For example to get the last two months of flickr photos, visit `http://localhost:8000/?flickr_modify=2` in your browser.

### Random Comments
Because all of the photos in the last two months posted to flickr have no comments, an option to "randomize" the number of flickr comments was added to showcase how the page sorts images into their proper position as they are loaded. This can be enabled by passing the URL parameter `randomize_flickr_comments`. For example, to get the past two months of flickr photos and randomize the number of comments they have visit `http://localhost:8000/?flickr_modify=2&randomize_flickr_comments` in your browser.

### Limit Flickr API Calls
Because every photo from flickr requires a separate API call it takes a long time to fetch all of the information. In order to limit the number of flickr images and thus speedup loading of the page for testing you can pass `flicker_limit=X` as a URL parameter, where X is the number of images you want to download. For example to download 15 images from flickr in the last two months you could visit `http://localhost:8000/?flickr_modify=2&flickr_limit=15` in your browser.

3rd Party Code
--------------
While all 3rd party code is packaged along with the challenge code, it is also listed here for reference.

* [Foundation 5.4.7](http://foundation.zurb.com/) - CSS framework used to style the challenge.
* [jQuery 2.1.1](https://jquery.com/) - Bundled along with Foundation, used to make AJAX calls and manipulate the DOM.
* [phpFlickr (master)](https://github.com/dan-coulter/phpflickr) - Library used to make API calls to flickr's endpoints. We use master instead of a stable version for commit [b5955ce](https://github.com/dan-coulter/phpflickr/commit/b5955ce81fb505221bcb18b8ff54b67cd8ddebdf) which introduced JSON as the return format.

"Building from Source"
----------------------
When cloning the source of this repo, be sure to pass a `--recursive` flag in order to fetch the submodules. If you forget, run:

    $ git submodule update --init