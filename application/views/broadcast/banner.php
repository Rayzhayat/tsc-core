<?php if (!empty($broadcasts_banner)): ?>
    <div id="broadcastBannerStack" style="margin-bottom: 20px;">
        <?php foreach ($broadcasts_banner as $b):
            $color_map = [
                'info' => ['bg' => '#e3f2fd', 'border' => '#1565c0', 'icon_bg' => '#1565c0', 'icon' => 'fa-info-circle', 'text' => '#0d3c6e'],
                'warning' => ['bg' => '#fff8e1', 'border' => '#f57f17', 'icon_bg' => '#f57f17', 'icon' => 'fa-exclamation-triangle', 'text' => '#7a4600'],
                'success' => ['bg' => '#e8f5e9', 'border' => '#2e7d32', 'icon_bg' => '#2e7d32', 'icon' => 'fa-check-circle', 'text' => '#1b4d1e'],
                'danger' => ['bg' => '#ffebee', 'border' => '#c62828', 'icon_bg' => '#c62828', 'icon' => 'fa-fire', 'text' => '#7b1b1b'],
            ];
            $c = $color_map[$b->tipe] ?? $color_map['info'];
            $ext = $b->attachment ? strtolower(pathinfo($b->attachment, PATHINFO_EXTENSION)) : null;
            $is_img = $ext && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            ?>
            <div class="broadcast-banner" id="banner-<?= $b->id ?>" data-id="<?= $b->id ?>" style="
            background:<?= $c['bg'] ?>; border-left:5px solid <?= $c['border'] ?>;
            border-radius:12px; padding:0; margin-bottom:12px;
            box-shadow:0 2px 12px rgba(0,0,0,.07); overflow:hidden;
            animation:bannerSlideIn .4s ease both;">
                <div style="display:flex; align-items:flex-start; padding:16px 18px;">
                    <div style="width:40px;height:40px;border-radius:10px;background:<?= $c['icon_bg'] ?>;color:#fff;
                    display:flex;align-items:center;justify-content:center;
                    font-size:1.1rem;flex-shrink:0;margin-right:14px;margin-top:1px;">
                        <i class="fas <?= $c['icon'] ?>"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                            <div>
                                <?php if ($b->is_pinned): ?>
                                    <span style="background:#fff3cd;color:#856404;padding:2px 8px;border-radius:20px;
                                    font-size:.68rem;font-weight:600;display:inline-block;margin-bottom:4px;">
                                        <i class="fas fa-thumbtack me-1"></i>Disematkan
                                    </span>
                                <?php endif ?>
                                <div style="font-weight:700;font-size:.95rem;color:<?= $c['text'] ?>;line-height:1.3;">
                                    <?= htmlspecialchars($b->judul) ?>
                                </div>
                                <div style="font-size:.78rem;color:#888;margin-top:2px;">
                                    <i class="fas fa-user-circle me-1"></i><?= htmlspecialchars($b->dibuat_oleh_nama) ?>
                                    &nbsp;·&nbsp;<?= date('d M Y H:i', strtotime($b->created_at)) ?>
                                </div>
                            </div>
                            <button type="button" onclick="dismissBanner(<?= $b->id ?>)" style="
                            background:none;border:none;cursor:pointer;color:#aaa;
                            font-size:.9rem;padding:2px 4px;flex-shrink:0;transition:color .2s;line-height:1;"
                                onmouseover="this.style.color='#555'" onmouseout="this.style.color='#aaa'" title="Tutup">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div style="font-size:.88rem;color:<?= $c['text'] ?>;margin-top:8px;line-height:1.65;opacity:.9;">
                            <?= nl2br(htmlspecialchars($b->isi)) ?>
                        </div>
                        <?php if ($b->attachment): ?>
                            <div style="margin-top:10px;">
                                <?php if ($is_img): ?>
                                    <a href="<?= base_url('uploads/broadcast/' . $b->attachment) ?>" target="_blank">
                                        <img src="<?= base_url('uploads/broadcast/' . $b->attachment) ?>" alt="Attachment"
                                            style="max-height:180px;max-width:100%;border-radius:8px;object-fit:cover;display:block;">
                                    </a>
                                <?php else: ?>
                                    <a href="<?= base_url('uploads/broadcast/' . $b->attachment) ?>" target="_blank" style="
                                    display:inline-flex;align-items:center;gap:6px;background:rgba(0,0,0,.06);
                                    color:<?= $c['text'] ?>;padding:5px 14px;border-radius:20px;
                                    font-size:.78rem;font-weight:600;text-decoration:none;">
                                        <i class="fas fa-paperclip"></i><?= htmlspecialchars($b->attachment) ?>
                                    </a>
                                <?php endif ?>
                            </div>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>

    <style>
        @keyframes bannerSlideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .broadcast-banner {
            transition: opacity .3s, transform .3s, max-height .4s, margin .3s, padding .3s;
        }

        .broadcast-banner.dismissing {
            opacity: 0;
            transform: translateX(30px);
            max-height: 0 !important;
            margin-bottom: 0 !important;
            overflow: hidden;
        }
    </style>

<?php endif ?>