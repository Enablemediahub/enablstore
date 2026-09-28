<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RestaurantMenuItem;

class RestaurantMenuPricing
{
    /**
     * @param array<int, string|array{id: string, quantity?: int}> $selectedOptions
     * @return array{unit_price_minor: int, selected_options: array<int, array{group: string, name: string, price_minor: int, quantity: int}>}
     */
    public function forSelection(RestaurantMenuItem $menuItem, array $selectedOptions): array
    {
        $selected = [];
        foreach ($selectedOptions as $selection) {
            $optionId = is_array($selection) ? (string) ($selection['id'] ?? '') : (string) $selection;
            $quantity = is_array($selection) ? (int) ($selection['quantity'] ?? 1) : 1;
            if ($optionId === '' || $quantity < 1 || $quantity > 99 || isset($selected[$optionId])) {
                throw new \DomainException('One or more selected option quantities are invalid.');
            }
            $selected[$optionId] = $quantity;
        }

        $matched = [];
        $selectedOptions = [];
        $unitPriceMinor = $menuItem->price_minor;

        foreach ($menuItem->option_groups ?? [] as $group) {
            $groupName = (string) ($group['name'] ?? 'Options');
            $groupSelections = [];

            foreach ($group['options'] ?? [] as $option) {
                $optionId = (string) ($option['id'] ?? '');
                if ($optionId === '' || ! isset($selected[$optionId])) {
                    continue;
                }

                $groupSelections[] = $option;
                $matched[$optionId] = true;
            }

            if (($group['required'] ?? false) && $groupSelections === []) {
                throw new \DomainException("Choose an option for {$groupName}.");
            }

            if (! ($group['multiple'] ?? false) && count($groupSelections) > 1) {
                throw new \DomainException("Choose only one option for {$groupName}.");
            }

            foreach ($groupSelections as $option) {
                $priceMinor = (int) ($option['price_minor'] ?? 0);
                $quantity = $selected[(string) $option['id']];
                $unitPriceMinor += $priceMinor * $quantity;
                $selectedOptions[] = [
                    'group' => $groupName,
                    'name' => (string) ($option['name'] ?? 'Option'),
                    'price_minor' => $priceMinor,
                    'quantity' => $quantity,
                ];
            }
        }

        if (count($matched) !== count($selected)) {
            throw new \DomainException('One or more selected options are not available for this food item.');
        }

        return [
            'unit_price_minor' => $unitPriceMinor,
            'selected_options' => $selectedOptions,
        ];
    }
}