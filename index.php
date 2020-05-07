<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$config = include_once("config.php");
include_once("TinyRoute.php");
include_once("TinyTemplate.php");

function uuidv4() {
    $result = preg_replace_callback('/[018]/',
        function($matches) {
            $c = $matches[0];
            return base_convert($c ^ random_int(0, 255) & 15 >> $c / 4, 10, 16);
            },
            '10000000-1000-4000-8000-100000000000');
    return $result;
}

$html_template = <<<'EOT'
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>gifgifgif - {{title}}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{css_path}}/style.css">
  </head>
  <body>
    <h1>{{title}}</h1>
    {{body}}
  </body>
</html>
EOT;


$router = new Aoloe\TinyRoute\Router();
$request = Aoloe\TinyRoute\HttpRequest::create();
$response = new Aoloe\TinyRoute\HttpResponse();

$router->get('/add/(\w*)', function($secret) use ($html_template, $config, $response) {
    if ($config['secret'] !== $secret) {
        $response->error_404();
        return;
    }
    $form = <<<'EOT'
  <form method="post">
  <p>Url: <input name="url"><input type="submit" value="»"></p>
  </form>
EOT;
    $template = new Aoloe\TinyTemplate();
    $template->
        add('title', 'Add')->
        add('body', $form);
    $response->respond($template->fetch($html_template));
});

$router->post('/add/(\w*)', function($secret) use($html_template, $config, $request, $response) {
    if ($config['secret'] !== $secret) {
        $response->error_404();
        return;
    }

    $img = file_get_contents($request->get('url'));
    // $img = file_get_contents('cache.webp');

    $type = null;
    if (substr($img, 0, 6) === 'GIF89a') {
        $type = 'gif';
    } elseif (substr($img, 8, 4) === 'WEBP') {
        $type = 'webp';
    } else {
        return;
    }
    $name = uuidv4();
    file_put_contents($config['media'].'/'.$name.'.'.$type, $img);

    $response->respond(Aoloe\TinyTemplate::factory()->
        add('title', 'Added')->
        add('body', '<p><a href="'.$request->get_url($name. '/image.'.$type).'">'.$name.'</a></p>')->
        fetch($html_template));
});

$router->get('/([a-z0-9-\.]+)/(gif|webp)/view', function($image, $type) use ($config, $request, $response) {
    $path = $config['media'].'/'.$image.'.'.$type;
    if (!file_exists($path)) {
        $response->error_404();
        return;
    }
    $view_template = <<<'EOT'
    <!DOCTYPE html>
    <html lang="en">
      <head>
        <meta charset="utf-8">
        <title>gifgifgif - {{title}}</title>
        <meta name="description" content="gifs gifs gifs!">
        <meta property="og:title" content="gif gif gif - {{title}}" />
        <meta property="og:url" content="{{url}}" />
        <meta property="og:description" content="gifs gifs gifs">
        <meta property="og:image" content="{{preview}}">
        <meta property="og:type" content="website" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
      </head>
      <body>
        <p><img src="{{image}}"></p>
      </body>
    </html>
    EOT;
    $template = new Aoloe\TinyTemplate();
    $template->
        add('title', 'da gif')->
        add('url', $request->get_url())->
        add('preview', $request->get_url($image.'/preview.'.$type))->
        add('image', $request->get_url($image.'/image.'.$type));
    $response->respond($template->fetch($view_template));
});

$router->get('/([a-z0-9-\.]+)/image.(gif|webp)', function($image, $type) use ($config, $response) {
    $path = $config['media'].'/'.$image.'.'.$type;
    if (file_exists($path)) {
        $response->pipe_file($path, 'image/'.$type);
    }
});

function get_preview($path, $delimiter, $closing) {
    $start = 0;
    $length = 10000;
    $found = 0;
    $pos = 0;
    $pos_start = 0;
    $bytes = file_get_contents($path, false, null, $start, $length);
    while ($found < 2) {
        if ($bytes === false) {
            break;
        }
        $pos = strpos($bytes, $delimiter, $pos_start);
        if ($pos !== false) {
            $found++;
            $pos_start = $pos + 1;
        } else {
            $start += $length;
            $bytes .= file_get_contents($path, false, null, $start, $length);
        }
    }
    return $found >= 2 ? substr($bytes, 0, $pos) . $closing : null;
}

$router->get('/([a-z0-9-\.]+)/preview.(gif|webp)', function($image, $type) use ($config, $response) {
    $path = $config['media'].'/'.$image.'.'.$type;

    if (!file_exists($path)) {
        $response->error_404();
    }

    if ($type === 'gif') {
        $bytes = get_preview($path, "\x00\x21\xF9\x04", "\x00\x3B");
    } elseif ($type === 'webp') {
        $bytes = get_preview($path, 'ANMF', '');
    } else {
        $response->error_404();
        return;
    }

    $response->respond($bytes, 'image/'.$type);
});

$router->get('/list/(\w*)', function($secret) use ($html_template, $config, $request, $response) {
    if ($config['secret'] !== $secret) {
        $response->error_404();
        return false;
    }
    $li_html = [];
    foreach (new DirectoryIterator($config['media'].'/') as $fileInfo) {
        if ($fileInfo->isDot()) continue;
        $li_html[] = [$fileInfo->getMTime(), $fileInfo->getBasename('.'.$fileInfo->getExtension()), $fileInfo->getExtension()];
    }
    usort($li_html, function($a, $b) {
        return $b[0] - $a[0];
    });
    array_walk($li_html, function(&$a, $k, &$request) {
        $a = Aoloe\TinyTemplate::factory()->
            add('url', $request->get_url($a[1].'/'.$a[2].'/view'))->
            add('url_preview', $request->get_url($a[1].'/preview.'.$a[2]))->
            fetch(
                '<li><a href="{{url}}"><img src="{{url_preview}}"></a></li>');
    }, $request);

    $response->respond(Aoloe\TinyTemplate::factory()->
        add('title', 'My Gifs')->
        add('css_path', $request->get_url())->
        add('body', '<ul class="list">'.implode("\n", $li_html).'</ul></p>')->
        fetch($html_template));
});

if (!$router->run($request)) {
    $response->error_404();
}
