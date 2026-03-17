<?php

namespace app\modules\am\services;

use Yii;
use app\components\AppHelper;

/**
 * Generates asset numbers (FSN) with configurable format and per-category/year sequence.
 * Supports patterns: {category}/{year}.{seq}, {category}/{seq}/{year}, {category}/{year}-{seq}
 */
class AssetNumberGenerator
{
    /** @var string Table name for sequences */
    const TABLE_SEQUENCES = 'am_asset_sequences';
    /** @var string Table name for formats */
    const TABLE_FORMATS = 'am_asset_number_formats';

    /**
     * Generate next asset number for the given category (FSN prefix e.g. 7910-003-0003).
     * Uses active format pattern and increments sequence for current Buddhist year.
     *
     * @param string $categoryId FSN prefix (asset_item_id / fsn_number)
     * @param string|null $date Optional date for year (default: now)
     * @return string Formatted asset number e.g. 7910-003-0003/66.01
     */
    public static function generate($categoryId, $date = null)
    {
        $categoryId = trim((string) $categoryId);
        if ($categoryId === '') {
            return '';
        }

        $year = (int) AppHelper::YearBudget($date);
        $yearShort = substr((string) $year, -2);
        $pattern = self::getActivePattern();
        $nextSeq = self::nextSequence($categoryId, $year);
        $seqPadded = str_pad((string) $nextSeq, 2, '0', STR_PAD_LEFT);

        $number = str_replace(
            ['{category}', '{year}', '{seq}'],
            [$categoryId, $yearShort, $seqPadded],
            $pattern
        );

        return $number;
    }

    /**
     * Get the active format pattern. Default: {category}/{year}.{seq}
     */
    public static function getActivePattern()
    {
        $table = self::TABLE_FORMATS;
        $exists = Yii::$app->db->getSchema()->getTableSchema($table) !== null;
        if (!$exists) {
            return '{category}/{year}.{seq}';
        }
        $row = Yii::$app->db->createCommand(
            "SELECT [[pattern]] FROM {{%{$table}}} WHERE [[is_active]] = 1 ORDER BY [[id]] ASC LIMIT 1"
        )->queryOne();
        return $row ? $row['pattern'] : '{category}/{year}.{seq}';
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
