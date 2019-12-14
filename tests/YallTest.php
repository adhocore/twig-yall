<?php

/*
 * This file is part of the TWIG-YALL package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\TwigYall\Test;

use Ahc\TwigYall\Yall;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class YallTest extends TestCase
{
    public static $twig;

    public static function setUpBeforeClass()
    {
        static::$twig = new Environment(new ArrayLoader);

        static::$twig->addExtension(new Yall);
    }

    public function testLazify()
    {
        $this->assertSame(
            '<img class="lazy yall" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEAAAAALAAAAAABAAEAAAI=" '
                . 'data-src="/my/img.jpg" />',
            $this->render('<img {{ lazify("/my/img.jpg") }} />'),
            'should use defaults if src only given'
        );

        $this->assertSame(
            '<img class="cls1 cls2 lazy yall" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEAAAAALAAAAAABAAEAAAI=" '
                . 'data-src="logo.png" />',
            $this->render('<img {{ lazify("logo.png", "cls1 cls2") }} />'),
            'should append to given class'
        );

        $this->assertSame(
            '<img class="cls1 cls2 lazy yall" src="/img/placeholder.png" data-src="logo.png" />',
            $this->render('<img {{ lazify("logo.png", "cls1 cls2", "/img/placeholder.png") }} />'),
            'should use custom placeholder from 3rd param'
        );
    }

    public function testLazifyComplexSrc()
    {
        $this->assertSame(
            '<video class="video lazy yall" poster="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEAAAAALAAAAAABAAEAAAI=" '
                . 'data-poster="video/poster.jpg">',
            $this->render('<video {{ lazify({poster: "video/poster.jpg"}, "video") }}>'),
            'should support video poster'
        );

        $this->assertSame(
            '<source class="lazy yall" data-srcset="img2x.jpg 2x, img1x.jpg 1x">',
            $this->render('<source {{ lazify({srcset: "img2x.jpg 2x, img1x.jpg 1x"}) }}>'),
            'should support srcset only'
        );

        $this->assertSame(
            '<img class="lazy yall" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEAAAAALAAAAAABAAEAAAI=" '
                . 'data-src="src.jpg" data-srcset="src2x.jpg 2x, src1x.jpg 1x" />',
            $this->render('<img {{ lazify(["src.jpg", "src2x.jpg 2x", "src1x.jpg 1x"]) }} />'),
            'should support src and srcset combo'
        );
    }

    public function testYallifyDefault()
    {
        $default = $this->render('{{ yallify() }}');

        $this->assertContains(
            'https://unpkg.com/yall-js@3.1.7/dist/yall.js',
            $default,
            'should contain yall.js'
        );

        $this->assertContains(
            'https://polyfill.io/v2/polyfill.min.js?features=IntersectionObserver',
            $default,
            'should contain polyfill.js'
        );

        $this->assertContains(
            'yall({"lazyClass":"lazy"})',
            $default,
            'should init yall with observe flag'
        );
    }

    public function testYallifyCustom()
    {
        $custom = $this->render('{{ yallify("3.1.6", "", {lazyClass: "lazzi", observeChanges: true}) }}');

        $this->assertContains(
            'https://unpkg.com/yall-js@3.1.6/dist/yall.js',
            $custom,
            'should contain yall.js 3.1.6'
        );

        $this->assertContains(
            'yall({"lazyClass":"lazzi"',
            $custom,
            'should override lazyClass option'
        );

        $this->assertContains(
            ',"observeChanges":true}',
            $custom,
            'should contain extra option'
        );

        $this->assertNotContains(
            'https://polyfill.io/v2/polyfill.min.js?features=IntersectionObserver',
            $custom,
            'shouldnt contain polyfill.js'
        );
    }

    public function testYallifyEvents()
    {
        $this->assertContains(
            '{"events":{"load":fnname,"unload":function(){}}',
            $this->render('{{ yallify(0, 0, {events: {load: "<raw>fnname</raw>", unload: "<raw>function(){}</raw>"}}) }}'),
            'should contain events as raw js (not json string)'
        );
    }

    protected function render(string $template): string
    {
        return static::$twig->createTemplate($template)->render();
    }
}
