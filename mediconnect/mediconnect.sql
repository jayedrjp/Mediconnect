-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2026 at 09:27 AM
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
-- Database: `mediconnect`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `email`, `password`, `created_at`) VALUES
(1, 'Super Admin', 'admin@mediconnect.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-03-11 15:34:06');

-- --------------------------------------------------------

--
-- Table structure for table `ambulances`
--

CREATE TABLE `ambulances` (
  `id` int(11) NOT NULL,
  `ambulance_name` varchar(150) NOT NULL,
  `driver_name` varchar(100) NOT NULL,
  `driver_phone` varchar(20) NOT NULL,
  `image` varchar(255) DEFAULT 'default-ambulance.png',
  `area` varchar(100) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `vehicle_no` varchar(50) NOT NULL,
  `status` enum('Available','On Route','Busy','Maintenance') DEFAULT 'Available',
  `eta` varchar(30) DEFAULT '10 mins',
  `hospital_affiliation` varchar(150) DEFAULT NULL,
  `facilities` text DEFAULT NULL COMMENT 'JSON array: ["ICU Support","Oxygen Support","Ventilator","AC Ambulance"]',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ambulances`
--

INSERT INTO `ambulances` (`id`, `ambulance_name`, `driver_name`, `driver_phone`, `image`, `area`, `latitude`, `longitude`, `vehicle_no`, `status`, `eta`, `hospital_affiliation`, `facilities`, `created_at`) VALUES
(1, 'MediConnect Ambulance 1', 'Md. Rahim Uddin', '01711-111001', 'default-ambulance.png', 'Dhanmondi', 23.74650000, 90.37560000, 'Dhaka Metro-A-11-1234', 'On Route', '5 mins', 'Square Hospital', '[\"ICU Support\",\"Oxygen Support\",\"AC Ambulance\"]', '2026-05-03 10:49:04'),
(2, 'MediConnect Ambulance 2', 'Karim Hossain', '01711-111002', 'default-ambulance.png', 'Gulshan', 23.79370000, 90.40660000, 'Dhaka Metro-A-11-2345', 'Available', '8 mins', 'Evercare Hospital', '[\"Oxygen Support\",\"Ventilator\",\"AC Ambulance\"]', '2026-05-03 10:49:04'),
(3, 'MediConnect Ambulance 3', 'Jalal Ahmed', '01711-111003', 'default-ambulance.png', 'Mirpur', 23.80660000, 90.36580000, 'Dhaka Metro-A-11-3456', 'On Route', '12 mins', 'DMCH', '[\"ICU Support\",\"Oxygen Support\"]', '2026-05-03 10:49:04'),
(4, 'MediConnect Ambulance 4', 'Selim Reza', '01711-111004', 'default-ambulance.png', 'Uttara', 23.87590000, 90.37950000, 'Dhaka Metro-A-11-4567', 'On Route', '10 mins', 'Uttara Crescent Hospital', '[\"Oxygen Support\",\"AC Ambulance\"]', '2026-05-03 10:49:04'),
(5, 'MediConnect Ambulance 5', 'Nizam Uddin', '01711-111005', 'default-ambulance.png', 'Demra', 23.72080000, 90.47000000, 'Dhaka Metro-A-11-5678', 'Available', '15 mins', 'Demra General Hospital', '[\"ICU Support\",\"Ventilator\",\"AC Ambulance\"]', '2026-05-03 10:49:04'),
(6, 'MediConnect Ambulance 6', 'Faruk Ahmed', '01711-111006', 'default-ambulance.png', 'Kaliganj', 23.93000000, 90.48000000, 'Dhaka Metro-A-11-6789', 'Busy', '20 mins', 'Kaliganj Health Center', '[\"Oxygen Support\"]', '2026-05-03 10:49:04'),
(7, 'MediConnect ICU Ambulance', 'Dr. Salam', '01711-111007', 'default-ambulance.png', 'Dhanmondi', 23.75190000, 90.38690000, 'Dhaka Metro-A-11-7890', 'Available', '7 mins', 'Ibn Sina Hospital', '[\"ICU Support\",\"Oxygen Support\",\"Ventilator\",\"AC Ambulance\"]', '2026-05-03 10:49:04'),
(8, 'MediConnect Ambulance 8', 'Habib Khan', '01711-111008', 'default-ambulance.png', 'Gulshan', 23.78080000, 90.40100000, 'Dhaka Metro-A-11-8901', 'Available', '6 mins', 'United Hospital', '[\"Oxygen Support\",\"AC Ambulance\"]', '2026-05-03 10:49:04');

-- --------------------------------------------------------

--
-- Table structure for table `ambulance_feedback`
--

CREATE TABLE `ambulance_feedback` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `ambulance_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ambulance_requests`
--

CREATE TABLE `ambulance_requests` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `ambulance_id` int(11) NOT NULL,
  `pickup_location` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `emergency_type` varchar(100) DEFAULT NULL,
  `request_status` enum('Requested','Accepted','On The Way','Arrived','Patient Picked','Completed','Cancelled') DEFAULT 'Requested',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
  `reason` text DEFAULT NULL,
  `call_type` enum('in-person','video') DEFAULT 'in-person',
  `room_id` varchar(100) DEFAULT NULL,
  `call_status` enum('pending','active','ended') DEFAULT 'pending',
  `call_started_at` datetime DEFAULT NULL,
  `call_ended_at` datetime DEFAULT NULL,
  `payment_status` enum('unpaid','paid','pending','failed') DEFAULT 'unpaid',
  `payment_method` enum('online','cash','pay_later') DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT 0.00,
  `transaction_id` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `cancelled_by` enum('patient','doctor') DEFAULT NULL,
  `cancel_reason` text DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `reason`, `call_type`, `room_id`, `call_status`, `call_started_at`, `call_ended_at`, `payment_status`, `payment_method`, `payment_amount`, `transaction_id`, `notes`, `cancelled_by`, `cancel_reason`, `cancelled_at`, `created_at`) VALUES
(18, 4, 256, '2026-05-06', '12:00:00', 'Confirmed', '', 'video', 'mc_18_d5e8db0b9121', 'ended', '2026-05-06 12:18:32', '2026-05-06 12:19:18', 'unpaid', NULL, 0.00, 'MC_18_1778571227', NULL, NULL, NULL, NULL, '2026-05-06 06:18:14'),
(19, 4, 256, '2026-05-06', '13:00:00', 'Completed', '', 'video', 'mc_19_97f6d1b3f260', 'ended', '2026-05-06 13:18:03', '2026-05-06 13:18:53', 'unpaid', NULL, 0.00, NULL, NULL, NULL, NULL, NULL, '2026-05-06 07:17:35'),
(20, 4, 283, '2026-05-12', '14:00:00', 'Pending', '', 'in-person', NULL, 'pending', NULL, NULL, 'unpaid', 'cash', 2000.00, NULL, NULL, NULL, NULL, NULL, '2026-05-12 07:32:48'),
(21, 4, 283, '2026-05-12', '15:30:00', 'Pending', '', 'in-person', NULL, 'pending', NULL, NULL, 'unpaid', 'online', 2000.00, 'MC_21_1778571405', NULL, NULL, NULL, NULL, '2026-05-12 07:36:45'),
(22, 4, 283, '2026-05-12', '16:00:00', 'Confirmed', '', 'in-person', NULL, 'pending', NULL, NULL, 'paid', 'online', 2000.00, 'MC_22_1778572175', NULL, NULL, NULL, NULL, '2026-05-12 07:49:35');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `specialization_id` int(11) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `consultation_fee` decimal(10,2) DEFAULT 0.00,
  `bio` text DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT 'default.png',
  `is_verified` tinyint(1) DEFAULT 0,
  `available_days` varchar(100) DEFAULT 'Mon,Tue,Wed,Thu,Fri',
  `available_time_start` time DEFAULT '09:00:00',
  `available_time_end` time DEFAULT '17:00:00',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `full_name`, `email`, `password`, `phone`, `specialization_id`, `hospital_id`, `qualification`, `experience_years`, `consultation_fee`, `bio`, `profile_pic`, `is_verified`, `available_days`, `available_time_start`, `available_time_end`, `created_at`) VALUES
(1, 'Prof. Dr. Md. Lutful Kabir', 'lutful.kabir@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 1, 1, 'MRCP (UK), FRCP (London)', 30, 1500.00, 'Professor & Senior Consultant in Medicine at Ibn Sina Hospital, Dhanmondi. Holds prestigious MRCP (UK) and FRCP (London) fellowships.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(2, 'Prof. Dr. Mohammad Zohir Uddin', 'mohammad.zohiruddin@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 1, 1, 'MBBS, FCPS (Medicine), MD (Internal Medicine), FACP (USA), FRCP (UK)', 28, 1500.00, 'Professor of Medicine at Ibn Sina Hospital, specializing in Internal Medicine. Holds fellowships from both the USA and UK.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(3, 'Prof. Dr. Shohael Mahmud Arafat', 'shohael.arafat@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 1, 1, 'FCPS (Medicine), MRCP (UK)', 25, 1500.00, 'Professor of Medicine at Ibn Sina Hospital with FCPS and MRCP (UK) qualifications.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(4, 'Dr. A.R. Khan', 'ar.khan@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 1, 1, 'MD, DABFP, FAAFP (USA)', 20, 1000.00, 'Senior Consultant & Medicine Specialist at Ibn Sina Hospital. Board-certified family physician from the USA.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '18:00:00', '2026-05-06 04:48:30'),
(5, 'Prof. Dr. Md. Ayub Ali Chowdhury', 'ayub.chowdhury@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 2, 1, 'MBBS, FCPS (Medicine), MD (Nephrology)', 27, 1500.00, 'Professor of Medicine & Nephrology at Ibn Sina Hospital. Specialist in kidney diseases.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(6, 'Dr. Ahmed Manadir Hossain', 'manadir.hossain@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 1, 1, 'MBBS, FCPS (Medicine), D-Card', 10, 800.00, 'Assistant Professor of Medicine at Ibn Sina Hospital, also trained in Cardiology.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(7, 'Dr. Sakina Anwar', 'sakina.anwar@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 1, 1, 'MBBS, MD (Internal Medicine)', 12, 1000.00, 'Senior Consultant in Internal Medicine at Ibn Sina Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '18:00:00', '2026-05-06 04:48:30'),
(8, 'Prof. Dr. M. Touhidul Haque', 'touhidul.haque@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 3, 1, 'MBBS, MD (Cardiology)', 26, 1500.00, 'Professor & Cardiology Specialist at Ibn Sina Hospital, Dhanmondi.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(9, 'Col. (Rtd.) Prof. Dr. Zehad Khan', 'zehad.khan@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 3, 1, 'MCPS, FCPS, FRCP (Glasgow), FACC (USA)', 30, 1500.00, 'Retired Colonel and Professor of Cardiology at Ibn Sina Hospital. Fellow of American College of Cardiology.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(10, 'Dr. Md. Monsurul Haque', 'monsurul.haque@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 3, 1, 'MD (Cardiology), USMLE (USA)', 14, 1000.00, 'Consultant Cardiologist at Ibn Sina Hospital, trained in the USA.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '18:00:00', '2026-05-06 04:48:30'),
(11, 'Dr. Sufia Jannat', 'sufia.jannat@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 3, 1, 'FCPS (Medicine), MD (Cardiology)', 12, 1000.00, 'Consultant Cardiologist at Ibn Sina Hospital, specializing in cardiac care.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '18:00:00', '2026-05-06 04:48:30'),
(12, 'Prof. Dr. S.M. Siddiqur Rahman', 'siddiqur.rahman@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 3, 1, 'MBBS, D-Card (DU), MD (Cardiology)', 27, 1500.00, 'Professor of Interventional & Clinical Cardiology at Ibn Sina Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(13, 'Dr. Md. Shafiqur Rahman Patwary', 'shafiqur.patwary@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 3, 1, 'MBBS, MD (Cardiology), FCPS (Medicine), MCPS (Medicine)', 15, 1000.00, 'Consultant in Cardiology & Medicine at Ibn Sina Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '18:00:00', '2026-05-06 04:48:30'),
(14, 'Prof. Dr. M.A. Baqui', 'ma.baqui@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 3, 1, 'D-Card, FACC (USA)', 28, 1500.00, 'Professor of Cardiology at Ibn Sina Hospital. Fellow of the American College of Cardiology.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(15, 'Prof. Dr. Mirza Mohammad Hiron', 'mirza.hiron@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 4, 1, 'MBBS, FCPS (Medicine), MD (Chest), FCCP (USA), FRCP (Ire.), FRCP (Edin), FRCP (Glasgow)', 30, 1500.00, 'Professor of Medicine & Pulmonologist at Ibn Sina Hospital. Holds multiple international fellowships in respiratory medicine.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(16, 'Prof. Dr. Mohammad Rofiqul Islam', 'rofiqul.islam@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 4, 1, 'MBBS (DMC), MD (Chest)', 25, 1500.00, 'Professor of Chest & Respiratory Medicine at Ibn Sina Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(17, 'Prof. Dr. Masuda Begum Ranu', 'masuda.ranu@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 5, 1, 'FCPS (Gynae & Obs), D-Med (UK)', 27, 1500.00, 'Professor of Gynaecology & Obstetrics at Ibn Sina Hospital. Trained in the UK.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(18, 'Dr. Badrunnesa Begum', 'badrunnesa.begum@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 5, 1, 'FCPS, DGO, MCPS (Gynae & Obs)', 10, 800.00, 'Assistant Professor of Obs & Gynae at Ibn Sina Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(19, 'Dr. Wakil Ahmed', 'wakil.ahmed@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 6, 1, 'MS (Ortho), MMEd', 14, 1000.00, 'Consultant in Orthopaedic Surgery, specializing in Arthroscopy & Joint Replacement at Ibn Sina Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '18:00:00', '2026-05-06 04:48:30'),
(20, 'Dr. Md. Kamrul Ahsan', 'kamrul.ahsan@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 6, 1, 'D-Ortho (DU), MS (Ortho)', 13, 1000.00, 'Consultant in Spine Surgery (Orthopaedic Spine Surgery) at Ibn Sina Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '18:00:00', '2026-05-06 04:48:30'),
(21, 'Prof. Dr. M. Fakhrul Islam', 'fakhrul.islam@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 7, 1, 'MBBS, Ph.D. (Surgery)', 28, 1500.00, 'Professor & Head of Urology at Ibn Sina Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(22, 'Prof. Dr. Zakir Hossain Galib', 'zakir.galib@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 8, 1, 'MBBS, MD (Dermatology)', 25, 1500.00, 'Professor of Skin, Allergy & VD at Ibn Sina Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(23, 'Lt. Col. (Retd) Prof. Dr. Md. Abdullah Hel Kafi', 'abdullah.kafi@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 9, 1, 'MBBS, MCPS (ENT), FCPS (ENT)', 28, 1500.00, 'Professor of ENT & Head Neck Surgery at Ibn Sina Hospital. Retired Lieutenant Colonel.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(24, 'Dr. Sultana Marufa Shafin', 'sultana.shafin@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 10, 1, 'MBBS, MD (Endocrinology)', 14, 1000.00, 'Consultant in Diabetes, Hormone & Medicine at Ibn Sina Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '18:00:00', '2026-05-06 04:48:30'),
(25, 'Prof. Dr. Jhunu Shamsun Nahar', 'jhunu.nahar@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 11, 1, 'FCPS (Psych), IFAPA (USA)', 26, 1500.00, 'Professor of Psychiatry & Psychotherapy at Ibn Sina Hospital. International Fellow of American Psychiatric Association.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(26, 'Prof. Dr. Md. Sarwar Ferdous', 'sarwar.ferdous@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 12, 1, 'MBBS (DMC), MRCP (England), DCH (Ireland), FRCP (Edin)', 28, 1500.00, 'Professor of Neonatal & Paediatric Medicine at Ibn Sina Hospital. Holds international fellowships from England and Ireland.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(27, 'Dr. M.S. Khaled', 'ms.khaled@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 12, 1, 'DCH, MD (Pediatric), FCCP (USA)', 16, 1000.00, 'Specialist in Paediatric Asthma, Allergy & Chest Disease at Ibn Sina Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '18:00:00', '2026-05-06 04:48:30'),
(28, 'Dr. Tahmina Satter', 'tahmina.satter@ibnsina.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01766633012', 13, 1, 'FCPS (Surgery), MS (Plastic Surgery)', 14, 1000.00, 'Consultant in General & Plastic Surgery at Ibn Sina Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '18:00:00', '2026-05-06 04:48:30'),
(29, 'Prof. Dr. Khan Abul Kalam Azad', 'kalam.azad@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 1, 2, 'MBBS, FCPS (Medicine), FRCP (Edin)', 30, 1000.00, 'Professor & Head of Department of Medicine at Dhaka Medical College Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(30, 'Prof. Dr. Md. Robed Amin', 'robed.amin@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 1, 2, 'MBBS, FCPS (Medicine), MD (Internal Medicine)', 27, 1000.00, 'Professor of Internal Medicine & ICU at Dhaka Medical College Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(31, 'Prof. Dr. Md. Nur Hossain Bhuiyan', 'nurhossain.bhuiyan@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 14, 2, 'MBBS, FCPS (Surgery), FMAS (India), FACS (USA), FRCS (UK)', 30, 1000.00, 'Professor of General, Colorectal & Laparoscopic Surgery at DMCH. Fellow of American College of Surgeons.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(32, 'Prof. Dr. Md. Abdus Salam', 'abdus.salam@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 14, 2, 'MBBS, FCPS (Surgery), FRCS (Glasg)', 30, 1000.00, 'Professor & Head of Department of Surgery at Dhaka Medical College Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(33, 'Prof. Dr. S.M. Mizanur Rahman', 'mizan.rahman@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 14, 2, 'MBBS, MS (Surgery), FICS (USA)', 28, 1000.00, 'Professor of General & Laparoscopic Surgery at DMCH.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(34, 'Prof. Dr. Anisur Rahman', 'anisur.rahman@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 14, 2, 'MBBS, FCPS (Surgery)', 26, 1000.00, 'Professor of General, Laparoscopic & Colorectal Surgery at DMCH.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(35, 'Prof. Dr. Zahidul Haq', 'zahidul.haq@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 14, 2, 'MBBS, FCPS (Surgery), FRCS (Glasgow), MS (Surgery), Fellow Colorectal (Singapore)', 28, 1000.00, 'Professor of Colorectal & Laparoscopic Surgery at DMCH. Trained in Colorectal Surgery at Singapore.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(36, 'Prof. Dr. Md. Shamsul Alam', 'shamsul.alam@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 15, 2, 'MBBS, MS (Neurosurgery), FRCS (Edin)', 28, 1000.00, 'Professor of Neurosurgery at Dhaka Medical College Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(37, 'Prof. Dr. Mohammad Hossain', 'mohammad.hossain@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 15, 2, 'MBBS, MS (Neurosurgery), FICS (Neurosurgery), Trained Spine Surgery USA', 27, 1000.00, 'Professor of Neurosurgery at DMCH. Trained in Spine Surgery in the USA.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(38, 'Prof. Dr. Kanak Kanti Barua', 'kanak.barua@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 15, 2, 'MBBS, FCPS (Surgery), MS (Neurosurgery), PhD, FICS', 30, 1000.00, 'Professor of Neurosurgery at DMCH. PhD holder and Fellow of International College of Surgeons.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(39, 'Dr. Soumitra Sarkar', 'soumitra.sarkar@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 15, 2, 'MBBS, MRCS (England), MRCS (Glasgow), MS (Neurosurgery), FRCS (Edin)', 14, 800.00, 'Consultant Neurosurgeon at Dhaka Medical College Hospital. Member of Royal College of Surgeons.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(40, 'Prof. Dr. Md. Sirajul Islam', 'sirajul.islam@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 3, 2, 'MBBS, MD (Cardiology), FCPS (Medicine)', 28, 1000.00, 'Professor & Head of Department of Cardiology at Dhaka Medical College Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(41, 'Dr. Md. Shahriar Siddiki', 'shahriar.siddiki@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 3, 2, 'MBBS, BCS (Health), FCPS (Medicine), FCPS (Neurology), MRCP (UK)', 16, 800.00, 'Consultant in Cardiology & Neurology at DMCH. Double FCPS holder.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(42, 'Prof. Dr. Md. Hanif', 'md.hanif@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 12, 2, 'MBBS, DCH (UK), FCPS (Paediatrics)', 28, 1000.00, 'Professor of Paediatrics & Child Health at DMCH. DCH from UK.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(43, 'Dr. Tahmina Begum', 'tahmina.begum@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 16, 2, 'MBBS, FCPS (Paediatrics), MD (Neonatology)', 18, 1000.00, 'Associate Professor of Neonatology at Dhaka Medical College Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(44, 'Prof. Dr. Md. Ruhul Amin', 'ruhul.amin@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 12, 2, 'MBBS, FCPS (Paediatrics), Fellow in Paediatric Pulmonology (UK)', 26, 1000.00, 'Professor of Paediatrics & Pulmonology at DMCH. Fellow trained in the UK.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(45, 'Prof. Dr. A.K.M. Amirul Morshed', 'amirul.morshed@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 12, 2, 'MBBS, DCM, DCH, MCPS, MD (Paediatrics), MD (Paediatric Haematology)', 28, 1000.00, 'Professor specializing in Child Disease, Cancer & Blood Disease at DMCH.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(46, 'Prof. Dr. Shaheen Ara Anwar', 'shaheen.anwar@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 5, 2, 'MBBS, FCPS (Gynae & Obs), MS (Gynae)', 27, 1000.00, 'Professor & Head of Gynaecology & Obstetrics at DMCH.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(47, 'Dr. Ruma Akhtar', 'ruma.akhtar@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 5, 2, 'MBBS, DGO, MCPS, FCPS (Gynae)', 18, 800.00, 'Associate Professor of Obs & Gynae at Dhaka Medical College Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(48, 'Prof. Dr. A.F.M. Ruhul Haque', 'ruhul.haque@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 6, 2, 'MBBS, MS (Ortho), FRCS (Edin)', 28, 1000.00, 'Professor & Head of Orthopaedic Surgery at DMCH.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(49, 'Dr. Md. Moinuddin Ahmad Chowdhury', 'moinuddin.dmch@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 6, 2, 'MBBS, MS (Ortho), RCD (USA)', 26, 1000.00, 'Professor of Orthopaedic Surgery at DMCH. Trained in the USA.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(50, 'Prof. Dr. Md. Ashraful Islam', 'ashraful.islam@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 17, 2, 'MBBS, FCPS (Medicine), MD (Gastroenterology)', 26, 1000.00, 'Professor of Gastroenterology at Dhaka Medical College Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(51, 'Dr. Farhad Hossain Md. Shahid', 'farhad.shahid@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 18, 2, 'MBBS, BCS (Health), MD (Hepatology)', 10, 800.00, 'Assistant Professor of Hepatology at DMCH.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(52, 'Prof. Dr. Harun Ur Rashid', 'harun.rashid@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 2, 2, 'MBBS, MD (Nephrology), FRCP (UK)', 30, 1000.00, 'Professor & Chief Nephrologist at Dhaka Medical College Hospital. FRCP from UK.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(53, 'Prof. Dr. Md. Abdul Wahab', 'abdul.wahab@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 8, 2, 'MBBS, DDV, MCPS, FACP (USA), FCPS (Dermatology), FRCP (UK)', 30, 1000.00, 'Professor of Dermatology & Venereology at DMCH. Fellow of American College of Physicians.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(54, 'Dr. Lubna Khondker', 'lubna.khondker@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 8, 2, 'MBBS, MPH, DDV (BSMMU), MCPS, FCPS (Dermatology & VD)', 18, 800.00, 'Associate Professor of Dermatology at Dhaka Medical College Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(55, 'Prof. Dr. Pran Gopal Datta', 'prangopal.datta@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 9, 2, 'MBBS, MCPS, FCPS (ENT), FRCS (Glasgow), PhD, MSc (Audiology)', 30, 1000.00, 'Professor of ENT & Head Neck Surgery at DMCH. PhD holder with specialization in Audiology.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(56, 'Prof. Dr. Sheikh Hasanur Rahman', 'hasanur.rahman@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 9, 2, 'MBBS, FCPS (ENT), MS (ENT)', 26, 1000.00, 'Professor of ENT & Head Neck Surgery at Dhaka Medical College Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(57, 'Dr. Md. Asif Ekram', 'asif.ekram@dmch.gov.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01819669601', 7, 2, 'MBBS, MS (Urology), Trained in Urology (Singapore)', 10, 800.00, 'Assistant Professor of Urology at DMCH. Trained in Urology at Singapore.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '14:00:00', '2026-05-06 04:48:30'),
(58, 'Prof. Dr. Farooque Ahmed', 'farooque.ahmed@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 19, 3, 'MBBS, MS (CTS)', 28, 2000.00, 'Professor & Chief Cardiac Surgeon at the National Heart Foundation Hospital, Mirpur.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '15:00:00', '2026-05-06 04:48:30'),
(59, 'Prof. Dr. Mohammad Sharifuzzaman', 'sharifuzzaman@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 19, 3, 'MBBS, MS (CTS)', 26, 2000.00, 'Professor of Cardiac Surgery at the National Heart Foundation Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '15:00:00', '2026-05-06 04:48:30'),
(60, 'Prof. Dr. M. Quamrul Islam Talukder', 'quamrul.talukder@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 19, 3, 'MBBS (DMU), FCPS (Surgery), MD (USA), FCVS (USA), Advanced Fellowship (Mayo Clinic, USA)', 30, 2500.00, 'Professor & Senior Consultant Cardiac Surgeon at NHF. Advanced Fellow trained at Mayo Clinic, USA.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '15:00:00', '2026-05-06 04:48:30'),
(61, 'Prof. Dr. Abul Kalm Shamsuddin', 'abul.shamsuddin@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 20, 3, 'MBBS, MS (Cardiovascular and Thoracic Surgery)', 28, 2000.00, 'Professor & Senior Consultant Paediatric Cardiac Surgeon at National Heart Foundation Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '15:00:00', '2026-05-06 04:48:30'),
(62, 'Dr. Haroon Rasheed', 'haroon.rasheed@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 19, 3, 'MBBS, MS (CV & TS)', 18, 1500.00, 'Associate Professor & Senior Consultant in Cardiac Surgery at NHF.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '15:00:00', '2026-05-06 04:48:30'),
(63, 'Dr. Samir Kumar Biswas', 'samir.biswas@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 19, 3, 'MBBS, MS (CTS)', 18, 1500.00, 'Associate Professor & Senior Consultant in Cardiac Surgery at National Heart Foundation.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '15:00:00', '2026-05-06 04:48:30'),
(64, 'Dr. Noel Cyprian Gomes', 'noel.gomes@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 19, 3, 'MBBS, MS (Cardiothoracic Surgery)', 18, 1500.00, 'Associate Professor & Senior Consultant in Cardiac Surgery at NHF.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '15:00:00', '2026-05-06 04:48:30'),
(65, 'Dr. Deen Mohammad Anwarul Kabir', 'anwarul.kabir@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 19, 3, 'MBBS, FCPS, Post Fellowship Training in Cardiac Surgery', 18, 1500.00, 'Associate Professor & Senior Consultant in Cardiac Surgery (CABG, Valvular & Congenital) at NHF.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '15:00:00', '2026-05-06 04:48:30'),
(66, 'Dr. Md. Hafizur Rahman', 'hafizur.rahman@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 19, 3, 'MBBS, MS (CTS)', 18, 1500.00, 'Associate Professor & Senior Consultant in Cardiac Surgery at National Heart Foundation.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '15:00:00', '2026-05-06 04:48:30'),
(67, 'Dr. Mohammad Eliyas Patwary', 'eliyas.patwary@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 20, 3, 'MBBS, MS (CV & TS)', 18, 1500.00, 'Associate Professor & Senior Consultant Paediatric Cardiac Surgeon at National Heart Foundation.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '08:00:00', '15:00:00', '2026-05-06 04:48:30'),
(68, 'National Prof. Brig. (Rtd) Abdul Malik', 'abdul.malik@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 3, 3, 'MBBS (Dhaka), MRCP (UK), FCCP (USA), FCPS (BD), FRCP (Glasgow), FRCP (Edin), FACC (USA)', 40, 3000.00, 'President of the National Heart Foundation. National Professor and Brigadier (Retd) with the most prestigious international fellowships in Cardiology.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '14:00:00', '2026-05-06 04:48:30'),
(69, 'Prof. Fazila-Tun-Nesa Malik', 'fazila.malik@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 3, 3, 'MBBS (Dhaka), FCPS (Medicine), MRCP (UK), FRCP (Edin), FACC', 30, 2000.00, 'Professor & Chief Consultant Cardiologist at National Heart Foundation Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '15:00:00', '2026-05-06 04:48:30'),
(70, 'Prof. Dr. Ashok Kumar Dutta', 'ashok.dutta@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 3, 3, 'MBBS, MD (Cardiology), FACC', 28, 2000.00, 'Professor & Senior Consultant Cardiologist at National Heart Foundation. FACC certified.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '15:00:00', '2026-05-06 04:48:30'),
(71, 'Prof. Dr. Nazir Ahmed', 'nazir.ahmed@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 3, 3, 'MBBS, MD (Cardiology), FACC', 27, 2000.00, 'Professor of Cardiology at National Heart Foundation Hospital. Fellow of American College of Cardiology.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '15:00:00', '2026-05-06 04:48:30'),
(72, 'Prof. Dr. Mohammad Badiuzzaman', 'badiuzzaman@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 3, 3, 'MBBS, FCPS (Medicine), MD (Cardiology), FACC, WHO Fellow (Interventional Cardiology)', 28, 2000.00, 'Professor of Cardiology at NHF. WHO Fellow in Interventional Cardiology trained in Singapore.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '15:00:00', '2026-05-06 04:48:30'),
(73, 'Prof. Dr. Mir Nesaruddin Ahmed', 'nesaruddin.ahmed@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 3, 3, 'MBBS, DTCD (DU), MD (Cardiology), FCCP, FACC', 28, 2000.00, 'Professor & Senior Consultant Cardiologist at National Heart Foundation.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '15:00:00', '2026-05-06 04:48:30'),
(74, 'Prof. Dr. Mohammad Kabiruzzaman', 'kabiruzzaman@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 3, 3, 'MBBS, MD (Cardiology), Associate Fellow ACC', 27, 2000.00, 'Professor & Senior Consultant Cardiologist at National Heart Foundation Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '15:00:00', '2026-05-06 04:48:30'),
(75, 'Prof. Dr. Dhiman Banik', 'dhiman.banik@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 3, 3, 'MBBS, D-Card, MD (Card.), Associate Fellow ACC', 26, 2000.00, 'Professor & Senior Consultant Cardiologist at National Heart Foundation Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '15:00:00', '2026-05-06 04:48:30'),
(76, 'Dr. Md. Habibur Rahman', 'habibur.rahman@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 3, 3, 'MBBS, FCPS (Medicine), MD (Cardiology), Associate Fellow ACC', 18, 1500.00, 'Associate Professor & Senior Consultant Cardiologist at NHF.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '15:00:00', '2026-05-06 04:48:30'),
(77, 'Dr. Tawfiq Shahriar Huq', 'tawfiq.huq@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 21, 3, 'MBBS, D-Card, MD (Cardiology)', 18, 1500.00, 'Associate Professor specializing in Paediatric Cardiology & Adult Congenital Heart Diseases at NHF.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '15:00:00', '2026-05-06 04:48:30'),
(78, 'Dr. Md. Kalimuddin', 'md.kalimuddin@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 3, 3, 'MBBS, FCPS, MD (Cardiology)', 18, 1500.00, 'Associate Professor & Senior Consultant Cardiologist at National Heart Foundation.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '15:00:00', '2026-05-06 04:48:30'),
(79, 'Dr. Naharuma Aive Hyder Chowdhury', 'naharuma.chowdhury@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 21, 3, 'MBBS, MD (Cardiology)', 18, 1500.00, 'Associate Professor & Senior Consultant in Paediatric Cardiology at National Heart Foundation.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '15:00:00', '2026-05-06 04:48:30'),
(80, 'Dr. Jesmin Hossain', 'jesmin.hossain@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 21, 3, 'MBBS, MD (Cardiology)', 18, 1500.00, 'Associate Professor & Senior Consultant in Paediatric Cardiology at NHF.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '15:00:00', '2026-05-06 04:48:30'),
(81, 'Dr. Shahreen Kabir', 'shahreen.kabir@nhf.org.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09666750075', 21, 3, 'MBBS, MD (Cardiology)', 10, 1200.00, 'Assistant Professor of Paediatric Cardiology at National Heart Foundation Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '15:00:00', '2026-05-06 04:48:30'),
(82, 'Prof. Dr. Md. Golam Kibria Khan', 'golam.kibria@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 22, 4, 'MBBS, FCPS (Medicine), MACP (USA), FACP (USA), Fellow Rheumatology (USA)', 30, 1500.00, 'Professor of Rheumatology, Arthritis & Medicine at Popular Diagnostic Centre. Fellow trained in Rheumatology in the USA.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(83, 'Prof. Dr. Md. Toufiqur Rahman', 'toufiqur.rahman@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 3, 4, 'MBBS, FCPS (Medicine), MD (Cardiology)', 27, 1500.00, 'Professor of Medicine & Cardiology at Popular Diagnostic Centre, Dhanmondi.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(84, 'Prof. Dr. Anisur Rahman', 'anisur.rahman@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 17, 4, 'MBBS, FCPS, Trained in Therapeutic Endoscopy (Japan)', 26, 1500.00, 'Professor of Gastroenterology at Popular Diagnostic Centre. Trained in Therapeutic Endoscopy in Japan.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(85, 'Prof. Dr. AHM Roshan', 'ahm.roshan@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 17, 4, 'MBBS, FCPS (Medicine), MD (Gastro), Commonwealth Fellow (UK)', 27, 1500.00, 'Professor of Gastroenterology at Popular Diagnostic Centre. Commonwealth Fellow from the UK.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(86, 'Dr. Md. Naimul Hasan', 'naimul.hasan@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 17, 4, 'MBBS, BCS, CCD (BIRDEM), MD (Gastro), JGHF Fellow (Australia)', 10, 1000.00, 'Assistant Professor of Gastroenterology at Popular Diagnostic Centre. Fellow trained in Australia.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(87, 'Prof. Dr. Nooruddin Ahmad', 'nooruddin.ahmad@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 18, 4, 'MBBS, FCPS (Medicine), MD (Hepatology)', 27, 1500.00, 'Consultant Physician & Hepatologist at Popular Diagnostic Centre.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(88, 'Prof. Dr. Golam Azam', 'golam.azam@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 18, 4, 'MBBS, FCPS (Medicine), MD (Hepatology)', 26, 1500.00, 'Professor of Hepatology & Gastroenterology at Popular Diagnostic Centre.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(89, 'Prof. Dr. SK Sader Hossain', 'sader.hossain@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 15, 4, 'MBBS, MS (Neurosurgery)', 27, 1500.00, 'Professor of Neurosurgery at Popular Diagnostic Centre, Dhanmondi.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(90, 'Prof. Dr. Kanak Kanti Barua', 'kanak.barua@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 15, 4, 'MBBS, FCPS (Surgery), MS (Neurosurgery), PhD, FICS', 30, 1500.00, 'Professor of Neurosurgery at Popular Diagnostic Centre. PhD holder.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(91, 'Prof. Dr. Mohammad Abdullah', 'mohammad.abdullah@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 9, 4, 'FCPS, FICS', 28, 1500.00, 'Professor of ENT & Head Neck Surgery at Popular Diagnostic Centre.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(92, 'Prof. Dr. Md. Manjurul Alam', 'manjurul.alam@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 9, 4, 'MBBS, FCPS (ENT), MS (ENT)', 27, 1500.00, 'Professor of ENT & Head Neck Surgery at Popular Diagnostic Centre, Dhanmondi.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(93, 'Assoc. Prof. Dr. Ahmed Rakib', 'ahmed.rakib@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 9, 4, 'MBBS, DLO, MS (ENT)', 18, 1200.00, 'Associate Professor of ENT at Popular Diagnostic Centre, Dhanmondi.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(94, 'Prof. Dr. Md. Omar Ali', 'omar.ali@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 14, 4, 'MBBS, FCPS, FICS (USA), WHO Fellowship in Laparoscopic Surgery (Thailand)', 28, 1500.00, 'Professor of General & Laparoscopic Surgery at Popular Diagnostic Centre. WHO Fellow trained in Thailand.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(95, 'Prof. Dr. Md. Nur Hossain Bhuiyan', 'nurhossain.bhuiyan@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 14, 4, 'MBBS, FCPS (Surgery), FRCS (UK), FMAS (India), FACS (USA)', 30, 1500.00, 'Professor of General, Colorectal & Laparoscopic Surgery at Popular Diagnostic Centre.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(96, 'Assoc. Prof. Dr. Mahmood Riyad', 'mahmood.riyad@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 14, 4, 'MBBS, FCPS (Surgery), MRCS (Edin), Fellowship in Laparoscopy', 18, 1200.00, 'Associate Professor of General & Laparoscopic Surgery at Popular Diagnostic Centre.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(97, 'Prof. Dr. Alamgir Kabir', 'alamgir.kabir@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 23, 4, 'MBBS, FCPS (Haematology), Member ASH (USA)', 27, 1500.00, 'Professor of Blood Disorders & Haematology at Popular Diagnostic Centre. Member of American Society of Haematology.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(98, 'Prof. Dr. Moinuddin Ahmad Chowdhury', 'moinuddin.popular@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 6, 4, 'MBBS, MS (Ortho), RCD (USA)', 26, 1500.00, 'Professor of Orthopaedic Surgery at Popular Diagnostic Centre. Trained in the USA.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(99, 'Dr. G.M. Reza', 'gm.reza@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 6, 4, 'MBBS, MCPS (Surgery), D (Ortho), MS (Ortho), AAOS (USA)', 16, 1000.00, 'Consultant Orthopaedic Surgeon at Popular Diagnostic Centre. AAOS certified.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(100, 'Prof. Dr. Md. Ruhul Amin', 'ruhul.amin@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 12, 4, 'MBBS, FCPS (Paediatrics), Fellow in Paediatric Pulmonology (UK)', 26, 1500.00, 'Professor of Paediatrics & Pulmonology at Popular Diagnostic Centre.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(101, 'Prof. Dr. Mohammad Hanif', 'mohammad.hanif@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 12, 4, 'MBBS, FCPS (Paediatrics)', 28, 1500.00, 'Professor & Head of Paediatrics at Popular Diagnostic Centre.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(102, 'Prof. Dr. Kazi Manzur Kader', 'manzur.kader@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 24, 4, 'MBBS, DMRT, MSC, FACP, FRCP, Fellowship Training Radiation Oncology (India), WHO Fellow Oncology (Bangkok)', 30, 1500.00, 'Professor of Cancer & Radiation Oncology at Popular Diagnostic Centre. WHO Fellow from Bangkok.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(103, 'Prof. Dr. Md. Hafizur Rahman Ansary', 'hafizur.ansary@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 24, 4, 'MBBS, DIH, DMRT, FELLOW (WHO)', 28, 1500.00, 'Professor of Cancer & Radiation Oncology at Popular Diagnostic Centre. WHO Fellow.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(104, 'Prof. Dr. Rehana Begum', 'rehana.begum@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 24, 4, 'MBBS (Dhaka), LM, DGO (Ireland)', 27, 1500.00, 'Professor and Breast Cancer Specialist at Popular Diagnostic Centre. Trained in Ireland.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(105, 'Prof. Dr. Md. Ahsan Ullah', 'ahsan.ullah@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 25, 4, 'MBBS, FCPS (Physical Medicine)', 27, 1500.00, 'Professor of Arthritis, Pain, Paralysis & Physical Medicine at Popular Diagnostic Centre.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(106, 'Prof. Dr. Md. Taslim Uddin', 'taslim.uddin@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 25, 4, 'MBBS, FCPS (Physical Medicine & Rehabilitation)', 26, 1500.00, 'Professor of Pain, Arthritis, Paralysis & Rehabilitation at Popular Diagnostic Centre.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(107, 'Prof. Dr. AKM Motiur Rahman Bhuiyan', 'motiur.bhuiyan@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 22, 4, 'MBBS, MPH, MD (Medicine)', 27, 1500.00, 'Professor of Rheumatology, Pain & Arthritis at Popular Diagnostic Centre.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(108, 'Prof. Dr. Moinul Hossain', 'moinul.hossain@popular.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09613787801', 25, 4, 'MBBS, FCPS (Anaesthesiology), Training (Japan)', 26, 1500.00, 'Professor of Pain Medicine at Popular Diagnostic Centre. Trained in Japan.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri,Sat', '10:00:00', '20:00:00', '2026-05-06 04:48:30'),
(109, 'Prof. Dr. K.M.H.S. Sirajul Haque', 'sirajul.haque@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 3, 5, 'MBBS, FCPS (Pakistan), FRCP (Edin), FCPS (BD), FACC (USA)', 30, 2000.00, 'Professor & Chairman of Cardiology at BSMMU. Senior cardiologist at Anwer Khan Modern Medical College Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(110, 'Prof. Dr. Syed Ali Ahsan', 'ali.ahsan@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 3, 5, 'MBBS, MD (Cardiology), FICC (India), FACC (USA)', 28, 2000.00, 'Professor & Ex-Chairman of Cardiology at BSMMU. FACC certified cardiologist at AKMMCH.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(111, 'Prof. Dr. Fazlur Rahman', 'fazlur.rahman@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 3, 5, 'MBBS (Dhaka), D-Card, MCPS (Medicine), MD (Cardiology), FRCP (UK), FACC (USA)', 28, 2000.00, 'Professor of Interventional Cardiology at AKMMCH. Trained in USA, India & Switzerland.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(112, 'Prof. Dr. Harisul Hoque', 'harisul.hoque@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 3, 5, 'MBBS, FCPS (Medicine), MD (Cardiology), FACC', 28, 2000.00, 'Former Professor of Cardiology & Head at BIRDEM. Cardiologist at AKMMCH.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(113, 'Dr. Mohammad Jakir Hossain', 'jakir.hossain@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 3, 5, 'MBBS, MD (Cardiology), CCD (BIRDEM)', 18, 1200.00, 'Associate Professor of Cardiology at Anwer Khan Modern Medical College Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(114, 'Prof. Dr. A.Z.M. Maidul Islam', 'maidul.islam@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 8, 5, 'MBBS, DD (Dhaka), DTAE (Paris), AESD & V (Paris), MCDA (Canada), FAAD (USA), FCPS', 30, 2000.00, 'Professor & Head of Dermatology & Venereology at AKMMCH. Trained in Paris, Canada & USA.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(115, 'Prof. Dr. Monira Yeasmin', 'monira.yeasmin@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 8, 5, 'MBBS, FCPS (Skin & VD), Fellow in Dermatosurgery & Laser (IOD, Bangkok)', 26, 2000.00, 'Professor of Dermatology at AKMMCH. Specialist in Dermatosurgery & Laser.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(116, 'Prof. Dr. Md. Akram Ullah Sikdar', 'akram.sikdar@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 8, 5, 'MBBS, DDV (DU), Clinical Fellow in Dermatology (Paris)', 27, 2000.00, 'Professor & Former Chairman of Dermatology at BSMMU. Practicing at AKMMCH.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(117, 'Prof. Dr. M.A. Mannan', 'ma.mannan@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 10, 5, 'MBBS, MCPS (Medicine), MD (Endocrinology), MACE (USA), FACE (USA)', 28, 2000.00, 'Professor & Head of Endocrinology at AKMMCH. Fellow and Member of American College of Endocrinology.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(118, 'Prof. Dr. Md. Jahangir Alam Sarkar', 'jahangir.sarkar@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 10, 5, 'MBBS (Dhaka), FCPS (Medicine), CCD (BIRDEM), Advanced Course in Endocrinology (Singapore)', 26, 2000.00, 'Professor of Endocrinology at AKMMCH. Advanced training from Singapore.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(119, 'Dr. Md. Moinul Islam', 'moinul.islam@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 10, 5, 'MBBS, MD (Endocrinology & Metabolism)', 18, 1200.00, 'Associate Professor of Endocrinology at DMCH, practicing at AKMMCH.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(120, 'Prof. Dr. M. Alamgir Chowdhury', 'alamgir.chowdhury@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 9, 5, 'MBBS, DLO, MS (ENT), FRCS (Glasgow), FICS (USA), Gold Medalist (Bangladesh & USA)', 30, 2000.00, 'Professor & Head of ENT at AKMMCH. Gold Medalist in both Bangladesh and USA.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(121, 'Prof. Dr. Md. Abul Hasnat Joarder', 'hasnat.joarder@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 9, 5, 'MBBS, FCPS (ENT), Clinical Fellow in Otology (Madras)', 27, 2000.00, 'Professor of ENT at BSMMU, practicing at AKMMCH. Trained in Otology at Madras.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(122, 'Prof. Dr. Md. Waziul Alam Chowdhury', 'waziul.chowdhury@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 9, 5, 'MBBS, FCPS (ENT)', 27, 2000.00, 'Professor of ENT at AKMMCH. Chairman Faculty of Otolaryngology, BCPS.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(123, 'Prof. Dr. S.K.A. Razzaque', 'ska.razzaque@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 17, 5, 'MBBS, FCPS (Medicine), MD (Gastroenterology)', 27, 2000.00, 'Professor of Gastroenterology at Anwer Khan Modern Medical College Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(124, 'Prof. Dr. Md. Faruque', 'md.faruque@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 17, 5, 'MBBS, FCPS (Medicine), MD (Gastroenterology)', 26, 2000.00, 'Professor of Gastroenterology at Anwer Khan Modern Medical College Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(125, 'Prof. Dr. Nasreen Sultana', 'nasreen.sultana@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 5, 5, 'MBBS, FCPS (Obs & Gynae), MS', 28, 2000.00, 'Professor & Head of Obs & Gynaecology at AKMMCH.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(126, 'Prof. Dr. Jesmine Banu', 'jesmine.banu@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 5, 5, 'MBBS, FCPS (Gynae & Obs)', 26, 2000.00, 'Professor of Obs & Gynae at Anwer Khan Modern Medical College Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(127, 'Prof. Dr. Sehereen F. Siddiqua', 'sehereen.siddiqua@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 5, 5, 'MBBS, FCPS (Gynae & Obs)', 26, 2000.00, 'Professor of Obs & Gynae at AKMMCH.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(128, 'Prof. Dr. Masuda Begum Ranu', 'masuda.ranu@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 5, 5, 'FCPS (Gynae & Obs), D-Med (UK)', 27, 2000.00, 'Professor of Obs & Gynae at AKMMCH. Also practices at Ibn Sina Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(129, 'Prof. Brig. Gen. (Retd.) Dr. Hasina Sultana', 'hasina.sultana@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 5, 5, 'MBBS, FCPS (Obs & Gynae)', 30, 2000.00, 'Professor of Obs & Gynae at AKMMCH. Retired Brigadier General.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(130, 'Dr. Sharmin Abbasi', 'sharmin.abbasi@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 5, 5, 'MBBS, FCPS (Gynae & Obs)', 14, 1200.00, 'Consultant in Obs & Gynae at Anwer Khan Modern Medical College Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30');
INSERT INTO `doctors` (`id`, `full_name`, `email`, `password`, `phone`, `specialization_id`, `hospital_id`, `qualification`, `experience_years`, `consultation_fee`, `bio`, `profile_pic`, `is_verified`, `available_days`, `available_time_start`, `available_time_end`, `created_at`) VALUES
(131, 'Dr. Farzana Deeba', 'farzana.deeba@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 26, 5, 'MBBS, FCPS, MSc Human Assisted Reproduction & Embryology (Spain), Diploma in Reproductive Medicine (Germany)', 16, 1500.00, 'Infertility & ART Specialist at AKMMCH. Trained in Spain and Germany in reproductive medicine.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(132, 'Prof. Dr. Mohammad Ahsanul Habib', 'ahsanul.habib@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 1, 5, 'MBBS, FCPS (Medicine), FRCP (Glasgow)', 27, 2000.00, 'Professor of Internal Medicine at Anwer Khan Modern Medical College Hospital. FRCP from Glasgow.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(133, 'Prof. Dr. Mohammad Tajul Islam', 'tajul.islam@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 1, 5, 'MBBS, FCPS (Medicine), MD (Internal Medicine)', 27, 2000.00, 'Professor of Internal Medicine at AKMMCH.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(134, 'Prof. Dr. Md. Nazrul Islam', 'nazrul.islam@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 1, 5, 'MBBS, FCPS (Medicine), FACP (USA)', 27, 2000.00, 'Professor of Medicine at AKMMCH. Fellow of American College of Physicians.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(135, 'Prof. Dr. Md. Matiur Rahman', 'matiur.rahman@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 1, 5, 'MBBS, FCPS (Medicine), FRCP (Edin)', 27, 2000.00, 'Professor of Medicine at AKMMCH. FRCP from Edinburgh.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(136, 'Prof. Dr. Syed Khairul Amin', 'khairul.amin@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 12, 5, 'MBBS, MRCP (UK), FRCP (Edin), DCH (Glasgow), FRCP (Glasgow)', 28, 2000.00, 'Professor of Paediatrics at AKMMCH. Holds MRCP (UK) and multiple FRCP fellowships.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(137, 'Prof. Dr. Mohammad Hanif', 'mohammad.hanif@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 12, 5, 'MBBS, FCPS (Paediatrics), FRCP (Edin)', 28, 2000.00, 'Professor & Head of Paediatrics at Dhaka Shishu Hospital. Practicing at AKMMCH.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(138, 'Prof. Dr. Selina Khanum', 'selina.khanum@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 12, 5, 'MBBS, FCPS (Paediatrics)', 26, 2000.00, 'Professor of Paediatrics at BSMMU. Practicing at AKMMCH.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(139, 'Dr. Soma Halder', 'soma.halder@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 12, 5, 'MBBS, FCPS (Paediatrics)', 18, 1200.00, 'Associate Professor of Paediatrics at Anwer Khan Modern Medical College Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(140, 'Dr. Syed Abdul Adil Rupas', 'adil.rupas@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 27, 5, 'MBBS, MS (Paediatric Surgery)', 10, 1000.00, 'Assistant Professor of Paediatric Surgery at DMCH. Practicing at AKMMCH.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(141, 'Prof. Dr. Firoz Ahmed Quaraishi', 'firoz.quaraishi@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 28, 5, 'MBBS, FCPS (Medicine), MD (Neurology)', 27, 2000.00, 'Professor of Neurology at Anwer Khan Modern Medical College Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(142, 'Prof. Dr. Nirmalendu Bikash Bhowmik', 'nirmalendu.bhowmik@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 15, 5, 'MBBS, MS (Neurosurgery), FRCS (Edin)', 28, 2000.00, 'Professor & Head of Neurosurgery at Anwer Khan Modern Medical College Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(143, 'Prof. Dr. Md. Ruhul Amin', 'ruhul.amin.nephro@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 2, 5, 'MBBS, FCPS (Medicine), MD (Nephrology), FRCP (UK)', 27, 2000.00, 'Professor of Nephrology at AKMMCH. FRCP from the UK.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(144, 'Prof. Dr. Uttam Kumar Saha', 'uttam.saha@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 2, 5, 'MBBS, FCPS (Medicine), MD (Nephrology)', 26, 2000.00, 'Professor of Nephrology at Anwer Khan Modern Medical College Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(145, 'Prof. Dr. Syed Atiqul Haq', 'atiqul.haq@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 22, 5, 'MBBS, FCPS (Medicine), FRCP, MD (Rheumatology)', 28, 2000.00, 'Ex-Chairman & Professor of Rheumatology at BSMMU. Practicing at AKMMCH.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(146, 'Prof. Dr. A.T.M. Asaduzzaman', 'atm.asaduzzaman@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 22, 5, 'MBBS, FCPS (Medicine), MD (Rheumatology)', 26, 2000.00, 'Professor of Rheumatology at Anwer Khan Modern Medical College Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(147, 'Dr. Imran Chowdhury', 'imran.chowdhury@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 13, 5, 'MBBS, FCPS (Plastic & Reconstructive Surgery)', 14, 1500.00, 'Consultant & Surgeon in Plastic Surgery at Anwer Khan Modern Medical College Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(148, 'Prof. Dr. Md. Rezaul Karim', 'rezaul.karim@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 6, 5, 'MBBS, MS (Orthopaedics), FRCS (Glasg)', 27, 2000.00, 'Professor & Head of Orthopaedic Surgery at Anwer Khan Modern Medical College Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(149, 'Prof. Dr. Rajibul Alam', 'rajibul.alam@akmmch.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '09678010652', 6, 5, 'MBBS, MS (Ortho), FRCS (Edin)', 26, 2000.00, 'Professor of Orthopaedic Surgery at Anwer Khan Modern Medical College Hospital.', 'default.png', 1, 'Mon,Tue,Wed,Thu,Fri', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(150, 'Dr. Muhammad Hasan Andalib', 'hasan.andalib@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000001', 26, 6, 'MBBS (DMC), MRCP (UK)', 12, 800.00, 'Consultant in Accident & Emergency at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(151, 'Dr. Md. Azharul Islam', 'azharul.islam@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000002', 9, 6, 'MBBS, FCPS (Anaesthesia)', 20, 1000.00, 'Coordinator & Senior Consultant in Anaesthesia at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(152, 'Dr. Lutful Aziz', 'lutful.aziz@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000003', 9, 6, 'MBBS, PhD (Japan), FCPS', 22, 1200.00, 'Senior Consultant Anaesthesiologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '10:00:00', '18:00:00', '2026-05-06 04:48:30'),
(153, 'Dr. Shyama Prosad Mitra', 'shyama.mitra@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000004', 9, 6, 'MBBS, Diploma in Anesthesiology, FCPS Anesthesiology', 15, 900.00, 'Consultant Anaesthesiologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(154, 'Dr. Hasina Akhter', 'hasina.akhter@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000005', 9, 6, 'MBBS, FCPS (Anaesthesia)', 14, 900.00, 'Consultant Anaesthesiologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Tue', '09:00:00', '15:00:00', '2026-05-06 04:48:30'),
(155, 'Dr. Shams Munwar', 'shams.munwar@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000006', 1, 6, 'MBBS, MRCP (UK), D.Card (London)', 18, 1500.00, 'Senior Consultant Cardiologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(156, 'Prof. Dr. A.Q.M. Reza', 'aqm.reza@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000007', 1, 6, 'MBBS, MD (Cardiology)', 30, 2000.00, 'Coordinator & Senior Consultant Cardiologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Tue,Wed,Thu', '10:00:00', '17:00:00', '2026-05-06 04:48:30'),
(157, 'Dr. Kazi Atiqur Rahman', 'atiqur.rahman@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000008', 1, 6, 'MBBS, MD (Cardiology), MRCP (UK)', 16, 1500.00, 'Senior Consultant Cardiologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(158, 'Prof. Dr. Md. Shahabuddin Talukder', 'shahabuddin.talukder@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000009', 1, 6, 'MBBS, D.Card (DU), FCPS (Medicine)', 28, 1800.00, 'Senior Consultant Cardiologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Mon,Tue,Wed', '10:00:00', '16:00:00', '2026-05-06 04:48:30'),
(159, 'Prof. Dr. Tamzeed Ahmed', 'tamzeed.ahmed@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000010', 1, 6, 'MBBS, MRCP (UK)', 25, 1800.00, 'Senior Consultant Cardiologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sun,Mon,Tue,Thu', '09:00:00', '15:00:00', '2026-05-06 04:48:30'),
(160, 'Prof. Dr. A.H.M. Waliul Islam', 'waliul.islam@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000011', 1, 6, 'MBBS, PhD Card. (Osaka University), FRCP (Glasgow)', 27, 1800.00, 'Associate Consultant Cardiologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Wed,Thu', '10:00:00', '16:00:00', '2026-05-06 04:48:30'),
(161, 'Prof. Dr. Md. Atahar Ali', 'atahar.ali@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000012', 1, 6, 'MBBS, FCPS (Internal Medicine), MD (Cardiology)', 26, 1800.00, 'Senior Consultant Cardiologist & Internal Medicine at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(162, 'Prof. Brig Gen (Retd) Dr. Md Mahbub Noor', 'mahbub.noor@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000013', 10, 6, 'MBBS, FCPS (Anaesthesiology)', 35, 2000.00, 'Senior Consultant Critical Care at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(163, 'Dr. Md. Zafor Iqbal', 'zafor.iqbal@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000014', 10, 6, 'MBBS, DA, FCPS, MD', 18, 1200.00, 'Consultant Critical Care at Evercare Hospital Dhaka.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(164, 'Dr. Jasmin Manzoor', 'jasmin.manzoor@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000015', 5, 6, 'MBBS, DDSc (UK), MDSc (USA)', 20, 1200.00, 'Coordinator & Senior Consultant Dermatologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(165, 'Prof. Lt. Col. (Retd.) Dr. Q.M. Mahabub Ullah', 'mahabub.ullah@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000016', 5, 6, 'MBBS, FRCP (Glasgow), DDV, MCPS, MD', 32, 1800.00, 'Senior Consultant Dermatologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Wed', '10:00:00', '16:00:00', '2026-05-06 04:48:30'),
(166, 'Prof. Dr. Hasibur Rahman', 'hasibur.rahman@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000017', 5, 6, 'MBBS, FCPS (Dermatology & VD), MRCPS (Glasg.), FACP (USA), FRCP (Edin.)', 28, 1500.00, 'Consultant Dermatologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sun,Mon,Tue,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(167, 'Dr. Rubaiya Ali', 'rubaiya.ali@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000018', 5, 6, 'MBBS, Diploma in Dermatology and Venereology, FCPS (Dermatology and Venereology)', 12, 1000.00, 'Consultant Dermatologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(168, 'Dr. A F M Ekramuddaula', 'ekramuddaula@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000019', 6, 6, 'MBBS, FCPS (ENT), MS (Otolaryngology)', 14, 1000.00, 'Consultant ENT Specialist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(169, 'Dr. Akhil Chandra Biswas', 'akhil.biswas@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000020', 6, 6, 'MBBS, MS (Otolaryngology)', 22, 1200.00, 'Senior Consultant ENT Specialist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sun,Mon,Wed,Thu', '10:00:00', '17:00:00', '2026-05-06 04:48:30'),
(170, 'Dr. Md. Sadiqul Islam', 'sadiqul.islam@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000021', 2, 6, 'MBBS, FCPS (Medicine)', 18, 1000.00, 'Senior Consultant Internal Medicine at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(171, 'Dr. Borhan Uddin Ahmad', 'borhan.ahmad@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000022', 2, 6, 'MBBS (DMC), MRCP (UK)', 20, 1200.00, 'Coordinator & Senior Consultant Internal Medicine at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(172, 'Dr. Nikhat Shahla Afsar', 'nikhat.afsar@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000023', 2, 6, 'MBBS, MD (Internal Medicine)', 13, 1000.00, 'Consultant Internal Medicine at Evercare Hospital Dhaka.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(173, 'Dr. Abdullah Al Mamun', 'abdullah.mamun@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000024', 2, 6, 'MBBS, FCPS (Medicine)', 16, 1000.00, 'Senior Consultant Internal Medicine at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Mon,Tue,Wed', '09:00:00', '16:00:00', '2026-05-06 04:48:30'),
(174, 'Dr. K.F.M. Ayaz', 'kfm.ayaz@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000025', 2, 6, 'MBBS, M.Sc. MD (Internal Medicine)', 14, 1000.00, 'Consultant Internal Medicine at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(175, 'Dr. Fahmida Begum', 'fahmida.begum@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000026', 3, 6, 'MBBS, MD (Nephrology)', 12, 1200.00, 'Consultant Nephrologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(176, 'Assoc. Prof. Dr. KBM Hadiuzzaman', 'hadiuzzaman@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000027', 3, 6, 'MBBS, MD (Nephrology) BSMMU', 16, 1200.00, 'Consultant Nephrologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(177, 'Prof. Dr. Md. Masum Kamal Khan', 'masum.khan@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000028', 3, 6, 'MBBS (DMC), FCPS (Medicine), MD (Nephrology)', 26, 1500.00, 'Senior Consultant Nephrologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Wed', '10:00:00', '17:00:00', '2026-05-06 04:48:30'),
(178, 'Dr. Ebadur Rahman', 'ebadur.rahman@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000029', 3, 6, 'MBBS, FRCP (Edin), FRCP (Ireland), FASN, Specialty Certificate in Nephrology (UK)', 20, 1500.00, 'Senior Consultant Nephrologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(179, 'Dr. Monowara Begum', 'monowara.begum@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000030', 7, 6, 'MBBS, FCPS (BCPS), MS (Obs./Gynae.)', 22, 1200.00, 'Coordinator & Senior Consultant Gynaecologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(180, 'Dr. Mrinal Kumar Sarker', 'mrinal.sarker@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000031', 7, 6, 'MBBS, DGO, FCPS', 20, 1200.00, 'Senior Consultant Gynaecologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(181, 'Dr. Gulshan Ara', 'gulshan.ara@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000032', 7, 6, 'MBBS, MCPS, MS (Obs./Gynae.), FCPS', 18, 1200.00, 'Senior Consultant Gynaecologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(182, 'Prof. Dr. Nilufar Sultana', 'nilufar.sultana@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000033', 7, 6, 'MBBS, FCPS', 28, 1500.00, 'Consultant Gynaecologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Mon,Tue,Wed', '10:00:00', '16:00:00', '2026-05-06 04:48:30'),
(183, 'Dr. Nasrin Zulfiqar', 'nasrin.zulfiqar@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000034', 7, 6, 'MBBS, FCPS, DGO', 15, 1000.00, 'Consultant Gynaecologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(184, 'Dr. Nandkumar Katakdhond', 'nandkumar.k@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000035', 4, 6, 'MBBS, MS (Orthopaedics)', 20, 1500.00, 'Senior Consultant Orthopaedic Surgeon at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(185, 'Dr. M. Ali', 'm.ali@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000036', 4, 6, 'MBBS, MS (Ortho.)', 22, 1500.00, 'Coordinator & Senior Consultant Orthopaedic Surgeon at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(186, 'Dr. Amit Kapoor', 'amit.kapoor@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000037', 4, 6, 'MBBS, DNB', 12, 1200.00, 'Consultant Orthopaedic Surgeon at Evercare Hospital Dhaka.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(187, 'Dr. ATM Mowlada Chowdhury', 'mowlada.chowdhury@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000038', 8, 6, 'MBBS, MS (Urology), FCPS (Surgery), MRCS (Edin), MRCPS (Glasgow)', 16, 1200.00, 'Consultant Urologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(188, 'Dr. M. Zahid Hasan', 'zahid.hasan@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000039', 8, 6, 'MBBS, MS (Urology)', 20, 1200.00, 'Senior Consultant Urologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(189, 'Dr. Mir Ehteshamul Haque', 'ehteshamul.haque@evercare.com.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000040', 8, 6, 'MBBS, MS (Urology)', 14, 1000.00, 'Consultant Urologist at Evercare Hospital Dhaka.', 'default.png', 1, 'Sat,Mon,Tue,Wed', '09:00:00', '16:00:00', '2026-05-06 04:48:30'),
(190, 'Prof. Dr. Md. Ahsanul Habib', 'ahsanul.habib@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000041', 9, 7, 'MBBS, FCPS', 30, 1500.00, 'Consultant Anaesthesiologist at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(191, 'Dr. Md. Azharul Islam', 'azharul.islam@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000042', 9, 7, 'MBBS, FCPS', 18, 1200.00, 'Consultant Anaesthesiologist (Cardiac) at Square Hospital Ltd.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(192, 'Prof. Dr. Md. Khalilur Rahman', 'khalilur.rahman@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000043', 9, 7, 'MBBS, MCPS, FCPS, DA (UK), FFARCS (Ireland)', 32, 2000.00, 'Consultant Anaesthesiologist at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Wed', '10:00:00', '16:00:00', '2026-05-06 04:48:30'),
(193, 'Dr. Abdullah Al Jamil', 'al.jamil@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000044', 1, 7, 'MBBS, FCPS, MD, FCAPSC, Trained EPS & RFA (AIIMS, India)', 18, 1500.00, 'Consultant Cardiologist at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(194, 'Dr. Khaled Mohsin', 'khaled.mohsin@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000045', 1, 7, 'MBBS (Gold Medalist DMC), MRCP (Ireland), MD (Cardiology), MSc Diagnostic & Interventional', 16, 1500.00, 'Consultant Cardiologist at Square Hospital Ltd.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(195, 'Dr. Md. Afzalur Rahman', 'afzalur.rahman@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000046', 1, 7, 'MBBS, MD (Card), PhD (Card), FACC (USA), FRCP (Glasgow), FRCP (Edin)', 28, 2000.00, 'Consultant Cardiologist at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Wed,Thu', '10:00:00', '17:00:00', '2026-05-06 04:48:30'),
(196, 'Dr. Kazi Ali Hassan', 'kazi.ali@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000047', 21, 7, 'MBBS, M.Phil (EM), MRCP (UK)', 18, 1200.00, 'Consultant Endocrinologist at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(197, 'Dr. M.H. Shaheel Mohmood', 'shaheel.mohmood@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000048', 6, 7, 'MBBS, FCPS, MS', 20, 1200.00, 'Consultant ENT Head & Neck Surgeon at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(198, 'Dr. Hiramoni Sarma', 'hiramoni.sarma@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000049', 22, 7, 'MBBS, DOMS, Fellow Retinal Lasers', 15, 1000.00, 'Consultant Ophthalmologist at Square Hospital Ltd.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(199, 'Dr. M. Motahar Hossain', 'motahar.hossain@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000050', 11, 7, 'FCPS (Medicine), MD (Hepatology)', 22, 1200.00, 'Consultant Gastroenterologist & Hepatologist at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(200, 'Prof. Dr. Mahmud Hasan', 'mahmud.hasan@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000051', 11, 7, 'MBBS, FCPS, PhD, FRCP', 30, 2000.00, 'Consultant Gastroenterologist at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Wed', '10:00:00', '16:00:00', '2026-05-06 04:48:30'),
(201, 'Prof. Dr. Anaware Begum', 'anaware.begum@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000052', 7, 7, 'MBBS, FCPS (Gyne & Obs)', 30, 1500.00, 'Consultant Gynaecologist at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(202, 'Dr. Kashefa Nazneen', 'kashefa.nazneen@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000053', 7, 7, 'MBBS, FCPS (Gyne & Obs)', 14, 1000.00, 'Associate Consultant Gynaecologist at Square Hospital Ltd.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(203, 'Dr. Khaleda Yeasmin Mirza', 'khaleda.mirza@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000054', 7, 7, 'MBBS, DGO (Obs & Gyne – Ireland)', 16, 1000.00, 'Associate Consultant Gynaecologist at Square Hospital Ltd.', 'default.png', 1, 'Sat,Mon,Tue,Wed', '09:00:00', '16:00:00', '2026-05-06 04:48:30'),
(204, 'Dr. A.B.M Sarwar-E-Alam', 'sarwar.alam@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000055', 2, 7, 'MBBS, FCPS', 20, 1000.00, 'Consultant Internal Medicine at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(205, 'Dr. Abu Reza Mohammad Nooruzzaman', 'nooruzzaman@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000056', 2, 7, 'MBBS, MD', 18, 1000.00, 'Consultant Internal Medicine at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(206, 'Dr. Jahangir Alam', 'jahangir.alam@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000057', 2, 7, 'MBBS (DMC), MRCP (UK)', 16, 1000.00, 'Consultant Internal Medicine at Square Hospital Ltd.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(207, 'Dr. M A Wahab Khan', 'wahab.khan@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000058', 3, 7, 'MBBS, MD (Nephrology)', 18, 1200.00, 'Consultant Nephrologist at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(208, 'Dr. Abdul Kader Shaikh', 'kader.shaikh@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000059', 12, 7, 'FCPS, MD', 20, 1200.00, 'Neuromedicine Specialist (Assistant Professor BSMMU) at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(209, 'Dr. Md. Ismail Chowdhury', 'ismail.chowdhury@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000060', 12, 7, 'MBBS, FCPS (Medicine), MD (Neurology)', 16, 1200.00, 'Associate Consultant Neurologist at Square Hospital Ltd.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(210, 'Dr. Khandaker Abu Talha', 'abu.talha@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000061', 13, 7, 'MBBS, MCPS, MS', 16, 1500.00, 'Consultant Neurosurgeon at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(211, 'Dr. Ahmed Zahid Hossain', 'zahid.hossain@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000062', 24, 7, 'MBBS, MS (Ped. Surgery)', 12, 1000.00, 'Associate Consultant Paediatric Surgeon at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(212, 'Prof. Dr. Md. Kabirul Islam', 'kabirul.islam@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000063', 24, 7, 'MBBS, MS (Ped. Surgery), FICS (USA), Trained Pediatric Urology (UK)', 30, 1500.00, 'Consultant Paediatric Surgeon at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Wed', '10:00:00', '16:00:00', '2026-05-06 04:48:30'),
(213, 'Dr. Md. Masudur Rahman', 'masudur.rahman@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000064', 15, 7, 'MBBS, MRCP (UK), FCPS (Pediatrics)', 18, 1200.00, 'Consultant Paediatrician at Square Hospital Ltd.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(214, 'Dr. Md. Amer Wahed', 'amer.wahed@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000065', 23, 7, 'MBBS (IPGMR), MD (USA), FACP (USA), Diplomate in Internal Medicine (UK)', 25, 1500.00, 'Consultant Pathologist at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(215, 'Dr. Md. Aminul Islam Khan', 'aminul.khan@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000066', 23, 7, 'MBBS (DMC), Board Certified in Pathology (AP & CP)', 20, 1200.00, 'Consultant Pathologist at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(216, 'Dr. M. A. Rashid', 'ma.rashid@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000067', 19, 7, 'MBBS, FCPS (Physical Medicine)', 18, 1000.00, 'Consultant Physical Medicine Specialist at Square Hospital Ltd.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(217, 'Dr. ATM Samdani', 'atm.samdani@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000068', 8, 7, 'MBBS, MD (Radiology and Imaging from BIRDEM)', 14, 1200.00, 'Associate Consultant Urologist at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(218, 'Prof. Dr. Ko Ninan Chac', 'ko.ninan@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000069', 8, 7, 'MBBS, MS, MCh (Urology), FRCS (Urology)', 28, 2000.00, 'Consultant Urologist at Square Hospital Ltd.', 'default.png', 1, 'Sat,Sun,Mon,Wed', '10:00:00', '16:00:00', '2026-05-06 04:48:30'),
(219, 'Dr. M. A. Zulkifl', 'ma.zulkifl@squarehospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000070', 8, 7, 'MBBS, FCPS, FRCS (England)', 22, 1500.00, 'Consultant Urologist at Square Hospital Ltd.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(220, 'Dr. Jahangir Kabir', 'jahangir.kabir@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000071', 25, 8, 'MBBS, MS (CTS)', 25, 2000.00, 'Chief Cardiac Surgeon & Director, Cardiac Centre at United Hospital Limited.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(221, 'Dr. Moral Nazrul Islam', 'moral.nazrul@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000072', 5, 8, 'MBBS, DD (Singapore), DHRS, FDCS, FICD (USA)', 22, 1500.00, 'Consultant Dermatologist at United Hospital Limited.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(222, 'Prof. Dr. Shah Ataur Rahman', 'shah.ataur@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000073', 5, 8, 'MBBS, Ph.D. (Japan), Postdoctoral Fellow (Japan & USA), Trained Cosmetic Dermatology (India, UAE)', 30, 2000.00, 'Consultant Dermatologist & Cosmetologist at United Hospital Limited.', 'default.png', 1, 'Sat,Sun,Mon,Wed', '10:00:00', '16:00:00', '2026-05-06 04:48:30'),
(223, 'Dr. Nazmul Islam', 'nazmul.islam@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000074', 21, 8, 'MBBS, Diploma in Internal Medicine (UK), MRCP, US Board Certified in Medicine, Diabetes & Endocrine', 20, 1500.00, 'Consultant Endocrinologist at United Hospital Limited.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(224, 'Dr. Abu Sayeed M.M. Rahman', 'abu.sayeed@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000075', 16, 8, 'MBBS, FRCS (Edin), FRCS (Glasg)', 24, 1500.00, 'Consultant General Surgeon at United Hospital Limited.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(225, 'Prof. Dr. Anisur Rahman', 'anisur.rahman@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000076', 16, 8, 'MBBS (DMC), MSc (Canada), FCPS (Surgery), FRCS (Glasgow, UK)', 30, 2000.00, 'Consultant General & Laparoscopic Surgeon at United Hospital Limited.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(226, 'Prof. Dr. Zahidul Haq', 'zahidul.haq@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000077', 27, 8, 'MBBS, FCPS (Surgery), FRCS (Glasgow), MS (Surgery), Fellow Colorectal Surgery (Singapore)', 28, 2000.00, 'Consultant General & Colorectal Surgeon at United Hospital Limited.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(227, 'Prof. Dr. A. F. S. A. Wasey', 'afsa.wasey@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000078', 28, 8, 'MBBS, DMM (Malaysia), FCPS (Microbiology), Advanced Training in Immunology', 28, 1500.00, 'Consultant Lab Medicine at United Hospital Limited.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(228, 'Ms. Razia Sultana', 'razia.sultana@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000079', 29, 8, 'BSc Psychology (DU), MSc Clinical Psychology (DU), MPH (AIUB), PGCert Mental Health (London UK)', 10, 800.00, 'Counselor – Mental Health at United Hospital Limited.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(229, 'Prof. Dr. M. Mujibul Haque Mollah', 'mujibul.mollah@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000080', 3, 8, 'MBBS, MRCP (UK), Fellow Nephrology (UK)', 32, 2000.00, 'Chief Nephrologist at United Hospital Limited.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(230, 'Dr. Ashraf A. Sheikh', 'ashraf.sheikh@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000081', 15, 8, 'MBBS, DCH (Glasgow), MRCP (London), MRCP (Ireland)', 20, 1200.00, 'Consultant Paediatrician at United Hospital Limited.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(231, 'Dr. Nargis Ara Begum', 'nargis.begum@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000082', 32, 8, 'MBBS, FCPS (Paed), MD (Neonatology), Fellow New Born Medicine (Singapore)', 18, 1500.00, 'Consultant Neonatologist at United Hospital Limited.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(232, 'Dr. Kanuj Kumar Barman', 'kanuj.barman@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000083', 12, 8, 'MBBS, M.Sc, MPH, MD (Neurology), AMBO Fellow (NCVCRI, Osaka, Japan)', 18, 1500.00, 'Consultant Neurologist at United Hospital Limited.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(233, 'Dr. M Al Amin Salek', 'al.amin.salek@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000084', 13, 8, 'MBBS, MCPS (Surgery), FCPS (Surgery), MRCS (Eng)', 15, 1500.00, 'Consultant Neurosurgeon at United Hospital Limited.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(234, 'Dr. Md. Ehteshamul Hoque', 'ehteshamul.hoque@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000085', 14, 8, 'MBBS, BCS (Health), M.Phil (Radiotherapy)', 18, 1500.00, 'Consultant Oncologist at United Hospital Limited.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(235, 'Dr. Rashid Un Nabi', 'rashid.nabi@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000086', 14, 8, 'MBBS, M.Phil (Radiotherapy), IAEA Fellow (Thailand), VARIAN Fellow (India), KFDA Fellow (Korea)', 20, 1500.00, 'Consultant Radiation Oncologist at United Hospital Limited.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(236, 'Prof. Dr. Santanu Chaudhuri', 'santanu.chaudhuri@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000087', 14, 8, 'MBBS, DMRT, PGDHM, MD (Tata Memorial Hospital), DNB, M.Phil', 28, 2000.00, 'Consultant & Director, Oncology Center at United Hospital Limited.', 'default.png', 1, 'Sat,Sun,Mon,Wed', '10:00:00', '16:00:00', '2026-05-06 04:48:30'),
(237, 'Dr. Aminul Hassan', 'aminul.hassan@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000088', 4, 8, 'MBBS, D.Orth, MS (Orth), FACS', 20, 1500.00, 'Orthopedic Surgeon at United Hospital Limited.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(238, 'Dr. Md. Abdul Mabin', 'abdul.mabin@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000089', 17, 8, 'MBBS (DMC), DSS (Univ of Vienna), Fellow Specialized Surgery (Plastic & Reconstructive)', 18, 1500.00, 'Consultant Plastic Surgeon at United Hospital Limited.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(239, 'Dr. Khan Md. Sayeduzzaman', 'sayeduzzaman@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000090', 20, 8, 'MBBS, MCPS (Medicine), MD (Chest Diseases)', 16, 1200.00, 'Consultant Chest & Respiratory Medicine at United Hospital Limited.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(240, 'Dr. Col. Shameem Waheed', 'shameem.waheed@uhlbd.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000091', 8, 8, 'MBBS, FCPS (Surgery), FCPS (Urology), Fellow SIU', 24, 1500.00, 'Consultant Urologist at United Hospital Limited.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(241, 'Prof. Dr. A.K.M. Aminul Haque', 'aminul.haque@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000092', 20, 9, 'MBBS, FCPS (Medicine), MD (Chest), FACP (USA)', 32, 1500.00, 'Professor – Chest Diseases & Medicine Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(242, 'Prof. Dr. AKM Fazlul Haque', 'fazlul.haque@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000093', 27, 9, 'MBBS, FCPS (Surgery), FICS (USA), Fellow Colorectal Surgery (Singapore)', 30, 2000.00, 'Professor – Colorectal Surgeon at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(243, 'Prof. Dr. Zahidul Haq', 'zahidul.haq@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000094', 16, 9, 'MBBS, FCPS (Surgery), FRCS (Glasgow), MS (Surgery), FICS, Fellow Colorectal Surgery (Singapore)', 28, 2000.00, 'Professor – Colorectal, Laparoscopic & General Surgeon at BSMMU PG Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(244, 'Prof. Lt. Col. Dr. Md. Abdul Wahab', 'abdul.wahab@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000095', 5, 9, 'MBBS, DDV, MCPS, FACP (USA), FCPS (Dermatology), FRCP (UK), Higher Training (Thailand)', 34, 1800.00, 'Professor – Skin, Allergy, Leprosy & Sexual Diseases Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(245, 'Dr. Lubna Khondker', 'lubna.khondker@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000096', 5, 9, 'MBBS, MPH, DDV (BSMMU), MCPS, FCPS (Dermatology & Venereology), Fellowship Cutaneous & LASER Surgery (Thailand)', 20, 1500.00, 'Skin, Hair, Nail, STDs, Cosmetology & Laser Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(246, 'Dr. ABM Khalekuzzaman Shipon', 'shipon@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000097', 5, 9, 'MBBS, PGT (Skin & VD), FRSH (London), Training Dermato & Cosmetic Surgery', 15, 1200.00, 'Skin, Sexual Diseases, Allergy, Hair & Cosmetic Surgery Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Mon,Tue,Wed', '09:00:00', '16:00:00', '2026-05-06 04:48:30'),
(247, 'Prof. Dr. Md. Farid Uddin', 'farid.uddin@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000098', 21, 9, 'MBBS, DEM, MD (Endocrinology)', 30, 1800.00, 'Professor – Diabetes, Thyroid & Hormone Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(248, 'Dr. Marufa Mustari', 'marufa.mustari@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000099', 21, 9, 'MBBS, FCPS (Endocrinology & Metabolism), MACE (USA)', 14, 1500.00, 'Diabetes, Thyroid & Hormone Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(249, 'Prof. Dr. Pran Gopal Datta', 'pran.gopal@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000100', 6, 9, 'MBBS, MCPS, ACORL, PhD, MSc (Audiology), FCPS (ENT), FRCS (Glasgow)', 35, 2000.00, 'Professor – ENT Specialist & Head Neck Surgeon at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(250, 'Prof. Dr. Sheikh Hasanur Rahman', 'hasanur.rahman@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000101', 6, 9, 'MBBS, FCPS (ENT), MS (ENT)', 28, 1800.00, 'Professor – ENT Specialist & Head Neck Surgeon at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '10:00:00', '17:00:00', '2026-05-06 04:48:30'),
(251, 'Prof. Dr. Mahmud Hasan', 'mahmud.hasan@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000102', 11, 9, 'MBBS, PhD (Edin), FCPS, FCPS (Pak), FRCP (Edin), FRCP (Glasgow)', 35, 2000.00, 'Professor – Gastroenterology, Liver & Pancreatic Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(252, 'Prof. Dr. Mobin Khan', 'mobin.khan@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000103', 33, 9, 'MBBS, MSc (Hepatology), FCPS, FRCP (Glasg & Edin), FACP (USA), FCCP (USA)', 30, 2000.00, 'Professor – Liver Diseases & Medicine Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(253, 'Prof. Dr. Md. Anwarul Kabir', 'anwarul.kabir@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000104', 11, 9, 'MBBS, MD (Gastroenterology)', 26, 1800.00, 'Professor – Gastroenterology, Liver & Pancreatic Diseases at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Wed', '10:00:00', '16:00:00', '2026-05-06 04:48:30'),
(254, 'Prof. Dr. ABM Abdullah', 'abm.abdullah@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000105', 2, 9, 'MBBS, MRCP (UK), FRCP (Edin)', 36, 2000.00, 'Professor – Medicine Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(255, 'Prof. Dr. AKM Anwar Ullah', 'anwar.ullah@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000106', 12, 9, 'MBBS, FCPS (Medicine), FRCP (Edin)', 30, 1800.00, 'Professor – Neuromedicine Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(256, 'Prof. Dr. Anisul Haque', 'anisul.haque@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000107', 12, 9, 'MBBS, PhD, FCPS (Medicine), FRCP (Edin)', 32, 2000.00, 'Professor – Brain, Stroke, Nerve & Neurology Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(257, 'Prof. Dr. Md. Shahidul Islam Selim', 'shahidul.selim@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000108', 3, 9, 'MBBS, MCPS (Medicine), MD (Nephrology), FACP, FASN (USA), FRCP (UK)', 30, 2000.00, 'Professor – Kidney Diseases & Medicine Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(258, 'Asst. Prof. Dr. Dulal Chandra Das', 'dulal.das@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000109', 30, 9, 'MBBS, FCPS (Pediatric Neurology)', 12, 1200.00, 'Assistant Professor – Child Neurological Disorders, Development & Autism Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(259, 'Prof. Dr. Md. Ahsan Ullah', 'ahsan.ullah@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000110', 19, 9, 'MBBS, FCPS (Physical Medicine)', 30, 1500.00, 'Professor – Arthritis, Pain, Paralysis & Physical Medicine Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(260, 'Prof. Dr. Syed Atiqul Haq', 'atiqul.haq@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000111', 18, 9, 'MBBS, FCPS (Medicine), FRCP, MD (Rheumatology)', 30, 2000.00, 'Professor – Rheumatology & Medicine Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(261, 'Dr. Muhammad Shoaib Momen Majumder', 'shoaib.majumder@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000112', 18, 9, 'MBBS, FCPS (Medicine), MACP (USA), MD (Rheumatology)', 16, 1500.00, 'Consultant – Rheumatology & Medicine Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(262, 'Prof. Dr. M. A. Salam', 'ma.salam@bsmmu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000113', 8, 9, 'MBBS, FCPS, FICS (USA), WHO Fellow (UK)', 32, 2000.00, 'Professor – Urology & Andrology Specialist at BSMMU PG Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Wed', '10:00:00', '16:00:00', '2026-05-06 04:48:30'),
(263, 'Dr. Lutfor Rahman', 'lutfor.rahman@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000114', 25, 10, 'MBBS, MS (CTS)', 25, 2000.00, 'Chief Cardiac Surgeon at Labaid Specialized Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(264, 'Dr. A P M Sohrabuzzaman', 'sohrabuzzaman@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000115', 1, 10, 'MBBS, MD (Cardiology)', 22, 1800.00, 'Senior Consultant Cardiologist, Director Cardiac Cath Lab & Heart Rhythm Services at Labaid Specialized Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(265, 'Dr. Mahbubor Rahman', 'mahbubor.rahman@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000116', 1, 10, 'MBBS, MD (Cardiology)', 20, 1500.00, 'Senior Consultant Cardiologist & Medicine Specialist, In-Charge CCU at Labaid Specialized Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(266, 'Prof. Dr. Abduz Zaher', 'abduz.zaher@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000117', 1, 10, 'MBBS, MD (Cardiology)', 30, 2000.00, 'Professor of Cardiology, Clinical and Interventional Cardiologist at Labaid Specialized Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Wed', '10:00:00', '17:00:00', '2026-05-06 04:48:30'),
(267, 'Dr. Arun Kumar Sharma', 'arun.sharma@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000118', 1, 10, 'MBBS, MD (Cardiology)', 18, 1500.00, 'Clinical & Interventional Cardiologist, Senior Consultant & In-charge CCU-2 at Labaid Specialized Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30');
INSERT INTO `doctors` (`id`, `full_name`, `email`, `password`, `phone`, `specialization_id`, `hospital_id`, `qualification`, `experience_years`, `consultation_fee`, `bio`, `profile_pic`, `is_verified`, `available_days`, `available_time_start`, `available_time_end`, `created_at`) VALUES
(268, 'Prof. Dr. Md. Sayedul Islam', 'sayedul.islam@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000119', 20, 10, 'MBBS, FCPS (Medicine), MD (Chest)', 30, 1500.00, 'Medicine, Asthma & Chest Diseases Specialist at Labaid Specialized Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(269, 'Dr. M Saifuddin', 'm.saifuddin@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000120', 21, 10, 'MBBS, FCPS (Medicine), MD (Endocrinology)', 18, 1500.00, 'Diabetes, Thyroid, Hormone & Medicine Specialist at Labaid Specialized Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(270, 'Dr. M. R. Islam', 'mr.islam@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000121', 6, 10, 'MBBS, MS (ENT)', 20, 1200.00, 'Ear Nose Throat & Head-Neck Surgeon at Labaid Specialized Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(271, 'Prof. Dr. Md. Ashraful Islam', 'ashraful.islam@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000122', 11, 10, 'MBBS, FCPS (Medicine), MD (Gastroenterology)', 30, 2000.00, 'Professor & Head (Ex) – Gastroenterology at Labaid Specialized Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(272, 'Prof. (Retd.) Dr. Nooruddin Ahmad', 'nooruddin.ahmad@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000123', 33, 10, 'MBBS, FCPS (Medicine), MD (Hepatology)', 35, 2000.00, 'Consultant Physician and Hepatologist, Former Chairman – Hepatology at Labaid Specialized Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Wed', '10:00:00', '16:00:00', '2026-05-06 04:48:30'),
(273, 'Prof. Dr. Md. Zulfiqur Rahman Khan', 'zulfiqur.khan@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000124', 33, 10, 'MBBS, FCPS (Surgery), MS (Surgery)', 32, 2000.00, 'Liver, Pancreas, Biliary & Laparoscopic Surgeon at Labaid Specialized Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(274, 'Prof. Dr. M Khademul Islam', 'khademul.islam@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000125', 16, 10, 'MBBS, FCPS (Surgery)', 30, 1800.00, 'General & Laparoscopic Surgeon, Ex. Professor and Head – Surgery at Labaid Specialized Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(275, 'Prof. Dr. Shahadot Hossain Sheikh', 'shahadot.sheikh@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000126', 27, 10, 'MBBS, FCPS (Surgery)', 28, 2000.00, 'Advanced Laparoscopic Colorectal Surgeon, Chairman – Colorectal Surgery at Labaid Specialized Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(276, 'Dr. Md. Saifullah', 'md.saifullah@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000127', 16, 10, 'MBBS, FCPS (Surgery)', 16, 1200.00, 'General, Laparoscopic, Colorectal & Cancer Surgeon at Labaid Specialized Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(277, 'Prof. Major (Rtd.) Dr. Laila Arjumand Banu', 'laila.banu@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000128', 7, 10, 'MBBS, FCPS (Obstetrics & Gynaecology)', 32, 2000.00, 'Chief Consultant – Obstetrics & Gynaecology at Labaid Specialized Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(278, 'Prof. Dr. Mariam Faruqui (Shati)', 'mariam.faruqui@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000129', 7, 10, 'MBBS, FCPS (Obstetrics & Gynaecology)', 28, 1800.00, 'Gynecologist, Obstetrician & Infertility Specialist at Labaid Specialized Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(279, 'Prof. Dr. Begum Hosne Ara', 'hosne.ara@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000130', 7, 10, 'MBBS, FCPS (Gynaecology)', 30, 1800.00, 'Laparoscopic Surgeon, Obstetrician and Gynecologist at Labaid Specialized Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Wed', '10:00:00', '16:00:00', '2026-05-06 04:48:30'),
(280, 'Prof. Dr. Md. Ashraf Ali', 'ashraf.ali@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000131', 12, 10, 'MBBS, FCPS (Medicine), MD (Neurology)', 32, 2000.00, 'Senior Consultant – Medicine & Neurology Specialist at Labaid Specialized Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(281, 'Prof. Dr. Subash Kanti Dey', 'subash.dey@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000132', 12, 10, 'MBBS, MD (Neurology)', 26, 2000.00, 'Stroke & Interventional Neurologist, Professor & Divisional Head at Labaid Specialized Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(282, 'Prof. Dr. M. Amjad Hossain', 'amjad.hossain@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000133', 4, 10, 'MBBS, MS (Orthopaedics)', 32, 2000.00, 'Chief Consultant & Head – Orthopaedic Surgery at Labaid Specialized Hospital.', 'default.png', 1, 'Sat,Sun,Mon,Tue,Wed', '09:00:00', '17:00:00', '2026-05-06 04:48:30'),
(283, 'Prof. Dr. Abu Zaffar Chowdhury (Biru)', 'abu.zaffar@labaidgroup.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01700000134', 4, 10, 'MBBS, MS (Orthopaedics)', 30, 2000.00, 'Arthroscopy & Replacement Surgeon, Ex. Chairman & Head – Orthopaedic Surgery at Labaid Specialized Hospital.', 'default.png', 1, 'Sun,Mon,Tue,Wed,Thu', '09:00:00', '17:00:00', '2026-05-06 04:48:30');

-- --------------------------------------------------------

--
-- Table structure for table `hospitals`
--

CREATE TABLE `hospitals` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hospitals`
--

INSERT INTO `hospitals` (`id`, `name`, `address`, `city`, `phone`, `email`, `website`, `description`, `is_verified`, `created_at`, `latitude`, `longitude`) VALUES
(1, 'Ibn Sina Hospital, Dhanmondi', 'House 48, Road 9/A, Dhanmondi, Dhaka 1209', 'Dhaka', '01766633012', 'info@ibnsina.com', 'www.ibnsinahospital.com', 'One of the leading private hospitals in Dhaka, offering comprehensive medical services.', 1, '2026-05-06 04:48:51', NULL, NULL),
(2, 'Dhaka Medical College Hospital', 'Bakshibazar, Dhaka 1000', 'Dhaka', '02-55165088', 'info@dmch.gov.bd', 'www.dmch.gov.bd', 'The largest government teaching hospital in Bangladesh with over 2600 beds.', 1, '2026-05-06 04:48:51', NULL, NULL),
(3, 'National Heart Foundation Hospital', 'Plot 7/2, Section 2, Mirpur, Dhaka 1216', 'Dhaka', '02-58053181', 'info@nhf.org.bd', 'www.nhf.org.bd', 'Specialized cardiac hospital providing advanced heart disease treatment and research.', 1, '2026-05-06 04:48:51', NULL, NULL),
(4, 'Popular Diagnostic Centre, Dhanmondi', 'House 16, Road 2, Dhanmondi, Dhaka 1205', 'Dhaka', '01713374472', 'info@populardiagnostic.com', 'www.populardiagnostic.com', 'Leading diagnostic and specialized hospital with modern medical facilities in Dhaka.', 1, '2026-05-06 04:48:51', NULL, NULL),
(5, 'Anwer Khan Modern Medical College Hospital', 'Road 8, Dhanmondi, Dhaka 1205', 'Dhaka', '09678010652', 'info@akmmch.com', 'www.akmmch.com', 'A reputed private medical college hospital providing quality healthcare services.', 1, '2026-05-06 04:48:51', NULL, NULL),
(6, 'Evercare Hospital Dhaka', 'Plot 81, Block E, Bashundhara R/A, Dhaka 1229', 'Dhaka', '02-55045555', 'info@evercare.com.bd', 'www.evercare.com.bd', 'International standard hospital with world-class facilities and experienced specialists.', 1, '2026-05-06 04:48:51', NULL, NULL),
(7, 'Square Hospital Ltd', '18/F, Bir Uttam Qazi Nuruzzaman Sarak, West Panthapath, Dhaka 1205', 'Dhaka', '02-55034000', 'info@squarehospital.com', 'www.squarehospital.com', 'A state-of-the-art private hospital offering advanced medical care across all specialties.', 1, '2026-05-06 04:48:51', NULL, NULL),
(8, 'United Hospital Limited', 'Plot 15, Road 71, Gulshan, Dhaka 1212', 'Dhaka', '02-55041234', 'info@uhlbd.com', 'www.uhlbd.com', 'One of the most advanced private hospitals in Bangladesh with international standard care.', 1, '2026-05-06 04:48:51', NULL, NULL),
(9, 'BSMMU – PG Hospital', 'Shahbagh, Dhaka 1000', 'Dhaka', '02-55165300', 'info@bsmmu.edu.bd', 'www.bsmmu.edu.bd', 'Bangladesh premier postgraduate medical university hospital with highly specialized doctors.', 1, '2026-05-06 04:48:51', NULL, NULL),
(10, 'Labaid Specialized Hospital', 'House 1, Road 4, Dhanmondi, Dhaka 1205', 'Dhaka', '01713374482', 'info@labaidgroup.com', 'www.labaidgroup.com', 'Renowned specialized hospital offering advanced diagnostics and treatment services in Dhaka.', 1, '2026-05-06 04:48:51', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `medical_history`
--

CREATE TABLE `medical_history` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `condition_name` varchar(150) DEFAULT NULL,
  `diagnosed_date` date DEFAULT NULL,
  `treatment` text DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `cancelled_by` enum('patient','doctor') DEFAULT NULL,
  `cancel_reason` text DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_tests`
--

CREATE TABLE `medical_tests` (
  `id` int(11) NOT NULL,
  `test_name` varchar(150) NOT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `fee` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('online','cash','pay_later') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `val_id` varchar(100) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','cancelled') DEFAULT 'pending',
  `payment_date` datetime DEFAULT NULL,
  `gateway_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `appointment_id`, `amount`, `payment_method`, `transaction_id`, `val_id`, `payment_status`, `payment_date`, `gateway_response`, `created_at`) VALUES
(1, 20, 2000.00, 'cash', NULL, NULL, 'pending', NULL, NULL, '2026-05-12 07:32:48'),
(2, 21, 2000.00, 'online', 'MC_21_1778571405', NULL, 'pending', NULL, NULL, '2026-05-12 07:36:45'),
(3, 22, 2000.00, 'online', 'MC_22_1778572175', '260512135250EavegBLXI2QFGW7', 'paid', '2026-05-12 13:52:56', '{\"status\":\"VALID\",\"tran_date\":\"2026-05-12 13:49:36\",\"tran_id\":\"MC_22_1778572175\",\"val_id\":\"260512135250EavegBLXI2QFGW7\",\"amount\":\"2000.00\",\"store_amount\":\"1950\",\"currency\":\"BDT\",\"bank_tran_id\":\"260512135250EgkkNgHkgAJs8Vz\",\"card_type\":\"BKASH-BKash\",\"card_no\":\"\",\"card_issuer\":\"BKash Mobile Banking\",\"card_brand\":\"MOBILEBANKING\",\"card_category\":\"MOBILE\",\"card_sub_brand\":\"\",\"card_issuer_country\":\"Bangladesh\",\"card_issuer_country_code\":\"BD\",\"currency_type\":\"BDT\",\"currency_amount\":\"2000.00\",\"currency_rate\":\"1.0000\",\"base_fair\":\"0.00\",\"value_a\":\"\",\"value_b\":\"\",\"value_c\":\"\",\"value_d\":\"\",\"emi_instalment\":\"0\",\"emi_amount\":\"0.00\",\"emi_description\":\"\",\"emi_issuer\":\"BKash Mobile Banking\",\"account_details\":\"\",\"risk_title\":\"Safe\",\"risk_level\":\"0\",\"discount_percentage\":\"0\",\"discount_amount\":\"0.00\",\"discount_remarks\":\"\",\"APIConnect\":\"DONE\",\"validated_on\":\"2026-05-12 13:52:55\",\"gw_version\":\"\",\"offer_avail\":1,\"card_ref_id\":\"dc1da4f52669828139e81ef5eb0f48a5a99ea054a131e00a562887d455417dd913\",\"isTokeizeSuccess\":0,\"campaign_code\":\"\"}', '2026-05-12 07:49:35');

-- --------------------------------------------------------

--
-- Table structure for table `pharmacies`
--

CREATE TABLE `pharmacies` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_open_24h` tinyint(1) DEFAULT 0,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pharmacies`
--

INSERT INTO `pharmacies` (`id`, `name`, `address`, `city`, `phone`, `is_open_24h`, `latitude`, `longitude`) VALUES
(1, 'Lazz Pharma', 'Dhanmondi, Dhaka', 'Dhaka', '01711111111', 1, 23.74610000, 90.37420000),
(2, 'Popular Pharmacy', 'Mirpur, Dhaka', 'Dhaka', '01711111112', 0, 23.80410000, 90.36670000),
(3, 'Nipa Pharmacy', 'Gulshan, Dhaka', 'Dhaka', '01711111113', 1, 23.79250000, 90.40780000),
(4, 'Medinova Pharmacy', 'Banani, Dhaka', 'Dhaka', '01711111114', 0, 23.79370000, 90.40660000),
(5, 'Shastho Pharmacy', 'Uttara, Dhaka', 'Dhaka', '01711111115', 1, 23.87590000, 90.37950000),
(6, 'Ibn Sina Pharmacy', 'Dhanmondi 27, Dhaka', 'Dhaka', '01711222111', 1, 23.74650000, 90.37560000),
(7, 'Square Pharmacy', 'Panthapath, Dhaka', 'Dhaka', '01711222112', 0, 23.75190000, 90.38690000),
(8, 'Beacon Pharmacy', 'Mohakhali, Dhaka', 'Dhaka', '01711222113', 1, 23.78080000, 90.40100000),
(9, 'City Pharmacy', 'Uttara Sector 6, Dhaka', 'Dhaka', '01711222114', 0, 23.86480000, 90.38500000),
(10, 'Health Point Pharmacy', 'Mirpur 10, Dhaka', 'Dhaka', '01711222115', 1, 23.80660000, 90.36580000),
(11, 'Green Pharmacy', 'Bashundhara R/A, Dhaka', 'Dhaka', '01711222116', 0, 23.81300000, 90.42640000),
(12, 'Life Care Pharmacy', 'Tejgaon, Dhaka', 'Dhaka', '01711222117', 1, 23.76720000, 90.40230000),
(13, 'Medico Pharmacy', 'Rayer Bazar, Dhaka', 'Dhaka', '01711222118', 0, 23.73890000, 90.36340000);

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `diagnosis` text DEFAULT NULL,
  `medicines` text DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `appointment_id`, `patient_id`, `doctor_id`, `diagnosis`, `medicines`, `instructions`, `follow_up_date`, `file_path`, `created_at`) VALUES
