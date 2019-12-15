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

class ParserTest extends TestCase
{
    protected static $twig;

    public static function setUpBeforeClass()
    {
        static::$twig = new Environment(new ArrayLoader);

        static::$twig->addExtension(new Yall([
            'placeholder' => 'img/default.png',
            'lazyClass'   => 'defer',
        ]));
    }

    public function testLazyloadSimple()
    {
        $simple = $this->render('
            {% lazyload %}
                <img src="apple.jpg" />
                <img src="ball.jpg" />
            {% endlazyload %}
        ');

        $this->assertContains(
            '<img class="defer yall" src="img/default.png" data-src="apple.jpg" />',
            $simple,
            'should defer image'
        );

        $this->assertContains(
            '<img class="defer yall" src="img/default.png" data-src="ball.jpg" />',
            $simple,
            'should defer image'
        );
    }

    public function testLazyLoadComplex()
    {
        $complex = $this->render('
            {% lazyload %}
              {% if true %}
                <img src="apple.jpg" />
              {% else %}
                <img src="ball.jpg" />
              {% endif %}
            {% endlazyload %}
        ');

        $this->assertContains(
            '<img class="defer yall" src="img/default.png" data-src="apple.jpg" />',
            $complex,
            'should defer image'
        );

        $this->assertNotContains(
            '<img class="defer yall" src="img/default.png" data-src="ball.jpg" />',
            $complex,
            'should defer image'
        );

        $this->assertNotContains(
            '<img src="ball.jpg" />',
            $complex,
            'should not defer image when not met'
        );
    }

    public function testLazyLoadMixed()
    {
        $mixed = $this->render('
            <img src="above.jpg" />
            {% lazyload %}
              <img src="inner.jpg" />
              {% if true %}
                <img src="apple.jpg" />
              {% else %}
                <img src="ball.jpg" />
              {% endif %}
              <img {{ lazify("lazify.jpg") }} />
            {% endlazyload %}
            <img src="below.jpg" />
        ');

        $this->assertContains(
            '<img src="above.jpg" />',
            $mixed,
            'should not defer image above lazyload block'
        );

        $this->assertContains(
            '<img src="below.jpg" />',
            $mixed,
            'should not defer image below lazyload block'
        );

        $this->assertContains(
            '<img class="defer yall" src="img/default.png" data-src="inner.jpg" />',
            $mixed,
            'should defer image inside block'
        );

        $this->assertContains(
            '<img class="defer yall" src="img/default.png" data-src="apple.jpg" />',
            $mixed,
            'should defer image when condition met'
        );

        $this->assertContains(
            '<img class="defer yall" src="img/default.png" data-src="lazify.jpg" />',
            $mixed,
            'should defer image already using lazify'
        );
    }

    public function testLazyLoadNoDefer()
    {
        $irregular = "
            <picture><source><img/></picture>
            <img class='no-defer' src='no-lazy.jpg' />
            <img data-src='already-lazy.jpg' />
            <source data-srcset='img1.jpg 1x, img2.jpg 2x'>
            <video data-poster='poster'>
        ";

        $this->assertContains(
            trim($irregular),
            $this->render("{% lazyload %}$irregular{% endlazyload %}"),
            'shouldnot defer empty or already deferred or no-defer enforced'
        );
    }

    public function testLazyloadNonImg()
    {
        $nonImg = $this->render("
            {% lazyload %}
            <video src='vid.mp4'></video>
            <video poster='vid.jpg'>
              <source src='vid1.mp4'>
              <source src='vid2.mp4'>
            </video>
            {% endlazyload %}
        ");

        $this->assertContains(
            '<video class="defer yall" src="img/default.png" data-src=\'vid.mp4\'></video>',
            $nonImg,
            'should defer video'
        );

        $this->assertContains(
            '<video class="defer yall" poster="img/default.png" data-poster=\'vid.jpg\'>',
            $nonImg,
            'should defer video[poster]'
        );

        $this->assertContains(
            "<source class=\"defer yall\" data-src='vid1.mp4'>",
            $nonImg,
            'should defer video.source'
        );

        $this->assertContains(
            "<source class=\"defer yall\" data-src='vid2.mp4'>",
            $nonImg,
            'should defer video.source'
        );
    }

    protected function render(string $template): string
    {
        return static::$twig->createTemplate($template)->render();
    }
}
