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

use Twig\Node\Node;
use Twig\Node\TextNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class Parser extends AbstractTokenParser
{
    protected $lazyClass;
    protected $placeholder;

    public function __construct(string $lazyClass, string $placeholder)
    {
        $this->lazyClass   = $lazyClass;
        $this->placeholder = $placeholder;
    }

    public function parse(Token $token): Node
    {
        $stream = $this->parser->getStream();

        $stream->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'isLazyloadEnd'], true);
        $stream->expect(Token::BLOCK_END_TYPE);

        return $this->traverse($body);
    }

    protected function traverse(Node $node)
    {
        if ($node instanceof TextNode) {
            $node->setAttribute('data', $this->doLazyload($node->getAttribute('data')));
        }

        foreach ($node as $sub) {
            $this->traverse($sub);
        }

        return $node;
    }

    protected function doLazyload(string $html): string
    {
        return \preg_replace_callback('/<(img|source|video)([^>]+)>/m', function ($match) {
            list($all, $tag, $props) = $match;

            if (empty(\trim($props, ' /')) || \strpos($props, "no-{$this->lazyClass}") !== false) {
                return $all;
            }

            if (\stripos($props, 'data-src') !== false || \stripos($props, 'data-poster') !== false) {
                return $all;
            }

            // For source no need src, for video no need src if not already there!
            $needSrc = $tag !== 'source' && ($tag !== 'video' || \stripos($props, 'src') !== false);

            if (\stripos($props, ' class') === false) {
                $tag .= " class=\"{$this->lazyClass} yall\"";
            }
            if (\stripos($props, ' poster') !== false) {
                $tag .= " poster=\"{$this->placeholder}\"";
            }

            $src = $needSrc ? " src=\"{$this->placeholder}\"" : '';

            return "<$tag$src" . $this->doReplacements($props) . '>';
        }, $html);
    }

    protected function doReplacements(string $props): string
    {
        $replacements = [
            ' src='      => ' data-src=',
            ' src ='     => ' data-src=',
            ' srcset='   => ' data-srcset=',
            ' srcset ='  => ' data-srcset=',
            ' class="'   => " class=\"{$this->lazyClass} yall ",
            ' class ="'  => " class=\"{$this->lazyClass} yall ",
            ' class = "' => " class=\"{$this->lazyClass} yall ",
            " class='"   => " class='{$this->lazyClass} yall ",
            " class ='"  => " class='{$this->lazyClass} yall ",
            " class = '" => " class='{$this->lazyClass} yall ",
            ' poster='   => ' data-poster=',
            ' poster ='  => ' data-poster=',
        ];

        return \strtr($props, $replacements);
    }

    public function isLazyloadEnd(Token $token): bool
    {
        return $token->test('endlazyload');
    }

    public function getTag(): string
    {
        return 'lazyload';
    }
}
