<?php

namespace App\Support;

final class EmployeeTaskTypes
{
    public static function definitions(): array
    {
        return config('employee_task_types.types', []);
    }

    /** @return list<string> */
    public static function codes(): array
    {
        return array_keys(self::definitions());
    }

    public static function definition(string $code): ?array
    {
        return self::definitions()[$code] ?? null;
    }

    public static function label(string $code): string
    {
        return self::definition($code)['label'] ?? $code;
    }

    /**
     * أنواع المهام المسموح تعيينها لموظف حسب كود وظيفته.
     * بدون وظيفة: الأنواع العامة فقط (job_codes = null).
     *
     * @return list<string>
     */
    public static function allowedTypesForEmployeeJob(?string $jobCode): array
    {
        $allowed = [];
        foreach (self::definitions() as $code => $def) {
            $jobs = $def['job_codes'] ?? null;
            if ($jobs === null) {
                $allowed[] = $code;

                continue;
            }
            if ($jobCode !== null && $jobCode !== '' && in_array($jobCode, $jobs, true)) {
                $allowed[] = $code;
            }
        }

        return $allowed;
    }

    public static function taskTypeAllowedForJob(string $taskType, ?string $jobCode): bool
    {
        $def = self::definition($taskType);
        if ($def === null) {
            return false;
        }
        $jobs = $def['job_codes'] ?? null;
        if ($jobs === null) {
            return true;
        }

        return $jobCode !== null && $jobCode !== '' && in_array($jobCode, $jobs, true);
    }

    public static function usesVideoDeliverableFields(string $taskType): bool
    {
        return (bool) (self::definition($taskType)['uses_video_deliverable_fields'] ?? false);
    }
}
