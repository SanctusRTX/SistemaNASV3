<?php

namespace App\Services;

class SystemStatsService
{
    public function getOverview(): array
    {
        $storagePath = storage_path('app/Almacenamiento');
        if (!is_dir($storagePath)) {
            @mkdir($storagePath, 0777, true);
        }

        $cpu = $this->getCpuStats();
        $memory = $this->getMemoryStats();
        $storage = $this->getStorageStats($storagePath);
        $uptime = $this->getUptime();

        $availableCount = collect([$cpu, $memory, $storage, $uptime])
            ->filter(fn ($m) => ($m['available'] ?? false))
            ->count();

        return [
            'success' => true,
            'host' => gethostname() ?: 'Servidor',
            'os' => $this->getOsLabel(),
            'php_version' => PHP_VERSION,
            'server_time' => now()->format('d/m/Y H:i:s'),
            'server_timestamp' => now()->timestamp,
            'cpu' => $cpu,
            'memory' => $memory,
            'uptime' => $uptime,
            'storage' => $storage,
            'status' => $availableCount >= 2 ? 'live' : ($availableCount >= 1 ? 'partial' : 'unavailable'),
        ];
    }

    private function getOsLabel(): string
    {
        $family = PHP_OS_FAMILY;
        $detail = php_uname('s') . ' ' . php_uname('r');

        return match ($family) {
            'Windows' => 'Windows',
            'Linux' => 'Linux',
            'Darwin' => 'macOS',
            default => trim($detail) ?: $family,
        };
    }

    private function getCpuStats(): array
    {
        if (PHP_OS_FAMILY === 'Linux') {
            return $this->getCpuStatsLinux();
        }

        if (PHP_OS_FAMILY === 'Windows') {
            return $this->getCpuStatsWindows();
        }

        return $this->unavailableMetric('CPU', 'Sistema no soportado');
    }

    private function getCpuStatsLinux(): array
    {
        $first = $this->readProcStat();
        if ($first === null) {
            return $this->unavailableMetric('CPU', 'No disponible');
        }

        usleep(400000);

        $second = $this->readProcStat();
        if ($second === null) {
            return $this->unavailableMetric('CPU', 'No disponible');
        }

        $idleDiff = $second['idle'] - $first['idle'];
        $totalDiff = $second['total'] - $first['total'];

        if ($totalDiff <= 0) {
            return $this->unavailableMetric('CPU', 'No disponible');
        }

        $percent = round((1 - ($idleDiff / $totalDiff)) * 100, 1);
        $cores = $this->readCpuCoresLinux();

        return [
            'available' => true,
            'percent' => $percent,
            'detail' => $cores > 0 ? $cores . ' núcleos' : 'Linux',
        ];
    }

    private function readProcStat(): ?array
    {
        $lines = @file('/proc/stat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return null;
        }

        foreach ($lines as $line) {
            if (!str_starts_with($line, 'cpu ')) {
                continue;
            }

            $parts = preg_split('/\s+/', trim($line));
            array_shift($parts);
            $values = array_map('intval', $parts);
            $idle = ($values[3] ?? 0) + ($values[4] ?? 0);
            $total = array_sum($values);

            return ['idle' => $idle, 'total' => $total];
        }

        return null;
    }

    private function readCpuCoresLinux(): int
    {
        $count = @file('/proc/cpuinfo');
        if (!$count) {
            return 0;
        }

        return substr_count(implode('', $count), 'processor');
    }

    private function getCpuStatsWindows(): array
    {
        $output = $this->runCommand('wmic cpu get loadpercentage /value 2>nul');
        if ($output !== null) {
            if (preg_match_all('/LoadPercentage=(\d+)/', $output, $matches) && !empty($matches[1])) {
                $values = array_map('intval', $matches[1]);
                $percent = round(array_sum($values) / count($values), 1);

                return [
                    'available' => true,
                    'percent' => $percent,
                    'detail' => count($values) . ' núcleos',
                ];
            }
        }

        $output = $this->runCommand('wmic path Win32_PerfFormattedData_PerfOS_Processor get Name,PercentProcessorTime /value 2>nul');
        if ($output !== null && preg_match('/Name=_Total[\s\S]*?PercentProcessorTime=(\d+)/', $output, $match)) {
            return [
                'available' => true,
                'percent' => (float) $match[1],
                'detail' => 'Windows',
            ];
        }

        return $this->unavailableMetric('CPU', 'Requiere permisos WMI');
    }

    private function getMemoryStats(): array
    {
        if (PHP_OS_FAMILY === 'Linux') {
            return $this->getMemoryStatsLinux();
        }

        if (PHP_OS_FAMILY === 'Windows') {
            return $this->getMemoryStatsWindows();
        }

        return $this->unavailableMetric('Memoria', 'Sistema no soportado');
    }

