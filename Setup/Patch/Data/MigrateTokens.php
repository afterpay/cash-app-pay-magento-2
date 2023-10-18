<?php declare(strict_types=1);

namespace Afterpay\CashApp\Setup\Patch\Data;

class MigrateTokens extends \Afterpay\Afterpay\Setup\Patch\Data\MigrateTokens
{
    protected string $paymentCode = \Afterpay\CashApp\Gateway\Config\Config::CODE;

    public static function getDependencies(): array
    {
        return [\Afterpay\Afterpay\Setup\Patch\Data\MigrateTokens::class];
    }
}
