DROP DATABASE IF EXISTS QLNV_UDPT;
CREATE DATABASE QLNV_UDPT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE QLNV_UDPT;

CREATE TABLE PhongBan (
	phongid VARCHAR(3) PRIMARY KEY,
    tenphong VARCHAR(50),
    quanlyid int,
    sothanhvien INT
);


CREATE TABLE Profile (
	empid INT AUTO_INCREMENT PRIMARY KEY,
    phongid VARCHAR(3),
    role VARCHAR(20) DEFAULT 'Nhân viên',
    hoten VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    tentaikhoan VARCHAR(50) NOT NULL,
    matkhau VARCHAR(50) NOT NULL,
    gioitinh VARCHAR(5) DEFAULT 'Nam',
    sodienthoai VARCHAR(20),
    cccd INT(20),
    stk INT,
    luong FLOAT,
    diemthuong INT,
    tinhtrang INT DEFAULT 1,
    diachi VARCHAR(255),
    image VARCHAR(50),
    FOREIGN KEY (phongid) REFERENCES PhongBan(phongid) ON DELETE SET NULL
);

ALTER TABLE PhongBan
ADD FOREIGN KEY (quanlyid) REFERENCES Profile(empid);


ALTER TABLE Profile MODIFY email VARCHAR(50) NOT NULL;
ALTER TABLE Profile ADD UNIQUE (email);

CREATE TABLE password_resets (
    email VARCHAR(50) NOT NULL,
    code VARCHAR(6) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (email, code),
    FOREIGN KEY (email) REFERENCES Profile(email) ON DELETE CASCADE
);

CREATE TABLE Check_inout (
    stt INT AUTO_INCREMENT PRIMARY KEY,
    empid INT,
    date_checkin DATE,
    time_checkin TIME NULL,
    time_checkout TIME NULL,
    overtime INT DEFAULT 0,
    late INT DEFAULT 0,
    workfromhome INT DEFAULT 0,
    nghi INT DEFAULT 0,
    FOREIGN KEY (empid) REFERENCES Profile(empid)
);

CREATE TABLE Project (
    projectid INT AUTO_INCREMENT PRIMARY KEY,
    ten VARCHAR(255),
    ngaygiao DATE,
    hanchotdukien DATE,
    hanchot DATE,
    tiendo VARCHAR(10),
    sogiothuchanh INT,
    phongid VARCHAR(3),
    quanly INT,
    tinhtrang VARCHAR(50),
    FOREIGN KEY (quanly) REFERENCES Profile(empid),
    FOREIGN KEY (phongid) REFERENCES PhongBan(phongid)
);

CREATE TABLE Time_sheet (
    time_sheetid INT AUTO_INCREMENT PRIMARY KEY,
    projectid INT,
    empid INT,
    tenduan VARCHAR(50),
    nguoigui VARCHAR(50),
    phongban VARCHAR(3),
    trangthai VARCHAR(50),
    sogiothuchien INT,
    ngaygiao DATE,
    hanchot DATE,
    diemthuong INT,
    tre INT DEFAULT 0,
    noidung TEXT,
    FOREIGN KEY (projectid) REFERENCES Project(projectid),
    FOREIGN KEY (empid) REFERENCES Profile(empid),
    FOREIGN KEY (phongban) REFERENCES PhongBan(phongid)
);

CREATE TABLE Request (
    requestid INT AUTO_INCREMENT PRIMARY KEY,
    empid INT,
    nguoigui VARCHAR(50),
    loai VARCHAR(20),
    tieude VARCHAR(50),
    ngaygui DATE,
    ngayxuly DATE NULL,
    ngaychon DATE NULL,
    trangthai INT DEFAULT 0,
    noidung TEXT,
    phanhoi TEXT NULL,
    time_sheetid INT NULL,
    up_tinhtrang_timesheet VARCHAR(50) NULL,
    up_thoigian_timesheet INT NULL,
    FOREIGN KEY (empid) REFERENCES Profile(empid),
    FOREIGN KEY (time_sheetid) REFERENCES Time_sheet(time_sheetid)
);

CREATE TABLE Voucher (
    voucherid INT AUTO_INCREMENT PRIMARY KEY,
    tenvoucher VARCHAR(255),
    trigia INT,
    hansudung DATE,
    chitiet TEXT,
    huongdansudung TEXT,
    tinhtrang VARCHAR(50)
);


CREATE TABLE Felicitation (
    felicitationid INT AUTO_INCREMENT PRIMARY KEY,
    point INT,
    date DATE,
    noidung VARCHAR(255),
    nguoinhan INT,
    nguoitang INT,
    voucherid INT,
    FOREIGN KEY (nguoinhan) REFERENCES Profile(empid),
    FOREIGN KEY (nguoitang) REFERENCES Profile(empid),
    FOREIGN KEY (voucherid) REFERENCES Voucher(voucherid)
);

