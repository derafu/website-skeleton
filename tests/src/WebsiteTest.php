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
use Derafu\Foundation\Testing\SiteTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * The site as it runs: the kernel with the real configuration of `config/`, a
 * request, and the response with the templates rendered and translated.
 *
 * `SiteTestCase` already tests what every site must do (its pages answer, a
 * page that does not exist is a 404, no route fails in the server); here is
 * only what is about this site.
 */
#[CoversClass(WebsiteTranslationResourceProvider::class)]
final class WebsiteTest extends SiteTestCase
{
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
}
