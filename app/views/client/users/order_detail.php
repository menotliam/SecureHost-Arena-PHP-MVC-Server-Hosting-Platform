<?php require APPROOT . '/views/layouts/client/header.php'; ?>
<div class="bg-gray-950 py-24 min-h-screen">
    <div class="max-w-4xl mx-auto px-4">
        <div class="mb-8 flex items-center justify-between">
            <h2 class="text-2xl font-bold text-white">Chi tiết đơn hàng <span class="text-cyan-400">#<?php echo $data['order']->id; ?></span></h2>
            <a href="<?php echo URLROOT; ?>/users/orders" class="text-sm text-gray-400 hover:text-cyan-400">Quay lại danh sách</a>
        </div>

        <div class="bg-gray-900/50 border border-white/5 rounded-3xl p-8 mb-8">
            <?php 
                $status = $data['order']->status;
                $steps = ['pending' => 1, 'processing' => 2, 'completed' => 3];
                $currentStep = $steps[$status] ?? 0;
                if($status == 'cancelled') $currentStep = -1;
            ?>
            
            <div class="relative flex items-center justify-between">
                <?php if($currentStep == -1): ?>
                    <div class="w-full text-center py-4 bg-rose-500/10 text-rose-500 rounded-xl font-bold border border-rose-500/20">
                        Đơn hàng này đã bị hủy.
                    </div>
                <?php else: ?>
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-gray-800"></div>
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-cyan-500 transition-all duration-500" style="width: <?php echo (($currentStep - 1) / 2) * 100; ?>%"></div>
                    
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center <?php echo $currentStep >= 1 ? 'bg-cyan-500 text-white' : 'bg-gray-800 text-gray-500'; ?>">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <span class="mt-2 text-xs font-bold text-gray-400 uppercase">Chờ xác nhận</span>
                    </div>
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center <?php echo $currentStep >= 2 ? 'bg-cyan-500 text-white' : 'bg-gray-800 text-gray-500'; ?>">
                            <i class="fa-solid fa-gears"></i>
                        </div>
                        <span class="mt-2 text-xs font-bold text-gray-400 uppercase">Đang thiết lập</span>
                    </div>
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center <?php echo $currentStep >= 3 ? 'bg-emerald-500 text-white' : 'bg-gray-800 text-gray-500'; ?>">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <span class="mt-2 text-xs font-bold text-gray-400 uppercase">Hoàn tất</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-gray-900 border border-white/5 rounded-3xl p-6">
            <h3 class="text-lg font-bold text-white mb-4">Sản phẩm đã thuê</h3>
            <?php foreach($data['items'] as $item): ?>
                <div class="flex items-center justify-between py-4 border-b border-gray-800 last:border-0">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gray-800 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-server text-cyan-500"></i>
                        </div>
                        <div>
                            <p class="text-white font-bold"><?php echo $item->name; ?></p>
                            <p class="text-xs text-gray-500">Thời hạn: <?php echo $item->duration_months; ?> tháng</p>
                        </div>
                    </div>
                    <p class="text-white font-medium"><?php echo number_format($item->price, 0, ',', '.'); ?>đ</p>
                </div>
            <?php endforeach; ?>
            <div class="mt-6 pt-6 border-t border-gray-800 flex justify-between items-center">
                <span class="text-gray-400">Tổng cộng:</span>
                <span class="text-2xl font-black text-cyan-400"><?php echo number_format($data['order']->total_amount, 0, ',', '.'); ?>đ</span>
            </div>
        </div>
    </div>
</div>
<?php require APPROOT . '/views/layouts/client/footer.php'; ?>