<?php declare(strict_types=1);

namespace Afterpay\CashApp\Model;

class CheckoutConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    private \Magento\Framework\Locale\Resolver $localeResolver;

    public function __construct(
        \Magento\Framework\Locale\Resolver $localeResolver
    ) {
        $this->localeResolver = $localeResolver;
    }

    public function getConfig(): array
    {
        return [
            'payment' => [
                'cashapp' => [
                    'locale' => $this->localeResolver->getLocale()
                ]
            ]
        ];
    }
}