(1, 18, 4, 256, 'back pain\nlumber dislocation', 'napa – 2 times/day – 7 days\nsergel 20 – 3 times/day – 10 days', '', NULL, NULL, '2026-05-06 06:20:44'),
(2, 19, 4, 256, 'back pain\nfever', 'napa – 3 times/day – 10 days\nnapa extra – 3 times/day – 14 days', '', NULL, NULL, '2026-05-06 07:20:09');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `patient_id`, `doctor_id`, `rating`, `comment`, `is_approved`, `created_at`) VALUES
(5, 4, 283, 5, 'not bad', 1, '2026-05-06 06:29:17');

-- --------------------------------------------------------

--
-- Table structure for table `specializations`
--

CREATE TABLE `specializations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'fas fa-stethoscope',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `specializations`
--

INSERT INTO `specializations` (`id`, `name`, `icon`, `description`) VALUES
(1, 'General Medicine', 'fas fa-stethoscope', 'General health checkup and treatment'),
(2, 'Cardiology', 'fas fa-heartbeat', 'Heart and cardiovascular system'),
(3, 'Orthopedics', 'fas fa-bone', 'Bones, joints, and musculoskeletal system'),
(4, 'Neurology', 'fas fa-brain', 'Brain and nervous system disorders'),
(5, 'Pediatrics', 'fas fa-baby', 'Healthcare for infants and children'),
(6, 'Gynecology', 'fas fa-venus', 'Women reproductive health'),
(7, 'Dermatology', 'fas fa-allergies', 'Skin, hair, and nail conditions'),
(8, 'Ophthalmology', 'fas fa-eye', 'Eye and vision care'),
(9, 'ENT', 'fas fa-deaf', 'Ear, nose, and throat'),
(10, 'Psychiatry', 'fas fa-brain', 'Mental health and behavioral disorders'),
(11, 'Dentistry', 'fas fa-tooth', 'Oral and dental health'),
(12, 'Oncology', 'fas fa-ribbon', 'Cancer diagnosis and treatment');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `date_of_birth`, `gender`, `blood_group`, `address`, `profile_pic`, `created_at`) VALUES
(4, 'Faisal Ahmed', 'faisal@gmail.com', '$2y$10$04YoP5prE03GpKu2o3UXdePEZIXNCD0byex1iRwezcrq69a7r4xtm', '', '0000-00-00', 'Male', 'B-', '', 'default.png', '2026-05-06 06:01:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `ambulances`
--
ALTER TABLE `ambulances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ambulance_feedback`
--
ALTER TABLE `ambulance_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `ambulance_id` (`ambulance_id`);

