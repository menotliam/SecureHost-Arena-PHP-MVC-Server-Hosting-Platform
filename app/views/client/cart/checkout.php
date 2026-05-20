<?php require APPROOT . '/views/layouts/client/header.php'; ?>

<div class="bg-gray-950 min-h-[80vh] py-24">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="text-4xl font-extrabold text-white mb-8 tracking-tight">
      Thanh toán <span class="text-cyan-400">đơn hàng</span>
    </h2>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'missing_info'): ?>
        <div class="bg-rose-500/10 border border-rose-500/50 text-rose-500 px-6 py-4 rounded-xl mb-8 flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation"></i>
            Vui lòng điền đầy đủ thông tin địa chỉ và số điện thoại!
        </div>
    <?php endif; ?>

    <div class="bg-gray-900/50 backdrop-blur-xl border border-white/5 p-8 rounded-3xl shadow-2xl">
        <form action="<?php echo URLROOT; ?>/cart/checkout" method="POST">
            
            <div class="mb-6">
                <label for="phone" class="block text-sm font-bold text-gray-400 mb-2">Số điện thoại liên hệ <span class="text-rose-500">*</span></label>
                <input type="text" id="phone" name="phone" required 
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 outline-none transition-all"
                       placeholder="Nhập số điện thoại của bạn...">
            </div>
            
            <div class="mb-8">
                <label for="address" class="block text-sm font-bold text-gray-400 mb-2">Địa chỉ thanh toán / Ghi chú Server <span class="text-rose-500">*</span></label>
                <textarea id="address" name="address" rows="4" required 
                          class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 outline-none transition-all"
                          placeholder="Nhập địa chỉ hoặc yêu cầu cài đặt cho server (nếu có)..."></textarea>
            </div>

            <div class="flex items-center justify-between border-t border-gray-800 pt-6">
                <a href="<?php echo URLROOT; ?>/cart" class="text-gray-400 hover:text-white transition-colors flex items-center">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại giỏ hàng
                </a>
                <button type="submit" class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold py-3 px-8 rounded-xl transition-all shadow-lg shadow-cyan-500/25 active:scale-[0.98]">
                    Xác nhận đặt hàng
                </button>
            </div>
            
        </form>
    </div>
  </div>
</div>

<?php require APPROOT . '/views/layouts/client/footer.php'; ?>