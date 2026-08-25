<?php
$badge_html = '';
if (!(isset($no_badge) && $no_badge) && isset($badge_text) && trim((string)$badge_text) !== '') {
    $badge_html = '<span class="pbadge">' . badge_label_id($badge_text) . '</span>';
}
$is_liked_card = !empty($p->is_liked)
    || (isset($user_like_ids) && in_array($p->id, $user_like_ids));
$img = $p->image && $p->image !== 'default.jpg'
    ? base_url('uploads/products/' . $p->image)
    : 'https://placehold.co/600x800/f6f2ea/1a1511?text=' . urlencode($p->name);
?>
<div class="pcard" data-product-id="<?= $p->id ?>" <?= isset($badge_text) ? 'data-is-recommended="true"' : '' ?>>
    <a href="<?= base_url('produk/' . $p->slug) ?>" class="pcard-img" aria-label="<?= htmlspecialchars($p->name) ?>">
        <?= $badge_html ?>
        <img src="<?= $img ?>" alt="<?= htmlspecialchars($p->name) ?>" loading="lazy">
        <button type="button" onclick="event.preventDefault(); toggleLike(<?= $p->id ?>, this)"
                class="pcard-heart <?= $is_liked_card ? 'on' : '' ?>" aria-label="Suka"
                data-like-id="<?= $p->id ?>">
            <i class="<?= $is_liked_card ? 'fas text-danger' : 'far' ?> fa-heart"></i>
        </button>
    </a>
    <div class="pcard-body">
        <span class="pcard-cat"><?= htmlspecialchars($p->category_name) ?></span>
        <a href="<?= base_url('produk/' . $p->slug) ?>" class="pcard-name"><?= htmlspecialchars($p->name) ?></a>
        <div class="pcard-meta">
            <span class="pcard-price tnum">Rp <?= number_format($p->price, 0, ',', '.') ?></span>
            <span class="d-flex align-items-center gap-3">
                <button type="button" onclick="toggleLike(<?= $p->id ?>, this)" class="pcard-like <?= !empty($p->is_liked) ? 'is-on' : '' ?>"
                        data-like-id="<?= $p->id ?>">
                    <i class="<?= !empty($p->is_liked) ? 'fas text-danger' : 'far' ?> fa-heart"></i><span class="like-count"><?= (int)($p->like_count ?? 0) ?></span>
                </button>
                <span class="pcard-like" style="cursor:default;"><i class="far fa-comment"></i><?= (int)($p->comment_count ?? 0) ?></span>
            </span>
        </div>
    </div>
</div>
