# Closes WAMP: its tray program, Apache and both database services.
# Windows services can only be stopped as administrator, so start-public.ps1
# runs this one file elevated. The website and tunnel themselves never are.

$manager = 'C:\wamp64\wampmanager.exe'

if ((Test-Path $manager) -and (Get-Process wampmanager -ErrorAction SilentlyContinue)) {
    Start-Process $manager -ArgumentList '-quit', '-id={wampserver64}'
    Start-Sleep -Seconds 2
    Get-Process wampmanager -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
}

foreach ($name in 'wampapache64', 'wampmysqld64', 'wampmariadb64') {
    Stop-Service -Name $name -Force -ErrorAction SilentlyContinue
}
