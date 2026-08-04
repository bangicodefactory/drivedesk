<?php

namespace App\Support;

/**
 * Parsers for date and time cells coming out of an uploaded spreadsheet.
 *
 * Extracted verbatim from BookingController so the traffic-violation importer
 * (BAN-260) reads dates the same way the booking importer does. The day-first
 * contract below is a deliberate, hard-won decision (IST-231) — duplicating it
 * would eventually mean two importers disagreeing about what 01/06/2026 means.
 */
class ExcelValue
{
    /**
     * A spreadsheet date cell as 'Y-m-d', or null if it cannot be trusted.
     *
     * String dates are interpreted as the import locale, day-first (d/m/Y),
     * matching the booking template. We deliberately do NOT fall back to the
     * American m/d/Y order: an ambiguous value like "01/06/2026" always means
     * 1 June (not 6 Jan), and a US-style value with day > 12 (e.g. "06/20/2026")
     * is rejected as invalid rather than silently swapped (IST-231). Use a real
     * Excel date cell or ISO YYYY-MM-DD for anything outside this format.
     */
    public static function date($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Numeric: Excel serial date (unambiguous).
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        $value = trim((string) $value);

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $fmt) {
            $parsed = \DateTime::createFromFormat($fmt, $value);
            if ($parsed && $parsed->format($fmt) === $value) {
                return $parsed->format('Y-m-d');
            }
        }

        return null;
    }

    /** A spreadsheet time cell as 'H:i:s', or null if it cannot be trusted. */
    public static function time($value): ?string
    {
        if (empty($value) && $value !== '0') {
            return null;
        }

        // Numeric: Excel fractional day (e.g. 0.375 = 09:00)
        if (is_numeric($value) && $value >= 0 && $value < 1) {
            $seconds = round((float) $value * 86400);

            return gmdate('H:i:s', $seconds);
        }

        $value = trim((string) $value);

        // HH:MM or HH:MM:SS
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
            return strlen($value) === 5 ? $value.':00' : $value;
        }

        return null;
    }
}
