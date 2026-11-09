<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class TrustCloudflare
{
    public const array RANGES = [
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22',
        '141.101.64.0/18', '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20',
        '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
        '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32',
        '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
    ];

    public const string COUNTRY = 'cloudflare.country';

    public function handle(Request $request, Closure $next): Response
    {
        $edge = (string) $request->server->get('REMOTE_ADDR');
        $visitor = trim((string) $request->headers->get('CF-Connecting-IP'));

        if ($visitor !== '' && filter_var($visitor, FILTER_VALIDATE_IP) && IpUtils::checkIp($edge, self::RANGES)) {
            $request->server->set('REMOTE_ADDR', $visitor);
            $request->headers->remove('X-Forwarded-For');

            $country = strtoupper(trim((string) $request->headers->get('CF-IPCountry')));
            if (preg_match('/^[A-Z][A-Z0-9]$/', $country) === 1 && $country !== 'XX') {
                $request->attributes->set(self::COUNTRY, $country);
            }
        }

        return $next($request);
    }
}
