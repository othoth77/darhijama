<?php

namespace Tests\Unit;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class MythosCoreBoundaryTest extends TestCase
{
    public function test_core_production_code_never_depends_on_application_namespaces(): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(base_path('Mythos/Core')),
        );

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $path = str_replace('\\', '/', $file->getPathname());

            if (str_contains($path, '/Tests/')) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            $this->assertStringNotContainsString('use App\\', $contents, $path);
            $this->assertStringNotContainsString('use Modules\\', $contents, $path);
            $this->assertStringNotContainsString('use Applications\\', $contents, $path);
        }
    }

    public function test_application_services_use_core_contracts_for_infrastructure(): void
    {
        $files = [
            base_path('Modules/Invitations/Services/InvitationQrCodeService.php'),
            base_path('Modules/Invitations/Services/InvitationPublicLinkService.php'),
            base_path('Modules/Templates/Services/EloquentTemplateCatalog.php'),
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($file);

            $this->assertStringContainsString('Mythos\\Core\\', $contents);
            $this->assertStringContainsString('\\Contracts\\', $contents);
        }
    }

    public function test_application_namespaces_are_isolated_from_framework_composition_and_each_other(): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(base_path('Applications')),
        );

        foreach ($iterator as $file) {
            if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $path = str_replace('\\', '/', $file->getPathname());

            if (str_contains($path, '/tests/')) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            $application = explode('/', str_replace(
                str_replace('\\', '/', base_path('Applications')).'/',
                '',
                $path,
            ))[0];

            $this->assertStringNotContainsString('use App\\', $contents, $path);
            $this->assertStringNotContainsString('use Modules\\', $contents, $path);

            preg_match_all('/use Applications\\\\([^\\\\;]+)\\\\/', $contents, $matches);

            foreach ($matches[1] as $dependency) {
                $this->assertSame($application, $dependency, $path);
            }
        }
    }
}