    private function getMemoryStatsLinux(): array
    {
        $info = @file_get_contents('/proc/meminfo');
        if (!$info) {
            return $this->unavailableMetric('Memoria', 'No disponible');
        }

        $data = [];
        foreach (explode("\n", $info) as $line) {
            if (preg_match('/^(\w+):\s+(\d+)/', $line, $m)) {
                $data[$m[1]] = (int) $m[2] * 1024;
            }
        }

        $total = $data['MemTotal'] ?? 0;
        $available = $data['MemAvailable'] ?? ($data['MemFree'] ?? 0);

        if ($total <= 0) {
            return $this->unavailableMetric('Memoria', 'No disponible');
        }

        $used = $total - $available;
        $percent = round(($used / $total) * 100, 1);

        return [
            'available' => true,
            'percent' => $percent,
            'detail' => $this->formatBytesShort($used) . ' / ' . $this->formatBytesShort($total),
        ];
    }

    private function getMemoryStatsWindows(): array
    {
        $output = $this->runCommand('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /value 2>nul');
        if ($output === null) {
            return $this->unavailableMetric('Memoria', 'No disponible');
        }

        $freeKb = null;
        $totalKb = null;

        if (preg_match('/FreePhysicalMemory=(\d+)/', $output, $m)) {
            $freeKb = (int) $m[1];
        }
        if (preg_match('/TotalVisibleMemorySize=(\d+)/', $output, $m)) {
            $totalKb = (int) $m[1];
        }

        if (!$freeKb || !$totalKb) {
            return $this->unavailableMetric('Memoria', 'No disponible');
        }

        $total = $totalKb * 1024;
        $free = $freeKb * 1024;
        $used = $total - $free;
        $percent = round(($used / $total) * 100, 1);

        return [
            'available' => true,
            'percent' => $percent,
            'detail' => $this->formatBytesShort($used) . ' / ' . $this->formatBytesShort($total),
        ];
    }

    private function getUptime(): array
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $raw = @file_get_contents('/proc/uptime');
            if ($raw && preg_match('/^([\d.]+)/', trim($raw), $m)) {
                $seconds = (int) floor((float) $m[1]);

                return [
                    'available' => true,
                    'seconds' => $seconds,
                    'human' => $this->formatDuration($seconds),
                ];
            }
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $output = $this->runCommand('wmic os get lastbootuptime /value 2>nul');
            if ($output && preg_match('/LastBootUpTime=(\d{14})/', $output, $m)) {
                $boot = \DateTime::createFromFormat('YmdHis', $m[1]);
                if ($boot) {
                    $seconds = max(0, time() - $boot->getTimestamp());

                    return [
                        'available' => true,
                        'seconds' => $seconds,
                        'human' => $this->formatDuration($seconds),
                    ];
                }
            }
        }

        return [
            'available' => false,
            'seconds' => null,
            'human' => 'N/D',
            'detail' => 'No disponible',
        ];
    }

    private function getStorageStats(string $path): array
    {
        $total = @disk_total_space($path);
        $free = @disk_free_space($path);

        if ($total === false || $free === false || $total <= 0) {
            return [
                'available' => false,
                'percent' => null,
                'label' => 'N/D',
                'detail' => 'No disponible',
                'path' => $path,
            ];
        }

        $used = $total - $free;
        $percent = round(($used / $total) * 100, 1);

        return [
            'available' => true,
            'percent' => $percent,
            'label' => $percent . '% usado',
            'detail' => $this->formatBytesShort($free) . ' libres de ' . $this->formatBytesShort($total),
            'used' => $this->formatBytesShort($used),
            'total' => $this->formatBytesShort($total),
            'free' => $this->formatBytesShort($free),
            'path' => $path,
        ];
    }

    private function unavailableMetric(string $name, string $detail): array
    {
        return [
            'available' => false,
            'percent' => null,
            'detail' => $detail,
        ];
    }

    private function runCommand(string $command): ?string
    {
        if (!$this->canRunShell()) {
            return null;
        }

        $result = @shell_exec($command);

        return $result !== null && trim($result) !== '' ? trim($result) : null;
    }

    private function canRunShell(): bool
    {
        if (!function_exists('shell_exec')) {
            return false;
        }

        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        return !in_array('shell_exec', $disabled, true);
    }

    private function formatBytesShort(int|float $bytes): string
    {
        $bytes = (float) $bytes;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 1) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return (int) $bytes . ' B';
    }

    private function formatDuration(int $seconds): string
    {
        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($days > 0) {
            return $days . 'd ' . $hours . 'h';
        }
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }

        return max(1, $minutes) . 'm';
    }
}
