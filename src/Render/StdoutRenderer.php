<?php

declare(strict_types=1);

namespace Testo\Render;

use Testo\Module\Interceptor\Internal\InterceptorMarker;

/**
 * Nothing special, just a marker for stdout renderers.
 *
 * The interface can be configured as an interceptor and only one stdout renderer
 * can be active at the same time.
 */
interface StdoutRenderer extends InterceptorMarker {}
