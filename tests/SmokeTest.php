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
use Symfony\Component\Routing\RouterInterface;

/**
 * The whole of the skeleton's test suite: a fresh installation boots and
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
     * `/` ANSWERS 200 WITH THE WELCOME PAGE, and that is the skeleton's
     * contract. Without it `/` is an honest 404 — the skeleton ships no
     * controllers, so in debug Symfony renders its own welcome-404, which would
     * be the first thing anybody saw after `composer create-project`: a correct
     * installation looking like a broken one.
     *
     * The route, the controller behind it and the page are all the shell's. What
     * is the skeleton's is the ONE LINE in `config/routes/shell.yaml` that
     * imports them. That line is why this answers, and editing or deleting it is
     * how an installation takes `/` back.
     *
     * The body is asserted because it is not a framework internal: it is a page
     * this platform ships. As strings rather than through a crawler — the
     * skeleton carries no css-selector and does not need one to know it served
     * the right page.
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

    /**
     * AND IT ANSWERS BY CONSENT, NOT BY ACCIDENT. The skeleton defines no route
     * of its own: it imports the shell's resource, and the shell's route is
     * named `welcome`. Both halves are asserted because both are what an
     * installation is told — the name is what `debug:router` shows somebody who
     * is about to replace the homepage, and the import is the one line they edit
     * to do it.
     */
    public function testTheHomepageIsImportedFromTheShellRatherThanDefinedHere(): void
    {
        self::bootKernel();
        $router = self::getContainer()->get('router');
        self::assertInstanceOf(RouterInterface::class, $router);

        $welcome = $router->getRouteCollection()->get('welcome');
        self::assertNotNull($welcome, 'The imported resource defines the welcome route.');
        self::assertSame('/', $welcome->getPath());

        $routes = (string) file_get_contents(\dirname(__DIR__).'/config/routes/shell.yaml');
        self::assertStringContainsString(
            "resource: '@UhifadhiShellBundle/config/routes/welcome.php'",
            $routes,
            'The skeleton imports the shell\'s route resource.',
        );
        self::assertStringNotContainsString(
            'controller:',
            $routes,
            'The skeleton defines no route of its own; it consents to the shell\'s.',
        );
    }
}
