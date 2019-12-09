<?php

namespace Ahc\TwigYall;

use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class Yall extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('lazy', [$this, 'lazy']),
            new TwigFunction('lazy_srcset', [$this, 'lazySrcset']),
            new TwigFunction('lazy_poster', [$this, 'lazyPoster']),
            new TwigFunction('yallify', [$this, 'yallify']),
        ];
    }

    public function lazy(string $src, string $classes = '', string $dummy = '')
    {
        return $this->lazify('src', $src, $classes, $dummy);
    }

    public function lazySrcset(string $src, string $classes = '', string $dummy = '')
    {
        return $this->lazify('srcset', $src, $classes, $dummy);
    }

    public function lazyPoster(string $src, string $classes = '', string $dummy = '')
    {
        return $this->lazify('poster', $src, $classes, $dummy);
    }

    public function lazify(string $attr, string $src, string $classes = '', string $dummy = '')
    {
        $classes = \trim("$classes lazy yall");
        $dummy   = $dummy ?: 'data:image/gif;base64,R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
        $markup  = \sprintf('class="%s" %s="%s" data-%s="%s"', $classes, $attr, $dummy, $attr, $src);

        return new Markup($markup, 'UTF-8');
    }

    public function yallify(string $version = '3.1.7', bool $polyfill = true)
    {
        $polyfill = $polyfill
            ? '<script src="https://polyfill.io/v2/polyfill.min.js?features=IntersectionObserver" async></script>'
            : '';

        $markup = [
            $polyfill,
            '<script src="https://polyfill.io/v2/polyfill.min.js?features=IntersectionObserver" async></script>',
            '<script type="text/javascript">',
            'document.addEventListener("DOMContentLoaded", function() {',
            '  window.setTimeout(function () { yall({ observeChanges: true }); }, 111);',
            '});',
            '</script>',
        ];

        return new Markup($markup, 'UTF-8');
    }
}
