<?php

namespace Skaleet\Interview\TransactionProcessing\UseCase;

class CurrencyFormatter
{

    /**
     * Converts an amount to its base units.
     *
     * @param float $amount The amount to be converted.
     * @param string $currency The currency code (e.g., 'USD', 'EUR').
     * @return float The amount in base units.
     */
    public static function toBase($amount, $currency): int
    {

        $baseUnits = self::baseUnits();
        // Get the base unit for the given currency
        $baseUnit = isset($baseUnits[$currency]) ? $baseUnits[$currency] : 1;

        // Convert the amount to base units
        return $amount * $baseUnit;
    }

    public static function toHumanReadable($amount, $currency): float
    {
        $baseUnits = self::baseUnits();
        // Get the base unit for the given currency
        $baseUnit = isset($baseUnits[$currency]) ? $baseUnits[$currency] : 1;

        // Convert the amount to human readable
        return $amount / $baseUnit;
    }

    public static function baseUnits(): array
    {
        // Define base units for common currencies.
        // Could be fetched from Databse, API, JSON.
        return $baseUnits = [
            'USD' => 100, // 1 USD = 100 cents
            'EUR' => 100, // 1 EUR = 100 cents
            'GBP' => 100, // 1 GBP = 100 pence
            'INR' => 100, // 1 INR = 100 paisa
            // Add more currencies as needed
        ];
    }
}
