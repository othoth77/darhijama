<?php

use Applications\DarHijama\Application\Services\DarHijamaOperations;
use Applications\DarHijama\Domain\Patient;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mythos\Core\Identity\Models\User;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$user = User::query()->create([
    'name' => 'Media Restore Probe',
    'email' => 'media-'.Str::uuid().'@example.test',
    'password' => Str::random(48),
]);
$patient = Patient::query()->create([
    'public_id' => (string) Str::uuid(),
    'reference' => 'MEDIA-'.Str::upper(Str::random(8)),
    'first_name' => 'Media',
    'last_name' => 'Probe',
    'active' => true,
    'created_by' => $user->getKey(),
    'updated_by' => $user->getKey(),
]);
$source = tempnam(sys_get_temp_dir(), 'mythos-media-');
file_put_contents($source, random_bytes(4096));
$upload = new UploadedFile($source, 'private-probe.bin', 'application/octet-stream', null, true);
app(DarHijamaOperations::class)->attachPatientMedia($patient, $upload);
$media = $patient->media()->firstOrFail();
$disk = Storage::disk((string) $media->disk);
$absolute = $disk->path((string) $media->path);
$backup = $absolute.'.backup';
copy($absolute, $backup);
$before = hash_file('sha256', $absolute);
$disk->delete((string) $media->path);
copy($backup, $absolute);
$after = hash_file('sha256', $absolute);
$orphan = 'dar-hijama/patients/orphan-probe-'.Str::uuid().'.bin';
$disk->put($orphan, 'orphan');
$kernel->call('dar-hijama:media-reconcile', ['--disk' => (string) $media->disk]);
$reconciliation = json_decode($kernel->output(), true, flags: JSON_THROW_ON_ERROR);

echo json_encode([
    'disk' => $media->disk,
    'private_disk' => $media->disk === 'local',
    'restored' => $disk->exists((string) $media->path),
    'checksum_match' => hash_equals($before, $after),
    'database_file_consistent' => $media->mediable()->is($patient),
    'orphan_detected' => in_array($orphan, $reconciliation['orphans'], true),
    'missing_count' => count($reconciliation['missing']),
], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

$disk->delete([(string) $media->path, $orphan]);
@unlink($backup);
$media->delete();
$patient->forceDelete();
$user->delete();
