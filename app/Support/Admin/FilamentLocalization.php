<?php

namespace App\Support\Admin;

use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\Support\Htmlable;

class FilamentLocalization
{
    public static function translate(string|Htmlable|null $label): string|Htmlable|null
    {
        if (! is_string($label) || $label === '') {
            return $label;
        }

        return __($label);
    }

    /**
     * @param  array<string|int, string|array<string|int, string>>  $options
     * @return array<string|int, string|array<string|int, string>>
     */
    public static function options(array $options): array
    {
        return collect($options)
            ->mapWithKeys(function (string|array $label, string|int $value): array {
                if (is_array($label)) {
                    return [__($value) => self::options($label)];
                }

                return [$value => __($label)];
            })
            ->all();
    }

    /**
     * @param  array<string|int, string|array<string|int, string>>  $options
     * @return array<array<string, mixed>>
     */
    public static function selectOptionsForJs(Select $component, array $options): array
    {
        return collect($options)
            ->map(function (string|array $label, string|int $value) use ($component): array {
                if (is_array($label)) {
                    return [
                        'label' => __($value),
                        'options' => self::selectOptionsForJs($component, $label),
                    ];
                }

                return [
                    'label' => __($label),
                    'value' => strval($value),
                    'isDisabled' => $component->isOptionDisabled($value, $label),
                ];
            })
            ->values()
            ->all();
    }

    public static function selectedOptionLabel(Select $component): ?string
    {
        $state = $component->getState();

        if (blank($state)) {
            return null;
        }

        if (is_array($state)) {
            return null;
        }

        return self::flattenOptions($component->getOptions())[$state] ?? strval($state);
    }

    /**
     * @return array<string, string>
     */
    public static function selectedOptionLabels(Select $component): array
    {
        $state = $component->getState();

        if (blank($state)) {
            return [];
        }

        $options = self::flattenOptions($component->getOptions());

        return collect(is_array($state) ? $state : [$state])
            ->mapWithKeys(fn (mixed $value): array => [strval($value) => $options[$value] ?? strval($value)])
            ->all();
    }

    /**
     * @return array<Indicator>
     */
    public static function selectFilterIndicators(SelectFilter $filter, array $state): array
    {
        if ($filter->isMultiple()) {
            if (blank($state['values'] ?? null)) {
                return [];
            }

            $labels = self::filterOptionLabels($filter, $state['values']);

            if (! count($labels)) {
                return [];
            }

            return [self::indicator($filter, collect($labels)->join(', ', ' و '))];
        }

        if (blank($state['value'] ?? null)) {
            return [];
        }

        $label = self::filterOptionLabels($filter, [$state['value']])[$state['value']] ?? null;

        if (blank($label)) {
            return [];
        }

        return [self::indicator($filter, $label)];
    }

    /**
     * @param  array<int, string>  $values
     * @return array<string, string>
     */
    private static function filterOptionLabels(SelectFilter $filter, array $values): array
    {
        if ($filter->queriesRelationships()) {
            return collect($values)
                ->mapWithKeys(function (string $value) use ($filter): array {
                    if ($filter->hasEmptyRelationshipOption() && $value === '__empty') {
                        return [$value => $filter->getEmptyRelationshipOptionLabel()];
                    }

                    $record = $filter->getRelationshipQuery()
                        ->when(
                            $filter->getRelationshipKey(),
                            fn ($query, string $relationshipKey) => $query->where($relationshipKey, $value),
                            fn ($query) => $query->whereKey($value),
                        )
                        ->first();

                    return filled($label = $record?->getAttributeValue($filter->getRelationshipTitleAttribute()))
                        ? [$value => $label]
                        : [];
                })
                ->all();
        }

        return collect(self::flattenOptions($filter->getOptions()))
            ->only($values)
            ->all();
    }

    /**
     * @param  array<string|int, string|array<string|int, string>>  $options
     * @return array<string, string>
     */
    private static function flattenOptions(array $options): array
    {
        return collect($options)
            ->flatMap(fn (string|array $label, string|int $value): array => is_array($label)
                ? self::flattenOptions($label)
                : [$value => __($label)])
            ->all();
    }

    private static function indicator(SelectFilter $filter, string $label): Indicator
    {
        $indicator = $filter->getIndicator();

        if ($indicator instanceof Indicator) {
            return $indicator;
        }

        return Indicator::make("{$indicator}: {$label}");
    }
}
