## adhocore/twig-yall

It is a twig extension around [`malchata/yall.js`](https://github.com/malchata/yall.js)
for lazy loading `img`, `picture`, `video`, `iframe` etc.

(Also supports `source` tag and `srcset` attribute).

[![Latest Version](https://img.shields.io/github/release/adhocore/twig-yall.svg?style=flat-square)](https://github.com/adhocore/twig-yall/releases)
[![Travis Build](https://img.shields.io/travis/com/adhocore/twig-yall.svg?branch=master&style=flat-square)](https://travis-ci.com/adhocore/twig-yall?branch=master)
[![Scrutinizer CI](https://img.shields.io/scrutinizer/g/adhocore/twig-yall.svg?style=flat-square)](https://scrutinizer-ci.com/g/adhocore/twig-yall/?branch=master)
[![Codecov branch](https://img.shields.io/codecov/c/github/adhocore/twig-yall/master.svg?style=flat-square)](https://codecov.io/gh/adhocore/twig-yall)
[![StyleCI](https://styleci.io/repos/172214338/shield)](https://styleci.io/repos/172214338)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](./LICENSE)


## Installation
```bash
composer require adhocore/twig-yall
```

## Usage

First setup twig to register this extension:
```php
// Use your loader of choice
$twig = new Twig\Environment(new Twig\Loader\ArrayLoader);

// Register Yall with defaults
$twig->addExtension(new Ahc\TwigYall\Yall);

// Configuring Yall instance:
$twig->addExtension(new Ahc\TwigYall\Yall(
    'polyfillJs'  => '<custom url to polyfill>',
    'yallJs'      => '<custom url to yall.js>',
    'lazyClass'   => '<default lazy class>',
    'placeholder' => '<default placeholder image url>',
));
```

Voila, then in twig templates you would either use `{% lazyload %}` block to lazyload whole block at once
OR individually lazyload each resources with `{{ lazify() }}`.

In both cases, you must call `{{ yallify() }}` somewhere at the footer.

### lazyload

With `placeholder` config set to `'default.png'`, below template
```twig
<img src="apple.jpg" />                   {# not lazyloaded #}
{% lazyload %}
<img src="ball.jpg" />                    {# lazyloaded #}
<img src="cat.jpg" class="no-lazy" />     {# not lazyloaded #}
<img src="cat.jpg" data-src="..." />      {# not lazyloaded #}
<video poster="vid.jpg">                  {# lazyloaded #}
  <source src="vid1.mp4">                 {# lazyloaded #}
  <source src="vid2.mp4">                 {# lazyloaded #}
</video>
<video class='no-lazy' src="..."></video> {# not lazyloaded #}
<picture><source src="pic.jpg"></picture> {# lazyloaded #}
{% endlazyload %}
<img src="...">                           {# not lazyloaded #}
```
will be rendered as:
```html
<img src="apple.jpg" />
<img class="lazy yall" src="default.png" data-src="ball.jpg" />
<img src="cat.jpg" class="no-lazy" />
<img src="cat.jpg" data-src="..." />
<video class="lazy yall" poster="default.png" data-poster="vid.jpg">
  <source class="lazy yall" data-src="vid1.mp4">
  <source class="lazy yall" data-src="vid2.mp4">
</video>
<video class='no-lazy' src="..."></video>
<picture><source class="lazy yall" data-src="pic.jpg"></picture>
<img src="...">
```

### lazify

#### only src
```twig
<img {{ lazify("/my/img.jpg") }} />
```
will be rendered as:
```html
<img class="lazy yall" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEAAAAALAAAAAABAAEAAAI=" data-src="/my/img.jpg" />
```

See [stackoverflow](https://stackoverflow.com/a/15960901) for the usage of `data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEAAAAALAAAAAABAAEAAAI=`.

#### with class
```twig
<img {{ lazify("logo.png", "cls1 cls2") }} />
```
will be rendered as:
```html
<img class="cls1 cls2 lazy yall" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEAAAAALAAAAAABAAEAAAI=" data-src="logo.png" />
```

#### custom placeholder
```twig
<img {{ lazify("logo.png", "cls1 cls2", "/img/placeholder.png") }} />
```
will be rendered as:
```html
<img class="cls1 cls2 lazy yall" src="/img/placeholder.png" data-src="logo.png" />
```

#### video poster
```twig
<video {{ lazify({poster: "video/poster.jpg"}, "video", "dummyposter.jpg") }}>
```
will be rendered as:
```html
<video class="video lazy yall" poster="dummyposter.jpg" data-poster="video/poster.jpg">
```

#### source tag
```twig
<source {{ lazify({srcset: "img2x.jpg 2x, img1x.jpg 1x"}) }}>
```
will be rendered as:
```html
<source class="lazy yall" data-srcset="img2x.jpg 2x, img1x.jpg 1x">
```

#### src+srcset
```twig
<img {{ lazify(["src.jpg", "src2x.jpg 2x", "src1x.jpg 1x"], "", "dummy.jpg") }} />
```
will be rendered as:
```html
<img class="lazy yall" src="dummy.jpg" data-src="src.jpg" data-srcset="src2x.jpg 2x, src1x.jpg 1x" />
```

### yallify

**Important:** Do not forget to put the yall loader somewhere in the footer twig template:

```twig
{{ yallify() }}
```

Which by default loads yall 3.1.7 with polyfills. You can set yall.js version, and turn off polyfill like so:
```twig
{{ yallify("3.1.6", "") }} {# load yall v3.1.6 but not polyfill #}
```

You can pass yall options in third param. For event callbacks wrap it in `<raw></raw>`:
```twig
{{ yallify(null, null, {observeChanges: true, events: {load: "<raw>function(){}</raw>"}}) }}
```
will be rendered as:
```html
<script src="https://polyfill.io/v2/polyfill.min.js?features=IntersectionObserver" async></script>
<script src="https://unpkg.com/yall-js@3.1.7/dist/yall.min.js" async></script>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function() {
  window.setTimeout(function () {
    yall({
      "observeChanges": true,
      "events": {
        "load": function(){}
      },
      "lazyClass": "lazy"
    });
  }, 99);
});
```

**PS:**
The inputs sent to `lazify()` or `yallify()` are not validated by this library.

From `malchata/yall.js`:
> Use appropriate width and height attributes, styles, and lightweight placeholders for your images.

## Contributing

Please check [the guide](./CONTRIBUTING.md)

## LICENSE

> &copy; [MIT](./LICENSE) | 2019, Jitendra Adhikari
