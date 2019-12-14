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
        $nodes = $this->parser->subparse([$this, 'isLazyloadEnd'], true);
        $stream->expect(Token::BLOCK_END_TYPE);

        return $this->traverse($nodes);
    }

    protected function traverse(Node $nodes)
    {
        foreach ($nodes as $node) {
            if ($node instanceof TextNode) {
                $node->setAttribute('data', $this->doLazyload($node->getAttribute('data')));
            } else {
                $this->traverse($node);
            }
        }

        return $nodes;
    }

    protected function doLazyload(string $html): string
    {
        return \preg_replace_callback('/<(img|source|video)([^>]+)>/m', function ($match) {
            list($all, $tag, $props) = $match;

            if (empty(\trim($props, ' /')) || \stripos($props, $this->lazyClass) !== false) {
                return $all;
            }

            if (\stripos($props, 'data-src') !== false || \stripos($props, 'data-poster') !== false) {
                return $all;
            }

            // For video, dont need src if not already there!
            $needSrc = $tag !== 'video' || \strpos($props, 'src') !== false;

            if (\stripos($props, ' class') === false) {
                $tag .= " class=\"{$this->lazyClass} yall\"";
            }
            if (\stripos($props, ' poster') !== false) {
                $tag .= " poster=\"{$this->placeholder}\"";
            }

            $replacements = [
                ' src='      => ' data-src=',
                ' src ='     => ' data-src=',
                ' srcset='   => ' data-srcset=',
                ' srcset ='  => ' data-srcset=',
                ' class="'   => " class=\"{$this->lazyClass} yall ",
                ' class ="'  => " class=\"{$this->lazyClass} yall ",
                ' class = "' => " class=\"{$this->lazyClass} yall ",
                ' poster='   => ' data-poster=',
                ' poster ='  => ' data-poster=',
            ];

            $src = $needSrc ? " src=\"{$this->placeholder}\" " : ' ';

            return "<$tag$src" . \trim(\strtr($props, $replacements)) . '>';
        }, $html);
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
