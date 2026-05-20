<?php require APPROOT . '/views/layouts/client/header.php'; ?>

<div class="bg-gray-950 min-h-screen py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-12 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-4xl font-black text-white tracking-tight mb-2">DASHBOARD <span class="text-cyan-400">CỦA BẠN</span></h2>
                <p class="text-gray-400">Chào mừng trở lại, <span class="text-white font-bold"><?php echo $_SESSION['user_name']; ?></span>. Quản lý các dịch vụ và đơn hàng của bạn tại đây.</p>
            </div>
            <div class="flex items-center gap-4 bg-gray-900/50 p-4 rounded-2xl border border-white/5">
                <div class="w-12 h-12 bg-cyan-500/10 rounded-xl flex items-center justify-center text-cyan-400">
                    <i class="fa-solid fa-wallet text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Số dư hiện tại</p>
                    <p class="text-xl font-black text-white"><?php echo number_format($_SESSION['user_credit'] ?? 0, 0, ',', '.'); ?>đ</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-gray-900/50 border border-white/5 rounded-[32px] p-8">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-xl font-bold text-white flex items-center gap-3">
                            <i class="fa-solid fa-microchip text-cyan-500"></i> Server đang hoạt động
                        </h3>
                    </div>

                    <?php if(empty($data['services'])): ?>
                        <div class="py-12 text-center">
                            <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-600">
                                <i class="fa-solid fa-ghost text-2xl"></i>
                            </div>
                            <p class="text-gray-500">Bạn chưa có server nào đang hoạt động.</p>
                            <a href="<?php echo URLROOT; ?>/products" class="text-cyan-400 hover:underline mt-2 inline-block font-bold">Thuê server ngay</a>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach($data['services'] as $s): ?>
                                <div class="bg-gray-800/40 border border-gray-700 p-5 rounded-2xl hover:border-cyan-500/30 transition-all">
                                    <div class="flex justify-between items-start mb-3">
                                        <span class="text-xs font-bold text-emerald-400 uppercase tracking-widest italic">● Online</span>
                                        <span class="text-xs text-gray-500">Hết hạn: <?php echo date('d/m/Y', strtotime($s->expires_at)); ?></span>
                                    </div>
                                    <h4 class="text-white font-bold mb-1"><?php echo $s->product_name; ?></h4>
                                    <p class="text-sm font-mono text-gray-400"><?php echo $s->ip_address; ?>:<?php echo $s->port; ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="space-y-8">
                <div class="bg-gray-900/50 border border-white/5 rounded-[32px] p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-white">Đơn hàng <span class="text-cyan-400">gần đây</span></h3>
                    </div>
                    
                    <div class="space-y-4">
                        <p class="text-gray-400 text-sm leading-relaxed mb-6">
                            Theo dõi trạng thái thiết lập Server và lịch sử thanh toán của bạn.
                        </p>
                        
                        <a href="<?php echo URLROOT; ?>/users/orders" class="group flex items-center justify-between p-5 bg-gray-800/40 border border-gray-700 rounded-2xl hover:bg-cyan-500 hover:border-cyan-500 transition-all duration-300">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-gray-700 group-hover:bg-white/20 rounded-xl flex items-center justify-center text-cyan-400 group-hover:text-white transition-colors">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                                <span class="text-white font-bold text-sm">Xem lịch sử đơn hàng</span>
                            </div>
                            <i class="fa-solid fa-chevron-right text-gray-500 group-hover:text-white transition-colors"></i>
                        </a>

                        <div class="pt-4 border-t border-gray-800 mt-4">
                            <p class="text-[10px] uppercase font-black text-gray-600 tracking-[0.2em] mb-4 text-center">Hỗ trợ kỹ thuật 24/7</p>
                            <a href="<?php echo URLROOT; ?>/contact" class="block w-full py-3 text-center rounded-xl border border-gray-800 text-gray-400 hover:text-white hover:border-gray-600 transition-all text-sm font-bold">
                                Gửi yêu cầu hỗ trợ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/layouts/client/footer.php'; ?>