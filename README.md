## adhocore/twig-yall

[![Latest Version](https://img.shields.io/github/release/adhocore/twig-yall.svg?style=flat-square)](https://github.com/adhocore/twig-yall/releases)
[![Travis Build](https://img.shields.io/travis/com/adhocore/twig-yall.svg?branch=master&style=flat-square)](https://travis-ci.com/adhocore/twig-yall?branch=master)
[![Scrutinizer CI](https://img.shields.io/scrutinizer/g/adhocore/twig-yall.svg?style=flat-square)](https://scrutinizer-ci.com/g/adhocore/twig-yall/?branch=master)
[![Codecov branch](https://img.shields.io/codecov/c/github/adhocore/twig-yall/master.svg?style=flat-square)](https://codecov.io/gh/adhocore/twig-yall)
[![StyleCI](https://styleci.io/repos/{styleci}/shield)](https://styleci.io/repos/{styleci})
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](./LICENSE)


## Installation
```bash
composer require adhocore/twig-yall
```

## Usage
In twig template you would do something like this:

```twig
<img {{ lazy('path/to/image.jpg', 'class1 class2') }} alt="something" />
```
Then it will be rendered as:

```html
<!-- indented into multiline for clarity only -->
<img src="path/to/placeholder.gif"
  data-src="path/to/image.jpg"
  class="lazy class1 class2"
  alt="something" />
```

Donot forget to put the yall loader somewhere in the footer:
```twig
{% yallify %}
```

## API

<!-- DOCS START -->
<!-- DOCS END -->

## Contributing

Please check [the guide](./CONTRIBUTING.md)

## LICENSE

> &copy; [MIT](./LICENSE) | 2019, Jitendra Adhikari
