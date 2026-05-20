<?php require APPROOT . '/views/layouts/client/header.php'; ?>
<div class="bg-gray-950 min-h-screen py-24">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-white mb-8">Lịch sử <span class="text-cyan-400">thuê Server</span></h2>
        
        <div class="bg-gray-900/50 border border-white/5 rounded-3xl overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-800/50 text-gray-400 text-sm uppercase">
                    <tr>
                        <th class="px-6 py-4">Mã đơn</th>
                        <th class="px-6 py-4">Ngày đặt</th>
                        <th class="px-6 py-4">Tổng tiền</th>
                        <th class="px-6 py-4">Trạng thái</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php foreach($data['orders'] as $order): ?>
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-5 font-bold text-cyan-400">#<?php echo $order->id; ?></td>
                        <td class="px-6 py-5 text-gray-400"><?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?></td>
                        <td class="px-6 py-5 text-white font-medium"><?php echo number_format($order->total_amount, 0, ',', '.'); ?>đ</td>
                        <td class="px-6 py-5">
                            <?php 
                                $statusMap = [
                                    'pending' => ['bg-amber-500/10', 'text-amber-500', 'Chờ xử lý'],
                                    'processing' => ['bg-blue-500/10', 'text-blue-500', 'Đang thiết lập'],
                                    'completed' => ['bg-emerald-500/10', 'text-emerald-500', 'Hoàn tất'],
                                    'cancelled' => ['bg-rose-500/10', 'text-rose-500', 'Đã hủy']
                                ];
                                $st = $statusMap[$order->status] ?? ['bg-gray-500/10', 'text-gray-500', $order->status];
                            ?>
                            <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $st[0] . ' ' . $st[1]; ?>">
                                <?php echo $st[2]; ?>
                            </span>
                        </td>
                        <td class="px-6 py-5 text-right">
                            <a href="<?php echo URLROOT; ?>/users/order_detail/<?php echo $order->id; ?>" class="text-gray-400 hover:text-white transition-colors">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i> Chi tiết
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require APPROOT . '/views/layouts/client/footer.php'; ?>