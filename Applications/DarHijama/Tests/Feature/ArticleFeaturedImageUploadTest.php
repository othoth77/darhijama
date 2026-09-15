<?php

namespace Applications\DarHijama\Tests\Feature;

use Applications\DarHijama\Domain\Article;
use Applications\DarHijama\Filament\Resources\ArticleResource\Pages\EditArticle;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mythos\Core\Identity\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArticleFeaturedImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public static function imageProvider(): array
    {
        return [
            'webp' => ['media.webp'],
            'jpg' => ['small.jpg'],
        ];
    }

    /** @dataProvider imageProvider */
    public function test_admin_can_upload_and_save_a_featured_image(string $filename): void
    {
        Storage::fake('public');
        $this->artisan('dar-hijama:install')->assertSuccessful();
        $admin = User::factory()->create();
        Role::findOrCreate('admin');
        $admin->assignRole(['dar-hijama-admin', 'admin']);
        $article = Article::factory()->published()->create(['slug' => 'image-upload-article']);

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->fillForm(['featured_image' => UploadedFile::fake()->image($filename, 1280, 800)->size(34)])
            ->call('save')
            ->assertHasNoFormErrors();

        $path = $article->fresh()->featured_image;

        $this->assertNotEmpty($path);
        $this->assertStringStartsWith('dar-hijama/articles/', $path);
        Storage::disk('public')->assertExists($path);

        $this->get('http://darhijama.tn/articles/image-upload-article')
            ->assertOk()
            ->assertSee('storage/'.$path, false);
    }
}
