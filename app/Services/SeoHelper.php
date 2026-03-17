<?php

namespace App\Services;

use App\Filament\Pages\SiteSettings;

class SeoHelper
{
    /**
     * Generate robots meta content from settings.
     */
    public static function robots(): string
    {
        $settings = SiteSettings::getSettings();
        return ($settings['seo_noindex'] ?? true) ? 'noindex, nofollow' : 'index, follow';
    }

    /**
     * Build full page title with suffix.
     */
    public static function title(?string $pageTitle = null): string
    {
        $settings = SiteSettings::getSettings();
        $suffix = $settings['seo_title_suffix'] ?? ' — GN Industrial';

        if ($pageTitle) {
            return $pageTitle . $suffix;
        }

        return 'GN Industrial' . $suffix;
    }

    /**
     * Get default meta description.
     */
    public static function defaultDescription(): string
    {
        $settings = SiteSettings::getSettings();
        return $settings['seo_default_description'] ?? 'Professional kitchen equipment for restaurants, hotels, and food industry.';
    }

    /**
     * Get default OG image.
     */
    public static function defaultOgImage(): string
    {
        $settings = SiteSettings::getSettings();
        return $settings['seo_og_image'] ?? url('/images/logo.png');
    }

    /**
     * Generate Organization + WebSite JSON-LD schema.
     */
    public static function globalSchema(): string
    {
        $settings = SiteSettings::getSettings();

        $schemas = [];

        // Organization / LocalBusiness
        $businessType = $settings['schema_business_type'] ?? 'Store';
        $org = [
            '@context' => 'https://schema.org',
            '@type' => $businessType,
            'name' => $settings['schema_business_name'] ?? 'GN Industrial',
            'url' => $settings['schema_url'] ?? url('/'),
            'telephone' => $settings['schema_phone'] ?? '',
            'email' => $settings['schema_email'] ?? '',
        ];

        if (! empty($settings['schema_logo'])) {
            $org['logo'] = $settings['schema_logo'];
            $org['image'] = $settings['schema_logo'];
        }

        if (! empty($settings['schema_street'])) {
            $org['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings['schema_street'],
                'addressLocality' => $settings['schema_city'] ?? '',
                'postalCode' => $settings['schema_postal'] ?? '',
                'addressCountry' => $settings['schema_country'] ?? 'GE',
            ];
        }

        // Social profiles
        $socials = array_filter([
            $settings['schema_facebook'] ?? 'https://www.facebook.com/gn.ge.official',
            $settings['schema_instagram'] ?? '',
            $settings['schema_youtube'] ?? '',
            $settings['schema_tiktok'] ?? '',
        ]);
        if ($socials) {
            $org['sameAs'] = array_values($socials);
        }

        $schemas[] = $org;

        // WebSite with SearchAction
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $settings['schema_business_name'] ?? 'GN Industrial',
            'url' => $settings['schema_url'] ?? url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => url('/shop') . '?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];

        $json = '';
        foreach ($schemas as $schema) {
            $json .= '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
        }

        return $json;
    }
}
