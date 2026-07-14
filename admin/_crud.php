<?php
/**
 * Shared CRUD building blocks for the admin content pages.
 * Keeps the per-entity pages small: cover images, galleries, delete, pagination.
 */
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

/**
 * Resolve a cover image on save.
 * - if a new file uploaded on $field -> store it (and delete $existing)
 * - else keep $existing
 * Returns the value to persist (may be null).
 */
function save_cover(string $field, string $subdir, ?string $existing): ?string
{
    $new = handle_upload($field, $subdir);
    if ($new !== null) {
        if ($existing) delete_upload($existing);
        return $new;
    }
    return $existing;
}

/** Add any files posted as multiple-upload $field into an images table. */
function add_gallery(string $imgTable, string $fkCol, int $ownerId, string $field, string $subdir): int
{
    if (empty($_FILES[$field]) || !is_array($_FILES[$field]['name'])) return 0;
    $count = count($_FILES[$field]['name']);
    $added = 0;
    $sortStart = (int)db()->query("SELECT COALESCE(MAX(sort),0) FROM `$imgTable` WHERE `$fkCol`=" . (int)$ownerId)->fetchColumn();
    for ($i = 0; $i < $count; $i++) {
        if (($_FILES[$field]['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
        // Re-shape a single file into the format handle_upload expects
        $_FILES['_one'] = [
            'name'     => $_FILES[$field]['name'][$i],
            'type'     => $_FILES[$field]['type'][$i],
            'tmp_name' => $_FILES[$field]['tmp_name'][$i],
            'error'    => $_FILES[$field]['error'][$i],
            'size'     => $_FILES[$field]['size'][$i],
        ];
        $url = handle_upload('_one', $subdir);
        unset($_FILES['_one']);
        if ($url) {
            $stmt = db()->prepare("INSERT INTO `$imgTable` (`$fkCol`, path, sort) VALUES (?,?,?)");
            $stmt->execute([$ownerId, $url, ++$sortStart]);
            $added++;
        }
    }
    return $added;
}

/** Remove gallery images by id (posted as remove_images[]) belonging to $ownerId. */
function remove_gallery(string $imgTable, string $fkCol, int $ownerId, array $imageIds): void
{
    foreach ($imageIds as $iid) {
        $iid = (int)$iid;
        if ($iid <= 0) continue;
        $stmt = db()->prepare("SELECT path FROM `$imgTable` WHERE id=? AND `$fkCol`=?");
        $stmt->execute([$iid, $ownerId]);
        $path = $stmt->fetchColumn();
        if ($path === false) continue;
        delete_upload((string)$path);
        db()->prepare("DELETE FROM `$imgTable` WHERE id=?")->execute([$iid]);
    }
}

/** Fetch gallery rows for an owner. */
function gallery_rows(string $imgTable, string $fkCol, int $ownerId): array
{
    $stmt = db()->prepare("SELECT id, path, sort FROM `$imgTable` WHERE `$fkCol`=? ORDER BY sort, id");
    $stmt->execute([$ownerId]);
    return $stmt->fetchAll();
}

/** Render an editable gallery block (checkbox to remove + new uploads). */
function gallery_editor(array $rows, string $uploadField = 'gallery'): string
{
    ob_start(); ?>
    <div class="field full">
      <label>Photo gallery</label>
      <?php if ($rows): ?>
        <div class="gallery">
          <?php foreach ($rows as $img): ?>
            <figure>
              <img src="../<?= e(ltrim($img['path'], '/')) ?>" alt="">
              <label class="rm" title="Remove this photo" style="cursor:pointer">
                <input type="checkbox" name="remove_images[]" value="<?= (int)$img['id'] ?>" style="accent-color:#fff">
                ✕
              </label>
            </figure>
          <?php endforeach; ?>
        </div>
        <span class="hint">Tick the ✕ on any photo to remove it when you save.</span>
      <?php endif; ?>
      <input type="file" name="<?= e($uploadField) ?>[]" accept="image/*" multiple style="margin-top:10px">
      <span class="hint">Select one or more images to add to the gallery.</span>
    </div>
    <?php
    return (string)ob_get_clean();
}

/** Delete a whole entity: its gallery files, cover file, then the row (FK cascades images rows). */
function delete_entity(string $table, int $id, ?string $imgTable = null, ?string $fkCol = null): void
{
    $pdo = db();
    // cover image
    $stmt = $pdo->prepare("SELECT cover_image FROM `$table` WHERE id=?");
    $stmt->execute([$id]);
    $cover = $stmt->fetchColumn();
    if ($cover && strpos((string)$cover, '/uploads/') !== false) delete_upload((string)$cover);
    // gallery files
    if ($imgTable && $fkCol) {
        foreach (gallery_rows($imgTable, $fkCol, $id) as $img) delete_upload($img['path']);
    }
    $pdo->prepare("DELETE FROM `$table` WHERE id=?")->execute([$id]);
}

/** Render a simple pagination bar. $base already contains any query string minus 'page'. */
function pagination_html(array $p, string $base): string
{
    if ($p['pages'] <= 1) return '';
    $sep = strpos($base, '?') !== false ? '&' : '?';
    $out = '<div class="pagination">';
    if ($p['hasPrev']) $out .= '<a href="' . e($base . $sep . 'page=' . ($p['page'] - 1)) . '">‹ Prev</a>';
    for ($i = 1; $i <= $p['pages']; $i++) {
        $out .= $i === $p['page']
            ? '<span class="cur">' . $i . '</span>'
            : '<a href="' . e($base . $sep . 'page=' . $i) . '">' . $i . '</a>';
    }
    if ($p['hasNext']) $out .= '<a href="' . e($base . $sep . 'page=' . ($p['page'] + 1)) . '">Next ›</a>';
    return $out . '</div>';
}

/** Cover-image field with live preview. */
function cover_field(?string $existing, string $field = 'cover_image'): string
{
    $src = $existing ? '../' . ltrim($existing, '/') : '';
    ob_start(); ?>
    <div class="field full">
      <label>Cover image</label>
      <div style="display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap">
        <img id="coverPrev" class="cover-preview" src="<?= e($src) ?>" alt="" <?= $src ? '' : 'style="display:none"' ?>>
        <div style="flex:1;min-width:220px">
          <input type="file" name="<?= e($field) ?>" accept="image/*" data-preview="#coverPrev">
          <span class="hint">JPG, PNG or WebP. Leave empty to keep the current image.</span>
        </div>
      </div>
    </div>
    <?php
    return (string)ob_get_clean();
}

/** Enquiries received for a specific listing (relationship view on edit pages). */
function enquiries_for(string $itemType, int $itemId): array
{
    if ($itemId <= 0) return [];
    $stmt = db()->prepare('SELECT id,name,phone,status,created_at FROM leads WHERE item_type=? AND item_id=? ORDER BY created_at DESC');
    $stmt->execute([$itemType, $itemId]);
    return $stmt->fetchAll();
}

/** Render a panel listing the enquiries tied to this listing. */
function enquiries_panel(string $itemType, int $itemId): string
{
    if ($itemId <= 0) return '';
    $rows = enquiries_for($itemType, $itemId);
    ob_start(); ?>
    <div class="card card-pad">
      <h3 style="font-size:14px;margin-bottom:12px">Enquiries for this listing <span class="mini muted">(<?= count($rows) ?>)</span></h3>
      <?php if ($rows): ?>
        <?php foreach ($rows as $r): ?>
          <div class="mini" style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;border-bottom:1px solid var(--line)">
            <a href="lead-view.php?id=<?= (int)$r['id'] ?>" style="color:var(--green);font-weight:600"><?= e($r['name']) ?></a>
            <span><span class="muted"><?= e($r['phone']) ?></span> <span class="badge <?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="mini muted">No enquiries yet for this listing.</p>
      <?php endif; ?>
    </div>
    <?php
    return (string)ob_get_clean();
}

/** Standard status <select>. */
function status_select(string $current, array $options, string $name = 'status'): string
{
    $out = '<select name="' . e($name) . '">';
    foreach ($options as $val => $label) {
        $out .= '<option value="' . e((string)$val) . '"' . ($current === $val ? ' selected' : '') . '>' . e($label) . '</option>';
    }
    return $out . '</select>';
}
