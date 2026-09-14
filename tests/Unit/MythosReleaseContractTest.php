<?php

namespace Tests\Unit;

use Mythos\Core\Support\Api\ApiStatus;
use Mythos\Core\Support\Api\Deprecated;
use Mythos\Core\Support\Api\PublicApi;
use Mythos\Core\Support\Deprecation\DeprecationRegistry;
use ReflectionClass;
use Tests\TestCase;

class MythosReleaseContractTest extends TestCase
{
    public function test_release_version_is_consistent_across_artifacts(): void
    {
        $version = trim(file_get_contents(base_path('VERSION')));
        $manifest = json_decode(
            file_get_contents(base_path('release/MANIFEST.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $contracts = json_decode(
            file_get_contents(base_path('release/contracts-v1.0.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertSame('1.0.0', $version);
        $this->assertSame($version, $manifest['version']);
        $this->assertSame($version, $contracts['version']);
        $this->assertSame($version, config('mythos.core_version'));
        $this->assertStringContainsString('## [1.0.0]', file_get_contents(base_path('CHANGELOG.md')));
    }

    public function test_stable_contract_sources_match_the_v1_release_snapshot(): void
    {
        $snapshot = json_decode(
            file_get_contents(base_path('release/contracts-v1.0.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        foreach ($snapshot['contracts'] as $path => $expectedHash) {
            $this->assertFileExists(base_path($path));
            $this->assertSame($expectedHash, hash_file('sha256', base_path($path)), $path);
        }
    }

    public function test_every_snapshotted_contract_is_marked_as_stable_public_api(): void
    {
        $manifest = json_decode(
            file_get_contents(base_path('release/MANIFEST.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        foreach ($manifest['stable_contracts'] as $contract) {
            $reflection = new ReflectionClass($contract);
            $attributes = $reflection->getAttributes(PublicApi::class);

            $this->assertTrue($reflection->isInterface(), $contract);
            $this->assertCount(1, $attributes, $contract);
            $this->assertSame(ApiStatus::Stable, $attributes[0]->newInstance()->status);
            $this->assertSame('1.0.0', $attributes[0]->newInstance()->since);
        }
    }

    public function test_release_manifest_matches_composer_and_registered_application(): void
    {
        $release = json_decode(
            file_get_contents(base_path('release/MANIFEST.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $composer = json_decode(
            file_get_contents(base_path('composer.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $application = json_decode(
            file_get_contents(base_path('Applications/NotreJour/mythos.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        foreach ($release['composer_requirements'] as $package => $constraint) {
            $this->assertSame($constraint, $composer['require'][$package]);
        }

        $this->assertSame($application['slug'], $release['applications'][0]['slug']);
        $this->assertSame($application['version'], $release['applications'][0]['version']);
    }

    public function test_deprecation_metadata_and_generated_documentation_are_release_ready(): void
    {
        $metadata = new Deprecated(
            since: '1.1.0',
            replacement: 'Replacement::method()',
            removalVersion: '2.0.0',
            reason: 'Example',
        );

        $this->assertSame('1.1.0', $metadata->since);
        $this->assertSame('Replacement::method()', $metadata->replacement);
        $this->assertSame('2.0.0', $metadata->removalVersion);
        $this->assertSame(
            app(DeprecationRegistry::class)->markdown(),
            file_get_contents(base_path('docs/DEPRECATIONS.md')),
        );
    }
}
