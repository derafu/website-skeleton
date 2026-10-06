<?php

declare(strict_types=1);

/**
 * Derafu: Website Skeleton - Base for Derafu's web sites.
 *
 * Copyright (c) 2026 Esteban De La Fuente Rubio / Derafu <https://www.derafu.dev>
 * Licensed under the MIT License.
 * See LICENSE file for more details.
 */

use Derafu\Http\Kernel;
use Derafu\Kernel\Environment;

require_once dirname(__DIR__) . '/app/bootstrap.php';

return fn (array $context): Kernel => new Kernel(new Environment(
    $context['APP_ENV'],
    (bool) $context['APP_DEBUG'],
    $context
));
