<?php
// Define default exchange rate (1 USD = 800 NGN)
if (!defined('NAIRA_RATE')) {
    define('NAIRA_RATE', 800);
}

/**
 * Convert USD to Naira
 *
 * @param float $usd
 * @return float
 */
function usdToNaira(float $usd): float
{
    return round($usd * NAIRA_RATE, 2);
}

/**
 * Convert Naira to USD
 *
 * @param float $naira
 * @return float
 */
function nairaToUsd(float $naira): float
{
    return round($naira / NAIRA_RATE, 2);
}
