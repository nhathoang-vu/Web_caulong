<?php
// =================================================================================
// === TRANG TẠO PHIẾU NHẬP (FULL OPTION: AJAX SEARCH + AUTO PRICE) ===============
// =================================================================================
session_start();
require_once '../connect.php'; 

// --- 0. API AJAX: TÌM KIẾM SẢN PHẨM & LẤY GIÁ NHẬP ---
if (isset($_GET['action']) && $_GET['action'] == 'search_products') {
    header('Content-Type: application/json');
    
    $keyword = isset($_GET['q']) ? $_GET['q'] : '';
    $brand_id = isset($_GET['brand_id']) ? $_GET['brand_id'] : 0;

    try {
        // Lấy thông tin sản phẩm kèm giá nhập
        $sql = "SELECT 
                    bt.id as id, 
                    sp.id as sanpham_id,
                    sp.ten_sanpham, 
                    bt.mau_sac, 
                    bt.kich_thuoc,
                    sp.gia_nhap  
                FROM bienthe_sanpham bt
                JOIN sanpham sp ON bt.sanpham_id = sp.id
                WHERE sp.thuonghieu_id = :brand_id 
                AND sp.ten_sanpham LIKE :keyword
                LIMIT 20";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':brand_id' => $brand_id,
            ':keyword' => "%$keyword%"
        ]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        foreach ($products as $p) {
            $attr = $p['mau_sac'] . ' - ' . $p['kich_thuoc'];
            // Format kết quả trả về cho Select2
            $results[] = [
                'id' => $p['id'], 
                'text' => $p['ten_sanpham'] . " (" . $attr . ")",
                'spid' => $p['sanpham_id'],
                'name_sp' => $p['ten_sanpham'],
                'attr_sp' => $attr,
                'price_import' => $p['gia_nhap'] // Truyền giá nhập xuống Client
            ];
        }
        echo json_encode(['results' => $results]);
    } catch (Exception $e) {
        echo json_encode(['results' => []]);
    }
    exit; 
}

// --- 1. LẤY DANH SÁCH NHÀ CUNG CẤP ---
$nccs = $conn->query("SELECT * FROM thuonghieu")->fetchAll(PDO::FETCH_ASSOC);

