<?php

namespace Ahc\TwigYall\Test;

use Ahc\TwigYall\Yall;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class YallTest extends TestCase
{
    public function testLazy()
    {
        $default = $this->twig('<img {{ lazy("/my/img.jpg") }} />')->render();
        $this->assertSame(
            '<img class="lazy yall" src='
            . '"data:image/gif;base64,R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" '
            . 'data-src="/my/img.jpg" />',
            $default
        );

        $classes = $this->twig('<img {{ lazy("logo.png", "cls1 cls2") }} />')->render();
        $this->assertSame(
            '<img class="cls1 cls2 lazy yall" src='
            . '"data:image/gif;base64,R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" '
            . 'data-src="logo.png" />',
            $classes
        );
    }

    protected function twig(string $template)
    {
        $twig = new Environment(new ArrayLoader);

        $twig->addExtension(new Yall);

        return $twig->createTemplate($template);
    }
}
