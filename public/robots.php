<?php
/**
 * Served as /robots.txt via .htaccess.
 * Uses URLROOT so Sitemap and Disallow paths match the deployment path.
 */
require_once __DIR__ . '/../app/config/config.php';

header('Content-Type: text/plain; charset=UTF-8');

$base = rtrim((string) URLROOT, '/');
$path = parse_url($base, PHP_URL_PATH);
$pathPrefix = is_string($path) ? rtrim($path, '/') : '';
$pre = $pathPrefix === '' ? '' : $pathPrefix;

echo "User-agent: *\n";
echo "Allow: /\n";
echo 'Disallow: ' . $pre . "/admin\n";
echo 'Disallow: ' . $pre . "/users\n";
echo 'Disallow: ' . $pre . "/cart\n";
echo "\n";
echo 'Sitemap: ' . $base . "/sitemap.xml\n";
