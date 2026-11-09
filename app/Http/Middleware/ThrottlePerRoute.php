<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

// Laravel's throttle keys by person alone, so every plain-throttled route would share one count.
class ThrottlePerRoute extends ThrottleRequests
{
    protected function resolveRequestSignature($request)
    {
        $route = $request->route();
        $which = $route === null ? '' : ($route->getName() ?? implode('|', $route->methods()).' '.$route->uri());

        return parent::resolveRequestSignature($request).'|'.sha1($which);
    }
}
