<?php

declare(strict_types=1);

/**
 * Derafu: Website Skeleton - Base for Derafu's web sites.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Tests;

use App\Translation\WebsiteTranslationResourceProvider;
use Derafu\Http\Kernel;
use Derafu\Kernel\Environment;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The site as it runs: the kernel with the real configuration of `config/`, a
 * request, and the response with the templates rendered and translated.
 */
#[CoversClass(WebsiteTranslationResourceProvider::class)]
final class WebsiteTest extends TestCase
{
    private string|false $locale;

    protected function setUp(): void
    {
        $this->locale = getenv('APP_LOCALE');
    }

    protected function tearDown(): void
    {
        putenv($this->locale === false ? 'APP_LOCALE' : 'APP_LOCALE=' . $this->locale);
    }

    /**
     * @return array{int, string} The status and the body of the response.
     */
    private function get(string $path, ?string $locale = null): array
    {
        putenv($locale === null ? 'APP_LOCALE' : 'APP_LOCALE=' . $locale);

        // Debug mode, so the container is built again with the configuration
        // of the moment and not taken from a cache that an earlier run left.
        $kernel = new Kernel(new Environment('test', true, [
            'APP_ENV' => 'test',
            'APP_DEBUG' => true,
            'PROJECT_DIR' => dirname(__DIR__),
            'URL_HOST' => 'localhost',
        ]));

        $response = $kernel->handle(new ServerRequest(
            'GET',
            'http://localhost' . $path,
            [],
            null,
            '1.1',
            ['SERVER_PORT' => 80, 'SERVER_NAME' => 'localhost', 'REQUEST_SCHEME' => 'http', 'HTTP_HOST' => 'localhost']
        ));

        return [$response->getStatusCode(), (string) $response->getBody()];
    }

    #[Test]
    public function theHomePageIsInEnglishByDefault(): void
    {
        [$status, $body] = $this->get('/');

        $this->assertSame(200, $status);
        $this->assertStringContainsString('<title>Home | Derafu</title>', $body);
        $this->assertStringContainsString('Documentation', $body);
        $this->assertStringContainsString('All rights reserved', $body);
        $this->assertStringContainsString('Copyright © ' . date('Y'), $body);
    }

    #[Test]
    public function theHomePageIsTranslatedToTheLanguageOfTheSite(): void
    {
        [$status, $body] = $this->get('/', 'es');

        $this->assertSame(200, $status);
        $this->assertStringContainsString('<title>Inicio | Derafu</title>', $body);
        // What the audit can not read, the texts that go to a component as a
        // property, is checked here in the page: each one is in Spanish.
        foreach ([
            'Academia',
            'Documentación',
            'Preguntas frecuentes',
            'Contacto y colaboración',
            'Conoce más',
            'Si te interesa contribuir, colaborar',
        ] as $text) {
            $this->assertStringContainsString($text, $body, $text);
        }
        foreach (['Academy', 'Documentation', 'FAQ', 'Learn more', 'Contact &amp; Collaboration'] as $english) {
            $this->assertStringNotContainsString($english, $body, $english);
        }
        $this->assertStringContainsString('Todos los derechos reservados', $body);
        $this->assertStringContainsString('Copyright © ' . date('Y'), $body);
        $this->assertStringNotContainsString('All rights reserved', $body);
    }

    #[Test]
    public function theContactPageIsTheOneOfTheContactFormPackage(): void
    {
        [$status, $body] = $this->get('/contact');

        $this->assertSame(200, $status);
        $this->assertStringContainsString('<form', $body);
        $this->assertStringContainsString('Contact Us', $body);
    }
}
