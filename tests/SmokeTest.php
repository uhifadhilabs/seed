<?php

declare(strict_types=1);

/*
 * This file is part of uhifadhi.
 *
 * (c) Ezekiel Mjema <https://github.com/eemjema>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * The whole of the seed's test suite: a freshly planted installation boots and
 * answers. Everything beyond this arrives as a module bundle and is tested there.
 */
final class SmokeTest extends WebTestCase
{
    public function testTheKernelBoots(): void
    {
        $kernel = self::bootKernel();

        self::assertSame('test', $kernel->getEnvironment());
        self::assertTrue(self::getContainer()->has('router'));
    }

    /**
     * `/` ANSWERS 200 WITH THE WELCOME PAGE, and that is the seed's contract
     * now. It used to be an honest 404 — the seed ships no controllers, so in
     * debug Symfony rendered its own welcome-404, which was the first thing
     * anybody saw after `composer create-project`: a correct installation
     * looking like a broken one.
     *
     * The fix is one route and no PHP. `config/routes/shell.yaml` points `/` at
     * the shell's `welcome.html.twig` through Symfony's own TemplateController,
     * so the seed still ships no controller class and the shell still ships no
     * route.
     *
     * The body is asserted here, unlike before, because it is no longer a
     * framework internal: it is a page this platform ships and this route names.
     * As strings rather than through a crawler — the seed carries no
     * css-selector and does not need one to know it served the right page.
     */
    public function testTheHomepageAnswersWithTheWelcomePage(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');

        self::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $html = (string) $client->getResponse()->getContent();

        self::assertStringContainsString('<div class="page">', $html, 'It renders inside the shell\'s page frame.');
        self::assertStringContainsString('uhifadhi/seam-module', $html);
        self::assertStringContainsString('uhifadhi/shell-module', $html);
    }
}
