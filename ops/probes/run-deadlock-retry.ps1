param([string]$Php = 'php')

$ErrorActionPreference = 'Stop'
$bookingProbe = Join-Path $PSScriptRoot 'booking-contention.php'
$deadlockProbe = Join-Path $PSScriptRoot 'deadlock-retry.php'
$one = (& $Php $bookingProbe setup | ConvertFrom-Json)
$two = (& $Php $bookingProbe setup | ConvertFrom-Json)
$barrier = Join-Path ([System.IO.Path]::GetTempPath()) ("mythos-deadlock-" + [guid]::NewGuid())
$jobs = @(
    Start-Job -ScriptBlock {
        param($PhpPath, $ProbePath, $First, $Second, $Barrier)
        & $PhpPath $ProbePath a $First $Second $Barrier
    } -ArgumentList $Php, $deadlockProbe, $one.practitioner, $two.practitioner, $barrier
    Start-Job -ScriptBlock {
        param($PhpPath, $ProbePath, $First, $Second, $Barrier)
        & $PhpPath $ProbePath b $First $Second $Barrier
    } -ArgumentList $Php, $deadlockProbe, $two.practitioner, $one.practitioner, $barrier
)
$results = @($jobs | Wait-Job | Receive-Job | ConvertFrom-Json)
$jobs | Remove-Job
Remove-Item -LiteralPath "$barrier.a","$barrier.b" -Force -ErrorAction SilentlyContinue
$retried = @($results | Where-Object { $_.attempts -gt 1 }).Count
[ordered]@{
    workers_completed = @($results | Where-Object completed).Count
    workers_retried = $retried
    accepted = ($results.Count -eq 2 -and $retried -ge 1)
    results = $results
} | ConvertTo-Json -Depth 4
if ($results.Count -ne 2 -or $retried -lt 1) { exit 1 }
