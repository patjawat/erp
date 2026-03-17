<?php

namespace app\modules\hr\helpers;

/**
 * Resolution-independent coordinate mapping for PDF templates (e.g. รายงานขอไปราชการ).
 *
 * UI coordinates are stored as normalized values 0.00–1.00 (relative to A4 container).
 * This helper converts between UI relative coordinates and PDF units (mm or pt).
 *
 * A4 paper: 210 × 297 mm, aspect ratio 1 : 1.414
 * A4 in points: 595.276 × 841.890 pt (72 pt/inch, 25.4 mm/inch)
 */
class PdfCoordinateHelper
{
    /** A4 width in mm */
    public const A4_WIDTH_MM = 210.0;

    /** A4 height in mm */
    public const A4_HEIGHT_MM = 297.0;

    /** A4 width in points (72 pt per inch) */
    public const A4_WIDTH_PT = 595.276;

    /** A4 height in points */
    public const A4_HEIGHT_PT = 841.890;

    /**
     * Convert pixel position inside a container to normalized coordinates (0–1).
     * Use when saving from UI: PositionX = CurrentX / CanvasWidth.
     *
     * @param float $xPx X position in pixels (e.g. left offset within container)
     * @param float $yPx Y position in pixels (e.g. top offset within container)
     * @param float $containerWidthPx Container width in pixels
     * @param float $containerHeightPx Container height in pixels
     * @return array{x: float, y: float} Clamped to [0, 1]
     */
    public static function normalizeToRelative(
        float $xPx,
        float $yPx,
        float $containerWidthPx,
        float $containerHeightPx
    ): array {
        if ($containerWidthPx <= 0 || $containerHeightPx <= 0) {
            return ['x' => 0.0, 'y' => 0.0];
        }
        $x = $xPx / $containerWidthPx;
        $y = $yPx / $containerHeightPx;
        return [
            'x' => self::clamp01($x),
            'y' => self::clamp01($y),
        ];
    }

    /**
     * Convert normalized coordinates (0–1) to PDF position in millimetres.
     * Use when generating PDF: PDF_X_mm = normX × A4_WIDTH_MM.
     *
     * @param float $normX Normalized X (0–1)
     * @param float $normY Normalized Y (0–1)
     * @param float $paperWidthMm Paper width in mm (default A4)
     * @param float $paperHeightMm Paper height in mm (default A4)
     * @return array{x: float, y: float} Position in mm
     */
    public static function relativeToPdfMm(
        float $normX,
        float $normY,
        float $paperWidthMm = self::A4_WIDTH_MM,
        float $paperHeightMm = self::A4_HEIGHT_MM
    ): array {
        return [
            'x' => self::clamp01($normX) * $paperWidthMm,
            'y' => self::clamp01($normY) * $paperHeightMm,
        ];
    }

    /**
     * Convert normalized coordinates (0–1) to PDF position in points.
     *
     * @param float $normX Normalized X (0–1)
     * @param float $normY Normalized Y (0–1)
     * @param float $paperWidthPt Paper width in pt (default A4)
     * @param float $paperHeightPt Paper height in pt (default A4)
     * @return array{x: float, y: float} Position in pt
     */
    public static function relativeToPdfPt(
        float $normX,
        float $normY,
        float $paperWidthPt = self::A4_WIDTH_PT,
        float $paperHeightPt = self::A4_HEIGHT_PT
    ): array {
        return [
            'x' => self::clamp01($normX) * $paperWidthPt,
            'y' => self::clamp01($normY) * $paperHeightPt,
        ];
    }

    /**
     * Accept stored value that may be either normalized (0–1) or legacy mm, and return mm.
     * For backward compatibility: if both x and y are <= 1, treat as normalized; else treat as mm.
     *
     * @param float $x Stored X (0–1 normalized or mm)
     * @param float $y Stored Y (0–1 normalized or mm)
     * @param float $paperWidthMm Paper width in mm
     * @param float $paperHeightMm Paper height in mm
     * @return array{x: float, y: float} Position in mm for FPDF/FPDI SetXY
     */
    public static function normalizedOrMmToMm(
        float $x,
        float $y,
        float $paperWidthMm = self::A4_WIDTH_MM,
        float $paperHeightMm = self::A4_HEIGHT_MM
    ): array {
        if ($x <= 1.0 && $y <= 1.0) {
            return self::relativeToPdfMm($x, $y, $paperWidthMm, $paperHeightMm);
        }
        return ['x' => $x, 'y' => $y];
    }

    /**
     * Clamp a value to [0, 1] for normalized coordinates.
     */
    public static function clamp01(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }

    /**
     * Validate and normalize a single coordinate for storage (0.0000–1.0000).
     * Production standard: never store px; only decimal ratio.
     *
     * @param float $value Raw value from request (ratio 0–1)
     * @return float Value in [0, 1] with 4 decimal places
     */
    public static function validateAndNormalizeCoordinate(float $value): float
    {
        return round(self::clamp01($value), 4);
    }
}
