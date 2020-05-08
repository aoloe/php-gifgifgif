# gif gif gif

Download animated Gifs from an url and provide a _good quality_ url to be shared with friends.

The main futile goal of this script is simple: provide a preview when sharing a custom animation in Whatsapp (and avoiding linking to sharing sites which might not be safe for your audience).

- Each upload gets a new random name.
- A copy of each image is stored on your server.
- Everybody with the link can access the html page with the animation in it.
- Those who know a _secret_ word, can add new gifs and see the list of all available gifs.

The script supports Gif and Webp animations.

This tool is meant for personal usage only and does not provide the features you would expect from a public gif repository.

## Install

- `gif clone https://github.com/aoloe/php-gifgifgif.git gifgifgif`
- `wget https://raw.githubusercontent.com/aoloe/php-tiny-template/master/src/TinyTemplate.php`.
- `wget https://raw.githubusercontent.com/aoloe/php-tiny-route/master/src/TinyRoute.php`.
- add an `.htaccess`:

  ```
  RewriteEngine On
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . index.php [L]
  ```

- create a `config.php` file based on `config-demo.php`.
- create a `style.css` file (a demo file is provided).
- create the `media` directory.

## Usage

- Adding an image: `http://example.com/gifgifgif/add/secret`.  
  Put the url of the original gif file in the input field and press ok.
- List of the images: `http://example.com/gifgifgif/list/secret`.  
  You'll get a preview with the first frame of each animation.
- Viewing an image: `http://example.com/gifgifgif/hash-hash-hash/gif/view`.  
  This is the link you should be sharing. The visitor should not be able to see the list of the stored images or add new images.
- The secret is a word you've set in the configuration file. It's a weak protection for the the listing of all stored images and for adding new images.

## Security

The upload and listing are protected by a weak password mechanism.

The secret should not be shared with other people, but since it appears in the url, it can easily be discovered by anybody who has access to your computer.

Curently, the main reason for not implementing a better identification process is, that it's very likely that nobody will use a strong password to protect his personal list of animated gifs. So: why bother?

What you  risk? In the best case you'll get more or less funny gifs from strangers... and in the worst case your website gets be abused as an exchange platform for illegal material.

Probably, the best profilaxy is to keep an eye on the activity and remove the script from the server if you do not use it anymore.

## Copyrights

You should avoid to post links to _your_ images in public forums.

Most animated Gifs you find online containt images that are protected by copyrights and depending on the place you're liviving you might be allowed to share them more or less freely.

If you want to be on the safe side, you should keep an eye on the visitors of your site and avoid that strangers are looking at _your_ Gifs.

## Implementation notes

### Preview in whatsapp

Headers to get the icons to be shown in Whatsapp (it must be a new url):

```
    <meta charset="UTF-8">
    <title>Testing the image</title>
    <meta name="description" content="gifs gifs gifs!">
    <meta property="og:title" content="Testing the image" />
    <meta property="og:url" content="https://a-l-e.neocities.org/gre/" />
    <meta property="og:description" content="gifs gifs gifs">
    <meta property="og:image" content="https://a-l-e.neocities.org/gre/22del.gif">
    <meta property="og:type" content="website" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
```

- it must probably be over https
- https://stackoverflow.com/questions/19778620/provide-an-image-for-whatsapp-link-sharing

### Create the preview for a gif

Extracting the first frame from a gif:

- open the file
- keep the first part up to the second occurrence of `\x00\x21\xF9\x04` not included (`^@ù`) (`ctr-v x 0 0` to input it in vim)
- add `\x00\x3B` at the end of the file (`^@;`)

See:

- https://stackoverflow.com/questions/12551646/how-to-extract-frames-of-an-animated-gif-with-php
- https://en.wikipedia.org/wiki/GIF

### Create the preview for a webp

It's very similar as for the gif files:

- <https://developers.google.com/speed/webp/docs/riff_container#animation>
- the delimiter is `ANMF`
- there are no _closing_ bytes

## Todo

- Store static versions of the preview (no need to calculate them each time).
- Let the user delete animations from the list.
