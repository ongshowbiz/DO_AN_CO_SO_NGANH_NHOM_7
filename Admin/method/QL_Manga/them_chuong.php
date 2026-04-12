<?php
require_once __DIR__ . '/../../../include/db.php';
$db = new Database();

$id_manga = (int)($_GET['id_manga'] ?? 0);
if ($id_manga == 0) {
    header("Location: index.php?method=QL_Manga-manga");
    exit;
}

// --- LẤY THÔNG TIN TRUYỆN ---
$db->query("SELECT id_manga, manga_name, slug FROM manga WHERE id_manga = :id LIMIT 1");
$db->bind(':id', $id_manga);
$manga = $db->single();
if (!$manga) {
    header("Location: index.php?method=QL_Manga-manga");
    exit;
}

// --- LẤY CHƯƠNG LỚN NHẤT HIỆN TẠI (gợi ý số chương tiếp theo) ---
$db->query("SELECT MAX(so_chuong) AS max_chap FROM chap WHERE id_manga = :mid");
$db->bind(':mid', $id_manga);
$max = $db->single();
$so_chuong_goi_y = ($max['max_chap'] ?? 0) + 1;

$errors = [];

// --- XỬ LÝ KHI BẤM LƯU ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $so_chuong     = (int)($_POST['so_chuong'] ?? 0);
    $tieu_de       = trim($_POST['tieu_de_chuong'] ?? '');
    $danh_sach_anh = trim($_POST['danh_sach_anh'] ?? ''); // Chuỗi nhiều dòng URL

    // ① Validate
    if ($so_chuong <= 0)    $errors[] = 'Số chương phải lớn hơn 0!';
    if (empty($tieu_de))    $errors[] = 'Tiêu đề chương không được để trống!';
    if (empty($danh_sach_anh)) $errors[] = 'Phải nhập ít nhất 1 URL ảnh!';

    // ② Kiểm tra số chương đã tồn tại chưa
    if (empty($errors)) {
        $db->query("SELECT COUNT(*) AS total FROM chap WHERE id_manga = :mid AND so_chuong = :chap");
        $db->bind(':mid',  $id_manga);
        $db->bind(':chap', $so_chuong);
        $row = $db->single();
        if ($row['total'] > 0) {
            $errors[] = "Chương {$so_chuong} đã tồn tại trong truyện này!";
        }
    }

    // ③ Xử lý danh sách ảnh → chuyển thành JSON
    if (empty($errors)) {
        // Tách từng dòng, lọc dòng trống
        $urls = array_filter(
            array_map('trim', explode("\n", $danh_sach_anh)),
            fn($u) => !empty($u)
        );
        $urls = array_values($urls); // reset index

        if (empty($urls)) {
            $errors[] = 'Danh sách ảnh không hợp lệ!';
        }
    }

    // ④ INSERT vào DB
    if (empty($errors)) {
        $json_anh = json_encode($urls, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        try {
            $db->beginTransaction();

            // INSERT vào bảng chap
            $db->query("
                INSERT INTO chap (id_manga, so_chuong, tieu_de_chuong, noi_dung, danh_sach_anh, ngay_dang)
                VALUES (:mid, :chap, :tieude, NULL, :anh, NOW())
            ");
            $db->bind(':mid',    $id_manga);
            $db->bind(':chap',   $so_chuong);
            $db->bind(':tieude', $tieu_de);
            $db->bind(':anh',    $json_anh);
            $db->execute();

            $id_chap_moi = $db->lastInsertId();

            // UPDATE bảng manga: cập nhật id_chap về chương vừa thêm
            $db->query("UPDATE manga SET id_chap = :id_chap WHERE id_manga = :mid");
            $db->bind(':id_chap', (int)$id_chap_moi);
            $db->bind(':mid',     $id_manga);
            $db->execute();

            $db->commit();

            $_SESSION['success_msg'] = "Thêm chương {$so_chuong} '{$tieu_de}' thành công! ({$id_chap_moi} ảnh)";
            header("Location: index.php?method=QL_Manga-chuong&id_manga={$id_manga}");
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = 'Lỗi hệ thống: ' . $e->getMessage();
        }
    }
}
?>

<div class="um-container">
    <!-- BREADCRUMB -->
    <div style="margin-bottom:15px; font-size:14px; color:#666;">
        <a href="index.php?method=QL_Manga-manga" style="color:#007bff; text-decoration:none;">
            <i class="fas fa-book"></i> Danh sách truyện
        </a>
        <i class="fas fa-chevron-right" style="margin:0 8px; font-size:11px;"></i>
        <a href="index.php?method=QL_Manga-chuong&id_manga=<?= $id_manga ?>" style="color:#007bff; text-decoration:none;">
            <?= htmlspecialchars($manga['manga_name']) ?>
        </a>
        <i class="fas fa-chevron-right" style="margin:0 8px; font-size:11px;"></i>
        <strong>Thêm chương mới</strong>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 class="um-title" style="margin:0;">
            <i class="fas fa-plus-circle"></i> Thêm Chương Mới —
            <span style="color:#007bff;"><?= htmlspecialchars($manga['manga_name']) ?></span>
        </h2>
        <a href="index.php?method=QL_Manga-chuong&id_manga=<?= $id_manga ?>"
           style="background:#6c757d; color:#fff; padding:8px 18px; border-radius:6px;
                  text-decoration:none; font-weight:600;">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- HIỂN THỊ LỖI -->
    <?php if (!empty($errors)): ?>
    <div style="background:#f8d7da; color:#721c24; padding:12px 18px; border-radius:6px;
                margin-bottom:18px; border:1px solid #f5c6cb;">
        <strong><i class="fas fa-exclamation-triangle"></i> Có lỗi xảy ra:</strong>
        <ul style="margin:8px 0 0 20px;">
            <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div style="background:#fff; border-radius:10px; padding:30px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
        <form method="POST">

            <!-- Số chương -->
            <div style="margin-bottom:18px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">
                    <i class="fas fa-sort-numeric-up"></i> Số chương <span style="color:red;">*</span>
                </label>
                <input type="number" name="so_chuong" min="1"
                       value="<?= $_POST['so_chuong'] ?? $so_chuong_goi_y ?>"
                       style="width:200px; padding:10px; border:1px solid #ddd;
                              border-radius:6px; font-size:14px;"
                       required>
                <small style="color:#888; margin-left:10px;">
                    Gợi ý: Chương <?= $so_chuong_goi_y ?>
                    (truyện hiện có <?= $so_chuong_goi_y - 1 ?> chương)
                </small>
            </div>

            <!-- Tiêu đề chương -->
            <div style="margin-bottom:18px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">
                    <i class="fas fa-heading"></i> Tiêu đề chương <span style="color:red;">*</span>
                </label>
                <input type="text" name="tieu_de_chuong"
                       value="<?= htmlspecialchars($_POST['tieu_de_chuong'] ?? '') ?>"
                       placeholder="Ví dụ: Uzumaki Naruto"
                       style="width:100%; padding:10px; border:1px solid #ddd;
                              border-radius:6px; font-size:14px;"
                       required>
            </div>

            <!-- Danh sách URL ảnh -->
            <div style="margin-bottom:25px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">
                    <i class="fas fa-images"></i> Danh sách URL ảnh <span style="color:red;">*</span>
                </label>
                <p style="color:#666; font-size:13px; margin-bottom:8px;">
                    Mỗi dòng 1 URL ảnh, theo thứ tự từ trang đầu đến trang cuối.
                </p>
                <textarea name="danh_sach_anh" rows="10"
                          placeholder="https://example.com/trang1.jpg&#10;https://example.com/trang2.jpg&#10;https://example.com/trang3.jpg"
                          style="width:100%; padding:10px; border:1px solid #ddd;
                                 border-radius:6px; font-size:13px; font-family:monospace;
                                 resize:vertical;"><?= htmlspecialchars($_POST['danh_sach_anh'] ?? '') ?></textarea>

                <!-- Nút xem trước ảnh -->
                <button type="button" onclick="xemTruocAnh()"
                        style="margin-top:10px; background:#17a2b8; color:#fff; border:none;
                               padding:8px 16px; border-radius:6px; cursor:pointer; font-size:13px;">
                    <i class="fas fa-eye"></i> Xem trước ảnh
                </button>

                <!-- Khu vực preview -->
                <div id="preview_anh" style="display:none; margin-top:15px; padding:15px;
                                              background:#f8f9fa; border-radius:8px;
                                              border:1px solid #dee2e6;">
                    <p style="font-weight:600; margin-bottom:10px;">
                        <i class="fas fa-eye"></i> Xem trước:
                    </p>
                    <div id="preview_container" style="display:flex; flex-wrap:wrap; gap:10px;"></div>
                </div>
            </div>

            <!-- Nút submit -->
            <div style="display:flex; gap:12px;">
                <button type="submit"
                        style="background:#28a745; color:#fff; border:none; padding:12px 30px;
                               border-radius:6px; font-size:15px; font-weight:600; cursor:pointer;">
                    <i class="fas fa-save"></i> Lưu chương
                </button>
                <a href="index.php?method=QL_Manga-chuong&id_manga=<?= $id_manga ?>"
                   style="background:#6c757d; color:#fff; padding:12px 24px; border-radius:6px;
                          text-decoration:none; font-size:15px; font-weight:600;">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function xemTruocAnh() {
    const textarea = document.querySelector('textarea[name="danh_sach_anh"]');
    const urls = textarea.value.split('\n').map(u => u.trim()).filter(u => u !== '');
    const container = document.getElementById('preview_container');
    const box = document.getElementById('preview_anh');

    if (urls.length === 0) {
        alert('Chưa nhập URL ảnh nào!');
        return;
    }

    container.innerHTML = '';
    urls.forEach((url, idx) => {
        const wrap = document.createElement('div');
        wrap.style.cssText = 'text-align:center; width:120px;';
        wrap.innerHTML = `
            <img src="${url}" alt="Trang ${idx+1}"
                 style="width:120px; height:170px; object-fit:cover; border-radius:4px;
                        border:2px solid #dee2e6;"
                 onerror="this.style.border='2px solid red'; this.alt='Lỗi ảnh'">
            <div style="font-size:11px; color:#666; margin-top:4px;">Trang ${idx+1}</div>
        `;
        container.appendChild(wrap);
    });

    box.style.display = 'block';
}
</script>