<?php require APPROOT . '/views/layouts/client/header.php'; ?>
<div class="min-h-[80vh] flex items-center justify-center bg-gray-950 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
  <!-- Decorative blobs -->
  <div class="absolute top-1/4 right-1/4 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl"></div>
  <div class="absolute bottom-1/4 left-1/4 w-64 h-64 bg-cyan-500/20 rounded-full blur-3xl"></div>

  <div class="max-w-md w-full space-y-8 bg-gray-900/50 backdrop-blur-xl p-10 rounded-3xl border border-white/5 relative z-10">
    <div>
      <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto shadow-lg shadow-cyan-500/20">
          <i class="fa-solid fa-user-plus text-white text-2xl"></i>
      </div>
      <h2 class="mt-6 text-center text-3xl font-bold text-white tracking-tight">
        Tạo tài khoản mới
      </h2>
      <p class="mt-2 text-center text-sm text-gray-400">
        Đã có tài khoản? 
        <a href="<?php echo URLROOT; ?>/users/login" class="font-medium text-cyan-400 hover:text-cyan-300 transition-colors">
          Đăng nhập ngay
        </a>
      </p>
    </div>
    <form class="mt-8 space-y-4" action="<?php echo URLROOT; ?>/users/register" method="POST">
      <input type="hidden" name="csrf_token" value="<?php echo $data['csrf_token'] ?? ''; ?>">
      <div>
        <label class="block text-sm font-medium text-gray-400 mb-2">Tên đăng nhập</label>
        <input name="username" type="text" class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition-all <?php echo (!empty($data['username_err'])) ? 'border-red-500 bg-red-500/5' : ''; ?>" placeholder="Nhập tên đăng nhập" value="<?php echo $data['username']; ?>">
        <span class="text-[10px] text-red-500 mt-1 block ml-1 uppercase font-bold"><?php echo $data['username_err']; ?></span>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-400 mb-2">Email</label>
        <input name="email" type="email" class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition-all <?php echo (!empty($data['email_err'])) ? 'border-red-500 bg-red-500/5' : ''; ?>" placeholder="example@email.com" value="<?php echo $data['email']; ?>">
        <span class="text-[10px] text-red-500 mt-1 block ml-1 uppercase font-bold"><?php echo $data['email_err']; ?></span>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-400 mb-2">Mật khẩu</label>
        <input name="password" type="password" class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition-all <?php echo (!empty($data['password_err'])) ? 'border-red-500 bg-red-500/5' : ''; ?>" placeholder="••••••••">
        <span class="text-[10px] text-red-500 mt-1 block ml-1 uppercase font-bold"><?php echo $data['password_err']; ?></span>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-400 mb-2">Xác nhận mật khẩu</label>
        <input name="confirm_password" type="password" class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500 transition-all <?php echo (!empty($data['confirm_password_err'])) ? 'border-red-500 bg-red-500/5' : ''; ?>" placeholder="••••••••">
        <span class="text-[10px] text-red-500 mt-1 block ml-1 uppercase font-bold"><?php echo $data['confirm_password_err']; ?></span>
      </div>

      <div class="pt-4">
        <button type="submit" class="w-full flex justify-center py-4 px-4 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-cyan-500/25 active:scale-[0.98]">
          Đăng ký tài khoản
        </button>
      </div>
    </form>
  </div>
</div>
<?php require APPROOT . '/views/layouts/client/footer.php'; ?>
