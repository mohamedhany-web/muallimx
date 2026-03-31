<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Lecture;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TeamsAttendanceImportService
{
    public function importFromFile(Lecture $lecture, string $absolutePath): array
    {
        $rows = $this->readRows($absolutePath);
        if (count($rows) < 2) {
            return [
                'total' => 0,
                'processed' => 0,
                'matched' => 0,
                'unmatched' => 0,
                'errors' => ['الملف لا يحتوي بيانات حضور كافية.'],
            ];
        }

        $header = array_map(fn ($v) => trim((string) $v), $rows[0]);
        $idx = $this->detectColumns($header);

        $students = $lecture->course
            ? $lecture->course->activeStudents()->get(['users.id', 'users.name', 'users.email'])
            : collect();
        $studentsByEmail = $students->keyBy(fn ($u) => mb_strtolower(trim((string) $u->email)));
        $studentsByName = $students->keyBy(fn ($u) => $this->normalizeName((string) $u->name));

        $total = 0;
        $processed = 0;
        $matched = 0;
        $unmatched = 0;
        $errors = [];

        foreach (array_slice($rows, 1) as $rowNo => $row) {
            $total++;
            $name = $this->valueAt($row, $idx['name']);
            $email = mb_strtolower($this->valueAt($row, $idx['email']));
            $joinRaw = $this->valueAt($row, $idx['join']);
            $leftRaw = $this->valueAt($row, $idx['left']);
            $durationRaw = $this->valueAt($row, $idx['duration']);

            if ($name === '' && $email === '') {
                continue;
            }

            $student = null;
            if ($email !== '' && $studentsByEmail->has($email)) {
                $student = $studentsByEmail->get($email);
            } elseif ($name !== '') {
                $student = $studentsByName->get($this->normalizeName($name));
            }

            if (!$student instanceof User) {
                $unmatched++;
                continue;
            }

            try {
                $joinedAt = $this->parseDateTime($joinRaw);
                $leftAt = $this->parseDateTime($leftRaw);
                $minutes = $this->parseDurationMinutes($durationRaw);
                if ($minutes === null && $joinedAt && $leftAt && $leftAt->greaterThan($joinedAt)) {
                    $minutes = $leftAt->diffInMinutes($joinedAt);
                }
                $minutes = max(0, (int) ($minutes ?? 0));

                $totalMinutes = max(1, (int) ($lecture->duration_minutes ?? 1));
                $percentage = min(100, round(($minutes / $totalMinutes) * 100, 2));
                $status = $this->statusFromMinutes($minutes, $percentage);

                AttendanceRecord::updateOrCreate(
                    ['lecture_id' => $lecture->id, 'student_id' => $student->id],
                    [
                        'joined_at' => $joinedAt,
                        'left_at' => $leftAt,
                        'attendance_minutes' => $minutes,
                        'total_minutes' => $totalMinutes,
                        'attendance_percentage' => $percentage,
                        'status' => $status,
                        'source' => 'teams_file',
                        'teams_data' => [
                            'name' => $name,
                            'email' => $email,
                            'raw_duration' => $durationRaw,
                        ],
                        'teams_file_path' => null,
                    ]
                );

                $processed++;
                $matched++;
            } catch (\Throwable $e) {
                $errors[] = 'سطر ' . ($rowNo + 2) . ': ' . $e->getMessage();
            }
        }

        return compact('total', 'processed', 'matched', 'unmatched', 'errors');
    }

    private function readRows(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        return $sheet->toArray(null, true, true, false);
    }

    private function detectColumns(array $header): array
    {
        $normalized = array_map(fn ($h) => mb_strtolower(trim((string) $h)), $header);
        $find = function (array $keywords) use ($normalized): ?int {
            foreach ($normalized as $i => $h) {
                foreach ($keywords as $k) {
                    if ($h !== '' && str_contains($h, $k)) {
                        return $i;
                    }
                }
            }
            return null;
        };

        return [
            'name' => $find(['name', 'الاسم', 'full name', 'participant']),
            'email' => $find(['email', 'البريد', 'mail']),
            'join' => $find(['join', 'joined', 'انضم', 'وقت الانضمام']),
            'left' => $find(['leave', 'left', 'غادر', 'وقت المغادرة']),
            'duration' => $find(['duration', 'المدة', 'attendance', 'مجموع']),
        ];
    }

    private function valueAt(array $row, ?int $index): string
    {
        if ($index === null || !array_key_exists($index, $row)) {
            return '';
        }
        return trim((string) $row[$index]);
    }

    private function normalizeName(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        return $value;
    }

    private function parseDateTime(string $value): ?Carbon
    {
        if ($value === '') {
            return null;
        }
        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseDurationMinutes(string $value): ?int
    {
        if ($value === '') {
            return null;
        }
        $v = mb_strtolower(trim($value));

        // HH:MM[:SS]
        if (preg_match('/^(\d{1,3}):(\d{1,2})(?::(\d{1,2}))?$/', $v, $m)) {
            $h = (int) $m[1];
            $min = (int) $m[2];
            return ($h * 60) + $min;
        }

        // "1h 25m" or "1 hr 25 min"
        if (preg_match('/(?:(\d+)\s*h(?:r)?s?)?\s*(?:(\d+)\s*m(?:in)?s?)?/', $v, $m)) {
            $h = isset($m[1]) && $m[1] !== '' ? (int) $m[1] : 0;
            $min = isset($m[2]) && $m[2] !== '' ? (int) $m[2] : 0;
            if ($h > 0 || $min > 0) {
                return ($h * 60) + $min;
            }
        }

        if (is_numeric($v)) {
            return (int) round((float) $v);
        }

        return null;
    }

    private function statusFromMinutes(int $minutes, float $percentage): string
    {
        if ($minutes <= 0) {
            return 'absent';
        }
        if ($percentage >= 75) {
            return 'present';
        }
        if ($percentage >= 40) {
            return 'partial';
        }
        return 'late';
    }
}

