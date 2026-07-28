param(
    [int]$Attempts = 50,
    [string]$Php = 'php'
)

$ErrorActionPreference = 'Stop'
$probe = Join-Path $PSScriptRoot 'booking-contention.php'
$fixture = (& $Php $probe setup | ConvertFrom-Json)
$jobs = 1..$Attempts | ForEach-Object {
    Start-Job -ScriptBlock {
        param($PhpPath, $ProbePath, $User, $Patient, $Practitioner, $Target)
        & $PhpPath $ProbePath book $User $Patient $Practitioner $Target
    } -ArgumentList $Php, $probe, $fixture.user, $fixture.patient, $fixture.practitioner, $fixture.target
}
$results = $jobs | Wait-Job | Receive-Job
$jobs | Remove-Job
$created = @($results | Where-Object { $_ -like 'CREATED:*' }).Count
$conflicts = @($results | Where-Object { $_ -eq 'CONFLICT' }).Count
$errors = $Attempts - $created - $conflicts

$payload = [ordered]@{
    attempts = $Attempts
    created = $created
    conflicts = $conflicts
    errors = $errors
    accepted = ($created -eq 1 -and $conflicts -eq ($Attempts - 1) -and $errors -eq 0)
}
$payload | ConvertTo-Json
if (-not $payload.accepted) { exit 1 }
