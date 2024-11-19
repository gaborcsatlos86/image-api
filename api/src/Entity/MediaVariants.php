<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Enums\MediaVariantType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'unique_media_type', columns: ['media_id', 'type'])]
#[ApiResource]
class MediaVariants
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[Groups(['media_variants:read'])]
    private ?int $id = null;
    
    #[ORM\ManyToOne(targetEntity: MediaObject::class, inversedBy: 'variants')]
    public ?MediaObject $media = null;
    
    #[ORM\Column(enumType: MediaVariantType::class, nullable: true)]
    #[Groups(['media_variants:read'])]
    public ?MediaVariantType $type = null;
    
    #[ApiProperty(writable: false)]
    #[ORM\Column(nullable: true)]
    #[Groups(['media_variants:read'])]
    public ?string $filePath = null;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setMedia(?MediaObject $media): self
    {
        $this->media = $media;
        
        return $this;
    }
    
    public function setType(?MediaVariantType $type): self
    {
        $this->type = $type;
        
        return $this;
    }
    
    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;
        
        return $this;
    }    
}
