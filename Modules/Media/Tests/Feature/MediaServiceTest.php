<?php

namespace Modules\Media\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Media\Services\MediaService;
use Modules\Templates\Models\Template;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    use RefreshDatabase;

    private MediaService $mediaService;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config()->set('media.disk', 'public');

        $this->mediaService = app(MediaService::class);
    }

    public function test_it_validates_names_and_stores_an_uploaded_image(): void
    {
        $file = UploadedFile::fake()->image('Mon mariage.png', 800, 600);

        $stored = $this->mediaService->upload(
            file: $file,
            directory: 'templates/previews',
            profile: 'image',
        );

        $this->assertSame('public', $stored->disk);
        $this->assertMatchesRegularExpression(
            '#^templates/previews/[0-9A-Z]{26}\.png$#',
            $stored->path,
        );
        $this->assertSame('Mon mariage.png', $stored->originalName);
        $this->assertSame('image/png', $stored->mimeType);
        Storage::disk('public')->assertExists($stored->path);
    }

    public function test_it_rejects_a_file_that_does_not_match_the_validation_profile(): void
    {
        $this->expectException(ValidationException::class);

        $this->mediaService->upload(
            file: UploadedFile::fake()->create('document.txt', 10, 'text/plain'),
            directory: 'templates/previews',
            profile: 'image',
        );
    }

    public function test_it_supports_an_s3_compatible_disk(): void
    {
        Storage::fake('s3');

        $stored = $this->mediaService->upload(
            file: UploadedFile::fake()->image('preview.jpg'),
            directory: 'templates/previews',
            profile: 'image',
            disk: 's3',
        );

        $this->assertSame('s3', $stored->disk);
        Storage::disk('s3')->assertExists($stored->path);
        $this->assertTrue($this->mediaService->delete($stored->path, 's3'));
        Storage::disk('s3')->assertMissing($stored->path);
    }

    public function test_it_stores_generated_contents_with_a_backward_compatible_path(): void
    {
        $stored = $this->mediaService->storeContents(
            contents: 'generated-qr',
            directory: 'qrcodes',
            extension: 'png',
            disk: 'public',
            filename: 'PUBLICTOKEN.png',
        );

        $this->assertSame('qrcodes/PUBLICTOKEN.png', $stored->path);
        $this->assertSame(
            Storage::disk('public')->url($stored->path),
            $this->mediaService->url($stored->path, 'public'),
        );
        Storage::disk('public')->assertExists($stored->path);
    }

    public function test_it_uploads_and_attaches_media_metadata_atomically(): void
    {
        $template = Template::factory()->create();

        $media = $this->mediaService->uploadFor(
            mediable: $template,
            file: UploadedFile::fake()->image('preview.jpg'),
            type: 'image',
            directory: 'templates/previews',
            profile: 'image',
        );

        $this->assertSame('template', $media->mediable_type);
        $this->assertSame($template->id, $media->mediable_id);
        $this->assertSame('preview.jpg', $media->original_name);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_metadata_cleanup_can_preserve_files_for_backward_compatibility(): void
    {
        $template = Template::factory()->create();
        $media = $this->mediaService->uploadFor(
            mediable: $template,
            file: UploadedFile::fake()->image('preview.jpg'),
            type: 'image',
            directory: 'templates/previews',
            profile: 'image',
        );

        $this->mediaService->deleteFor($template, deleteFiles: false);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_it_deletes_a_media_record_and_its_physical_file(): void
    {
        $template = Template::factory()->create();
        $media = $this->mediaService->uploadFor(
            mediable: $template,
            file: UploadedFile::fake()->image('preview.jpg'),
            type: 'image',
            directory: 'templates/previews',
            profile: 'image',
        );

        $this->assertTrue($this->mediaService->deleteMedia($media));

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        Storage::disk('public')->assertMissing($media->path);
    }
}
