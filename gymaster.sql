-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2025 at 02:49 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gymaster`
--

-- --------------------------------------------------------

--
-- Table structure for table `coach`
--

CREATE TABLE `coach` (
  `COACH_ID` smallint(6) NOT NULL,
  `COACH_FNAME` varchar(50) NOT NULL,
  `COACH_LNAME` varchar(30) NOT NULL,
  `EMAIL` varchar(50) NOT NULL,
  `PHONE_NUMBER` varchar(15) NOT NULL,
  `GENDER` enum('MALE','FEMALE','OTHER') NOT NULL,
  `SPECIALIZATION` varchar(550) NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coach`
--

INSERT INTO `coach` (`COACH_ID`, `COACH_FNAME`, `COACH_LNAME`, `EMAIL`, `PHONE_NUMBER`, `GENDER`, `SPECIALIZATION`, `IS_ACTIVE`) VALUES
(1, 'Michael', 'Johnson', 'michael.johnson@example.com', '09171234567', 'MALE', 'Strength Training', 1),
(2, 'Jessica', 'Parker', 'jessica.parker@example.com', '09181234567', 'FEMALE', 'Strength Training', 1),
(3, 'Sarah', 'Williams', 'sarah.williams@example.com', '09191234567', 'FEMALE', 'Cardio', 1),
(4, 'Robert', 'Brown', 'robert.brown@example.com', '09201234567', 'MALE', 'Cardio', 1),
(5, 'Emma', 'Davis', 'emma.davis@example.com', '09211234567', 'FEMALE', 'Yoga', 1),
(6, 'David', 'Wilson', 'david.wilson@example.com', '09221234567', 'MALE', 'Yoga', 1),
(7, 'Lisa', 'Martinez', 'lisa.martinez@example.com', '09231234567', 'FEMALE', 'CrossFit', 1),
(8, 'James', 'Taylor', 'james.taylor@example.com', '09241234567', 'MALE', 'CrossFit', 1);

-- --------------------------------------------------------

--
-- Table structure for table `comorbidities`
--

