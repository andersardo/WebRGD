CREATE DATABASE RGDindatavalid CHARACTER SET utf8 COLLATE utf8_swedish_ci;
CREATE USER 'rgd'@'localhost' IDENTIFIED BY 'mzs388insJ';
GRANT ALL PRIVILEGES ON RGDindatavalid.* TO 'rgd'@'localhost' WITH GRANT OPTION;
USE RGDindatavalid;
SOURCE ./RGDindatavalid.sql;
