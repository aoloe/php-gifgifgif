# gif gif gif

Download animated gifs from an url and provide a _good quality_ url.

The main futile goal is to get a preview when sharing a custom animation in Whatsapp.

- Each upload gets a new random name.
- Everybody can access the direct link to the gif and an html page that provide the gif and the correct preview.
- Those who know a _secret_ word, can fetch new gifs and see the list of all available gifs.

It also supports Webp animations.

This tool is meant for personal usage only and does not provide the features you would expect from a public gif repository.

## Install

- `gif clone https://github.com/aoloe/php-gifgifgif.git gifgifgif`
- `wget https://raw.githubusercontent.com/aoloe/php-tiny-template/master/src/TinyTemplate.php`.
- `wget TinyRoute`.
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
