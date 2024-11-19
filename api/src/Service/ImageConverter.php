<?php

declare(strict_types=1);

namespace App\Service;

use App\Enums\MediaVariantType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class ImageConverter implements ImageConverterInterface
{
    
    private ?string $newFilePath = null; 
    
    public function __construct(
        private ParameterBagInterface $params,
        private LoggerInterface $logger
    ) {
    }
    
    public function getNewFilePath(): ?string
    {
        return $this->newFilePath;
    }
    
    public function createConverteredImage(MediaVariantType $type, string $filePath): bool
    {
        if (!$this->params->has('app.media_variant.'. $type->value)) {
            $this->logger->error('No resize param for this type');
            return false;
        }
        $resizeData = $this->params->get('app.media_variant.'. $type->value);
        preg_match('/(\d+)x(\d+)/', $resizeData, $match = []);
        if (empty($match)) {
            $this->logger->error('Invalid resize param for this type');
            return false;
        }
        $sourcePathinfo = pathinfo($filePath);
        if (!$this->cropImage($filePath, $sourcePathinfo['dirname'] . $sourcePathinfo['filename'] . '.' . $type->value . '.' . $sourcePathinfo['extension'], $match[1], $match[2])) {
            $this->logger->error('Some error occured on image process');
            return false;
        }
        return true;
    }
    
    public function createConverteredImageWithWatermark(string $filePath): bool
    {
        $sourcePathinfo = pathinfo($filePath);
        $watermarkFilePath = $this->params->get('kernel.project_dir'). '/public/' . $this->params->get('app.media_variant.watermark');
        if (!is_file($watermarkFilePath)) {
            $this->logger->error('There is no watermark file');
            return false;
        }
        try {
            $stamp = imagecreatefrompng($watermarkFilePath);
            if ($sourcePathinfo['extension'] == 'png') {
                $img = imagecreatefrompng($filePath);
            } else {
                $img = imagecreatefromjpeg($filePath);
            }
            imagecopy($img, $stamp,0,0,0,0,imagesx($stamp), imagesy($stamp));
            return $this->createNewFile($img, $sourcePathinfo['dirname'] . $sourcePathinfo['filename'] . '_with_watermark.' . $sourcePathinfo['extension'], $sourcePathinfo['extension']);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }
    
    private function cropImage(string $filePath, string $newFileName, int $newWidth, int $newHeight): bool
    {
        $this->newFilePath = null;
        try {
            list($width, $height) = getimagesize($filePath);
            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            
            $thumb = imagecreatetruecolor($newWidth, $newHeight);
            $source = imagecreatefromjpeg($filePath);
            
            imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $this->newFilePath = $newFileName;
            return $this->createNewFile($thumb, $newFileName, $ext);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }
    
    private function createNewFile($image, string $newFileName, string $ext): bool
    {
        try {
            if ($ext == 'png') {
                return imagepng($image, $this->params->get('kernel.project_dir'). '/public/media/'.$newFileName);
            } else {
                return imagejpeg($image, $this->params->get('kernel.project_dir'). '/public/media/'.$newFileName);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }
}