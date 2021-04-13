<?php

namespace Padam87\SimplePayBundle\Service;

class ConfigHelper
{
    private array $config;

    public function __construct(array $config)
    {
        if ($config['base_url'] === null) {
            $config['base_url'] = $config['sandbox'] ? $config['sandbox_url'] : $config['live_url'];
        }

        $this->config = $config;
    }

    public function get($name)
    {
        return $this->config[$name];
    }

    public function getTransactionEntity(): string
    {
        return $this->get('transaction_entity');
    }

    public function getMerchant(string $currency): array
    {
        $currency = strtoupper($currency);

        if (!array_key_exists($currency, $this->config['merchants'])) {
            throw new \Exception(sprintf('No merchant found for currency "%s"', $currency));
        }

        return $this->config['merchants'][$currency];
    }

    public function getMerchantById(string $id): array
    {
        foreach ($this->config['merchants'] as $currency => $merchant) {
            if ($merchant['id'] === $id) {
                return $merchant;
            }
        }

        throw new \Exception(sprintf('No merchant found for id "%s"', $id));
    }

    public function getMerchantId(string $currency): string
    {
        return $this->getMerchant($currency)['id'];
    }

    public function getMerchantSecret(string $currency): string
    {
        return $this->getMerchant($currency)['secret'];
    }

    public function getStartUrl(): string
    {
        return $this->config['base_url'] . '/start';
    }

    public function getDoUrl(): string
    {
        return $this->config['base_url'] . '/do';
    }

    public function getDoRecurringUrl(): string
    {
        return $this->config['base_url'] . '/dorecurring';
    }
}
