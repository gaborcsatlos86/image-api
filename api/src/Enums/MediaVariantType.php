<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaVariantType: string
{
    case Thumb = 'thumb';
    case ThumbWithWatermark = 'thumb_with_watermark';
    case OriginalWithWatermark = 'original_with_watermark';
    case Square = 'square';
    case SquareWithWatermark = 'square_with_watermark';
    
    public static function getClearTypes(): array
    {
        return [
            self::Thumb,
            self::Square
        ];
    }
    
    public static function getWatermarkReferers(): array
    {
        return [
            self::ThumbWithWatermark->value => self::Thumb,
            self::OriginalWithWatermark->value => null,
            self::SquareWithWatermark->value => self::Square
        ];
    }
    
}
