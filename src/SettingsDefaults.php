<?php

declare(strict_types=1);

namespace Logush;

final class SettingsDefaults
{
    private static ?array $defaults = null;

    public static function defaults(): array
    {
        if (self::$defaults !== null) {
            return self::$defaults;
        }

        $path = dirname(__DIR__) . '/storage/defaults/settings.json';
        if (!is_file($path)) {
            self::$defaults = [];
            return self::$defaults;
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            self::$defaults = [];
            return self::$defaults;
        }

        $decoded = json_decode($raw, true);
        self::$defaults = is_array($decoded) ? $decoded : [];
        return self::$defaults;
    }

    public static function merge(array $settings): array
    {
        $defaults = self::defaults();
        $merged = array_merge($defaults, $settings);

        $merged['phone'] = self::trimOrDefault($merged['phone'] ?? null, $defaults['phone'] ?? '');
        $merged['email'] = self::trimOrDefault($merged['email'] ?? null, $defaults['email'] ?? '');
        $merged['whatsapp'] = self::trimOrDefault($merged['whatsapp'] ?? null, $defaults['whatsapp'] ?? '');
        $merged['telegram'] = self::trimOrDefault($merged['telegram'] ?? null, $defaults['telegram'] ?? '');

        $merged['slider1Images'] = self::normalizeImages($merged['slider1Images'] ?? null, $defaults['slider1Images'] ?? []);
        $merged['slider2Images'] = self::normalizeImages($merged['slider2Images'] ?? null, $defaults['slider2Images'] ?? []);

        $merged['pageContent'] = self::mergePageContent(
            is_array($defaults['pageContent'] ?? null) ? $defaults['pageContent'] : [],
            is_array($merged['pageContent'] ?? null) ? $merged['pageContent'] : []
        );

        $merged['seo'] = self::mergeSeo(
            is_array($defaults['seo'] ?? null) ? $defaults['seo'] : [],
            is_array($merged['seo'] ?? null) ? $merged['seo'] : []
        );

        $merged['pageBlocks'] = self::mergePageBlocks(
            $defaults['pageBlocks'] ?? [],
            $merged['pageBlocks'] ?? []
        );

        return $merged;
    }

    private static function trimOrDefault(mixed $value, string $default): string
    {
        $text = trim((string) ($value ?? ''));
        return $text !== '' ? $text : $default;
    }

    private static function normalizeImages(mixed $value, mixed $default): array
    {
        $arr = is_array($value) ? $value : (is_array($default) ? $default : []);
        $result = [];
        foreach ($arr as $item) {
            $text = trim((string) $item);
            if ($text === '') {
                continue;
            }
            $result[] = $text;
        }
        return array_slice($result, 0, 4);
    }

    private static function mergePageContent(array $defaults, array $input): array
    {
        $result = [];
        foreach (['home', 'about', 'services', 'vacancies'] as $page) {
            $def = is_array($defaults[$page] ?? null) ? $defaults[$page] : [];
            $val = is_array($input[$page] ?? null) ? $input[$page] : [];

            if ($page === 'home') {
                $result['home'] = [
                    'heroParagraph1' => self::trimOrDefault($val['heroParagraph1'] ?? null, (string) ($def['heroParagraph1'] ?? '')),
                    'heroParagraph2' => self::trimOrDefault($val['heroParagraph2'] ?? null, (string) ($def['heroParagraph2'] ?? '')),
                    'heroParagraph3' => self::trimOrDefault($val['heroParagraph3'] ?? null, (string) ($def['heroParagraph3'] ?? '')),
                    'heroButtonText' => self::trimOrDefault($val['heroButtonText'] ?? null, (string) ($def['heroButtonText'] ?? '')),
                ];
                continue;
            }

            $result[$page] = [
                'title' => self::trimOrDefault($val['title'] ?? null, (string) ($def['title'] ?? '')),
                'subtitle' => self::trimOrDefault($val['subtitle'] ?? null, (string) ($def['subtitle'] ?? '')),
                'paragraph1' => self::trimOrDefault($val['paragraph1'] ?? null, (string) ($def['paragraph1'] ?? '')),
                'paragraph2' => self::trimOrDefault($val['paragraph2'] ?? null, (string) ($def['paragraph2'] ?? '')),
            ];
        }
        return $result;
    }

    private static function mergeSeo(array $defaults, array $input): array
    {
        $result = [];
        foreach (['home', 'about', 'services', 'vacancies'] as $page) {
            $def = is_array($defaults[$page] ?? null) ? $defaults[$page] : [];
            $val = is_array($input[$page] ?? null) ? $input[$page] : [];
            $result[$page] = [
                'title' => self::trimOrDefault($val['title'] ?? null, (string) ($def['title'] ?? '')),
                'description' => self::trimOrDefault($val['description'] ?? null, (string) ($def['description'] ?? '')),
                'keywords' => self::trimOrDefault($val['keywords'] ?? null, (string) ($def['keywords'] ?? '')),
            ];
        }
        return $result;
    }

    private static function mergePageBlocks(mixed $defaults, mixed $input): mixed
    {
        if (is_string($defaults)) {
            $text = is_string($input) ? trim($input) : '';
            return $text !== '' ? $text : $defaults;
        }

        if (is_array($defaults)) {
            // List case.
            if (array_keys($defaults) === range(0, count($defaults) - 1)) {
                $inList = is_array($input) ? $input : [];
                if ($inList === []) {
                    return $defaults;
                }

                // Allow lists to grow beyond defaults (e.g. reviews/faqs edited in admin).
                $max = max(count($defaults), count($inList));
                $out = [];
                for ($i = 0; $i < $max; $i++) {
                    if (array_key_exists($i, $defaults)) {
                        $out[$i] = self::mergePageBlocks($defaults[$i], $inList[$i] ?? null);
                    } else {
                        $out[$i] = self::normalizeFree($inList[$i] ?? null);
                    }
                }
                return $out;
            }

            // Assoc case.
            $in = is_array($input) ? $input : [];
            $out = [];
            // Preserve unknown keys from input (e.g. new blocks added later).
            $keys = array_values(array_unique(array_merge(array_keys($defaults), array_keys($in))));
            foreach ($keys as $key) {
                if (array_key_exists($key, $defaults)) {
                    $out[$key] = self::mergePageBlocks($defaults[$key], $in[$key] ?? null);
                } else {
                    $out[$key] = self::normalizeFree($in[$key] ?? null);
                }
            }
            return $out;
        }

        return $defaults;
    }

    private static function normalizeFree(mixed $value): mixed
    {
        if (is_string($value)) {
            return trim($value);
        }
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = self::normalizeFree($v);
            }
            return $out;
        }
        return $value;
    }
}
