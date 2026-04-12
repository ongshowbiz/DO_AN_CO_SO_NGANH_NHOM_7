<?php
require_once __DIR__ . '/../../../include/db.php';
$db = new Database();

$id_chap  = (int)($_GET['id_chap']  ?? 0);
$id_manga = (int)($_GET['id_manga'] ?? 0);
if ($id_chap == 0 || $id_manga == 0) {
    header("Location: index.php?method=QL_Manga-manga");
    exit;
}

// --- LẤY THÔNG TIN CHƯƠNG ---
$db->query("SELECT * FROM chap WHERE id_chap = :id AND id_manga = :mid LIMIT 1");
$db->bind(':id',  $id_chap);
$db->bind(':mid', $id_manga);
$chap = $db->single();
if (!$chap) {
    header("Location: index.php?method=QL_Manga-chuong&id_manga={$id_manga}");
    exit;
}

// Chuyển JSON ảnh thành chuỗi mỗi dòng 1 URL (để hiển thị trong textarea)
$urls_hien_tai = '';
if (!empty($chap['danh_sach_anh'])) {
    $arr = json_decode($chap['danh_sach_anh'], true);
    if (is_array($arr)) {
        $urls_hien_tai = implode("\n", $arr);
    }
}

// --- LẤY TÊN TRUYỆN ---
$db->query("SELECT manga_name FROM manga WHERE id_manga = :id LIMIT 1");
$db->bind(':id', $id_manga);
$manga = $db->single();

$errors = [];

