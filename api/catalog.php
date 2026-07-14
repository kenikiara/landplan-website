<?php
/**
 * Public catalog feed (JSON) for the listing pages.
 * GET ?type=land|house|project|article  [&featured=1] [&limit=50] [&slug=…]
 * Returns published items only.
 */
declare(strict_types=1);
require_once __DIR__ . '/../app/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=120');

$type    = get('type', 'land');
$limit   = min(100, max(1, (int)get('limit', '60')));
$featured= get('featured') === '1';
$slug    = get('slug');
$pdo     = db();

function img_url(?string $p): string {
    if (!$p) return '';
    return strpos($p, 'http') === 0 ? $p : '/' . ltrim($p, '/');
}

try {
    if ($type === 'land') {
        $where = "status='published'"; $args = [];
        if ($featured) $where .= ' AND featured=1';
        if ($slug !== '') { $where .= ' AND slug=?'; $args[] = $slug; }
        $q = $pdo->prepare("SELECT * FROM land_listings WHERE $where ORDER BY featured DESC, created_at DESC LIMIT $limit");
        $q->execute($args);
        $items = array_map(fn($r) => [
            'id'       => (int)$r['id'],
            'title'    => $r['title'],
            'slug'     => $r['slug'],
            'location' => $r['location'],
            'size'     => $r['size'],
            'price'    => $r['price'] !== null ? ksh($r['price']) : 'Price on request',
            'category' => strtoupper($r['category']),
            'title_status' => $r['title_status'],
            'status'   => $r['status'],
            'image'    => img_url($r['cover_image']),
            'url'      => 'land-detail.php?slug=' . $r['slug'],
        ], $q->fetchAll());

    } elseif ($type === 'house') {
        $where = "status='published'"; $args = [];
        if ($featured) $where .= ' AND featured=1';
        if ($slug !== '') { $where .= ' AND slug=?'; $args[] = $slug; }
        $q = $pdo->prepare("SELECT * FROM houses WHERE $where ORDER BY featured DESC, created_at DESC LIMIT $limit");
        $q->execute($args);
        $items = array_map(fn($r) => [
            'id'       => (int)$r['id'],
            'title'    => $r['title'],
            'slug'     => $r['slug'],
            'location' => $r['location'],
            'bedrooms' => (int)$r['bedrooms'],
            'bathrooms'=> (int)$r['bathrooms'],
            'price'    => $r['price'] !== null ? ksh($r['price']) : 'Price on request',
            'status'   => $r['status'],
            'image'    => img_url($r['cover_image']),
            'url'      => 'house-detail.php?slug=' . $r['slug'],
        ], $q->fetchAll());

    } elseif ($type === 'project') {
        $where = '1=1'; $args = [];
        if ($featured) $where .= ' AND featured=1';
        $pstatus = get('pstatus');
        if (in_array($pstatus, ['ongoing','completed'], true)) { $where .= ' AND status=?'; $args[] = $pstatus; }
        if ($slug !== '') { $where .= ' AND slug=?'; $args[] = $slug; }
        $q = $pdo->prepare("SELECT * FROM projects WHERE $where ORDER BY featured DESC, created_at DESC LIMIT $limit");
        $q->execute($args);
        $items = array_map(fn($r) => [
            'id'       => (int)$r['id'],
            'title'    => $r['title'],
            'slug'     => $r['slug'],
            'location' => $r['location'],
            'status'   => $r['status'],
            'image'    => img_url($r['cover_image']),
            'url'      => 'project-detail.php?slug=' . $r['slug'],
        ], $q->fetchAll());

    } elseif ($type === 'article') {
        $where = "status='published'"; $args = [];
        if ($slug !== '') { $where .= ' AND slug=?'; $args[] = $slug; }
        $q = $pdo->prepare("SELECT * FROM articles WHERE $where ORDER BY COALESCE(published_at,created_at) DESC LIMIT $limit");
        $q->execute($args);
        $items = array_map(fn($r) => [
            'id'       => (int)$r['id'],
            'title'    => $r['title'],
            'slug'     => $r['slug'],
            'excerpt'  => $r['excerpt'],
            'category' => $r['category'],
            'date'     => nice_date($r['published_at'] ?: $r['created_at']),
            'image'    => img_url($r['cover_image']),
            'url'      => 'article.php?slug=' . $r['slug'],
        ], $q->fetchAll());

    } else {
        json_out(['ok' => false, 'error' => 'Unknown type'], 400);
    }

    json_out(['ok' => true, 'type' => $type, 'count' => count($items), 'items' => $items]);
} catch (Throwable $e) {
    error_log('Catalog error: ' . $e->getMessage());
    json_out(['ok' => false, 'error' => 'Unable to load listings'], 500);
}
