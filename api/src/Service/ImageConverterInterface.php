<?php

declare(strict_types=1);

namespace App\Service;

use App\Enums\MediaVariantType;

interface ImageConverterInterface
{
    public function getNewFilePath(): ?string;
    
    public function createConverteredImage(MediaVariantType $type, string $filePath): bool;
    
    public function createConverteredImageWithWatermark(string $filePath): bool;
    
}