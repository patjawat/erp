<?php

namespace app\modules\jd\components;

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

/**
 * Rich text used in JD document blocks. Existing plain-text records remain
 * readable while new values can preserve basic Word-like formatting.
 */
class RichText
{
    public const ALLOWED_TAGS = 'p,br,ul,ol,li,strong,em,b,i,u';
    private const HTML_PROBE = '/<(?:p|br|ul|ol|li|strong|em|b|i|u)\b[^>]*>/i';

    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $clean = HtmlPurifier::process($html, [
            'HTML.Allowed' => self::ALLOWED_TAGS,
            'AutoFormat.RemoveEmpty' => true,
            'AutoFormat.RemoveEmpty.RemoveNbsp' => true,
        ]);

        return trim(strip_tags((string) $clean)) === '' ? '' : trim((string) $clean);
    }

    public static function render(?string $value): string
    {
        $value = (string) $value;
        if (trim($value) === '') {
            return '';
        }
        if (preg_match(self::HTML_PROBE, $value)) {
            return HtmlPurifier::process($value, [
                'HTML.Allowed' => self::ALLOWED_TAGS,
                'AutoFormat.RemoveEmpty' => true,
            ]);
        }
        return nl2br(Html::encode($value));
    }
}
