<?php

/*
 * This file is part of the TWIG-YALL package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\TwigYall;

use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class Yall extends AbstractExtension
{
    /** @var array Configuration */
    protected $config = [];

    /**
     * Constructor.
     *
     * @param array $config Optinal configuration
     */
    public function __construct(array $config = [])
    {
        $this->config = $config + [
            'polyfillJs'  => 'https://polyfill.io/%s/polyfill.min.js?features=IntersectionObserver',
            // @see: https://stackoverflow.com/a/15960901
            'placeholder' => 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEAAAAALAAAAAABAAEAAAI=',
            'yallJs'      => 'https://unpkg.com/yall-js@%s/dist/yall.min.js',
            'lazyClass'   => 'lazy',
        ];
    }

    /**
     * Get twig functions defined by this extension.
     *
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('lazify', [$this, 'lazify']),
            new TwigFunction('yallify', [$this, 'yallify']),
        ];
    }

    /**
     * Get token parsers defined by this extension.
     *
     * @return array
     */
    public function getTokenParsers(): array
    {
        return [new Parser($this->config['lazyClass'], $this->config['placeholder'])];
    }

    /**
     * Loads yall and polyfill scripts then triggers lazy loading.
     *
     * @param string|null $yall     Yall version
     * @param string|null $polyfill Polyfill version ('' = off)
     * @param array       $options  Options for `yall({})` callback
     *
     * @return Markup
     */
    public function yallify(string $yall = null, string $polyfill = null, array $options = []): Markup
    {
        $yallJs   = \sprintf($this->config['yallJs'], $yall ?: '3.1.7');
        $options += ['lazyClass' => $this->config['lazyClass']];
        $jsonFlag = \JSON_UNESCAPED_SLASHES | \JSON_FORCE_OBJECT;

        $jsonOpts = \json_encode($options, $jsonFlag);
        $jsonOpts = \str_replace(['"<raw>', '</raw>"'], ['', ''], $jsonOpts);

        $markup = [
            $polyfill ?? 'v2'
                ? \sprintf('<script src="%s" async></script>', \sprintf($this->config['polyfillJs'], $polyfill ?? 'v2'))
                : '',
            \sprintf('<script src="%s" async></script>', $yallJs),
            '<script type="text/javascript">',
            'document.addEventListener("DOMContentLoaded", function() {',
            \sprintf('  window.setTimeout(function () { yall(%s); }, 99);', $jsonOpts),
            '});',
            '</script>',
        ];

        return new Markup(\implode("\n", $markup), 'UTF-8');
    }

    /**
     * Lazify resources.
     *
     * @param string|string[] $src     The sources to lazy load
     * @param string          $classes The optional element classes
     * @param string          $dummy   The optional placeholder image
     *
     * @return Markup
     */
    public function lazify($src, string $classes = '', string $dummy = ''): Markup
    {
        $attr = 'src';
        if (\is_array($src)) {
            list($attr, $src) = $this->normalizeSrc($src);
        }

        $classes = \trim("$classes {$this->config['lazyClass']} yall");
        if ('srcset' !== $attr) {
            $dummy = \sprintf(' %s="%s"', $attr, $dummy ?: $this->config['placeholder']);
        }

        $markup = \sprintf('class="%s"%s data-%s="%s"', $classes, $dummy, $attr, $src);

        return new Markup($markup, 'UTF-8');
    }

    protected function normalizeSrc(array $src): array
    {
        if ($src['poster'] ?? false) {
            return ['poster', $src['poster']];
        }

        if ($src['srcset'] ?? false) {
            return ['srcset', $src['srcset']];
        }

        $srcset = $src;
        $src    = \array_shift($srcset);

        return ['src', $src . '" data-srcset="' . \implode(', ', $srcset)];
    }
}
