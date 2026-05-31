<?php

namespace App\Support\Admin;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DashboardMetrics
{
    public static function number(int|float|null $value): string
    {
        return number_format($value ?? 0);
    }

    public static function money(int|float|null $value, string $currency = 'IRR'): string
    {
        return number_format($value ?? 0).' '.$currency;
    }

    /**
     * @return array<int, string>
     */
    public static function labelsForLastDays(int $days): array
    {
        return collect(range($days - 1, 0))
            ->map(fn (int $offset): string => CarbonImmutable::now()->subDays($offset)->format('M j'))
            ->all();
    }

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @return array<int, int>
     */
    public static function countByDay(string $modelClass, int $days, ?Closure $queryScope = null, string $column = 'created_at'): array
    {
        return static::seriesByDay(
            $modelClass,
            $days,
            fn (Model $record): int => 1,
            $queryScope,
            $column,
        );
    }

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @return array<int, int>
     */
    public static function sumByDay(string $modelClass, string $field, int $days, ?Closure $queryScope = null, string $column = 'created_at'): array
    {
        return static::seriesByDay(
            $modelClass,
            $days,
            fn (Model $record): int => (int) ($record->{$field} ?? 0),
            $queryScope,
            $column,
        );
    }

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @param  Closure(TModel): int  $valueResolver
     * @return array<int, int>
     */
    private static function seriesByDay(string $modelClass, int $days, Closure $valueResolver, ?Closure $queryScope, string $column): array
    {
        $dates = static::dateKeysForLastDays($days);
        $start = CarbonImmutable::now()->subDays($days - 1)->startOfDay();

        /** @var Builder<TModel> $query */
        $query = $modelClass::query()
            ->where($column, '>=', $start);

        if ($queryScope !== null) {
            $queryScope($query);
        }

        /** @var Collection<int, TModel> $records */
        $records = $query->get();
        $values = array_fill_keys($dates, 0);

        foreach ($records as $record) {
            $date = $record->{$column};

            if ($date === null) {
                continue;
            }

            $key = $date instanceof \DateTimeInterface
                ? $date->format('Y-m-d')
                : CarbonImmutable::parse($date)->format('Y-m-d');

            if (array_key_exists($key, $values)) {
                $values[$key] += $valueResolver($record);
            }
        }

        return array_values($values);
    }

    /**
     * @return array<int, string>
     */
    private static function dateKeysForLastDays(int $days): array
    {
        return collect(range($days - 1, 0))
            ->map(fn (int $offset): string => CarbonImmutable::now()->subDays($offset)->format('Y-m-d'))
            ->all();
    }
}
