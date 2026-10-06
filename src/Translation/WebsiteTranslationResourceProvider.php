<?php

declare(strict_types=1);

/**
 * Derafu: Website Skeleton - Base for Derafu's web sites.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

namespace App\Translation;

use Derafu\Translation\Contract\TranslationResourceProviderInterface;

/**
 * Provides the translations of the site: the texts of its templates, in the
 * domain `website`.
 */
final class WebsiteTranslationResourceProvider implements TranslationResourceProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getDirectories(): iterable
    {
        return [__DIR__ . '/../../resources/translations'];
    }
}
