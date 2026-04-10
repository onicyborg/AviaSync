<?php

namespace App\Traits;

use App\Models\SystemLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

trait LogsSystemChanges
{
    public static function bootLogsSystemChanges(): void
    {
        static::created(function (Model $model) {
            if (!self::shouldLog($model)) {
                return;
            }

            self::writeSystemLog($model, 'created', null, $model->attributesToArray());
        });

        static::updated(function (Model $model) {
            if (!self::shouldLog($model)) {
                return;
            }

            $changes = Arr::except($model->getChanges(), ['updated_at']);
            if (empty($changes)) {
                return;
            }

            $newValues = Arr::only($model->attributesToArray(), array_keys($changes));
            $oldValues = [];
            foreach (array_keys($changes) as $field) {
                $oldValues[$field] = $model->getOriginal($field);
            }

            self::writeSystemLog($model, 'updated', $oldValues, $newValues);
        });

        static::deleted(function (Model $model) {
            if (!self::shouldLog($model)) {
                return;
            }

            self::writeSystemLog($model, 'deleted', $model->attributesToArray(), null);
        });
    }

    protected static function shouldLog(Model $model): bool
    {
        return !($model instanceof SystemLog);
    }

    protected static function writeSystemLog(Model $model, string $action, ?array $oldValues, ?array $newValues): void
    {
        try {
            $request = request();
            $payload = $request ? Arr::except($request->all(), ['password', 'password_confirmation']) : null;

            SystemLog::create([
                'user_id' => Auth::id(),
                'action' => sprintf('%s:%s', $model->getTable(), $action),
                'table_name' => $model->getTable(),
                'record_id' => (string) $model->getKey(),
                'old_values' => empty($oldValues) ? null : $oldValues,
                'new_values' => empty($newValues) ? null : $newValues,
                'method' => $request?->method(),
                'url' => $request?->fullUrl(),
                'request_payload' => empty($payload) ? null : $payload,
                'ip_address' => $request?->ip(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
