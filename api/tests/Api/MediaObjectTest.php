<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\MediaObject;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaObjectTest extends ApiTestCase
{
    public function testCreateAMediaObject(): void
    {
        $file = new UploadedFile(__DIR__ . '/../../fixtures/image.jpg', 'image.jpg');
        $client = self::createClient();
        
        $client->request('POST', '/media_objects', [
            'headers' => [
                'Content-Type' => 'multipart/form-data'
            ],
            'extra' => [
                'parameters' => [],
                'files' => [
                    'file' => $file,
                ],
            ]
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertMatchesResourceItemJsonSchema(MediaObject::class);
        $this->assertJsonContains([
            '@context' => '/contexts/MediaObject',
            '@type' => 'MediaObject',
            '@type' => 'https://schema.org/MediaObject'
        ]);
    }
    
    public function testListOfMediaObjects(): void
    {
        $client = self::createClient();
        $response = $client->request('GET', '/media_objects');
        var_dump($response->getContent());
        $this->assertResponseIsSuccessful();
    }
}