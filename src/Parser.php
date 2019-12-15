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
    /** @var string yall lazyClass */
    protected $lazyClass;

    /** @var string URI for placeholder image */
    protected $placeholder;

    /**
     * Constructor.
     *
     * @param string $lazyClass
     * @param string $placeholder
     */
    public function __construct(string $lazyClass, string $placeholder)
    {
        $this->lazyClass   = $lazyClass;
        $this->placeholder = $placeholder;
    }

    /**
     * Parse `{% lazyload %}...{% endlazyload %}` block.
     *
     * @param Token $token
     *
     * @return Node
     */
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

            if (empty(\trim($props, ' /'))) {
                return $all;
            }

            // Already there or no-flagged
            if (\preg_match('/(data-(src|poster)|no-' . $this->lazyClass . ')/i', $props)) {
                return $all;
            }

            $src = $this->getSrc($tag, $props);
            if (\stripos($props, ' class') === false) {
                $tag .= " class=\"{$this->lazyClass} yall\"";
            }
            if (\stripos($props, ' poster') !== false) {
                $tag .= " poster=\"{$this->placeholder}\"";
            }

            return "<$tag$src" . $this->doReplacements($props) . '>';
        }, $html);
    }

    protected function getSrc(string $tag, string $props): string
    {
        // For source no need src
        if ($tag === 'source') {
            return '';
        }

        // For video no need src if not already there!
        if ($tag === 'video' && \stripos($props, 'src') === false) {
            return '';
        }

        return ' src="' . $this->placeholder . '"';
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

    /**
     * @internal
     */
    public function isLazyloadEnd(Token $token): bool
    {
        return $token->test('endlazyload');
    }

    /**
     * Gets the tag name used in block.
     *
     * @return string
     */
    public function getTag(): string
    {
        return 'lazyload';
    }
}
