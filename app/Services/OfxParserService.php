<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class OfxParserService
{
    /**
     * Parse an OFX file and return a collection of transactions.
     *
     * Each transaction is an array with keys:
     * - fitid: unique bank transaction ID
     * - type: 'income' or 'expense'
     * - date: Carbon date
     * - amount: float (always positive)
     * - description: string
     */
    public function parse(string $filePath): Collection
    {
        $content = file_get_contents($filePath);

        // Remove BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Normalize line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        $transactions = collect();

        // Extract all <STMTTRN>...</STMTTRN> blocks
        preg_match_all('/<STMTTRN>(.*?)<\/STMTTRN>/si', $content, $matches);

        if (empty($matches[1])) {
            // Try SGML style (no closing tags on values)
            $transactions = $this->parseSgml($content);
        } else {
            foreach ($matches[1] as $block) {
                $transaction = $this->parseTransactionBlock($block);
                if ($transaction) {
                    $transactions->push($transaction);
                }
            }
        }

        return $transactions->sortBy('date')->values();
    }

    /**
     * Parse SGML-style OFX (most common format from Brazilian banks).
     */
    private function parseSgml(string $content): Collection
    {
        $transactions = collect();

        // Split by <STMTTRN> and process each
        $parts = preg_split('/<STMTTRN>/i', $content);

        // Skip first part (header/before first transaction)
        array_shift($parts);

        foreach ($parts as $block) {
            // Cut at next major tag or end of transaction list
            $block = preg_split('/<\/STMTTRN>|<\/BANKTRANLIST>/i', $block)[0] ?? $block;
            $transaction = $this->parseTransactionBlock($block);
            if ($transaction) {
                $transactions->push($transaction);
            }
        }

        return $transactions;
    }

    /**
     * Parse a single transaction block (SGML or XML style).
     */
    private function parseTransactionBlock(string $block): ?array
    {
        $trnType = $this->extractValue($block, 'TRNTYPE');
        $dtPosted = $this->extractValue($block, 'DTPOSTED');
        $amount = $this->extractValue($block, 'TRNAMT');
        $fitId = $this->extractValue($block, 'FITID');
        $memo = $this->extractValue($block, 'MEMO');
        $name = $this->extractValue($block, 'NAME');

        if (!$amount || !$dtPosted) {
            return null;
        }

        $amountFloat = (float) str_replace(',', '.', $amount);
        $description = trim($memo ?: $name ?: 'Transacao OFX');

        // Clean up description: remove excessive spaces
        $description = preg_replace('/\s+/', ' ', $description);

        // Determine type based on amount sign
        $type = $amountFloat >= 0 ? 'income' : 'expense';

        // Parse date (YYYYMMDD or YYYYMMDDHHMMSS or YYYYMMDDHHMMSS[-3:BRT])
        $dateStr = preg_replace('/\[.*\]/', '', $dtPosted); // remove timezone brackets
        $dateStr = substr($dateStr, 0, 8); // take only YYYYMMDD

        try {
            $date = Carbon::createFromFormat('Ymd', $dateStr);
        } catch (\Exception $e) {
            return null;
        }

        return [
            'fitid' => $fitId ?? uniqid('ofx_'),
            'type' => $type,
            'date' => $date->format('Y-m-d'),
            'amount' => abs($amountFloat),
            'description' => $description,
            'original_amount' => $amountFloat,
        ];
    }

    /**
     * Extract a tag value from an SGML/XML block.
     */
    private function extractValue(string $block, string $tag): ?string
    {
        // Try XML style first: <TAG>value</TAG>
        if (preg_match("/<{$tag}>(.*?)<\/{$tag}>/si", $block, $match)) {
            return trim($match[1]);
        }

        // SGML style: <TAG>value\n
        if (preg_match("/<{$tag}>([^\n<]+)/i", $block, $match)) {
            return trim($match[1]);
        }

        return null;
    }
}
