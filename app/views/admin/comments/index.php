<?php require APPROOT . '/views/layouts/admin/header.php'; ?>

<div class="content-header">
    <div class="header-left">
        <h1>Quản lý bình luận</h1>
        <p>Duyệt, ẩn hoặc xóa các bình luận của người dùng trên bài viết.</p>
    </div>
</div>

<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Người dùng</th>
                <th>Bài viết</th>
                <th>Nội dung</th>
                <th>Trạng thái</th>
                <th>Ngày gửi</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($data['comments'])): ?>
                <tr>
                    <td colspan="7" class="text-center">Chưa có bình luận nào.</td>
                </tr>
            <?php else: ?>
                <?php foreach($data['comments'] as $comment): ?>
                    <tr>
                        <td><?php echo $comment->id; ?></td>
                        <td><strong><?php echo $comment->username; ?></strong></td>
                        <td>
                            <div class="news-ref"><?php echo $comment->news_title; ?></div>
                        </td>
                        <td>
                            <div class="comment-preview" title="<?php echo htmlspecialchars($comment->comment); ?>">
                                <?php echo substr(htmlspecialchars($comment->comment), 0, 100); ?><?php echo strlen($comment->comment) > 100 ? '...' : ''; ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $comment->status; ?>">
                                <?php 
                                    if($comment->status == 'approved') echo 'Đã duyệt';
                                    elseif($comment->status == 'pending') echo 'Chờ duyệt';
                                    else echo 'Đã ẩn';
                                ?>
                            </span>
                        </td>
                        <td><?php echo date('H:i d/m/Y', strtotime($comment->created_at)); ?></td>
                        <td class="actions">
                            <?php if($comment->status != 'approved'): ?>
                                <form action="<?php echo URLROOT; ?>/admin/comments/approve/<?php echo $comment->id; ?>" method="POST">
                                    <button type="submit" class="btn-icon approve" title="Duyệt">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <form action="<?php echo URLROOT; ?>/admin/comments/hide/<?php echo $comment->id; ?>" method="POST">
                                    <button type="submit" class="btn-icon hide" title="Ẩn">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <form action="<?php echo URLROOT; ?>/admin/comments/delete/<?php echo $comment->id; ?>" method="POST" onsubmit="return confirm('Xóa bình luận này?')">
                                <button type="submit" class="btn-icon delete" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
/* Reusing table styles */
.news-ref {
    max-width: 200px;
    font-size: 0.85rem;
    color: #3a7bd5;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.comment-preview {
    max-width: 300px;
    font-size: 0.9rem;
    color: #ccc;
}

.status-badge.approved { background: rgba(40, 167, 69, 0.1); color: #28a745; }
.status-badge.pending { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
.status-badge.hidden { background: rgba(220, 53, 69, 0.1); color: #dc3545; }

.btn-icon.approve:hover { background: #28a745; color: #fff; }
.btn-icon.hide:hover { background: #6c757d; color: #fff; }

.admin-table td {
    padding: 15px;
}
</style>

<?php require APPROOT . '/views/layouts/admin/footer.php'; ?>
