<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use App\Models\TenantSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TenantSettingsController extends Controller
{
    public function index(Request $request): Response
    {
        $hero = $this->heroSettings();
        $storefront = $this->storefrontSettings();

        return Inertia::render('Tenant/Settings/Index', [
            'catalogueMode' => TenantSetting::query()->where('key', 'catalogue_mode')->value('value') ?? PlatformSetting::value('catalogue_mode_default', 'shared'),
            'storefrontHero' => [
                'title' => $hero['title'],
                'subtitle' => $hero['subtitle'],
                'badge' => $hero['badge'],
                'imageUrl' => $this->heroImageUrl($request, $hero['image_path'] ?? null),
                'hasCustomImage' => filled($hero['image_path'] ?? null),
            ],
            'storefrontConfig' => $this->storefrontConfigForAdmin($storefront),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'catalogue_mode' => ['required', 'in:shared,separate_online'],
            'storefront_hero_title' => ['nullable', 'string', 'max:120'],
            'storefront_hero_subtitle' => ['nullable', 'string', 'max:300'],
            'storefront_hero_badge' => ['nullable', 'string', 'max:60'],
            'storefront_store_name' => ['nullable', 'string', 'max:80'],
            'storefront_delivery_locations' => ['nullable', 'string', 'max:2000'],
            'storefront_default_delivery_location' => ['nullable', 'string', 'max:120'],
            'storefront_delivery_message' => ['nullable', 'string', 'max:160'],
            'storefront_hero_delivery_message' => ['nullable', 'string', 'max:160'],
            'storefront_new_arrivals_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'storefront_customer_service_title' => ['nullable', 'string', 'max:80'],
            'storefront_customer_service_phone' => ['nullable', 'string', 'max:40'],
            'storefront_customer_service_email' => ['nullable', 'string', 'max:120'],
            'storefront_customer_service_hours' => ['nullable', 'string', 'max:120'],
            'storefront_customer_service_message' => ['nullable', 'string', 'max:500'],
        ]);

        TenantSetting::query()->updateOrCreate(
            ['key' => 'catalogue_mode'],
            ['value' => $validated['catalogue_mode']],
        );

        $hero = $this->heroSettings();
        $hero['title'] = $validated['storefront_hero_title'] ?: $this->defaultHero()['title'];
        $hero['subtitle'] = $validated['storefront_hero_subtitle'] ?: $this->defaultHero()['subtitle'];
        $hero['badge'] = $validated['storefront_hero_badge'] ?: $this->defaultHero()['badge'];

        TenantSetting::query()->updateOrCreate(
            ['key' => 'storefront_hero'],
            ['value' => $hero],
        );

        $locations = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['storefront_delivery_locations'] ?? '')))
            ->map(static fn (string $location): string => trim($location))
            ->filter()
            ->values()
            ->all();

        $defaultLocation = trim((string) ($validated['storefront_default_delivery_location'] ?? ''));
        if ($defaultLocation === '' && $locations !== []) {
            $defaultLocation = $locations[0];
        }

        $defaults = $this->defaultStorefrontSettings();
        $customerServiceDefaults = $defaults['customer_service'];

        TenantSetting::query()->updateOrCreate(
            ['key' => 'storefront_config'],
            ['value' => [
                'store_name' => filled($validated['storefront_store_name'] ?? null)
                    ? trim((string) $validated['storefront_store_name'])
                    : null,
                'delivery_locations' => $locations !== [] ? $locations : $defaults['delivery_locations'],
                'default_delivery_location' => $defaultLocation !== ''
                    ? $defaultLocation
                    : $defaults['default_delivery_location'],
                'delivery_message' => filled($validated['storefront_delivery_message'] ?? null)
                    ? trim((string) $validated['storefront_delivery_message'])
                    : $defaults['delivery_message'],
                'hero_delivery_message' => filled($validated['storefront_hero_delivery_message'] ?? null)
                    ? trim((string) $validated['storefront_hero_delivery_message'])
                    : $defaults['hero_delivery_message'],
                'new_arrivals_days' => $validated['storefront_new_arrivals_days'] ?? $defaults['new_arrivals_days'],
                'customer_service' => [
                    'title' => filled($validated['storefront_customer_service_title'] ?? null)
                        ? trim((string) $validated['storefront_customer_service_title'])
                        : $customerServiceDefaults['title'],
                    'phone' => filled($validated['storefront_customer_service_phone'] ?? null)
                        ? trim((string) $validated['storefront_customer_service_phone'])
                        : $customerServiceDefaults['phone'],
                    'email' => filled($validated['storefront_customer_service_email'] ?? null)
                        ? trim((string) $validated['storefront_customer_service_email'])
                        : $customerServiceDefaults['email'],
                    'hours' => filled($validated['storefront_customer_service_hours'] ?? null)
                        ? trim((string) $validated['storefront_customer_service_hours'])
                        : $customerServiceDefaults['hours'],
                    'message' => filled($validated['storefront_customer_service_message'] ?? null)
                        ? trim((string) $validated['storefront_customer_service_message'])
                        : $customerServiceDefaults['message'],
                ],
            ]],
        );

        return back()->with('success', 'Store settings updated.');
    }

    public function updateHeroImage(Request $request): RedirectResponse
    {
        $request->validate([
            'storefront_hero_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $hero = $this->heroSettings();
        $previousPath = $hero['image_path'] ?? null;

        $storedPath = Storage::disk('public')->putFile(
            'storefront/'.tenant()->getTenantKey(),
            $request->file('storefront_hero_image'),
        );

        $hero['image_path'] = 'storage/'.$storedPath;

        TenantSetting::query()->updateOrCreate(
            ['key' => 'storefront_hero'],
            ['value' => $hero],
        );

        if (is_string($previousPath) && $previousPath !== $hero['image_path']) {
            Storage::disk('public')->delete(str_replace('storage/', '', $previousPath));
        }

        return back()->with('success', 'Storefront hero image updated.');
    }

    public function destroyHeroImage(): RedirectResponse
    {
        $hero = $this->heroSettings();
        $previousPath = $hero['image_path'] ?? null;

        unset($hero['image_path']);

        TenantSetting::query()->updateOrCreate(
            ['key' => 'storefront_hero'],
            ['value' => $hero],
        );

        if (is_string($previousPath)) {
            Storage::disk('public')->delete(str_replace('storage/', '', $previousPath));
        }

        return back()->with('success', 'Storefront hero image removed.');
    }

    /**
     * @return array{title: string, subtitle: string, badge: string, image_path?: string}
     */
    public static function heroSettingsForStorefront(Request $request): array
    {
        $controller = new self;
        $hero = $controller->heroSettings();

        return [
            'title' => $hero['title'],
            'subtitle' => $hero['subtitle'],
            'badge' => $hero['badge'],
            'imageUrl' => $controller->heroImageUrl($request, $hero['image_path'] ?? null),
        ];
    }

    /**
     * @return array{
     *     storeName: string,
     *     deliveryLocations: array<int, string>,
     *     defaultDeliveryLocation: string,
     *     deliveryMessage: string,
     *     heroDeliveryMessage: string,
     *     newArrivalsDays: int,
     *     customerService: array{title: string, phone: string, email: string, hours: string, message: string}
     * }
     */
    public static function storefrontConfigForStorefront(Request $request): array
    {
        $controller = new self;
        $settings = $controller->storefrontSettings();
        $tenantName = (string) (tenant()?->name ?? 'Store');

        return [
            'subscribedTenantName' => $tenantName,
            'storeName' => filled($settings['store_name'] ?? null)
                ? (string) $settings['store_name']
                : $tenantName,
            'deliveryLocations' => $settings['delivery_locations'],
            'defaultDeliveryLocation' => $settings['default_delivery_location'],
            'deliveryMessage' => $settings['delivery_message'],
            'heroDeliveryMessage' => $settings['hero_delivery_message'],
            'newArrivalsDays' => (int) $settings['new_arrivals_days'],
            'customerService' => $settings['customer_service'],
        ];
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function storefrontConfigForAdmin(array $settings): array
    {
        return [
            'store_name' => $settings['store_name'] ?? '',
            'delivery_locations' => implode("\n", $settings['delivery_locations']),
            'default_delivery_location' => $settings['default_delivery_location'],
            'delivery_message' => $settings['delivery_message'],
            'hero_delivery_message' => $settings['hero_delivery_message'],
            'new_arrivals_days' => $settings['new_arrivals_days'],
            'customer_service_title' => $settings['customer_service']['title'],
            'customer_service_phone' => $settings['customer_service']['phone'],
            'customer_service_email' => $settings['customer_service']['email'],
            'customer_service_hours' => $settings['customer_service']['hours'],
            'customer_service_message' => $settings['customer_service']['message'],
        ];
    }

    /**
     * @return array{title: string, subtitle: string, badge: string, image_path?: string}
     */
    private function heroSettings(): array
    {
        $stored = TenantSetting::query()->where('key', 'storefront_hero')->value('value');
        $defaults = $this->defaultHero();

        if (! is_array($stored)) {
            return $defaults;
        }

        return [
            'title' => filled($stored['title'] ?? null) ? (string) $stored['title'] : $defaults['title'],
            'subtitle' => filled($stored['subtitle'] ?? null) ? (string) $stored['subtitle'] : $defaults['subtitle'],
            'badge' => filled($stored['badge'] ?? null) ? (string) $stored['badge'] : $defaults['badge'],
            'image_path' => filled($stored['image_path'] ?? null) ? (string) $stored['image_path'] : null,
        ];
    }

    /**
     * @return array{title: string, subtitle: string, badge: string}
     */
    private function defaultHero(): array
    {
        return [
            'title' => 'Everyday essentials, delivered simply.',
            'subtitle' => 'Shop trusted products for your home, pantry, and daily routine. Add what you need and we will take care of the rest.',
            'badge' => 'In stock and ready to ship',
        ];
    }

    private function heroImageUrl(Request $request, ?string $imagePath): string
    {
        if (! filled($imagePath)) {
            return $request->getSchemeAndHttpHost().'/images/storefront/hero-default.svg';
        }

        $normalized = ltrim(str_replace('storage/', '', $imagePath), '/');

        return $request->getSchemeAndHttpHost().'/client/'.tenant()->getTenantKey().'/media/'.$normalized;
    }

    /**
     * @return array{
     *     store_name?: string|null,
     *     delivery_locations: array<int, string>,
     *     default_delivery_location: string,
     *     delivery_message: string,
     *     hero_delivery_message: string,
     *     new_arrivals_days: int,
     *     customer_service: array{title: string, phone: string, email: string, hours: string, message: string}
     * }
     */
    private function storefrontSettings(): array
    {
        $stored = TenantSetting::query()->where('key', 'storefront_config')->value('value');
        $defaults = $this->defaultStorefrontSettings();

        if (! is_array($stored)) {
            return $defaults;
        }

        $customerService = is_array($stored['customer_service'] ?? null)
            ? $stored['customer_service']
            : [];

        $locations = collect($stored['delivery_locations'] ?? [])
            ->filter(static fn ($location): bool => is_string($location) && trim($location) !== '')
            ->map(static fn (string $location): string => trim($location))
            ->values()
            ->all();

        return [
            'store_name' => filled($stored['store_name'] ?? null) ? (string) $stored['store_name'] : null,
            'delivery_locations' => $locations !== [] ? $locations : $defaults['delivery_locations'],
            'default_delivery_location' => filled($stored['default_delivery_location'] ?? null)
                ? (string) $stored['default_delivery_location']
                : ($locations[0] ?? $defaults['default_delivery_location']),
            'delivery_message' => filled($stored['delivery_message'] ?? null)
                ? (string) $stored['delivery_message']
                : $defaults['delivery_message'],
            'hero_delivery_message' => filled($stored['hero_delivery_message'] ?? null)
                ? (string) $stored['hero_delivery_message']
                : $defaults['hero_delivery_message'],
            'new_arrivals_days' => filled($stored['new_arrivals_days'] ?? null)
                ? (int) $stored['new_arrivals_days']
                : $defaults['new_arrivals_days'],
            'customer_service' => [
                'title' => filled($customerService['title'] ?? null)
                    ? (string) $customerService['title']
                    : $defaults['customer_service']['title'],
                'phone' => filled($customerService['phone'] ?? null)
                    ? (string) $customerService['phone']
                    : $defaults['customer_service']['phone'],
                'email' => filled($customerService['email'] ?? null)
                    ? (string) $customerService['email']
                    : $defaults['customer_service']['email'],
                'hours' => filled($customerService['hours'] ?? null)
                    ? (string) $customerService['hours']
                    : $defaults['customer_service']['hours'],
                'message' => filled($customerService['message'] ?? null)
                    ? (string) $customerService['message']
                    : $defaults['customer_service']['message'],
            ],
        ];
    }

    /**
     * @return array{
     *     store_name?: string|null,
     *     delivery_locations: array<int, string>,
     *     default_delivery_location: string,
     *     delivery_message: string,
     *     hero_delivery_message: string,
     *     new_arrivals_days: int,
     *     customer_service: array{title: string, phone: string, email: string, hours: string, message: string}
     * }
     */
    private function defaultStorefrontSettings(): array
    {
        return [
            'store_name' => null,
            'delivery_locations' => ['Accra', 'Kumasi', 'Tema', 'Takoradi', 'Cape Coast'],
            'default_delivery_location' => 'Accra',
            'delivery_message' => 'Fast local delivery on every order',
            'hero_delivery_message' => 'Free local delivery on every order',
            'new_arrivals_days' => 30,
            'customer_service' => [
                'title' => 'Customer Service',
                'phone' => '+233 30 000 0000',
                'email' => 'support@enablstore.test',
                'hours' => 'Mon–Sat, 8:00am – 6:00pm',
                'message' => 'Need help with an order, delivery, or product question? Our team is ready to assist you.',
            ],
        ];
    }
}
