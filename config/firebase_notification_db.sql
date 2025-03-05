-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 05, 2025 at 05:36 AM
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
-- Database: `push_notification_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `chash`
--

CREATE TABLE `chash` (
  `id` int(11) NOT NULL,
  `pass_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `pass_chash` char(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chash`
--

INSERT INTO `chash` (`id`, `pass_hash`, `created_at`, `pass_chash`) VALUES
(1, '7051d0b2b641ad2fc4f3ac7bd310f36589a15b0d6cba81efc7dc1ead000715d8', '2025-03-05 04:15:07', '7051d0b2b641ad2fc4f3ac7bd310f36589a15b0d6cba81efc7dc1ead000715d8');

-- --------------------------------------------------------

--
-- Table structure for table `notification_configs`
--

CREATE TABLE `notification_configs` (
  `id` int(11) NOT NULL,
  `firebase_server_key` varchar(255) DEFAULT NULL,
  `firebase_project_id` varchar(100) DEFAULT NULL,
  `firebase_topic` varchar(100) DEFAULT NULL,
  `google_services_json` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_configs`
--

INSERT INTO `notification_configs` (`id`, `firebase_server_key`, `firebase_project_id`, `firebase_topic`, `google_services_json`, `created_by`, `created_at`, `updated_at`) VALUES
(1, NULL, 'name-xxxxxx', 'allDevices', '{\r\n  \"type\": \"service_account\",\r\n  \"project_id\": \"xxxxxxxxxx\",\r\n  \"private_key_id\": \"xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx\",\r\n  \"private_key\": \"-----BEGIN PRIVATE KEY-----\\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDp7eoxseSceA/y\\npFDAcstaQIJwcZsjNRHb1c/HaNOwTkTkGi1VQZG5N4de4GePObTxkRfLMJzd593L\\nE00XWH1GbguQm/xAYxhysAYUH3kNcDJKVSPU/+luNMCeLTZsZir5IRyf5jpk9Z5b\\nltzLxJsQiFUlme/+bRLN98l7vXwtKeGhRzhKdPCWuaQmqX6I49YhHvkul6+mCoLt\\nDyzjGsl1DYGx5FqPudItyndTNd9gRnv6VTexCLAAV7MkdF4CmpHu7DA27ek6fS6+\\nPWnvz1SY2j6HcivNrISueuSMV4zY5jJmmBTAOcp/xX7RPA5k9rqOJlV3p7Y05ll4\\ndPZQwLu/AgMBAAECggEAAZnTtvhZMKrvrQDr3o1Kj/kOC3fzkFeeIiuB6wLwI+49\\nMXRuZ/11YlebcQ5HFydVcivEhmr0P96E2Y1twyGjZdZ99MTkgopLEgnbWMroWKuy\\nZGjVwHDp54ur38k+bJp8Y80StZNtReCab1UGeMyFJSVRs9qsVkjnzooKEnM3BNYX\\nsD0Fg4E677DjRlD5F9W2nyHTssxUVbrijEIXvxOrhpm7GV6FQpSUCGIgOIAqciBH\\nQAQs5DbTzZ2ZRCR/E8mT6Q5N+6OrBM4SFcI9GGbbhVFO99FrIPQAIhHeL7N8KZjo\\nA5+16ZHTugmC+m76euCbhUUdfmP3tvmqXZu1DzyJeQKBgQD080rR8MdX9AHGE1qg\\ndxYPX21kjorLxMEhQpJO+kHHKi61zONOfsdHBkkLKiCKOmdTGB7h8Ix/dc0gxDCD\\ncvWnmsrqEEABpRpZsCcBqjFa7oYfXPYz28Pb1Lxb7J1rr8Uyn27PuEt0C9/F+GC3\\nqNOH3me4MvIW9wCBCjw7+v3JJwKBgQD0e1ndoXVoKc+TL2u+gBO5JVN/JiujD65A\\nw4W2JIsJju7zONeXSB8AMEMlmHPURJEILiuK5ghPHG89wtyzTMXoijuCHb7wKSuH\\nPUdLFNdiTj5ExBeKJBCQ1lGm1iPNxzLqWydATgBvEwMb6SYpcHUqMc67O1A/GJRQ\\nI/ohZR4nqQKBgA3WKGXPihMnz2nW8gmacH6Rz2Ycvy5fgOFWF1mqvUh404alejmW\\n477ZVgrxaEEmp2uEM0pkAiu5BctSCcODHOlIzymFnXf6UZC7aJipenw+eQkQgT/Q\\nrDGgxLsUlJfep+8CHopRSMHXYd6W9y+os7o7D/TRu+ccMMUZROnGdcuFAoGBAJk/\\nGL2MD4QTcUHZX7gxoCtV8lipHIFBuwBtjsr1bRG4vp9G6hyx4HzFw9E1FqXftOlj\\nx3dxaZPtRu13z8+0N/njbBnLe3we0mIbTy0JC1lbojyIjhjRMnEXZmclo5vBWXD9\\nYkU0n6EAUdqU8o7XJxojzmoea9ahGZaCdq+oKpbBAoGAVIjnuekx3kfgpsJKsp87\\ncEgLcD2mBJmBIiIT3aZCQze+tgthrTt2mpj3ZgAHmphr26GfAAaVr2ZnXmshOSs2\\ndtc+4AnCHvlBffsBa4s2YNT6rZ+A1KE1Rr/pSK0pLajNGjSi9ahA4a2NJqGCBA4k\\neRY9T+IAW9PmhAXqyh7kJS8=\\n-----END PRIVATE KEY-----\\n\",\r\n  \"client_email\": \"firebase-adminsdk-no5tt\",\r\n  \"client_id\": \"102782858081526126876\",\r\n  \"auth_uri\": \"https://accounts.google.com/o/oauth2/auth\",\r\n  \"token_uri\": \"https://oauth2.googleapis.com/token\",\r\n  \"auth_provider_x509_cert_url\": \"https://www.googleapis.com/oauth2/v1/certs\",\r\n  \"client_x509_cert_url\": \"https://www.googleapis.com/robot/v1/metadata/x509/firebase-adminsdk-no5tt%40hsc-bmt-guide-boi.iam.gserviceaccount.com\",\r\n  \"universe_domain\": \"googleapis.com\"\r\n}\r\n', 1, '2025-03-05 02:49:24', '2025-03-05 04:35:39');

-- --------------------------------------------------------

--
-- Table structure for table `notification_history`
--

CREATE TABLE `notification_history` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL,
  `sent_by` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_history`
--

INSERT INTO `notification_history` (`id`, `title`, `message`, `image_url`, `action_url`, `sent_by`, `status`, `sent_at`) VALUES
(1, 'নতুন নোটিফিকেশন', 'বিবরণ', 'https://cdn.jsdelivr.net/gh/hscbmt/img@main/welcome.png', '', 1, 'success', '2025-03-05 03:14:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@example.com', '123456', '2025-01-16 01:39:55', '2025-01-16 02:56:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chash`
--
ALTER TABLE `chash`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_configs`
--
ALTER TABLE `notification_configs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `notification_history`
--
ALTER TABLE `notification_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sent_by` (`sent_by`);

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
-- AUTO_INCREMENT for table `chash`
--
ALTER TABLE `chash`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notification_configs`
--
ALTER TABLE `notification_configs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notification_history`
--
ALTER TABLE `notification_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notification_configs`
--
ALTER TABLE `notification_configs`
  ADD CONSTRAINT `notification_configs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `notification_history`
--
ALTER TABLE `notification_history`
  ADD CONSTRAINT `notification_history_ibfk_1` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
