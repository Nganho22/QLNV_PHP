DROP DATABASE IF EXISTS QLNV_UDPT;
CREATE DATABASE QLNV_UDPT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE QLNV_UDPT;

CREATE TABLE PhongBan (
    PHONGID VARCHAR(3) PRIMARY KEY,
    TenPhong VARCHAR(50),
    QuanLyID INT,
    SoThanhVien INT
);


CREATE TABLE Profile (
    EmpID INT AUTO_INCREMENT PRIMARY KEY,
    PhongID VARCHAR(3),
    Role VARCHAR(20),
    HOTEN VARCHAR(50),
    Email VARCHAR(50),
    TenTaiKhoan VARCHAR(50),
    MatKhau VARCHAR(50),
    GioiTinh VARCHAR(5),
    SoDienThoai VARCHAR(20),
    CCCD INT(20),
    STK VARCHAR(20),
    Luong FLOAT,
    DiemThuong INT,
    TinhTrang INT,
    DiaChi VARCHAR(255),
    Image VARCHAR(50)
);


CREATE TABLE password_resets (
    email VARCHAR(50) NOT NULL,
    code VARCHAR(6) NOT NULL,
    created_at DATETIME NOT NULL
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
    Nghi INT DEFAULT 0
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
    TinhTrang VARCHAR(50)
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
    TaiLieu TEXT NULL
);

CREATE TABLE Request (
    RequestID INT AUTO_INCREMENT PRIMARY KEY,
    EmpID INT,
    NguoiGui VARCHAR(50),
    Loai VARCHAR(20),
    TieuDe VARCHAR(50),
    NgayGui DATE,
    NgayXuLy DATE NULL,
    NgayChon DATE NULL,
    TrangThai INT DEFAULT 0,
    NoiDung TEXT,
    PhanHoi TEXT NULL,
    Time_sheetID INT NULL,
    Up_TinhTrang_Timesheet VARCHAR(50) NULL,
    Up_ThoiGian_Timesheet INT NULL
);

CREATE TABLE Voucher (
    VoucherID INT AUTO_INCREMENT PRIMARY KEY,
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
    FelicitationID INT AUTO_INCREMENT PRIMARY KEY,
    Point INT,
    Date DATE,
    NoiDung VARCHAR(255),
    NguoiNhan INT,
    NguoiTang INT,
    VoucherID INT
);

CREATE TABLE emp_activity (
    emp_activityID INT AUTO_INCREMENT PRIMARY KEY,
    EmpID INT,
    ActivityID VARCHAR(10),
    Start_date_join DATE NOT NULL,
    End_date_join DATE NOT NULL,
    NgayThamGia DATE NOT NULL,
    ThanhTich VARCHAR(255),
    ThoiGianThucHien DATE NOT NULL
);

