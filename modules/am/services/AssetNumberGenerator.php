<?php

namespace app\modules\am\services;

use Yii;
use app\components\AppHelper;

/**
 * Generates asset numbers (FSN) with configurable format and per-category/year sequence.
 *
 * Tokens supported in pattern:
 *   {category}        FSN prefix (e.g. 7910-003-0003)
 *   {year}            Buddhist year, 2 digits (alias of {year:2})
 *   {year:2}          Buddhist year, 2 digits (e.g. 66)
 *   {year:4}          Buddhist year, 4 digits (e.g. 2566)
 *   {year:ad}         Christian year, 2 digits (alias of {year:ad:2})
 *   {year:ad:2}       Christian year, 2 digits (e.g. 23)
 *   {year:ad:4}       Christian year, 4 digits (e.g. 2023)
 *   {seq}             Running sequence padded to 2 digits (alias of {seq:2})
 *   {seq:0}           Running sequence, no padding (e.g. 1, 15, 120)
 *   {seq:N}           Running sequence padded to N digits (N up to 6)
 *
 * Examples:
 *   {category}/{year}.{seq}        -> 7910-003-0003/66.01
 *   {category}-{seq:0}/{year:2}    -> 7910-003-0003-1/66
 *   {category}-{seq:3}/{year:4}    -> 7910-003-0003-001/2566
 */
class AssetNumberGenerator
{
    /** @var string Table name for sequences */
    const TABLE_SEQUENCES = 'am_asset_sequences';
    /** @var string Table name for formats */
    const TABLE_FORMATS = 'am_asset_number_formats';

    /** @var string Default pattern when no active format configured */
    const DEFAULT_PATTERN = '{category}/{year}.{seq}';

    /** @var string Sample category id used for preview rendering */
    const SAMPLE_CATEGORY = '7910-003-0003';

    /**
     * Generate next asset number for the given category (FSN prefix e.g. 7910-003-0003).
     * Uses active format pattern and increments sequence for the resolved Buddhist year.
     * The sequence is partitioned by category + Buddhist year so back-dated entries
     * keep their own numbering stream.
     *
     * @param string $categoryId FSN prefix (asset_item_id / fsn_number)
     * @param string|int|null $dateOrYear
     *   - null: use current budget year (1 Oct boundary)
     *   - int / numeric 4-digit BE year (2400–2700): use as-is
     *   - date string: convert via AppHelper::YearBudget()
     * @return string Formatted asset number
     */
    public static function generate($categoryId, $dateOrYear = null)
    {
        $categoryId = trim((string) $categoryId);
        if ($categoryId === '') {
            return '';
        }

        $yearBe = self::resolveYearBe($dateOrYear);
        $pattern = self::getActivePattern();
        $nextSeq = self::nextSequence($categoryId, $yearBe);

        return self::renderPattern($pattern, [
            'category' => $categoryId,
            'year_be' => $yearBe,
            'seq' => $nextSeq,
        ]);
    }

    /**
     * Resolve input into a Buddhist year integer.
     */
    public static function resolveYearBe($dateOrYear)
    {
        if ($dateOrYear === null || $dateOrYear === '') {
            return (int) AppHelper::YearBudget();
        }
        if (is_numeric($dateOrYear)) {
            $n = (int) $dateOrYear;
            if ($n >= 2400 && $n <= 2700) {
                return $n;
            }
        }
        return (int) AppHelper::YearBudget((string) $dateOrYear);
    }

    /**
     * Render an asset-number pattern against a context without touching the database.
     * Useful for previews in setting UIs.
     *
     * @param string $pattern Pattern containing tokens like {category}, {year:4}, {seq:0}
     * @param array $ctx ['category' => string, 'year_be' => int, 'seq' => int]
     * @return string
     */
    public static function renderPattern($pattern, array $ctx)
    {
        return preg_replace_callback(
            '/\{(\w+)((?::\w+)*)\}/',
            function ($m) use ($ctx) {
                $name = $m[1];
                $opts = [];
                if (isset($m[2]) && $m[2] !== '') {
                    $raw = explode(':', ltrim($m[2], ':'));
                    $opts = array_values(array_filter($raw, function ($s) {
                        return $s !== '';
                    }));
                }
                return self::expandToken($name, $opts, $ctx);
            },
            (string) $pattern
        );
    }

