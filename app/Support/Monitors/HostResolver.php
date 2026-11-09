<?php

namespace App\Support\Monitors;

class HostResolver
{
    /** @return list<string> */
    public function resolve(string $host): array
    {
        $host = trim($host, '[]');

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return $this->allowed($host) ? [$host] : [];
        }

        $addresses = [];
        foreach (@dns_get_record($host, DNS_A | DNS_AAAA) ?: [] as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;
            if (is_string($ip)) {
                $addresses[] = $ip;
            }
        }
        if ($addresses === []) {
            $addresses = array_values(array_filter((array) @gethostbynamel($host)));
        }

        foreach ($addresses as $ip) {
            if (! $this->allowed($ip)) {
                return [];
            }
        }

        usort($addresses, fn (string $a, string $b) => str_contains($a, ':') <=> str_contains($b, ':'));

        return $addresses;
    }

    public function allowed(string $ip): bool
    {
        if (config('monitors.allow_private')) {
            return filter_var($ip, FILTER_VALIDATE_IP) !== false;
        }

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false
            && ! str_starts_with($ip, '100.64.')
            && ! str_starts_with(strtolower($ip), '::ffff:');
    }
}
