<?php

namespace App\Support;

/**
 * VAT notice printed on the Fankhauser PDF templates.
 *
 * The issuing company is a German small business (Kleinunternehmer, § 19 UStG).
 * Country-specific wording (e.g. reverse charge for EU business customers) is
 * deliberately left out until it has been confirmed by the tax advisor.
 */
final class SmallBusinessTaxNotice
{
    public const DOMESTIC = 'Gemäß § 19 UStG wird keine Umsatzsteuer berechnet.';

    /**
     * Returns null when the document already charges tax, since the notice would contradict it.
     */
    public static function for(int|float $taxAmount): ?string
    {
        return $taxAmount > 0 ? null : self::DOMESTIC;
    }
}