    /**
     * Expand a single token. Unknown tokens are returned verbatim so the pattern
     * stays self-describing if a config typo slips through.
     *
     * @param string $name Token name
     * @param array $opts Modifier list (e.g. ['ad', '4'] for {year:ad:4})
     * @param array $ctx Render context
     * @return string
     */
    public static function expandToken($name, array $opts, array $ctx)
    {
        switch ($name) {
            case 'category':
                return (string) ($ctx['category'] ?? '');

            case 'year':
                $calendar = 'be';
                $digits = 2;
                foreach ($opts as $opt) {
                    if ($opt === 'be' || $opt === 'ad') {
                        $calendar = $opt;
                    } elseif (ctype_digit($opt)) {
                        $digits = (int) $opt;
                    }
                }
                $year = (int) ($ctx['year_be'] ?? 0);
                if ($calendar === 'ad') {
                    $year -= 543;
                }
                $str = (string) $year;
                return $digits === 2 ? substr($str, -2) : $str;

            case 'seq':
                $pad = 2;
                if (isset($opts[0]) && ctype_digit($opts[0])) {
                    $pad = (int) $opts[0];
                }
                $str = (string) ($ctx['seq'] ?? '');
                if ($pad <= 0) {
                    return $str;
                }
                return str_pad($str, $pad, '0', STR_PAD_LEFT);

            default:
                $tail = empty($opts) ? '' : ':' . implode(':', $opts);
                return '{' . $name . $tail . '}';
        }
    }

    /**
     * Render a pattern with sample values for UI preview.
     *
     * @param string $pattern
     * @param int $seq Sample sequence number (default 1)
     * @param string|null $categoryId Sample category (default SAMPLE_CATEGORY)
     * @param int|null $yearBe Sample BE year (default current budget year)
     * @return string
     */
    public static function preview($pattern, $seq = 1, $categoryId = null, $yearBe = null)
    {
        return self::renderPattern($pattern, [
            'category' => $categoryId ?? self::SAMPLE_CATEGORY,
            'year_be' => $yearBe ?? (int) AppHelper::YearBudget(),
            'seq' => $seq,
        ]);
    }

    /**
     * Get the active format pattern. Default: {category}/{year}.{seq}
     */
    public static function getActivePattern()
    {
        $table = self::TABLE_FORMATS;
        $exists = Yii::$app->db->getSchema()->getTableSchema($table) !== null;
        if (!$exists) {
            return self::DEFAULT_PATTERN;
        }
        $row = Yii::$app->db->createCommand(
            "SELECT [[pattern]] FROM {{%{$table}}} WHERE [[is_active]] = 1 ORDER BY [[id]] ASC LIMIT 1"
        )->queryOne();
        return $row ? $row['pattern'] : self::DEFAULT_PATTERN;
    }

    /**
     * Get and increment sequence for category_id + year. Resets per year.
     *
     * @param string $categoryId FSN prefix
     * @param int $year Buddhist year
     * @return int Next sequence (1-based)
     */
    public static function nextSequence($categoryId, $year)
    {
        $table = self::TABLE_SEQUENCES;
        $schema = Yii::$app->db->getSchema()->getTableSchema($table);
        if ($schema === null) {
            return self::nextSequenceFallback($categoryId, $year);
        }

        $db = Yii::$app->db;
        $transaction = $db->beginTransaction();
        try {
            $row = $db->createCommand(
                'SELECT [[id]], [[current_sequence]] FROM {{%am_asset_sequences}} WHERE [[category_id]] = :cid AND [[year]] = :yr',
                [':cid' => $categoryId, ':yr' => $year]
            )->queryOne();

            if ($row) {
                $next = (int) $row['current_sequence'] + 1;
                $db->createCommand()->update(
                    '{{%am_asset_sequences}}',
                    ['current_sequence' => $next, 'updated_at' => date('Y-m-d H:i:s')],
                    ['id' => $row['id']]
                )->execute();
            } else {
                $next = self::resolveInitialSequence($categoryId, $year);
                $db->createCommand()->insert('{{%am_asset_sequences}}', [
                    'category_id' => $categoryId,
                    'year' => $year,
                    'current_sequence' => $next,
                    'updated_at' => date('Y-m-d H:i:s'),
                ])->execute();
            }
            $transaction->commit();
            return $next;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return self::nextSequenceFallback($categoryId, $year);
        }
    }

    /**
     * Resolve initial sequence: max from existing asset codes for this category/year, then +1.
     */
    private static function resolveInitialSequence($categoryId, $year)
    {
        $yearShort = substr((string) $year, -2);
        $like = $categoryId . '/' . $yearShort . '.%';
        $max = Yii::$app->db->createCommand(
            'SELECT MAX(CAST(SUBSTRING_INDEX([[code]], \'.\', -1) AS UNSIGNED)) FROM {{%asset}} WHERE [[code]] LIKE :like',
            [':like' => $like]
        )->queryScalar();
        return $max ? (int) $max + 1 : 1;
    }

    /**
     * Fallback when sequence table missing or lock failed: scan asset table (backward compatible).
     */
    private static function nextSequenceFallback($categoryId, $year)
    {
        return self::resolveInitialSequence($categoryId, $year);
    }
}
