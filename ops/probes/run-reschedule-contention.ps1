param([string]$Php = 'php')

$ErrorActionPreference = 'Stop'
$probe = Join-Path $PSScriptRoot 'booking-contention.php'
$fixture = (& $Php $probe setup | ConvertFrom-Json)
$target = [DateTimeOffset]::Parse($fixture.target)
$sourceA = $target.AddHours(1).ToString('o')
$sourceB = $target.AddHours(2).ToString('o')
$destination = $target.AddHours(5).ToString('o')
$a = (& $Php $probe book $fixture.user $fixture.patient $fixture.practitioner $sourceA) -replace '^CREATED:', ''
$b = (& $Php $probe book $fixture.user $fixture.patient $fixture.practitioner $sourceB) -replace '^CREATED:', ''
& $Php $probe confirm $fixture.user $a | Out-Null
& $Php $probe confirm $fixture.user $b | Out-Null
$jobs = @(
    Start-Job -ScriptBlock {
        param($PhpPath, $ProbePath, $User, $Appointment, $Target)
        & $PhpPath $ProbePath reschedule $User $Appointment $Target
    } -ArgumentList $Php, $probe, $fixture.user, $a, $destination
    Start-Job -ScriptBlock {
        param($PhpPath, $ProbePath, $User, $Appointment, $Target)
        & $PhpPath $ProbePath reschedule $User $Appointment $Target
    } -ArgumentList $Php, $probe, $fixture.user, $b, $destination
)
$results = $jobs | Wait-Job | Receive-Job
$jobs | Remove-Job
$success = @($results | Where-Object { $_ -like 'RESCHEDULED:*' }).Count
$conflicts = @($results | Where-Object { $_ -eq 'CONFLICT' }).Count
$payload = [ordered]@{
    attempts = 2
    rescheduled = $success
    conflicts = $conflicts
    accepted = ($success -eq 1 -and $conflicts -eq 1)
}
$payload | ConvertTo-Json
if (-not $payload.accepted) { exit 1 }
