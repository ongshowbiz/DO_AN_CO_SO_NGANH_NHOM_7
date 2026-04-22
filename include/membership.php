<?php

class MembershipHelper
{
    /**
     * Lấy quyền lợi của user theo gói membership đang active.
     * Cache vào $_SESSION để tránh query lặp trong cùng request.
     *
     * @param  int  $user_id   ID từ $_SESSION['user_id'] (0 = khách / chưa đăng nhập)
     * @param  bool $refresh   true = bỏ cache, query lại DB (gọi sau khi đăng ký gói mới)
     * @return array
     */
    public static function get(int $user_id, bool $refresh = false): array
    {
        // Quyền mặc định (gói Free / chưa đăng nhập)
        $default = [
            'ten_goi'      => 'Free',
            'id_package'   => 0,
            'is_active'    => false,    // có gói trả phí đang active không
            'doc_vo_han'   => false,    // đọc truyện miễn phí không giới hạn
            'doc_tra_phi'  => false,    // đọc truyện trả phí
            'giam_gia_mua' => 0,        // % giảm giá khi mua sách (int)
            'doc_truoc'    => false,    // đọc trước chương mới
            'he_so_diem'   => 1.0,      // hệ số nhân điểm tích lũy
            'ngay_het_han' => null,
        ];

        if ($user_id === 0) return $default;

        $key = 'mem_' . $user_id;

        if (!$refresh && isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        // db.php nằm cùng thư mục include/
        require_once __DIR__ . '/db.php';
        $db = new Database();

        $db->query("
            SELECT
                mp.id_package,
                mp.ten_goi,
                mp.doc_vo_han,
                mp.doc_tra_phi,
                mp.giam_gia_mua,
                mp.doc_truoc,
                mp.he_so_diem,
                um.ngay_het_han
            FROM user_membership um
            JOIN membership_package mp ON um.id_package = mp.id_package
            WHERE um.id_taikhoan  = :uid
              AND um.trang_thai   = 'active'
              AND um.ngay_het_han >= CURDATE()
            ORDER BY mp.sort_order DESC
            LIMIT 1
        ");
        $db->bind(':uid', $user_id);
        $row = $db->single();

        if (!$row) {
            $_SESSION[$key] = $default;
            return $default;
        }

        $result = [
            'ten_goi'      => $row['ten_goi'],
            'id_package'   => (int)$row['id_package'],
            'is_active'    => true,
            'doc_vo_han'   => (bool)$row['doc_vo_han'],
            'doc_tra_phi'  => (bool)$row['doc_tra_phi'],
            'giam_gia_mua' => (int)$row['giam_gia_mua'],
            'doc_truoc'    => (bool)$row['doc_truoc'],
            'he_so_diem'   => (float)$row['he_so_diem'],
            'ngay_het_han' => $row['ngay_het_han'],
        ];

        $_SESSION[$key] = $result;
        return $result;
    }

    /**
     * Xóa cache — gọi ngay sau khi user đăng ký / nâng cấp gói thành công.
     */
    public static function clearCache(int $user_id): void
    {
        unset($_SESSION['mem_' . $user_id]);
    }

    /**
     * Tính giá sau khi áp dụng % giảm giá membership.
     *
     * @param  float $gia_goc  Giá gốc (VNĐ)
     * @param  int   $user_id
     * @return float           Giá sau giảm (làm tròn)
     */
    public static function applyDiscount(float $gia_goc, int $user_id): float
    {
        $pct = self::get($user_id)['giam_gia_mua'];
        if ($pct <= 0) return $gia_goc;
        return round($gia_goc * (1 - $pct / 100));
    }

    /**
     * Tính điểm thực tế (base × hệ số gói).
     */
    public static function calcDiem(int $base_diem, int $user_id): int
    {
        return (int)round($base_diem * self::get($user_id)['he_so_diem']);
    }
}