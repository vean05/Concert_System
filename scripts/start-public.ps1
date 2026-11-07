# Opens the local site to the internet through a temporary Cloudflare tunnel.
# Started by start-public.bat. Closing the window takes the site offline.

$ErrorActionPreference = 'Stop'
$project = Split-Path -Parent $PSScriptRoot
$port = 8010
$logs = Join-Path $project 'storage\logs'
$serverLog = Join-Path $logs 'public-server.log'
$serverErrors = Join-Path $logs 'public-server-errors.log'
$tunnelLog = Join-Path $logs 'public-tunnel.log'
$tunnelOutput = Join-Path $logs 'public-tunnel-output.log'

function Test-Port([int] $number) {
    $client = New-Object System.Net.Sockets.TcpClient
    try {
        $wait = $client.BeginConnect('127.0.0.1', $number, $null, $null)
        return $wait.AsyncWaitHandle.WaitOne(600) -and $client.Connected
    } catch {
        return $false
    } finally {
        $client.Close()
    }
}

function Find-Cloudflared {
    $found = Get-Command cloudflared -ErrorAction SilentlyContinue
    if ($found) {
        return $found.Source
    }

    $places = @(
        "$env:LOCALAPPDATA\Microsoft\WinGet\Links\cloudflared.exe",
        "$env:ProgramFiles\cloudflared\cloudflared.exe",
        "${env:ProgramFiles(x86)}\cloudflared\cloudflared.exe"
    )
    $places += Get-ChildItem "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\Cloudflare.cloudflared*\*.exe" -ErrorAction SilentlyContinue |
        ForEach-Object { $_.FullName }

    return $places | Where-Object { $_ -and (Test-Path $_) } | Select-Object -First 1
}

$server = $null
$tunnel = $null

try {
    Write-Host ''
    Write-Host 'ConcertHub public demo' -ForegroundColor White
    Write-Host '----------------------'

    $cloudflared = Find-Cloudflared
    if (-not $cloudflared) {
        Write-Host 'Installing cloudflared (first run only)...'
        winget install --id Cloudflare.cloudflared --exact --silent --accept-source-agreements --accept-package-agreements | Out-Host
        $cloudflared = Find-Cloudflared
        if (-not $cloudflared) {
            throw 'cloudflared could not be installed. Run: winget install Cloudflare.cloudflared'
        }
    }

    if (-not (Test-Port 3306)) {
        Write-Host 'Starting WAMP. Accept the Windows prompt if one appears...'
        if (Test-Path 'C:\wamp64\wampmanager.exe') {
            Start-Process 'C:\wamp64\wampmanager.exe'
        }
        $deadline = (Get-Date).AddSeconds(90)
        while (-not (Test-Port 3306)) {
            if ((Get-Date) -gt $deadline) {
                throw 'MySQL did not start. Open WAMP, wait for its icon to turn green, and run this again.'
            }
            Start-Sleep -Seconds 2
        }
    }

    if (Test-Port $port) {
        throw "Port $port is already in use. Close the other ConcertHub window first."
    }

    # Real environment variables win over .env, so normal local use keeps its own settings.
    $env:APP_ENV = 'production'
    $env:APP_DEBUG = 'false'
    $env:PUBLIC_DEMO = 'true'

    Write-Host 'Starting the website...'
    $server = Start-Process -FilePath 'php' -PassThru -NoNewWindow -WorkingDirectory $project `
        -ArgumentList '-d', 'memory_limit=512M', '-S', "127.0.0.1:$port", '-t', 'public', 'server.php' `
        -RedirectStandardOutput $serverLog -RedirectStandardError $serverErrors

    $deadline = (Get-Date).AddSeconds(20)
    while (-not (Test-Port $port)) {
        if ((Get-Date) -gt $deadline) {
            throw "The website did not start. See $serverErrors"
        }
        Start-Sleep -Milliseconds 400
    }

    Write-Host 'Opening the tunnel...'
    Remove-Item $tunnelLog -ErrorAction SilentlyContinue
    $tunnel = Start-Process -FilePath $cloudflared -PassThru -NoNewWindow `
        -ArgumentList 'tunnel', '--no-autoupdate', '--url', "http://127.0.0.1:$port" `
        -RedirectStandardOutput $tunnelOutput -RedirectStandardError $tunnelLog

    $url = $null
    $deadline = (Get-Date).AddSeconds(60)
    while (-not $url) {
        if ((Get-Date) -gt $deadline -or $tunnel.HasExited) {
            throw "The tunnel did not open. See $tunnelLog"
        }
        Start-Sleep -Milliseconds 500
        $text = Get-Content $tunnelLog -Raw -ErrorAction SilentlyContinue
        if ($text -match 'https://[a-z0-9-]+\.trycloudflare\.com') {
            $url = $Matches[0]
        }
    }

    Set-Clipboard -Value $url

    Write-Host ''
    Write-Host 'Your site is live. Send this link to your friends:' -ForegroundColor Green
    Write-Host ''
    Write-Host "    $url" -ForegroundColor Yellow
    Write-Host ''
    Write-Host 'The link is already copied. It changes every time you start this.'
    Write-Host 'Keep this window open and your computer awake while people visit.'
    Write-Host ''
    Start-Process $url

    Read-Host 'Press Enter to take the site offline and close everything'
} catch {
    Write-Host ''
    Write-Host $_.Exception.Message -ForegroundColor Red
    Read-Host 'Press Enter to close'
} finally {
    foreach ($process in @($tunnel, $server)) {
        if ($process -and -not $process.HasExited) {
            Stop-Process -Id $process.Id -Force -ErrorAction SilentlyContinue
        }
    }
    Write-Host 'The site is offline.'

    if (Get-Process wampmanager, httpd, mysqld -ErrorAction SilentlyContinue) {
        Write-Host 'Closing WAMP. Accept the Windows prompt...'
        try {
            Start-Process powershell -Verb RunAs -Wait -WindowStyle Hidden `
                -ArgumentList '-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', "`"$PSScriptRoot\stop-wamp.ps1`""
        } catch {
            Write-Host 'WAMP was left running because the Windows prompt was declined.' -ForegroundColor Yellow
            Read-Host 'Press Enter to close'
        }
    }
}