--
-- Indexes for table `ambulance_requests`
--
ALTER TABLE `ambulance_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `ambulance_id` (`ambulance_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_double_booking` (`doctor_id`,`appointment_date`,`appointment_time`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `specialization_id` (`specialization_id`),
  ADD KEY `hospital_id` (`hospital_id`);

--
-- Indexes for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `medical_tests`
--
ALTER TABLE `medical_tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hospital_id` (`hospital_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `pharmacies`
--
ALTER TABLE `pharmacies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `specializations`
--
ALTER TABLE `specializations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ambulances`
--
ALTER TABLE `ambulances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ambulance_feedback`
--
ALTER TABLE `ambulance_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ambulance_requests`
--
ALTER TABLE `ambulance_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=284;

--
-- AUTO_INCREMENT for table `hospitals`
--
ALTER TABLE `hospitals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `medical_history`
--
ALTER TABLE `medical_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_tests`
--
ALTER TABLE `medical_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pharmacies`
--
ALTER TABLE `pharmacies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `specializations`
--
ALTER TABLE `specializations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ambulance_feedback`
--
ALTER TABLE `ambulance_feedback`
  ADD CONSTRAINT `ambulance_feedback_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `ambulance_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ambulance_feedback_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ambulance_feedback_ibfk_3` FOREIGN KEY (`ambulance_id`) REFERENCES `ambulances` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ambulance_requests`
--
ALTER TABLE `ambulance_requests`
  ADD CONSTRAINT `ambulance_requests_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ambulance_requests_ibfk_2` FOREIGN KEY (`ambulance_id`) REFERENCES `ambulances` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_history`
--
ALTER TABLE `medical_history`
  ADD CONSTRAINT `medical_history_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_history_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
