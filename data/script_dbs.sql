DROP DATABASE IF EXISTS QLNV_UDPT;
CREATE DATABASE QLNV_UDPT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE QLNV_UDPT;

CREATE TABLE PhongBan (
    PhongID VARCHAR(3) PRIMARY KEY,
    TenPhong VARCHAR(50),
    QuanLyID INT,
    SoThanhVien INT
);


CREATE TABLE Profile (
    EmpID INT AUTO_INCREMENT PRIMARY KEY,
    PhongID VARCHAR(3),
    Role VARCHAR(20) DEFAULT 'Nhân viên',
    HoTen VARCHAR(50) NOT NULL,
    Email VARCHAR(50) NOT NULL,
    TenTaiKhoan VARCHAR(50) NOT NULL,
    MatKhau VARCHAR(50) NOT NULL,
    GioiTinh VARCHAR(5) DEFAULT 'Nam',
    SoDienThoai VARCHAR(20),
    CCCD INT(20),
    STK INT,
    Luong FLOAT,
    DiemThuong INT,
    TinhTrang INT DEFAULT 1,
    DiaChi VARCHAR(255),
    Image VARCHAR(50),
    FOREIGN KEY (PhongID) REFERENCES PhongBan(PhongID) ON DELETE SET NULL
);

ALTER TABLE PhongBan
ADD FOREIGN KEY (QuanLyID) REFERENCES Profile(EmpID);


ALTER TABLE Profile MODIFY Email VARCHAR(50) NOT NULL;
ALTER TABLE Profile ADD UNIQUE (Email);

CREATE TABLE password_resets (
    email VARCHAR(50) NOT NULL,
    code VARCHAR(6) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (email, code),
    FOREIGN KEY (email) REFERENCES Profile(Email) ON DELETE CASCADE
);

CREATE TABLE Check_inout (
    STT INT AUTO_INCREMENT PRIMARY KEY,
    EmpID INT,
    Date_checkin DATE,
    Time_checkin TIME NULL,
    Time_checkout TIME NULL,
    Overtime INT DEFAULT 0,
    Late INT DEFAULT 0,
    WorkFromHome INT DEFAULT 0,
    Nghi INT DEFAULT 0,
    FOREIGN KEY (EmpID) REFERENCES Profile(EmpID)
);

CREATE TABLE Project (
    ProjectID VARCHAR(10) PRIMARY KEY,
    Ten VARCHAR(255),
    NgayGiao DATE,
    HanChotDuKien DATE,
    HanChot DATE,
    TienDo VARCHAR(10),
    SoGioThucHanh INT,
    PhongID VARCHAR(3),
    QuanLy INT,
    TinhTrang VARCHAR(50),
    FOREIGN KEY (QuanLy) REFERENCES Profile(EmpID),
    FOREIGN KEY (PhongID) REFERENCES PhongBan(PhongID)
);

CREATE TABLE Time_sheet (
    Time_sheetID INT AUTO_INCREMENT PRIMARY KEY,
    ProjectID VARCHAR(10),
    EmpID INT,
    TenDuAn VARCHAR(50),
    NguoiGui VARCHAR(50),
    PhongBan VARCHAR(3),
    TienDo INT,
    TrangThai VARCHAR(50),
    SoGioThucHien INT,
    NgayGiao DATE,
    HanChot DATE,
    DiemThuong INT,
    Tre INT DEFAULT 0,
    NoiDung TEXT,
    TaiLieu TEXT NULL,
    FOREIGN KEY (ProjectID) REFERENCES Project(ProjectID),
    FOREIGN KEY (EmpID) REFERENCES Profile(EmpID),
FOREIGN KEY (PhongBan) REFERENCES PhongBan(PhongID)
);

CREATE TABLE Request (
    RequestID INT AUTO_INCREMENT PRIMARY KEY,
    EmpID INT,
    NguoiGui VARCHAR(50),
    Loai VARCHAR(20),
    TieuDe VARCHAR(50),
    NgayGui DATE,
    NgayXuLy DATE NULL,
    TrangThai INT DEFAULT 0,
    NoiDung TEXT,
    PhanHoi TEXT NULL,
    Time_sheetID INT NULL,
    Up_TinhTrang_Timesheet VARCHAR(50) NULL,
    Up_ThoiGian_Timesheet INT NULL,
    FOREIGN KEY (EmpID) REFERENCES Profile(EmpID),
    FOREIGN KEY (Time_sheetID) REFERENCES Time_sheet(Time_sheetID)
);

CREATE TABLE Assignment (
    AssignmentID INT AUTO_INCREMENT PRIMARY KEY,
    EmpID INT,
    ProjectID VARCHAR(10),
    Date_start_assignment DATE NOT NULL,
    Date_end_assignment DATE NOT NULL,
    Hours INT,
    Content TEXT NOT NULL,
    Point INT NOT NULL,
    Documents TEXT,
    FOREIGN KEY (EmpID) REFERENCES Profile(EmpID),
    FOREIGN KEY (ProjectID) REFERENCES Project(ProjectID)
);

CREATE TABLE Voucher (
    VoucherID INT PRIMARY KEY,
    TenVoucher VARCHAR(255),
    TriGia INT,
    HanSuDung DATE,
    ChiTiet TEXT,
    HuongDanSuDung TEXT,
    TinhTrang VARCHAR(50)
);

CREATE TABLE Activity (
    ActivityID VARCHAR(10) PRIMARY KEY,
    TenHoatDong VARCHAR(255),
    LoaiHoatDong VARCHAR(255),
    TrangThai VARCHAR(50),
    Point INT,
    NoiDung TEXT,
    ChiTiet TEXT,
    SoNguoiThamGia INT,
    ChiPhi DECIMAL(10, 2),
    HanCuoiDangKy DATE,
    NgayBatDau DATE,
    NgayKetThuc DATE
);

CREATE TABLE Felicitation (
    FelicitationID VARCHAR(10) PRIMARY KEY,
    Point INT,
    Date DATE,
    NoiDung VARCHAR(255),
    NguoiNhan INT,
    FOREIGN KEY (NguoiNhan) REFERENCES Profile(EmpID)
);
