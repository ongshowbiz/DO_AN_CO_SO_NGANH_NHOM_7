<?php
require_once __DIR__ . '/../config.php';

class Database {
    private $host = DB_HOST; // Lay host tu config.php
    private $user = DB_USER; // Lay user tu config.php
    private $pass = DB_PASS; // Lay password tu config.php
    private $dbname = DB_NAME; // Lay ten database tu config.php

    // -- THUOC TINH CHO PDO (PHP Data Objects)--
    private $pdo;   // Se giu doi tuong PDO sau khi ket noi thanh cong
    private $stmt;  // Se giu cau lenh da duoc chuan bi (prepared statement)
    private $error; // Se giu thong bao loi (neu co)

    // Tu dong chay khi mot doi tuong Database moi duoc tao ra
    public function __construct() {
        // Chuoi DSN (Data Source Name) xac dinh cach ket noi den CSDL
        $dsn = 'mysql:host=' . $this->host . ';port=' . DB_PORT . ';dbname=' . $this->dbname . ';charset=utf8';        
        
        // Cac tuy chon cho ket noi PDO
        $options = [
            PDO::ATTR_PERSISTENT => true, // Giu ket noi mo de tai su dung. không đóng kết nối sau khi thực thi xong
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Nem ra ngoai le khi co loi CSDL
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Mac dinh tra ve ket qua duoi dang mang ket hop
            
            // PDO::ATTR_EMULATE_PREPARES => true, // ==> Sử dụng prepared statements gốc của MySQL chống SQL injection
        ];

        // Su dung khoi try-catch de xu ly loi ket noi
        try {
            // Tao mot OOP moi cua PDO de ket noi den CSDL
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $ex) {
            // Neu ket noi that bai, luu thong bao loi va hien thi
            $this->error = $ex->getMessage();

            error_log($this->error); 
            // Hiển thị thông báo chung cho người dùng thay cho hiển thị lỗi trưc tiếp
            die("Lỗi kết nối CSDL: Không thể truy cập dữ liệu. Vui lòng thử lại sau.");
        }
    }

    // Nhan mot cau lenh SQL va chuan bi no de thuc thi
    public function query($sql) {
        $this->stmt = $this->pdo->prepare($sql);
    }

    // Ngan chan SQL injection bang cach rang buoc gia tri an toan
    public function bind($param, $value, $type = null) {
        // Kiem tra kieu du lieu cua gia tri neu khong duoc chi dinh ro
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT; // Kieu so nguyen
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL; // Kieu boolean
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL; // Kieu null
                    break;
                default:
                    $type = PDO::PARAM_STR; // Kieu chuoi (mac dinh)
            }
        }
        // Gan gia tri vao tham so trong cau lenh da chuan bi
        $this->stmt->bindValue($param, $value, $type);
    }
    // -- HAM THUC THI CAU LENH 
    public function execute() {
        return $this->stmt->execute();
    }

    // -- HAM LAY TAT CA CAC DONG KET QUA 
    public function resultSet() {
        $this->execute(); // Thuc thi cau lenh
        return $this->stmt->fetchAll(); // Tra ve mot mang chua tat ca cac dong
    }

    // -- HAM LAY MOT DONG KET QUA DUY NHAT 
    public function single() {
        $this->execute(); // Thuc thi cau lenh
        return $this->stmt->fetch(); // Tra ve dong ket qua dau tien
    }

    // -- HAM DEM SO DONG BI ANH HUONG HOẶC TÁC ĐỘNG
    public function rowCount() {
        return $this->stmt->rowCount(); // Tra ve so luong dong bi tac dong boi cau lenh
    }

    // Sử dụng lastInsertId() của đối tượng PDO
    public function lastInsertId() {        
        return $this->pdo->lastInsertId(); // lấy ID của bản ghi mới nhất được chèn vào
    }

    // == CÁC PHƯƠNG THỨC TRANSACTION ==

    public function beginTransaction() {
        return $this->pdo->beginTransaction(); // Bắt đầu transaction
    }

    public function commit() {
        return $this->pdo->commit(); // Xác nhận transaction, nếu thành công thêm vào CSDL
    }

    public function rollBack() {
        return $this->pdo->rollBack(); // Hủy bỏ transaction, hoàn tác lại tất cả các thay đổi đã thực hiện.
    }
}
?>