// --- 2. XỬ LÝ LƯU PHIẾU NHẬP ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_import') {
    try {
        $thuonghieu_id = $_POST['thuonghieu_id'];
        $items = json_decode($_POST['items_json'], true);
        
        if (empty($items)) throw new Exception("Danh sách nhập hàng đang trống!");

        $ma_phieu = 'PN-' . date('Ymd-His'); 
        $ngay_tao = date('Y-m-d H:i:s');

        $conn->beginTransaction();

        foreach ($items as $item) {
            // Lưu vào bảng phiếu nhập
            $stmt = $conn->prepare("INSERT INTO phieunhap (ma_phieu, thuonghieu_id, sanpham_id, bienthe_id, so_luong, don_gia, thanh_tien, ngay_tao) 
                                    VALUES (:ma, :th, :sp, :bt, :sl, :dg, :tt, :nt)");
            $stmt->execute([
                ':ma' => $ma_phieu,
                ':th' => $thuonghieu_id,
                ':sp' => $item['sanpham_id'],
                ':bt' => $item['bienthe_id'],
                ':sl' => $item['so_luong'],
                ':dg' => $item['don_gia'],
                ':tt' => $item['thanh_tien'],
                ':nt' => $ngay_tao
            ]);

            // Cập nhật số lượng tồn kho
            $stmt_update = $conn->prepare("UPDATE bienthe_sanpham SET so_luong_ton = so_luong_ton + :sl WHERE id = :id");
            $stmt_update->execute([':sl' => $item['so_luong'], ':id' => $item['bienthe_id']]);
        }

        $conn->commit();
        // Chuyển hướng sang trang in phiếu (hoặc danh sách)
        header("Location: inphieunhap.php?ma_phieu=" . $ma_phieu);
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        $error_msg = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo Phiếu Nhập Kho</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <style>
        /* --- CSS GIỮ NGUYÊN 100% --- */
        body { font-family: 'Segoe UI', sans-serif; background-color: #f8fafc; color: #334155; margin: 0; }
        .wrap-content { padding: 30px; max-width: 1200px; margin: 0 auto; }
        
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; }
        .page-title { font-size: 24px; font-weight: 700; color: #0f172a; margin: 0; }
        .btn-back { text-decoration: none; color: #64748b; font-weight: 500; display: flex; align-items: center; gap: 5px; transition: 0.2s; }
        .btn-back:hover { color: #0f172a; }

        .grid-container { display: grid; grid-template-columns: 1fr 1.5fr; gap: 25px; align-items: start; }
        .row-2-col { display: flex; gap: 15px; }
        .col-half { flex: 1; }

        .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden; }
        .card-header { padding: 15px 20px; background: #fff; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #334155; display: flex; align-items: center; gap: 10px; }
        .card-body { padding: 20px; }

        .form-group { margin-bottom: 15px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 6px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; box-sizing: border-box; transition: 0.2s; }
        .form-control:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        
        /* Input Readonly: Màu xám, in đậm */
        .form-control[readonly] { background-color: #f1f5f9; cursor: not-allowed; color: #475569; font-weight: 700; }
        
        .select2-container .select2-selection--single { height: 42px !important; border: 1px solid #cbd5e1 !important; border-radius: 8px !important; display: flex; align-items: center; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { top: 8px !important; }

        .btn { border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; font-size: 14px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; text-decoration: none; padding: 12px; }
        .btn-add, .btn-save { background-color: #eef2f7; color: #4a69bd; margin-top: 15px; }
        .btn-add:hover, .btn-save:hover { background-color: #4a69bd; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(74, 105, 189, 0.2); }
        .btn-save { margin-top: 20px; font-size: 15px; text-transform: uppercase; }

        .table-wrapper { max-height: 400px; overflow-y: auto; border: 1px solid #f1f5f9; border-radius: 8px; }
        .custom-table { width: 100%; border-collapse: collapse; }
        .custom-table th { background: #f8fafc; color: #64748b; font-size: 12px; text-transform: uppercase; font-weight: 700; padding: 12px 15px; text-align: left; position: sticky; top: 0; z-index: 10; border-bottom: 1px solid #e2e8f0; }
        .custom-table td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; vertical-align: middle; }
        
        .item-name { font-weight: 600; color: #334155; display: block; }
        .item-attr { font-size: 12px; color: #64748b; }
        .btn-del-row { color: #ef4444; background: #fee2e2; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 6px; cursor: pointer; border: none; transition: 0.2s; }
        .btn-del-row:hover { background: #ef4444; color: #fff; }

        .total-section { margin-top: 20px; padding-top: 20px; border-top: 2px dashed #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .total-label { font-size: 16px; font-weight: 600; color: #64748b; }
        .total-value { font-size: 24px; font-weight: 800; color: #dc2626; }

        /* Toast & Modal */
        #toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        .toast { min-width: 300px; padding: 16px 20px; margin-bottom: 10px; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 12px; transform: translateX(120%); transition: transform 0.3s ease; font-size: 14px; font-weight: 500; }
        .toast.show { transform: translateX(0); }
        .toast.error { border-left: 5px solid #ef4444; color: #b91c1c; }
        .toast.success { border-left: 5px solid #22c55e; color: #15803d; }

        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: none; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; }
        .modal-overlay.show { display: flex; opacity: 1; }
        .modal-box { background: #fff; width: 400px; max-width: 90%; padding: 25px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); text-align: center; transform: translateY(-20px); transition: transform 0.3s; }
        .modal-overlay.show .modal-box { transform: translateY(0); }
        .modal-title { font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 10px; }
        .modal-desc { font-size: 14px; color: #64748b; margin-bottom: 25px; line-height: 1.5; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .btn-modal { padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; font-size: 14px; }
        .btn-modal-cancel { background: #f1f5f9; color: #64748b; }
        .btn-modal-cancel:hover { background: #e2e8f0; color: #334155; }
        .btn-modal-confirm { background: #4a69bd; color: #fff; }
        .btn-modal-confirm:hover { background: #3c5aa6; box-shadow: 0 4px 10px rgba(74, 105, 189, 0.3); }
    </style>
</head>
<body>

<div id="toast-container"></div>

<div id="confirmModal" class="modal-overlay">
    <div class="modal-box">
        <div style="margin-bottom: 15px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#4a69bd" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        </div>
        <div class="modal-title">Xác nhận lưu phiếu?</div>
        <div class="modal-desc">
            Hành động này sẽ lưu phiếu nhập vào hệ thống và cập nhật số lượng tồn kho. Bạn có muốn tiếp tục không?
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-modal btn-modal-cancel" onclick="closeModal()">Hủy bỏ</button>
            <button type="button" class="btn-modal btn-modal-confirm" onclick="processSubmit()">Đồng ý</button>
        </div>
    </div>
</div>

<?php 
if (file_exists('includes/header.php')) include 'includes/header.php';
elseif (file_exists('header.php')) include 'header.php';
?>

<div class="wrap-content">
    <div class="page-header">
        <h1 class="page-title">Tạo phiếu nhập kho</h1>
        <a href="nhapkho.php" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Quay lại
        </a>
    </div>

    <?php if(isset($error_msg)): ?>
        <div style="background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca;">
            ⚠️ <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <div class="grid-container">
        
        <div class="left-col">
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Thông tin nhà cung cấp
                </div>
                <div class="card-body">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Chọn nhà cung cấp</label>
                        <select id="thuonghieu_id" class="form-control select2" onchange="resetProductSelect()">
                            <option value="">-- Chọn thương hiệu --</option>
                            <?php foreach($nccs as $ncc): ?>
                                <option value="<?php echo $ncc['id']; ?>"><?php echo $ncc['ten_thuonghieu']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    Nhập hàng hóa
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Tìm & Chọn sản phẩm (Gõ tên để tìm)</label>
                        <select id="variant_select" class="form-control">
                            <option value="">-- Vui lòng chọn Nhà cung cấp trước --</option>
                        </select>
                    </div>

                    <div class="row-2-col">
                        <div class="col-half">
                            <div class="form-group">
                                <label class="form-label">Số lượng</label>
                                <input type="number" id="input_soluong" class="form-control" value="1" min="1">
                            </div>
                        </div>
                        <div class="col-half">
                            <div class="form-group">
                                <label class="form-label">Đơn giá nhập (VNĐ)</label>
                                <input type="number" id="input_dongia" class="form-control" placeholder="Tự động cập nhật" readonly>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-add" onclick="addToCart()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Thêm vào danh sách
                    </button>
                </div>
            </div>
        </div>

        <div class="right-col">
            <div class="card" style="height: 100%;">
                <div class="card-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                    Danh sách hàng chờ nhập
                </div>
                
                <form method="POST" id="mainForm" style="display: flex; flex-direction: column; height: 100%;">
                    <input type="hidden" name="action" value="save_import">
                    <input type="hidden" name="items_json" id="items_json">
                    <input type="hidden" name="thuonghieu_id" id="hidden_ncc">

                    <div class="card-body" style="flex-grow: 1;">
                        <div class="table-wrapper">
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th style="text-align: center;">SL</th>
                                        <th style="text-align: right;">Đơn giá</th>
                                        <th style="text-align: right;">Thành tiền</th>
                                        <th style="width: 40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="cart_body">
                                    <tr><td colspan="5" style="text-align:center; padding: 40px; color: #94a3b8;">Chưa có sản phẩm nào.</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="total-section">
                            <span class="total-label">Tổng tiền phiếu:</span>
                            <span class="total-value" id="display_total">0 đ</span>
                        </div>

                        <button type="button" class="btn btn-save" onclick="openConfirmModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            HOÀN TẤT & IN PHIẾU
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<script>
    $(document).ready(function() {
        // Init cho thương hiệu
        $('#thuonghieu_id').select2({ width: '100%' });

        // Init cho sản phẩm (AJAX)
        $('#variant_select').select2({
            width: '100%',
            placeholder: '-- Nhập tên sản phẩm để tìm --',
            allowClear: false, // [FIX] Xóa nút X gây lỗi hiển thị
            ajax: {
                url: window.location.href, // Gửi về chính trang hiện tại
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    var brand_id = $('#thuonghieu_id').val();
                    if(!brand_id) return false;
                    
                    return {
                        action: 'search_products',
                        q: params.term,
                        brand_id: brand_id
                    };
                },
                processResults: function (data) {
                    return { results: data.results };
                },
                cache: true
            },
            language: {
                noResults: function() { return "Không tìm thấy sản phẩm nào"; },
                searching: function() { return "Đang tìm kiếm..."; }
            }
        });

        // --- SỰ KIỆN TỰ ĐỘNG ĐIỀN GIÁ ---
        $('#variant_select').on('select2:select', function (e) {
            var data = e.params.data;
            if(data.price_import) {
                $('#input_dongia').val(data.price_import);
            }
        });
    });

    let cart = []; 

    // --- RESET KHI ĐỔI NCC ---
    function resetProductSelect() {
        $('#variant_select').val(null).trigger('change');
        $('#variant_select').empty(); 
        $('#input_dongia').val(''); 
        // [FIX] Đã xóa thông báo Toast ở đây
    }

    // --- TOAST NOTIFICATION ---
    function showToast(type, message) {
        const icon = type === 'error' ? '✕' : '✓';
        const html = `
            <div class="toast ${type}">
                <div class="toast-icon">${icon}</div>
                <div class="toast-message">${message}</div>
            </div>
        `;
        const $toast = $(html);
        $('#toast-container').append($toast);
        setTimeout(() => $toast.addClass('show'), 10);
        setTimeout(() => {
            $toast.removeClass('show');
            setTimeout(() => $toast.remove(), 300);
        }, 3000);
    }

    // --- ADD TO CART ---
    function addToCart() {
        let data = $('#variant_select').select2('data')[0];
        
        if (!data || !data.id) { 
            let brand_id = $('#thuonghieu_id').val();
            if(!brand_id) showToast('error', "Vui lòng chọn Nhà cung cấp trước!");
            else showToast('error', "Vui lòng tìm và chọn sản phẩm!"); 
            return; 
        }

        let bienthe_id = data.id;
        let sanpham_id = data.spid;
        let ten_sanpham = data.name_sp;
        let phan_loai = data.attr_sp;
        
        let so_luong = parseInt($('#input_soluong').val());
        let don_gia = parseInt($('#input_dongia').val()); // Lấy từ ô readonly

        if (so_luong <= 0 || isNaN(so_luong)) { showToast('error', "Số lượng phải lớn hơn 0"); return; }
        if (isNaN(don_gia) || don_gia <= 0) { showToast('error', "Sản phẩm chưa có giá nhập trong hệ thống!"); return; }

        let exists = false;
        for(let item of cart) {
            if(item.bienthe_id == bienthe_id && item.don_gia == don_gia) {
                item.so_luong += so_luong;
                item.thanh_tien = item.so_luong * item.don_gia;
                exists = true;
                break;
            }
        }

        if (!exists) {
            cart.push({
                bienthe_id: bienthe_id,
                sanpham_id: sanpham_id,
                ten_sanpham: ten_sanpham,
                phan_loai: phan_loai,
                so_luong: so_luong,
                don_gia: don_gia,
                thanh_tien: so_luong * don_gia
            });
        }

        renderTable();
        showToast('success', "Đã thêm sản phẩm vào danh sách");
        
        $('#input_soluong').val(1);
        $('#input_dongia').val('');
        $('#variant_select').val(null).trigger('change');
        $('#variant_select').select2('open'); 
    }

    function removeItem(index) {
        cart.splice(index, 1);
        renderTable();
    }

    function renderTable() {
        let html = '';
        let total = 0;

        if (cart.length === 0) {
            html = '<tr><td colspan="5" style="text-align:center; padding: 40px; color: #94a3b8;">Chưa có sản phẩm nào.</td></tr>';
        } else {
            cart.forEach((item, index) => {
                total += item.thanh_tien;
                html += `
                    <tr>
                        <td>
                            <span class="item-name">${item.ten_sanpham}</span>
                            <span class="item-attr">${item.phan_loai}</span>
                        </td>
                        <td style="text-align: center;"><b>${item.so_luong}</b></td>
                        <td style="text-align: right;">${item.don_gia.toLocaleString()}</td>
                        <td style="text-align: right; color: #4a69bd; font-weight:600;">${item.thanh_tien.toLocaleString()}</td>
                        <td style="text-align: center;">
                            <button type="button" class="btn-del-row" onclick="removeItem(${index})">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            </button>
                        </td>
                    </tr>
                `;
            });
        }

        $('#cart_body').html(html);
        $('#display_total').text(total.toLocaleString() + ' đ');
    }

    function openConfirmModal() {
        if (cart.length === 0) {
            showToast('error', "Danh sách đang trống! Vui lòng thêm sản phẩm.");
            return;
        }
        $('#confirmModal').addClass('show');
    }

    function closeModal() {
        $('#confirmModal').removeClass('show');
    }

    function processSubmit() {
        $('#items_json').val(JSON.stringify(cart));
        $('#hidden_ncc').val($('#thuonghieu_id').val());
        $('#mainForm').submit();
    }
</script>

</body>
</html>