<?php

namespace Kerisy\Core;

/**
 * Interface MiddlewareContract
 *
 * @package Kerisy\Http
 */
interface MiddlewareContract
{
    public function handle($value);
}
