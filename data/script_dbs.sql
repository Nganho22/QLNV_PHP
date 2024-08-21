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

CREATE TABLE Check_inout (
    STT INT AUTO_INCREMENT PRIMARY KEY,
    EmpID INT,
    Date_checkin DATE,
    Time_checkin TIME NULL,
    Date_checkout DATE,
    Time_checkout TIME NULL,
    Overtime INT DEFAULT 0,
    Late INT DEFAULT 0,
    WorkFromHome INT DEFAULT 0,
    Nghi INT DEFAULT 0,
    FOREIGN KEY (EmpID) REFERENCES Profile(EmpID)
);