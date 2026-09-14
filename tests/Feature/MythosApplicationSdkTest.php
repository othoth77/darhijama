<?php

namespace Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Mythos\Core\Applications\Capabilities\CoreCapabilityCatalog;
use Mythos\Core\Applications\Contracts\ApplicationRegistry;
use Mythos\Core\Applications\Exceptions\DuplicateApplicationSlug;
use Mythos\Core\Applications\Exceptions\InvalidApplicationManifest;
use Mythos\Core\Applications\Manifest\ManifestValidator;
use Tests\TestCase;

class MythosApplicationSdkTest extends TestCase
{
    private Filesystem $files;

    private string $generatedRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = app(Filesystem::class);
        $this->generatedRoot = base_path('Applications/PhaseSdkFixture');

        if ($this->files->isDirectory($this->generatedRoot)) {
            $this->files->deleteDirectory($this->generatedRoot);
        }
    }

    protected function tearDown(): void
    {
        if ($this->files->isDirectory($this->generatedRoot)) {
            $this->files->deleteDirectory($this->generatedRoot);
        }

        parent::tearDown();
    }

    public function test_notre_jour_is_registered_as_the_reference_application(): void
    {
        $registry = app(ApplicationRegistry::class);
        $manifest = $registry->find('notre-jour');

        $this->assertNotNull($manifest);
        $this->assertSame('Applications\\NotreJour\\Application', $manifest->applicationClass);
        $this->assertContains('media', $manifest->capabilities);
        $this->assertContains('invitations.manage', $manifest->permissions);
        $this->assertNotEmpty($registry->navigation('notre-jour'));
    }

    public function test_duplicate_slugs_and_invalid_manifests_fail_clearly(): void
    {
        $registry = app(ApplicationRegistry::class);

        $this->expectException(DuplicateApplicationSlug::class);
        $registry->registerManifest(base_path('Applications/NotreJour/mythos.json'));
    }

    public function test_unknown_manifest_keys_are_rejected(): void
    {
        $path = storage_path('framework/testing/invalid-mythos.json');
        $manifest = json_decode(
            file_get_contents(base_path('Applications/NotreJour/mythos.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $manifest['unexpected'] = true;
        $this->files->put($path, json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        try {
            $this->expectException(InvalidApplicationManifest::class);
            app(ManifestValidator::class)->validateFile($path);
        } finally {
            $this->files->delete($path);
        }
    }

    public function test_generator_creates_valid_isolated_and_loadable_application(): void
    {
        $this->artisan('mythos:make-application', ['name' => 'PhaseSdkFixture'])
            ->assertSuccessful();

        $manifestPath = "{$this->generatedRoot}/mythos.json";
        $manifestData = json_decode(
            $this->files->get($manifestPath),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $manifestData['core_capabilities'] = ['media', 'public-links'];
        $this->files->put(
            $manifestPath,
            json_encode($manifestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        );

        $manifest = app(ManifestValidator::class)->validateFile($manifestPath);
        $registered = app(ApplicationRegistry::class)->registerManifest($manifestPath);

        $this->assertSame('phase-sdk-fixture', $manifest->slug);
        $this->assertSame($manifest->slug, $registered->slug);
        $this->assertDirectoryExists("{$this->generatedRoot}/Domain");
        $this->assertFileExists("{$this->generatedRoot}/tests/Feature/ApplicationHealthTest.php");
        $migrationPaths = array_map(
            fn (string $path) => str_replace('\\', '/', $path),
            app('migrator')->paths(),
        );
        $this->assertContains(
            str_replace('\\', '/', "{$this->generatedRoot}/database/migrations"),
            $migrationPaths,
        );
        $this->assertSame(
            'PhaseSdkFixture',
            config('applications.phase-sdk-fixture.name'),
        );

        foreach ($this->phpFiles($this->generatedRoot) as $file) {
            $contents = $this->files->get($file);

            if (str_contains(str_replace('\\', '/', $file), '/tests/')) {
                continue;
            }

            $this->assertStringNotContainsString('use App\\', $contents);
            $this->assertStringNotContainsString('use Modules\\', $contents);
            $this->assertStringNotContainsString('NotreJour', $contents);
        }

        $catalog = app(CoreCapabilityCatalog::class);

        foreach ($manifest->capabilities as $capability) {
            $contract = $catalog->contract($capability);
            $this->assertNotNull($contract);
            $this->assertTrue(app()->bound($contract) || class_exists($contract));
        }

        $this->get(route('mythos.phase-sdk-fixture.health'))
            ->assertOk()
            ->assertJson([
                'application' => 'phase-sdk-fixture',
                'status' => 'ok',
            ]);
    }

    /** @return list<string> */
    private function phpFiles(string $root): array
    {
        return collect($this->files->allFiles($root))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->map(fn ($file) => $file->getPathname())
            ->values()
            ->all();
    }
}
