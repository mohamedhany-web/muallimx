<?php

namespace App\Services;

use App\Models\LiveServer;
use Illuminate\Support\Facades\Crypt;
use phpseclib3\Net\SFTP;

final class ServerSshService
{
    private ?SFTP $sftp = null;

    public function connect(LiveServer $server): bool
    {
        $host = $this->getSshHost($server);
        $port = (int) $this->getSshPort($server);
        $username = $this->getSshUsername($server);
        $password = $this->getSshPassword($server);

        if ($host === '' || $username === '' || $password === '') {
            return false;
        }

        try {
            $this->sftp = new SFTP($host, $port, 15);
            if (!$this->sftp->login($username, $password)) {
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** @return array{dirs: array, files: array, error: string|null} */
    public function listDirectory(string $path): array
    {
        if ($this->sftp === null) {
            return ['dirs' => [], 'files' => [], 'error' => 'لا يوجد اتصال SSH.'];
        }

        $path = $this->normalizePath($path);
        if ($path === '') {
            $path = '/';
        }

        try {
            $raw = $this->sftp->rawlist($path);
            if ($raw === false || !is_array($raw)) {
                return ['dirs' => [], 'files' => [], 'error' => 'لا يمكن قراءة المسار أو الصلاحيات غير كافية.'];
            }

            $dirs = [];
            $files = [];
            $base = rtrim($path, '/') . '/';
            foreach ($raw as $name => $stat) {
                if ($name === '.' || $name === '..') {
                    continue;
                }
                $full = $base . $name;
                $isDir = isset($stat['type']) && (int) $stat['type'] === 2;
                $size = isset($stat['size']) ? (int) $stat['size'] : 0;
                if ($isDir) {
                    $dirs[] = ['name' => $name, 'path' => $full];
                } else {
                    $files[] = ['name' => $name, 'path' => $full, 'size' => $size];
                }
            }
            usort($dirs, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
            usort($files, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
            return ['dirs' => $dirs, 'files' => $files, 'error' => null];
        } catch (\Throwable $e) {
            return ['dirs' => [], 'files' => [], 'error' => $e->getMessage()];
        }
    }

    /** @return array{content: string|null, error: string|null, binary: bool} */
    public function readFile(string $path): array
    {
        if ($this->sftp === null) {
            return ['content' => null, 'error' => 'لا يوجد اتصال SSH.', 'binary' => false];
        }

        $path = $this->normalizePath($path);
        try {
            $content = $this->sftp->get($path);
            if ($content === false) {
                return ['content' => null, 'error' => 'لا يمكن قراءة الملف.', 'binary' => false];
            }
            $binary = !mb_check_encoding($content, 'UTF-8');
            if ($binary) {
                return ['content' => null, 'error' => 'الملف ثنائي ولا يمكن عرضه كنص.', 'binary' => true];
            }
            return ['content' => $content, 'error' => null, 'binary' => false];
        } catch (\Throwable $e) {
            return ['content' => null, 'error' => $e->getMessage(), 'binary' => false];
        }
    }

    public function getCurrentPath(): string
    {
        if ($this->sftp === null) {
            return '/';
        }
        try {
            $pwd = $this->sftp->pwd();
            return $pwd !== false ? $pwd : '/';
        } catch (\Throwable $e) {
            return '/';
        }
    }

    private function getSshHost(LiveServer $server): string
    {
        $config = $server->config ?? [];
        $host = trim((string) ($config['ssh_host'] ?? ''));
        if ($host !== '') {
            return $host;
        }
        return $server->ip_address ?: preg_replace('#^https?://#', '', $server->domain);
    }

    private function getSshPort(LiveServer $server): int
    {
        $config = $server->config ?? [];
        $port = (int) ($config['ssh_port'] ?? 22);
        return $port > 0 ? $port : 22;
    }

    private function getSshUsername(LiveServer $server): string
    {
        $config = $server->config ?? [];
        return trim((string) ($config['ssh_username'] ?? ''));
    }

    private function getSshPassword(LiveServer $server): string
    {
        $config = $server->config ?? [];
        $encrypted = $config['ssh_password_encrypted'] ?? '';
        if ($encrypted === '') {
            return '';
        }
        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function normalizePath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || $path === '.') {
            return '/';
        }
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);
        return $path ?: '/';
    }
}