CREATE TABLE `comorbidities` (
  `COMOR_ID` smallint(6) NOT NULL,
  `COMOR_NAME` varchar(50) NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comorbidities`
--

INSERT INTO `comorbidities` (`COMOR_ID`, `COMOR_NAME`, `IS_ACTIVE`) VALUES
(1, 'Hypertension', 1),
(2, 'Diabetes', 1),
(3, 'Heart Disease', 1),
(4, 'Asthma', 1),
(5, 'Arthritis', 1);

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `MEMBER_ID` smallint(6) NOT NULL,
  `MEMBER_FNAME` varchar(50) NOT NULL,
  `MEMBER_LNAME` varchar(30) NOT NULL,
  `EMAIL` varchar(50) NOT NULL,
  `PHONE_NUMBER` varchar(15) NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1,
  `PROGRAM_ID` smallint(6) NOT NULL,
  `USER_ID` smallint(6) NOT NULL,
  `JOINED_DATE` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `member_comorbidities`
--

CREATE TABLE `member_comorbidities` (
  `MEMBER_ID` smallint(6) NOT NULL,
  `COMOR_ID` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `member_subscription`
--

CREATE TABLE `member_subscription` (
  `MEMBER_ID` smallint(6) NOT NULL,
  `SUB_ID` smallint(6) NOT NULL,
  `START_DATE` date NOT NULL DEFAULT curdate(),
  `END_DATE` date NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PAYMENT_ID` smallint(6) NOT NULL,
  `PAY_METHOD` varchar(20) NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PAYMENT_ID`, `PAY_METHOD`, `IS_ACTIVE`) VALUES
(1, 'Cash', 1),
(2, 'Credit Card', 1),
(3, 'Debit Card', 1),
(4, 'Online Banking', 1),
(5, 'GCash', 1);

-- --------------------------------------------------------

--
-- Table structure for table `program`
--

CREATE TABLE `program` (
  `PROGRAM_ID` smallint(6) NOT NULL,
  `PROGRAM_NAME` varchar(50) NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program`
--

INSERT INTO `program` (`PROGRAM_ID`, `PROGRAM_NAME`, `IS_ACTIVE`) VALUES
(1, 'Strength Training', 1),
(2, 'Yoga', 1),
(3, 'Cardio Fitness', 1),
(4, 'CrossFit', 1);

-- --------------------------------------------------------

--
-- Table structure for table `program_coach`
--

CREATE TABLE `program_coach` (
  `PROGRAM_ID` smallint(6) NOT NULL,
  `COACH_ID` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_coach`
--

INSERT INTO `program_coach` (`PROGRAM_ID`, `COACH_ID`) VALUES
(1, 1),
(1, 2),
(2, 3),
(2, 4),
(3, 5),
(3, 6),
(4, 7),
(4, 8);

-- --------------------------------------------------------

--
-- Table structure for table `subscription`
--

CREATE TABLE `subscription` (
  `SUB_ID` smallint(6) NOT NULL,
  `SUB_NAME` varchar(20) NOT NULL,
  `DURATION` varchar(10) NOT NULL,
  `PRICE` decimal(10,2) NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription`
--

INSERT INTO `subscription` (`SUB_ID`, `SUB_NAME`, `DURATION`, `PRICE`, `IS_ACTIVE`) VALUES
(1, 'Monthly Plan', '30', 1500.00, 1),
(2, 'Quarterly Plan', '90', 4000.00, 1),
(3, 'Annual Plan', '365', 15000.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `TRANSACTION_ID` smallint(6) NOT NULL,
  `MEMBER_ID` smallint(6) NOT NULL,
  `SUB_ID` smallint(6) NOT NULL,
  `PAYMENT_ID` smallint(6) NOT NULL,
  `TRANSAC_DATE` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_log`
--

CREATE TABLE `transaction_log` (
  `LOG_ID` smallint(6) NOT NULL,
  `TRANSACTION_ID` int(11) NOT NULL,
  `OPERATION` varchar(15) NOT NULL,
  `MODIFIEDDATE` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `USER_ID` smallint(6) NOT NULL,
  `USER_FNAME` varchar(50) NOT NULL,
  `USER_LNAME` varchar(30) NOT NULL,
  `USERNAME` varchar(20) NOT NULL,
  `PASSWORD` varchar(15) NOT NULL,
  `USER_TYPE` enum('ADMINISTRATOR','STAFF') NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`USER_ID`, `USER_FNAME`, `USER_LNAME`, `USERNAME`, `PASSWORD`, `USER_TYPE`, `IS_ACTIVE`) VALUES
(1, 'Admin', 'User', 'admin', 'admin123', 'ADMINISTRATOR', 1),
(2, 'Staff', 'User', 'staff', 'staff123', 'STAFF', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `coach`
--
ALTER TABLE `coach`
  ADD PRIMARY KEY (`COACH_ID`),
  ADD KEY `IDX_COACH` (`COACH_FNAME`,`COACH_LNAME`);

--
-- Indexes for table `comorbidities`
--
ALTER TABLE `comorbidities`
  ADD PRIMARY KEY (`COMOR_ID`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`MEMBER_ID`),
  ADD KEY `PROGRAM_ID` (`PROGRAM_ID`),
  ADD KEY `USER_ID` (`USER_ID`),
  ADD KEY `IDX_MEM` (`MEMBER_FNAME`,`MEMBER_LNAME`);

--
-- Indexes for table `member_comorbidities`
--
ALTER TABLE `member_comorbidities`
  ADD PRIMARY KEY (`MEMBER_ID`,`COMOR_ID`),
  ADD KEY `COMOR_ID` (`COMOR_ID`);

--
-- Indexes for table `member_subscription`
--
ALTER TABLE `member_subscription`
  ADD PRIMARY KEY (`MEMBER_ID`,`SUB_ID`,`START_DATE`,`END_DATE`),
  ADD KEY `SUB_ID` (`SUB_ID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PAYMENT_ID`);

--
-- Indexes for table `program`
--
ALTER TABLE `program`
  ADD PRIMARY KEY (`PROGRAM_ID`);

--
-- Indexes for table `program_coach`
--
ALTER TABLE `program_coach`
  ADD PRIMARY KEY (`PROGRAM_ID`,`COACH_ID`),
  ADD KEY `COACH_ID` (`COACH_ID`);

--
-- Indexes for table `subscription`
--
ALTER TABLE `subscription`
  ADD PRIMARY KEY (`SUB_ID`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`TRANSACTION_ID`),
  ADD KEY `MEMBER_ID` (`MEMBER_ID`),
  ADD KEY `SUB_ID` (`SUB_ID`),
  ADD KEY `PAYMENT_ID` (`PAYMENT_ID`);

--
-- Indexes for table `transaction_log`
--
ALTER TABLE `transaction_log`
  ADD PRIMARY KEY (`LOG_ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`USER_ID`),
  ADD UNIQUE KEY `USERNAME` (`USERNAME`),
  ADD KEY `IDX_USER` (`USER_FNAME`,`USER_LNAME`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `coach`
--
ALTER TABLE `coach`
  MODIFY `COACH_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `comorbidities`
--
ALTER TABLE `comorbidities`
  MODIFY `COMOR_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `MEMBER_ID` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PAYMENT_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `program`
--
ALTER TABLE `program`
  MODIFY `PROGRAM_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subscription`
--
ALTER TABLE `subscription`
  MODIFY `SUB_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `TRANSACTION_ID` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaction_log`
--
ALTER TABLE `transaction_log`
  MODIFY `LOG_ID` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `USER_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `member`
--
ALTER TABLE `member`
  ADD CONSTRAINT `member_ibfk_1` FOREIGN KEY (`PROGRAM_ID`) REFERENCES `program` (`PROGRAM_ID`),
  ADD CONSTRAINT `member_ibfk_2` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`);

--
-- Constraints for table `member_comorbidities`
--
ALTER TABLE `member_comorbidities`
  ADD CONSTRAINT `member_comorbidities_ibfk_1` FOREIGN KEY (`MEMBER_ID`) REFERENCES `member` (`MEMBER_ID`),
  ADD CONSTRAINT `member_comorbidities_ibfk_2` FOREIGN KEY (`COMOR_ID`) REFERENCES `comorbidities` (`COMOR_ID`);

--
-- Constraints for table `member_subscription`
--
ALTER TABLE `member_subscription`
  ADD CONSTRAINT `member_subscription_ibfk_1` FOREIGN KEY (`MEMBER_ID`) REFERENCES `member` (`MEMBER_ID`),
  ADD CONSTRAINT `member_subscription_ibfk_2` FOREIGN KEY (`SUB_ID`) REFERENCES `subscription` (`SUB_ID`);

--
-- Constraints for table `program_coach`
--
ALTER TABLE `program_coach`
  ADD CONSTRAINT `program_coach_ibfk_1` FOREIGN KEY (`PROGRAM_ID`) REFERENCES `program` (`PROGRAM_ID`),
  ADD CONSTRAINT `program_coach_ibfk_2` FOREIGN KEY (`COACH_ID`) REFERENCES `coach` (`COACH_ID`);

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`MEMBER_ID`) REFERENCES `member` (`MEMBER_ID`),
  ADD CONSTRAINT `transaction_ibfk_2` FOREIGN KEY (`SUB_ID`) REFERENCES `subscription` (`SUB_ID`),
  ADD CONSTRAINT `transaction_ibfk_3` FOREIGN KEY (`PAYMENT_ID`) REFERENCES `payment` (`PAYMENT_ID`);

--
-- Add TRANSAC_TYPE column to transaction table
--

ALTER TABLE `transaction` 
ADD COLUMN `TRANSAC_TYPE` enum('NEW','RENEWAL','UPGRADE','DOWNGRADE') DEFAULT 'NEW' AFTER `PAYMENT_ID`;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
