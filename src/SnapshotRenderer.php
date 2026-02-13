<?php

declare(strict_types=1);

namespace Logush;

final class SnapshotRenderer
{
    /**
     * @var array<string, string>
     */
    private const ROUTE_MAP = [
        '/' => 'home',
        '/about' => 'about',
        '/services' => 'services',
        '/contact' => 'contact',
        '/vacancies' => 'vacancies',
        '/sale' => 'sale',
        '/cart' => 'cart',
        '/checkout' => 'checkout',
        '/quote' => 'quote',
        '/order-success' => 'order-success',
        '/colors' => 'colors',
        '/size-table' => 'size-table',
        '/login' => 'login',
        '/404' => '404',
    ];

    /**
     * @var array<string, array{mtime:int, html:string}>
     */
    private static array $cache = [];

    private string $ssrDir;

    public function __construct(string $baseDir)
    {
        $this->ssrDir = rtrim($baseDir, '/') . '/storage/ssr';
    }

    public function render(string $path): bool
    {
        $key = $this->resolveKey($path);
        if ($key === null) {
            return false;
        }

        $snapshotPath = $this->ssrDir . '/' . $key . '.html';
        if (!is_file($snapshotPath)) {
            return false;
        }

        header('Content-Type: text/html; charset=utf-8');

        $html = $this->readSnapshot($snapshotPath);
        if ($html === '') {
            readfile($snapshotPath);
            return true;
        }

        $html = $this->transformHtml($key, $html);
        echo $this->injectClientRuntime($html);
        return true;
    }

    private function resolveKey(string $path): ?string
    {
        $key = self::ROUTE_MAP[$path] ?? null;
        if ($key !== null) {
            return $key;
        }

        if (preg_match('#^/product/([^/]+)$#', $path, $matches) !== 1) {
            return null;
        }

        $id = trim(rawurldecode($matches[1]));
        if ($id === '') {
            return null;
        }

        return 'product-' . preg_replace('/[^a-zA-Z0-9_-]+/', '', $id);
    }

    private function readSnapshot(string $path): string
    {
        $mtime = (int) (filemtime($path) ?: 0);
        $cached = self::$cache[$path] ?? null;
        if (is_array($cached) && $cached['mtime'] === $mtime) {
            return $cached['html'];
        }

        $html = file_get_contents($path);
        if (!is_string($html)) {
            return '';
        }

        self::$cache[$path] = [
            'mtime' => $mtime,
            'html' => $html,
        ];

        return $html;
    }

    private function injectClientRuntime(string $html): string
    {
        if (stripos($html, '/js/app.js') !== false) {
            return $html;
        }

        $script = '<script src="/js/app.js" defer></script>';
        if (stripos($html, '</body>') !== false) {
            $replaced = preg_replace('~</body>~i', $script . '</body>', $html, 1);
            return is_string($replaced) ? $replaced : ($html . $script);
        }

        return $html . $script;
    }

    private function transformHtml(string $key, string $html): string
    {
        if ($key === 'login') {
            // The login snapshot is static HTML (no React). Make the form submit to PHP handler.
            $html = preg_replace_callback('~<form\\b([^>]*)>~i', static function (array $m): string {
                $attrs = $m[1] ?? '';
                $out = $attrs;

                if (!preg_match('~\\bmethod\\s*=~i', $out)) {
                    $out .= ' method="post"';
                }
                if (!preg_match('~\\baction\\s*=~i', $out)) {
                    $out .= ' action="/login"';
                }

                return '<form' . $out . '>';
            }, $html, 1) ?? $html;
        }

        if ($key === 'home') {
            // Home is served from SSR snapshot (minified HTML). Inject accordion markup for FAQ so the
            // PHP site's JS (`/js/app.js`) can animate it.
            $faqAnswers = $this->extractFaqAnswersFromJsonLd($html);
            if ($faqAnswers !== []) {
                $pattern = '~(<div class="border-b[^>]*>)<button\\b([^>]*)>([^<]*)((?:<svg[^>]*>.*?</svg>))</button></div>~s';
                $html = preg_replace_callback($pattern, static function (array $m) use ($faqAnswers): string {
                    $wrapperOpen = $m[1] ?? '<div>';
                    $btnAttrs = (string) ($m[2] ?? '');
                    $questionRaw = (string) ($m[3] ?? '');
                    $svg = (string) ($m[4] ?? '');

                    $questionKey = trim(html_entity_decode(strip_tags($questionRaw), ENT_QUOTES, 'UTF-8'));
                    $answer = $faqAnswers[$questionKey] ?? null;
                    if ($answer === null || $answer === '') {
                        return $m[0];
                    }

                    if (!preg_match('~\\bdata-faq-button\\b~i', $btnAttrs)) {
                        $btnAttrs .= ' data-faq-button';
                    }

                    // Same inline transitions as in `views/pages/home-new.php` for consistent behavior.
                    $answerStyle = 'max-height: 0; opacity: 0; overflow: hidden; transition: max-height 0.3s ease, opacity 0.3s ease; display: none;';
                    $answerHtml = htmlspecialchars($answer, ENT_QUOTES, 'UTF-8');

                    return $wrapperOpen
                        . '<button' . $btnAttrs . '>'
                        . $questionRaw
                        . $svg
                        . '</button>'
                        . '<div style="' . $answerStyle . '" class="text-base leading-7 text-gray-600">'
                        . '<div class="mt-4">' . $answerHtml . '</div>'
                        . '</div>'
                        . '</div>';
                }, $html) ?? $html;
            }
        }

        return $html;
    }

    /**
     * @return array<string, string> Map: Question -> Answer
     */
    private function extractFaqAnswersFromJsonLd(string $html): array
    {
        $out = [];

        if (preg_match_all('~<script\\s+type="application/ld\\+json"\\s*>(.*?)</script>~is', $html, $matches) !== 1) {
            // preg_match_all returns 1+ matches; 0 when none; false on error.
            // We'll handle 0/false with empty map.
        }

        $scripts = $matches[1] ?? [];
        if (!is_array($scripts) || $scripts === []) {
            return [];
        }

        foreach ($scripts as $raw) {
            if (!is_string($raw) || $raw === '') {
                continue;
            }

            $json = trim($raw);
            $data = json_decode($json, true);
            if (!is_array($data)) {
                continue;
            }

            // Some generators output an array of JSON-LD blocks.
            $blocks = array_is_list($data) ? $data : [$data];
            foreach ($blocks as $block) {
                if (!is_array($block)) {
                    continue;
                }
                if (($block['@type'] ?? null) !== 'FAQPage') {
                    continue;
                }

                $entities = $block['mainEntity'] ?? null;
                if (!is_array($entities)) {
                    continue;
                }

                foreach ($entities as $entity) {
                    if (!is_array($entity)) {
                        continue;
                    }
                    $q = $entity['name'] ?? null;
                    $a = $entity['acceptedAnswer']['text'] ?? null;
                    if (!is_string($q) || !is_string($a)) {
                        continue;
                    }
                    $q = trim($q);
                    $a = trim($a);
                    if ($q === '' || $a === '') {
                        continue;
                    }
                    $out[$q] = $a;
                }
            }
        }

        return $out;
    }
}
