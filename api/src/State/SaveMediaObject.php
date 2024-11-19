<?php

declare(strict_types=1);

namespace App\State;

use App\Entity\MediaObject;
use App\Entity\MediaVariants;
use App\Enums\MediaVariantType;
use App\Service\ImageConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @implements ProcessorInterface<MediaObject, MediaObject|void>
 */
final class SaveMediaObject implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        #[Autowire(service: 'api_platform.doctrine.orm.state.remove_processor')]
        private ProcessorInterface $removeProcessor,
        private ImageConverterInterface $imageConverter,
        private EntityManagerInterface $em,
        private LoggerInterface $logger
    )
    {}
    
    /**
     * @return MediaObject|void
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($operation instanceof DeleteOperationInterface) {
            return $this->removeProcessor->process($data, $operation, $uriVariables, $context);
        }
        $data = $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        $sourceTypes = [];
        foreach (MediaVariantType::getClearTypes() as $type) {
            if ($this->imageConverter->createConverteredImage($type, $data->filePath)) {
                $newFilePath = $this->imageConverter->getNewFilePath();
                $sourceTypes[$type->value] = $newFilePath;
                
                $mediaVariants = (new MediaVariants())
                    ->setMedia($data)
                    ->setType($type)
                    ->setFilePath($newFilePath)
                ;
                $this->em->persist($mediaVariants);
            } else {
                $this->logger->error('Image converter error!');
            }
        }
        
        foreach (MediaVariantType::getWatermarkReferers() as $watermarkType => $referer) {
            if ($referer == null) {
                $sourceFilePath = $data->filePath;
            } else {
                $sourceFilePath = $sourceTypes[$referer->value];
            }
            if ($this->imageConverter->createConverteredImageWithWatermark($sourceFilePath)) {
                $mediaVariants = (new MediaVariants())
                    ->setMedia($data)
                    ->setType($watermarkType)
                    ->setFilePath($this->imageConverter->getNewFilePath())
                ;
                $this->em->persist($mediaVariants);
            } else {
                $this->logger->error('Image watermark error!');
            }
            
        }
        $this->em->flush();
        
        return $data;
    }
}