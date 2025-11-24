<?php

namespace Cheesegrits\FilamentGoogleMaps\Helpers;

// 🟢 V4 CHANGE: Use the Schema Component as the base class
use Filament\Schemas\Components\Component;
use Filament\Forms\Components\Field;

class FieldHelper
{
    public static function getTopComponent(Component $component): Component
    {
        $parentComponent = $component->getContainer()->getParentComponent();

        return $parentComponent ? static::getTopComponent($parentComponent) : $component;
    }

    public static function getFlatFields($topComponent): array
    {
        $flatFields = [];

        // In v4, we can use the schema component iterator
        foreach ($topComponent->getContainer()->getComponents() as $component) {
            if ($component instanceof Field) {
                $flatFields[$component->getName()] = $component;
            }

            foreach ($component->getChildComponentContainers() as $container) {
                if ($container->isHidden()) {
                    continue;
                }

                $flatFields = array_merge($flatFields, $container->getFlatFields());
            }
        }

        return $flatFields;
    }

    public static function getFieldId(string $field, Component $component): ?string
    {
        $topComponent = self::getTopComponent($component);
        $flatFields   = static::getFlatFields($topComponent);

        $fieldsCollection = collect($flatFields);

        if ($fieldsCollection->has($field)) {
            $fieldComponent = $fieldsCollection->get($field);

            $statePath = $fieldComponent->getStatePath();

            // 🛑 V4 FIX: Ensure 'data.' prefix exists for root fields so Livewire can find them
            if (! str_contains($statePath, '.') && ! str_starts_with($statePath, 'data.')) {
                return 'data.' . $statePath;
            }

            return $statePath;
        }

        // Fallback for when the field object isn't found but we know the name
        if (! str_contains($field, '.') && ! str_starts_with($field, 'data.')) {
            return 'data.' . $field;
        }

        return $field;
    }
}
