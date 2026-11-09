<?php

namespace App\Enums;

enum Plan: string
{
    case Essential = 'starter';
    case Pro = 'pro';
    case Business = 'business';

    public const int ANNUAL_MONTHS_CHARGED = 10;

    public function label(): string
    {
        return match ($this) {
            self::Essential => 'Essential',
            self::Pro => 'Pro',
            self::Business => 'Business',
        };
    }

    public function monthlyPrice(Currency $currency = Currency::GBP): int
    {
        return match ($currency) {
            Currency::GBP => match ($this) {
                self::Essential => 500,
                self::Pro => 900,
                self::Business => 1900,
            },
            Currency::USD => match ($this) {
                self::Essential => 600,
                self::Pro => 1200,
                self::Business => 2400,
            },
            Currency::EUR => match ($this) {
                self::Essential => 600,
                self::Pro => 1000,
                self::Business => 2200,
            },
        };
    }

    public function annualPrice(Currency $currency = Currency::GBP): int
    {
        return $this->monthlyPrice($currency) * self::ANNUAL_MONTHS_CHARGED;
    }

    public function price(BillingInterval $interval, Currency $currency = Currency::GBP): int
    {
        return $interval === BillingInterval::Annual ? $this->annualPrice($currency) : $this->monthlyPrice($currency);
    }

    public function machineLimit(): ?int
    {
        return match ($this) {
            self::Essential => 1,
            self::Pro => 5,
            self::Business => null,
        };
    }

    public function mcpCallLimit(): int
    {
        return match ($this) {
            self::Essential => 500,
            self::Pro => 2500,
            self::Business => 10000,
        };
    }

    public function hasStaffRoles(): bool
    {
        return $this !== self::Essential;
    }

    public function hasCustomRoles(): bool
    {
        return $this === self::Business;
    }

    public function hasSso(): bool
    {
        return $this === self::Business;
    }

    public function hasScim(): bool
    {
        return $this === self::Business;
    }

    public function hasCustomDomains(): bool
    {
        return $this === self::Business;
    }

    public function hasOwnLogStorage(): bool
    {
        return $this === self::Business;
    }

    public function hasFleetStatusPage(): bool
    {
        return $this === self::Business;
    }

    public function logRetentionDays(): int
    {
        return match ($this) {
            self::Essential => 3,
            self::Pro => 14,
            self::Business => 30,
        };
    }

    public function logLinesPerDay(): int
    {
        return match ($this) {
            self::Essential => 200_000,
            self::Pro => 1_000_000,
            self::Business => 2_000_000,
        };
    }

    public function statusSubscriberLimit(): int
    {
        return match ($this) {
            self::Essential => 100,
            self::Pro => 1_000,
            self::Business => 10_000,
        };
    }

    public function activityRetentionDays(): int
    {
        return match ($this) {
            self::Essential => 90,
            self::Pro => 180,
            self::Business => 365,
        };
    }

    public function monitorLimit(): int
    {
        return match ($this) {
            self::Essential => 10,
            self::Pro => 50,
            self::Business => 250,
        };
    }

    public function minMonitorInterval(): int
    {
        return match ($this) {
            self::Essential => 180,
            self::Pro => 60,
            self::Business => 30,
        };
    }

    public function summary(): string
    {
        return self::translated(match ($this) {
            self::Essential => 'For a single server and everything on it.',
            self::Pro => 'For small teams running a handful of servers.',
            self::Business => 'For teams with many machines that need control and guarantees.',
        });
    }

    private static function translated(string $text): string
    {
        $line = __($text);

        return is_string($line) ? $line : $text;
    }

    /** @return list<string> */
    public function features(): array
    {
        return array_map(self::translated(...), match ($this) {
            self::Essential => [
                'Import existing servers',
                'Unlimited apps and sites',
                'Deployments and backups',
                'Monitoring and alerts',
            ],
            self::Pro => [
                'Everything in Essential',
                'Workflows',
                'Server and coding agents',
                'Status pages for each machine',
                'Staff roles',
            ],
            self::Business => [
                'Everything in Pro',
                'One status page for every machine',
                'Custom roles',
                'Single sign-on (SAML, OIDC)',
                'Logs in your own database',
                'SLA',
            ],
        });
    }

    /** @return array{value: string, label: string, summary: string, monthlyPrice: int, annualPrice: int, machineLimit: int|null, mcpCallLimit: int, monitorLimit: int, minMonitorInterval: int, logRetentionDays: int, logLinesPerDay: int, activityRetentionDays: int, features: list<string>} */
    public function toPageProps(Currency $currency = Currency::GBP): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'summary' => $this->summary(),
            'monthlyPrice' => $this->monthlyPrice($currency),
            'annualPrice' => $this->annualPrice($currency),
            'machineLimit' => $this->machineLimit(),
            'mcpCallLimit' => $this->mcpCallLimit(),
            'monitorLimit' => $this->monitorLimit(),
            'minMonitorInterval' => $this->minMonitorInterval(),
            'logRetentionDays' => $this->logRetentionDays(),
            'logLinesPerDay' => $this->logLinesPerDay(),
            'activityRetentionDays' => $this->activityRetentionDays(),
            'features' => $this->features(),
        ];
    }
}
