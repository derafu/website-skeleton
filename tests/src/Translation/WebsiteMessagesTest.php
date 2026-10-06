<?php

declare(strict_types=1);

/**
 * Derafu: Website Skeleton - Base for Derafu's web sites.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace Tests\Translation;

use App\Translation\WebsiteTranslationResourceProvider;
use Derafu\Twig\Lint\TwigTranslationAudit;
use Derafu\Twig\Service\TwigService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * The site is translated: every text of its templates goes through the
 * translation and has its Spanish translation, and the catalogue has nothing
 * that the templates do not use.
 *
 * The audit reads the templates, so a new text without its entry in the
 * catalogue fails here, instead of showing in English in a site in another
 * language.
 *
 * The only texts that are not translated are the ones declared here, on purpose:
 *
 *   - The name of the site, in the title: a placeholder that every site replaces.
 *   - The labels of the debug page of the errors (`error.html.twig`). It is only
 *     shown with `context.error.debug`, to whoever develops the application,
 *     like the output of a command: it is technical, and it is not translated.
 *
 * The texts that a template gives to a component as a property are out of reach
 * of the audit, so the ones that have to be translated are written with `|trans`,
 * which the audit does read.
 */
#[CoversClass(WebsiteTranslationResourceProvider::class)]
final class WebsiteMessagesTest extends TestCase
{
    public function testTheTemplatesAreTranslated(): void
    {
        $root = dirname(__DIR__, 3);

        // The templates are written for a site that has routes: the functions
        // that they use are only declared here.
        $application = new class () extends AbstractExtension {
            public function getFunctions(): array
            {
                return array_map(
                    fn (string $name) => new TwigFunction($name, fn () => ''),
                    ['path', 'asset']
                );
            }
        };

        $report = (new TwigTranslationAudit())->audit(
            $root . '/src',
            $root . '/templates',
            new WebsiteTranslationResourceProvider(),
            (new TwigService([
                'extra' => false,
                'paths' => [$root . '/templates', $root . '/vendor/derafu/twig/resources/templates'],
                'extensions' => [$application],
            ]))->getTwig(),
            allowedTexts: [
                // The name of the site.
                '| Derafu',
                'Derafu Website',
                // Debug page of the errors.
                'Technical Details',
                'Reference URI:',
                'File:',
                'Request URI:',
                'Timestamp:',
                'Environment:',
                'Stack Trace',
                'Previous Exception',
            ]
        );

        // Finding nothing would look like a clean result.
        $this->assertFalse($report->nothingFound);
        $this->assertSame([], $report->describe($report->untranslatedTexts));
        $this->assertSame([], $report->describe($report->dynamicMessages));
        $this->assertSame([], $report->describe($report->missingTranslations));
        $this->assertSame([], $report->describe($report->notUsedBySources));
        $this->assertSame([], $report->describe($report->notTranslatable));
    }
}
