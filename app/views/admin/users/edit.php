<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<?php
$user = $data['user'] ?? null;
$errors = $data['errors'] ?? [];
?>

<?php if (!$user): ?>
    <div class="alert alert-danger">Không tìm thấy người dùng.</div>
    <a href="<?php echo URLROOT; ?>/admin/users" class="btn btn-outline-light">Quay lại</a>
<?php else: ?>
    <div class="row g-3">
        <div class="col-12">
            <section class="card panel-card">
                <div class="card-body">
                    <div class="panel-header">
                        <h2 class="panel-title">Chỉnh sửa hồ sơ thành viên</h2>
                    </div>

                    <form method="POST" action="<?php echo URLROOT; ?>/admin/editUser/<?php echo (int) $user->id; ?>" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_admin'] ?? ''); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label">Họ và tên</label>
                                <input
                                    type="text"
                                    id="full_name"
                                    name="full_name"
                                    class="form-control <?php echo !empty($errors['full_name']) ? 'is-invalid' : ''; ?>"
                                    value="<?php echo htmlspecialchars($user->full_name ?? ''); ?>"
                                    maxlength="100"
                                    required
                                >
                                <?php if (!empty($errors['full_name'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['full_name']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control <?php echo !empty($errors['email']) ? 'is-invalid' : ''; ?>"
                                    value="<?php echo htmlspecialchars($user->email ?? ''); ?>"
                                    maxlength="100"
                                    required
                                >
                                <?php if (!empty($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="role" class="form-label">Vai trò</label>
                                <select id="role" name="role" class="form-select <?php echo !empty($errors['role']) ? 'is-invalid' : ''; ?>" required>
                                    <option value="member" <?php echo ($user->role ?? 'member') === 'member' ? 'selected' : ''; ?>>Thành viên</option>
                                    <option value="admin" <?php echo ($user->role ?? '') === 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                                </select>
                                <?php if (!empty($errors['role'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['role']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">Trạng thái</label>
                                <select id="status" name="status" class="form-select <?php echo !empty($errors['status']) ? 'is-invalid' : ''; ?>" required>
                                    <option value="active" <?php echo ($user->status ?? 'active') === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                                    <option value="banned" <?php echo ($user->status ?? '') === 'banned' ? 'selected' : ''; ?>>Đã khóa</option>
                                </select>
                                <?php if (!empty($errors['status'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['status']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="settings-actions mt-4">
                            <a href="<?php echo URLROOT; ?>/admin/users" class="btn btn-outline-light">Hủy</a>
                            <button type="submit" class="btn btn-primary">Lưu cập nhật</button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
<?php endif; ?>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>