// --- XỬ LÝ KHI BẤM LƯU ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tieu_de       = trim($_POST['tieu_de_chuong'] ?? '');
    $danh_sach_anh = trim($_POST['danh_sach_anh'] ?? '');

    if (empty($tieu_de))       $errors[] = 'Tiêu đề không được để trống!';
    if (empty($danh_sach_anh)) $errors[] = 'Phải có ít nhất 1 URL ảnh!';

    if (empty($errors)) {
        $urls = array_filter(
            array_map('trim', explode("\n", $danh_sach_anh)),
            fn($u) => !empty($u)
        );
        $json_anh = json_encode(array_values($urls), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $db->query("
            UPDATE chap
            SET tieu_de_chuong = :tieude, danh_sach_anh = :anh
            WHERE id_chap = :id AND id_manga = :mid
        ");
        $db->bind(':tieude', $tieu_de);
        $db->bind(':anh',    $json_anh);
        $db->bind(':id',     $id_chap);
        $db->bind(':mid',    $id_manga);
        $db->execute();

        $_SESSION['success_msg'] = "Cập nhật chương {$chap['so_chuong']} thành công!";
        header("Location: index.php?method=QL_Manga-chuong&id_manga={$id_manga}");
        exit;
    }

    $urls_hien_tai = $_POST['danh_sach_anh'] ?? $urls_hien_tai;
    $chap['tieu_de_chuong'] = $_POST['tieu_de_chuong'] ?? $chap['tieu_de_chuong'];
}
?>

<div class="um-container">
    <div style="margin-bottom:15px; font-size:14px; color:#666;">
        <a href="index.php?method=QL_Manga-manga" style="color:#007bff; text-decoration:none;">
            <i class="fas fa-book"></i> Danh sách truyện
        </a>
        <i class="fas fa-chevron-right" style="margin:0 8px; font-size:11px;"></i>
        <a href="index.php?method=QL_Manga-chuong&id_manga=<?= $id_manga ?>" style="color:#007bff; text-decoration:none;">
            <?= htmlspecialchars($manga['manga_name'] ?? '') ?>
        </a>
        <i class="fas fa-chevron-right" style="margin:0 8px; font-size:11px;"></i>
        <strong>Sửa Chương <?= $chap['so_chuong'] ?></strong>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 class="um-title" style="margin:0;">
            <i class="fas fa-edit"></i> Sửa Chương <?= $chap['so_chuong'] ?>
        </h2>
        <a href="index.php?method=QL_Manga-chuong&id_manga=<?= $id_manga ?>"
           style="background:#6c757d; color:#fff; padding:8px 18px; border-radius:6px;
                  text-decoration:none; font-weight:600;">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <?php if (!empty($errors)): ?>
    <div style="background:#f8d7da; color:#721c24; padding:12px 18px; border-radius:6px;
                margin-bottom:18px; border:1px solid #f5c6cb;">
        <strong><i class="fas fa-exclamation-triangle"></i> Có lỗi:</strong>
        <ul style="margin:8px 0 0 20px;">
            <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div style="background:#fff; border-radius:10px; padding:30px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
        <!-- Thông tin không thể sửa -->
        <div style="background:#f8f9fa; padding:12px 18px; border-radius:8px;
                    margin-bottom:20px; border-left:4px solid #007bff;">
            <strong>Số chương:</strong> <?= $chap['so_chuong'] ?>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Ngày đăng:</strong> <?= date('d/m/Y H:i', strtotime($chap['ngay_dang'])) ?>
            &nbsp;&nbsp;
            <small style="color:#888;">(Không thể thay đổi số chương sau khi đã tạo)</small>
        </div>

        <form method="POST">
            <div style="margin-bottom:18px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">
                    <i class="fas fa-heading"></i> Tiêu đề chương <span style="color:red;">*</span>
                </label>
                <input type="text" name="tieu_de_chuong"
                       value="<?= htmlspecialchars($chap['tieu_de_chuong']) ?>"
                       style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px;"
                       required>
            </div>

            <div style="margin-bottom:25px;">
                <label style="font-weight:600; display:block; margin-bottom:6px;">
                    <i class="fas fa-images"></i> Danh sách URL ảnh <span style="color:red;">*</span>
                </label>
                <p style="color:#666; font-size:13px; margin-bottom:8px;">
                    Mỗi dòng 1 URL. Sửa, thêm hoặc xóa dòng tùy ý.
                </p>
                <textarea name="danh_sach_anh" rows="10"
                          style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;
                                 font-size:13px; font-family:monospace; resize:vertical;"><?= htmlspecialchars($urls_hien_tai) ?></textarea>
                <button type="button" onclick="xemTruocAnh()"
                        style="margin-top:10px; background:#17a2b8; color:#fff; border:none;
                               padding:8px 16px; border-radius:6px; cursor:pointer; font-size:13px;">
                    <i class="fas fa-eye"></i> Xem trước ảnh
                </button>
                <div id="preview_anh" style="display:none; margin-top:15px; padding:15px;
                                              background:#f8f9fa; border-radius:8px;">
                    <p style="font-weight:600; margin-bottom:10px;"><i class="fas fa-eye"></i> Xem trước:</p>
                    <div id="preview_container" style="display:flex; flex-wrap:wrap; gap:10px;"></div>
                </div>
            </div>

            <div style="display:flex; gap:12px;">
                <button type="submit"
                        style="background:#ffc107; color:#212529; border:none; padding:12px 30px;
                               border-radius:6px; font-size:15px; font-weight:600; cursor:pointer;">
                    <i class="fas fa-save"></i> Cập nhật chương
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
    const urls = document.querySelector('textarea[name="danh_sach_anh"]').value
        .split('\n').map(u => u.trim()).filter(u => u !== '');
    const container = document.getElementById('preview_container');
    const box = document.getElementById('preview_anh');
    if (!urls.length) { alert('Chưa có URL ảnh nào!'); return; }
    container.innerHTML = '';
    urls.forEach((url, idx) => {
        container.innerHTML += `
            <div style="text-align:center; width:120px;">
                <img src="${url}" style="width:120px;height:170px;object-fit:cover;
                            border-radius:4px;border:2px solid #dee2e6;"
                     onerror="this.style.border='2px solid red'">
                <div style="font-size:11px;color:#666;margin-top:4px;">Trang ${idx+1}</div>
            </div>`;
    });
    box.style.display = 'block';
}
</script>