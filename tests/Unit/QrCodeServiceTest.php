<?php

namespace Tests\Unit;

use App\Support\QrCode\QrCodeService;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Modules\Media\Services\MediaService;
use Modules\Media\Services\StoredMediaFile;
use RuntimeException;
use Tests\TestCase;

class QrCodeServiceTest extends TestCase
{
    public function test_generation_is_idempotent_while_the_cached_file_exists(): void
    {
        Storage::fake('public');
        $service = app(QrCodeService::class);
        $path = 'qrcodes/TOKEN.png';

        $service->getOrCreate('https://example.test/first', $path, 'public');
        $firstContents = Storage::disk('public')->get($path);

        $service->getOrCreate('https://example.test/second', $path, 'public');

        $this->assertSame($firstContents, Storage::disk('public')->get($path));
        $this->assertSame([$path], Storage::disk('public')->allFiles('qrcodes'));
    }

    public function test_regeneration_replaces_the_file_and_cleans_temporary_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('qrcodes/TOKEN.png', 'corrupted');

        app(QrCodeService::class)->regenerate(
            'https://example.test/regenerated',
            'qrcodes/TOKEN.png',
            'public',
        );

        $contents = Storage::disk('public')->get('qrcodes/TOKEN.png');

        $this->assertNotSame('corrupted', $contents);
        $this->assertStringStartsWith("\x89PNG", $contents);
        $this->assertSame(
            ['qrcodes/TOKEN.png'],
            Storage::disk('public')->allFiles('qrcodes'),
        );
    }

    public function test_it_generates_a_missing_file_on_an_s3_compatible_disk(): void
    {
        Storage::fake('s3');

        $stored = app(QrCodeService::class)->getOrCreate(
            'https://example.test/s3',
            'qrcodes/TOKEN.png',
            's3',
        );

        $this->assertSame('s3', $stored->disk);
        $this->assertSame('qrcodes/TOKEN.png', $stored->path);
        Storage::disk('s3')->assertExists($stored->path);
    }

    public function test_storage_failure_is_propagated_without_promotion(): void
    {
        $mediaService = Mockery::mock(MediaService::class);
        $mediaService->shouldReceive('exists')
            ->once()
            ->with('qrcodes/TOKEN.png', 'public')
            ->andReturnFalse();
        $mediaService->shouldReceive('storeContents')
            ->once()
            ->andThrow(new RuntimeException('storage failed'));

        $service = new QrCodeService($mediaService);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('storage failed');

        $service->getOrCreate(
            'https://example.test/failure',
            'qrcodes/TOKEN.png',
            'public',
        );
    }

    public function test_failed_promotion_restores_the_previous_cached_file(): void
    {
        $mediaService = Mockery::mock(MediaService::class);
        $temporary = new StoredMediaFile(
            disk: 'public',
            path: 'qrcodes/TEMP.png',
            originalName: 'TEMP.png',
            mimeType: 'image/png',
            size: 100,
        );

        $mediaService->shouldReceive('storeContents')->once()->andReturn($temporary);
        $mediaService->shouldReceive('exists')
            ->once()->with('qrcodes/TOKEN.png', 'public')->andReturnTrue();
        $mediaService->shouldReceive('delete')
            ->once()->with('qrcodes/TOKEN.png.backup', 'public')->andReturnTrue();
        $mediaService->shouldReceive('move')
            ->once()->with('qrcodes/TOKEN.png', 'qrcodes/TOKEN.png.backup', 'public')->andReturnTrue();
        $mediaService->shouldReceive('move')
            ->once()->with('qrcodes/TEMP.png', 'qrcodes/TOKEN.png', 'public')->andReturnFalse();
        $mediaService->shouldReceive('delete')
            ->once()->with('qrcodes/TEMP.png', 'public')->andReturnTrue();
        $mediaService->shouldReceive('exists')
            ->once()->with('qrcodes/TOKEN.png.backup', 'public')->andReturnTrue();
        $mediaService->shouldReceive('delete')
            ->once()->with('qrcodes/TOKEN.png', 'public')->andReturnTrue();
        $mediaService->shouldReceive('move')
            ->once()->with('qrcodes/TOKEN.png.backup', 'qrcodes/TOKEN.png', 'public')->andReturnTrue();

        $service = new QrCodeService($mediaService);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Impossible de promouvoir le nouveau QR Code.');

        $service->regenerate(
            'https://example.test/failure',
            'qrcodes/TOKEN.png',
            'public',
        );
    }
}
