<?php require APPROOT . '/views/layouts/client/header.php'; ?>

<div class="bg-gray-950 py-24 min-h-[80vh]">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
  <div class="mb-12">
    <a href="<?php echo URLROOT; ?>/products" class="inline-flex items-center text-cyan-400 hover:text-cyan-300 transition-colors font-bold text-sm uppercase tracking-widest">
      <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại sản phẩm
    </a>
  </div>

  <div class="bg-gray-900/50 backdrop-blur-xl border border-white/5 rounded-[40px] overflow-hidden shadow-2xl">
    <div class="lg:flex">
    <div class="lg:w-1/2 p-8 lg:p-12 flex items-center justify-center bg-gray-800/30">
      <?php
      $prodImg = '';
      if (!empty($data['product']->image)) {
        $prodImg = $data['product']->image;
      } elseif (!empty($data['product']->image_url)) {
        $prodImg = $data['product']->image_url;
      }
      if ($prodImg):
        // handle absolute URLs / data URIs or build safe uploads URL
        if (preg_match('#^https?://#i', $prodImg) || strpos($prodImg, 'data:') === 0) {
          $prodImgUrl = $prodImg;
        } else {
          $segments = explode('/', ltrim($prodImg, '/'));
          $encSegments = array_map('rawurlencode', $segments);
          // support media/ and uploads/ (and other relative folders)
          $prodImgUrl = rtrim(URLROOT, '/') . '/' . implode('/', $encSegments);
        }
      ?>
        <img src="<?php echo htmlspecialchars($prodImgUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($data['product']->name, ENT_QUOTES, 'UTF-8'); ?>" class="w-full h-auto object-cover rounded-[32px] shadow-2xl transition-transform hover:scale-105 duration-700">
      <?php else: ?>
        <div class="w-full aspect-square bg-gray-800/50 rounded-[32px] flex items-center justify-center border border-white/5">
          <i class="fa-solid fa-server text-9xl text-gray-700"></i>
        </div>
      <?php endif; ?>
    </div>
    <div class="p-8 lg:p-16 lg:w-1/2">
      <div class="inline-block px-4 py-1 bg-cyan-500/10 border border-cyan-500/20 rounded-full text-[10px] font-bold text-cyan-400 uppercase tracking-[0.2em] mb-6">
      Dịch vụ lưu trữ
      </div>
      <h2 class="text-4xl lg:text-5xl font-black text-white mb-6 tracking-tight leading-tight">
      <?php echo htmlspecialchars($data['product']->name, ENT_QUOTES, 'UTF-8'); ?>
      </h2>
          
      <div class="flex items-baseline gap-2 mb-10">
        <span class="text-4xl font-black text-white"><?php echo number_format($data['product']->price, 0, ',', '.'); ?>đ</span>
        <span class="text-gray-500 font-medium">/tháng</span>
      </div>
          
      <div class="prose prose-invert prose-cyan text-gray-400 leading-relaxed mb-10">
      <?php echo nl2br(htmlspecialchars($data['product']->description, ENT_QUOTES, 'UTF-8')); ?>
      </div>

      <div class="flex flex-col sm:flex-row items-center gap-4 mb-10">
      <form action="<?php echo htmlspecialchars(URLROOT, ENT_QUOTES, 'UTF-8'); ?>/cart/add/<?php echo htmlspecialchars($data['product']->id, ENT_QUOTES, 'UTF-8'); ?>" method="POST" class="w-full flex gap-4">
        <input type="number" name="quantity" min="1" max="100" value="1" class="w-24 bg-gray-800 border border-gray-700 rounded-xl px-4 py-4 text-white text-center font-bold focus:ring-2 focus:ring-cyan-500/50 outline-none">
        <button type="submit" class="flex-1 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold py-4 px-8 rounded-xl transition-all shadow-lg shadow-cyan-500/25 active:scale-[0.98] flex items-center justify-center gap-3">
        <i class="fa-solid fa-cart-plus"></i> Thuê ngay bây giờ
        </button>
      </form>
      </div>
          
      <div class="grid grid-cols-2 gap-4 border-t border-gray-800 pt-10">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-500">
          <i class="fa-solid fa-check text-xs"></i>
        </div>
        <span class="text-sm text-gray-400 font-medium">Khởi tạo tức thì</span>
      </div>
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-500">
          <i class="fa-solid fa-check text-xs"></i>
        </div>
        <span class="text-sm text-gray-400 font-medium">Hỗ trợ 24/7</span>
      </div>
      </div>
    </div>
    </div>
  </div>
  </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 mb-12">
  <div class="border-t border-gray-800 pt-16">
    <div class="flex items-center justify-between mb-8">
      <h3 class="text-2xl font-bold text-white flex items-center gap-3">
        <i class="fa-solid fa-star text-yellow-500"></i> Đánh giá khách hàng
      </h3>
      <?php if(!empty($data['reviews']) && count($data['reviews']) > 0): ?>
        <div class="bg-gray-900 border border-gray-800 px-4 py-2 rounded-xl flex items-center gap-2">
          <span class="text-2xl font-black text-white"><?php echo isset($data['avgRating']) ? htmlspecialchars($data['avgRating']) : '0'; ?></span>
          <i class="fa-solid fa-star text-yellow-500"></i>
          <span class="text-gray-500 text-sm">(<?php echo count($data['reviews']); ?> đánh giá)</span>
        </div>
      <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 space-y-4">
        <?php if (empty($data['reviews'])): ?>
          <div class="bg-gray-900/50 border border-gray-800 rounded-2xl p-8 text-center">
            <i class="fa-regular fa-comments text-4xl text-gray-600 mb-3"></i>
            <p class="text-gray-400">Chưa có đánh giá nào cho Server này.</p>
          </div>
        <?php else: ?>
          <?php foreach($data['reviews'] as $rev): ?>
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 flex gap-4">
              <div class="flex-shrink-0">
                <?php if(!empty($rev->avatar)):
                    if (preg_match('#^https?://#i', $rev->avatar) || strpos($rev->avatar, 'data:') === 0) {
                      $avatarUrl = $rev->avatar;
                    } else {
                      $avSeg = explode('/', ltrim($rev->avatar, '/'));
                      $avEnc = array_map('rawurlencode', $avSeg);
                      $avatarUrl = rtrim(URLROOT, '/') . '/' . implode('/', $avEnc);
                    }
                ?>
                  <img src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>" class="w-12 h-12 rounded-full object-cover border border-gray-700" alt="<?php echo htmlspecialchars($rev->full_name ?: $rev->username, ENT_QUOTES, 'UTF-8'); ?>">
                <?php else: ?>
                  <div class="w-12 h-12 rounded-full bg-gray-800 flex items-center justify-center text-gray-400">
                    <i class="fa-solid fa-user"></i>
                  </div>
                <?php endif; ?>
              </div>
              <div class="flex-1">
                <div class="flex items-center justify-between mb-1">
                  <h4 class="text-white font-bold"><?php echo htmlspecialchars($rev->full_name ?: $rev->username, ENT_QUOTES, 'UTF-8'); ?></h4>
                  <span class="text-xs text-gray-500"><?php echo date('d/m/Y', strtotime($rev->created_at)); ?></span>
                </div>
                <div class="text-yellow-500 text-xs mb-3">
                  <?php for($i=1; $i<=5; $i++): ?>
                    <i class="fa-<?php echo $i <= $rev->rating ? 'solid' : 'regular'; ?> fa-star"></i>
                  <?php endfor; ?>
                </div>
                <p class="text-gray-300 text-sm leading-relaxed"><?php echo nl2br(htmlspecialchars($rev->comment, ENT_QUOTES, 'UTF-8')); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="lg:col-span-1">
        <div class="bg-gradient-to-b from-gray-900 to-gray-950 border border-gray-800 rounded-2xl p-6 sticky top-24">
          <?php if (empty($_SESSION['user_id'])): ?>
            <div class="text-center py-6">
              <i class="fa-solid fa-lock text-3xl text-gray-600 mb-3"></i>
              <p class="text-gray-400 text-sm mb-4">Vui lòng đăng nhập để đánh giá.</p>
              <a href="<?php echo URLROOT; ?>/users/login" class="inline-block bg-gray-800 text-white px-6 py-2 rounded-xl text-sm font-bold hover:bg-gray-700">Đăng nhập</a>
            </div>
          <?php elseif (!empty($data['hasReviewed'])): ?>
            <div class="text-center py-6">
              <div class="w-12 h-12 bg-emerald-500/10 rounded-full flex items-center justify-center text-emerald-500 mx-auto mb-3">
                <i class="fa-solid fa-check"></i>
              </div>
              <p class="text-emerald-400 font-bold">Cảm ơn bạn!</p>
              <p class="text-gray-500 text-sm mt-1">Bạn đã gửi đánh giá cho sản phẩm này.</p>
            </div>
          <?php elseif (isset($data['canReview']) && !$data['canReview']): ?>
            <div class="text-center py-6">
              <i class="fa-solid fa-cart-shopping text-3xl text-gray-600 mb-3"></i>
              <p class="text-gray-400 text-sm">Bạn cần mua và sử dụng Server này trước khi để lại đánh giá.</p>
            </div>
          <?php else: ?>
            <form action="<?php echo URLROOT; ?>/products/submitReview" method="POST">
              <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($data['product']->id, ENT_QUOTES, 'UTF-8'); ?>">
              <input type="hidden" name="slug" value="<?php echo htmlspecialchars($data['product']->slug, ENT_QUOTES, 'UTF-8'); ?>">
                            
              <h4 class="text-white font-bold mb-4">Viết đánh giá của bạn</h4>
                            
              <div class="mb-4">
                <label class="text-gray-400 text-sm block mb-2">Chất lượng Server</label>
                <select name="rating" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-yellow-500 font-bold focus:outline-none focus:border-cyan-500">
                  <option value="5">⭐⭐⭐⭐⭐ Tuyệt vời</option>
                  <option value="4">⭐⭐⭐⭐ Tốt</option>
                  <option value="3">⭐⭐⭐ Bình thường</option>
                  <option value="2">⭐⭐ Tệ</option>
                  <option value="1">⭐ Rất tệ</option>
                </select>
              </div>

              <div class="mb-4">
                <label class="text-gray-400 text-sm block mb-2">Nhận xét chi tiết</label>
                <textarea name="comment" rows="4" required placeholder="Ping thấp, chạy mượt..." class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-500 resize-none"></textarea>
              </div>

              <button type="submit" class="w-full bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-3 rounded-xl transition-colors">
                Gửi đánh giá
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($data['relatedProducts'])): ?>
  <div class="mt-24 border-t border-gray-800 pt-16">
    <h3 class="text-2xl font-bold text-white mb-8">
      <i class="fa-solid fa-server text-cyan-500 mr-3"></i>Khách hàng cũng xem
    </h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
          <?php foreach ($data['relatedProducts'] as $p): ?>
      <a href="<?php echo URLROOT; ?>/products/show/<?php echo htmlspecialchars($p->slug, ENT_QUOTES, 'UTF-8'); ?>" class="bg-gray-900/50 border border-gray-800 rounded-2xl p-4 hover:border-cyan-500/50 hover:bg-gray-800/80 transition-all duration-300 group block">
        <div class="h-40 rounded-xl bg-gray-800 mb-4 overflow-hidden flex items-center justify-center border border-gray-700">
          <?php
          $img = !empty($p->image_url) ? $p->image_url : (!empty($p->image) ? $p->image : '');
          if (!empty($img)):
          if (preg_match('#^https?://#i', $img) || strpos($img, 'data:') === 0) {
            $imgPath = $img;
          } else {
            $segments = explode('/', ltrim($img, '/'));
            $encSegments = array_map('rawurlencode', $segments);
            $imgPath = rtrim(URLROOT, '/') . '/' . implode('/', $encSegments);
          }
          ?>
            <img src="<?php echo htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
          <?php else: ?>
            <i class="fa-solid fa-server text-4xl text-gray-600"></i>
          <?php endif; ?>
        </div>
        <h4 class="text-white font-bold text-base mb-1 truncate group-hover:text-cyan-400 transition-colors"><?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?></h4>
        <p class="text-cyan-400 font-bold text-sm"><?php echo number_format($p->price, 0, ',', '.'); ?>đ/tháng</p>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<?php require APPROOT . '/views/layouts/client/footer.php'; ?>
