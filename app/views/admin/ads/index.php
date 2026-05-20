<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<div class="row g-3">
    <div class="col-12">
        <section class="card panel-card">
            <div class="card-body">
                <div class="admin-module-header">
                    <div>
                        <h1 class="admin-module-title">Quản lý quảng cáo</h1>
                        <p class="admin-module-lead">Banner hiển thị trên website.</p>
                    </div>
                    <a href="<?php echo URLROOT; ?>/admin/ads/add" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Thêm quảng cáo
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table admin-table align-middle">
                        <thead>
                            <tr>
                                <th>Banner</th>
                                <th>Thông tin</th>
                                <th>Vị trí</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['ads'])): ?>
                                <?php foreach ($data['ads'] as $ad): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo htmlspecialchars(URLROOT . $ad->image_url, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars((string) $ad->title, ENT_QUOTES, 'UTF-8'); ?>" class="rounded" style="max-width: 150px;">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars((string) $ad->title, ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <div class="small mt-1">
                                                <a href="<?php echo htmlspecialchars((string) $ad->link_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="text-break"><?php echo htmlspecialchars((string) $ad->link_url, ENT_QUOTES, 'UTF-8'); ?></a>
                                            </div>
                                        </td>
                                        <td><span class="badge rounded-pill badge-soft-primary"><?php echo htmlspecialchars((string) $ad->position, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td>
                                            <?php if ($ad->status === 'active'): ?>
                                                <span class="pill-badge pill-status-replied">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="pill-badge pill-priority-normal">Tạm dừng</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-muted">
                                            <div>Bắt đầu: <?php echo $ad->start_at ? htmlspecialchars(date('d/m/Y', strtotime($ad->start_at)), ENT_QUOTES, 'UTF-8') : '—'; ?></div>
                                            <div>Kết thúc: <?php echo $ad->end_at ? htmlspecialchars(date('d/m/Y', strtotime($ad->end_at)), ENT_QUOTES, 'UTF-8') : '—'; ?></div>
                                        </td>
                                        <td class="text-end admin-post-actions">
                                            <a href="<?php echo URLROOT; ?>/admin/ads/edit/<?php echo (int) $ad->id; ?>" class="btn btn-soft btn-sm btn-icon" title="Sửa"><i class="fas fa-edit"></i></a>
                                            <form action="<?php echo URLROOT; ?>/admin/ads/delete/<?php echo (int) $ad->id; ?>" method="POST" class="d-inline" onsubmit="return confirm('Xóa quảng cáo này?');">
                                                <button type="submit" class="btn btn-soft btn-sm btn-icon text-danger" title="Xóa"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Chưa có quảng cáo.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>
