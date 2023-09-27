-- Use the 'mysql' database to run the following commands
USE mysql;

-- Create the 'slim_docker_api' database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `slim_docker_api` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Switch to the 'slim_docker_api' database
USE `slim_docker_api`;

-- Table structure for table `categories`
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `categories`
INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'alexander vincent', 'lexis is a coder', '2023-09-19 05:14:28', '2023-09-19 05:14:28');
