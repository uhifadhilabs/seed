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

namespace Uhifadhi\Tests;

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
     * The seed deliberately ships no routes, so `/` is an honest 404 — in debug
     * Symfony renders its own welcome page on that 404, which is the first thing
     * you see after `composer create-project`. The status, not the body, is the
     * contract here: asserting the welcome markup would couple the seed to a
     * framework internal, and the page disappears the moment the first route
     * lands. What this pins is that the request cycle completes end to end.
     */
    public function testTheHomepageAnswers(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');

        self::assertSame(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }
}
