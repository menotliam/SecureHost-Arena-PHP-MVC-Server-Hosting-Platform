<?php require APPROOT . '/views/layouts/client/header.php'; ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="bg-gray-950 min-h-[80vh] py-24">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h2 class="text-4xl font-extrabold text-white mb-12 tracking-tight">
      Giỏ hàng <span class="text-cyan-400">của bạn</span>
    </h2>

    <div id="emptyCartDiv" class="<?php echo empty($data['cartItems']) ? '' : 'hidden'; ?>">
      <div class="bg-gray-900/50 backdrop-blur-xl border border-white/5 p-16 text-center rounded-3xl">
        <div class="w-24 h-24 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-8">
            <i class="fa-solid fa-cart-shopping text-4xl text-gray-600"></i>
        </div>
        <h3 class="text-2xl font-bold text-white mb-4">Giỏ hàng trống</h3>
        <p class="text-gray-400 mb-10">Bạn chưa thêm sản phẩm nào vào giỏ hàng của mình.</p>
        <a href="<?php echo URLROOT; ?>/products" class="inline-block bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white px-10 py-4 rounded-xl font-bold transition-all shadow-lg shadow-cyan-500/25">Khám phá sản phẩm</a>
      </div>
    </div>

    <div id="fullCartDiv" class="<?php echo !empty($data['cartItems']) ? '' : 'hidden'; ?>">
      <div class="bg-gray-900/50 backdrop-blur-xl border border-white/5 rounded-3xl overflow-hidden shadow-2xl">
        <form action="<?php echo URLROOT; ?>/cart/update" method="POST" id="cartForm">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-800">
              <thead class="bg-gray-800/50">
                <tr>
                  <th scope="col" class="px-8 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Sản phẩm</th>
                  <th scope="col" class="px-8 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Đơn giá</th>
                  <th scope="col" class="px-8 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Số lượng</th>
                  <th scope="col" class="px-8 py-5 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Thành tiền</th>
                  <th scope="col" class="px-8 py-5 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Thao tác</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-800" id="cartTableBody">
                <?php foreach($data['cartItems'] as $item): ?>
                  <tr class="hover:bg-white/5 transition-colors" id="cart-item-<?php echo $item->id; ?>">
                    <td class="px-8 py-6 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-12 w-12 rounded-xl overflow-hidden border border-gray-700">
                          <?php if($item->image_url): ?>
                              <img class="h-full w-full object-cover" src="<?php echo URLROOT . '/' . $item->image_url; ?>" alt="">
                          <?php else: ?>
                              <div class="h-full w-full bg-gray-800 flex items-center justify-center">
                                  <i class="fa-solid fa-server text-gray-600"></i>
                              </div>
                          <?php endif; ?>
                        </div>
                        <div class="ml-4">
                          <div class="text-base font-bold text-white"><?php echo $item->name; ?></div>
                        </div>
                      </div>
                    </td>
                    <td class="px-8 py-6 whitespace-nowrap">
                      <div class="text-sm text-gray-300 font-medium"><?php echo number_format($item->price, 0, ',', '.'); ?>đ</div>
                    </td>
                    <td class="px-8 py-6 whitespace-nowrap">
                      <div class="flex items-center bg-gray-800 rounded-lg max-w-[120px] p-1 border border-gray-700">
                          <button type="button" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white hover:bg-gray-700 rounded-md transition-colors qty-btn-minus" data-id="<?php echo $item->id; ?>"><i class="fa-solid fa-minus text-xs"></i></button>
                          <input type="number" name="quantities[<?php echo $item->id; ?>]" id="qty-input-<?php echo $item->id; ?>" value="<?php echo $item->quantity; ?>" min="1" class="w-10 bg-transparent py-1 text-sm text-white text-center outline-none qty-input" data-id="<?php echo $item->id; ?>">
                          <button type="button" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-white hover:bg-gray-700 rounded-md transition-colors qty-btn-plus" data-id="<?php echo $item->id; ?>"><i class="fa-solid fa-plus text-xs"></i></button>
                      </div>
                    </td>
                    <td class="px-8 py-6 whitespace-nowrap text-sm font-bold text-cyan-400 subtotal-text" id="subtotal-<?php echo $item->id; ?>">
                      <?php echo number_format($item->subtotal, 0, ',', '.'); ?>đ
                    </td>
                    <td class="px-8 py-6 whitespace-nowrap text-right text-sm font-medium">
                      <button type="button" class="text-rose-500 hover:text-rose-400 transition-colors remove-btn" data-id="<?php echo $item->id; ?>">
                        <i class="fa-solid fa-trash-can"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="bg-gray-800/30 px-8 py-6 flex flex-col md:flex-row items-center justify-between gap-6 border-t border-gray-800">
            <div>
              <p class="text-gray-400 text-sm"><i class="fa-solid fa-circle-info text-cyan-500 mr-2"></i> Hệ thống tự động cập nhật giỏ hàng khi bạn thay đổi số lượng</p>
            </div>
            <div class="flex items-center gap-8">
              <div class="text-right">
                <span class="text-gray-500 text-sm font-medium uppercase tracking-wider block mb-1">Tổng cộng</span>
                <span class="text-3xl font-black text-white" id="cartTotalAmount"><?php echo number_format($data['totalAmount'], 0, ',', '.'); ?>đ</span>
              </div>
              <a href="<?php echo URLROOT; ?>/cart/checkout" class="bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white px-8 py-4 rounded-xl font-bold transition-all shadow-lg shadow-emerald-500/20 active:scale-95">
                Thanh toán ngay
              </a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Hàm gọi AJAX cập nhật số lượng
    const updateQuantity = (productId, newQty) => {
        const formData = new FormData();
        formData.append('productId', productId);
        formData.append('quantity', newQty);

        fetch('<?php echo URLROOT; ?>/cart/update', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // Cập nhật lại UI Total và Subtotal
                document.getElementById('subtotal-' + productId).innerText = data.itemSubtotal;
                document.getElementById('cartTotalAmount').innerText = data.totalAmount;
                
                // Hiển thị Toast thông báo nhẹ nhàng
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });
                Toast.fire({
                    icon: 'success',
                    title: 'Đã cập nhật giỏ hàng'
                });
            }
        })
        .catch(err => console.error(err));
    }

    // Sự kiện nút Tăng/Giảm
    document.querySelectorAll('.qty-btn-minus').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const input = document.getElementById('qty-input-' + id);
            let val = parseInt(input.value);
            if(val > 1) {
                val--;
                input.value = val;
                updateQuantity(id, val);
            }
        });
    });

    document.querySelectorAll('.qty-btn-plus').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const input = document.getElementById('qty-input-' + id);
            let val = parseInt(input.value);
            val++;
            input.value = val;
            updateQuantity(id, val);
        });
    });

    // Sự kiện gõ trực tiếp vào ô input (debounce để không spam request)
    let timeoutId;
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const id = this.getAttribute('data-id');
            let val = parseInt(this.value);
            if(isNaN(val) || val < 1) {
                val = 1;
                this.value = val;
            }
            
            timeoutId = setTimeout(() => {
                updateQuantity(id, val);
            }, 500); // Đợi 500ms sau khi ngừng gõ mới call API
        });
    });

    // Cập nhật Xóa sản phẩm
    document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            
            Swal.fire({
                title: 'Bỏ sản phẩm này?',
                text: "Bạn sẽ xóa gói Server này khỏi giỏ hàng!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#06b6d4', // cyan-500
                cancelButtonColor: '#1f2937', // gray-800
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Huỷ'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('<?php echo URLROOT; ?>/cart/remove/' + productId, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            // Xóa row tương ứng với hiệu ứng
                            const row = document.getElementById('cart-item-' + productId);
                            row.style.transition = 'all 0.4s ease';
                            row.style.opacity = '0';
                            row.style.transform = 'translateX(-20px)';
                            
                            setTimeout(() => {
                                row.remove();
                                document.getElementById('cartTotalAmount').innerText = data.totalAmount;
                                
                                // Nếu xoá hết thì hiện div empty
                                if(data.isEmpty) {
                                    document.getElementById('fullCartDiv').classList.add('hidden');
                                    document.getElementById('emptyCartDiv').classList.remove('hidden');
                                    // Bật animation fade in cho emptyDiv
                                    const emptyDiv = document.getElementById('emptyCartDiv');
                                    emptyDiv.style.opacity = '0';
                                    setTimeout(() => {
                                        emptyDiv.style.transition = 'opacity 0.5s ease';
                                        emptyDiv.style.opacity = '1';
                                    }, 50);
                                }
                            }, 400);

                            Swal.fire({
                                icon: 'success',
                                title: 'Đã xóa!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    })
                    .catch(err => console.error(err));
                }
            });
        });
    });

});
</script>

<?php require APPROOT . '/views/layouts/client/footer.php'; ?>