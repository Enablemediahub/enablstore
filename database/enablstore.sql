-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: enablstore
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `enablstore`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `enablstore` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `enablstore`;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domains`
--

DROP TABLE IF EXISTS `domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domains` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL,
  `tenant_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domains_domain_unique` (`domain`),
  KEY `domains_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `domains_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domains`
--

LOCK TABLES `domains` WRITE;
/*!40000 ALTER TABLE `domains` DISABLE KEYS */;
/*!40000 ALTER TABLE `domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2019_09_15_000010_create_tenants_table',1),(5,'2019_09_15_000020_create_domains_table',1),(6,'2026_09_17_222236_create_permission_tables',1),(7,'2026_09_17_223000_extend_tenants_table',1),(8,'2026_09_17_223010_create_plans_table',1),(9,'2026_09_17_223020_create_subscriptions_table',1),(10,'2026_09_17_223030_create_payments_table',1),(11,'2026_09_17_230100_create_super_admins_table',1),(12,'2026_09_20_000001_add_username_and_tenant_to_users_table',1),(13,'2026_09_20_000002_create_platform_settings_table',1),(14,'2026_09_20_000003_add_role_to_users_table',1),(15,'2026_09_20_000004_add_pos_pin_to_users_table',1),(16,'2026_09_20_000005_add_username_to_super_admins_table',1),(17,'2026_09_21_180000_make_user_email_nullable',2),(18,'2026_09_21_183000_add_subscriber_codes_to_tenants',3),(19,'2026_09_26_000002_create_tenant_paystack_settings_table',4),(20,'2026_09_26_000003_create_tenant_payment_intents_table',5),(21,'2026_09_26_000004_add_test_keys_to_tenant_paystack_settings',6),(22,'2026_09_27_000003_add_amount_minor_to_subscriptions',7),(23,'2026_09_27_000004_add_billing_interval_months_to_plans',7),(24,'2026_09_27_000005_backfill_plan_billing_interval_months',8),(25,'2026_09_28_000002_add_whatsapp_phone_to_tenants',9);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(255) NOT NULL,
  `subscription_id` bigint(20) unsigned DEFAULT NULL,
  `provider` varchar(30) NOT NULL DEFAULT 'paystack',
  `provider_reference` varchar(255) NOT NULL,
  `amount_minor` bigint(20) unsigned NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'GHS',
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_provider_reference_unique` (`provider_reference`),
  KEY `payments_subscription_id_foreign` (`subscription_id`),
  KEY `payments_tenant_id_status_index` (`tenant_id`,`status`),
  CONSTRAINT `payments_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,'royal',2,'manual','manual-ef1a934b-d047-4e76-8109-b28cb57bc77f',9900,'GHS','paid','2026-09-27 14:50:25','{\"source\":\"super_admin_manual_activation\"}','2026-09-27 14:50:25','2026-09-27 14:50:25'),(2,'the-meat-box',3,'manual','manual-38d11e30-da01-4c26-ac44-054419548e3f',100000,'GHS','paid','2026-09-27 17:12:17','{\"source\":\"super_admin_manual_enrollment\"}','2026-09-27 17:12:17','2026-09-27 17:12:17');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price_minor` bigint(20) unsigned NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'GHS',
  `billing_interval` varchar(20) NOT NULL DEFAULT 'monthly',
  `billing_interval_months` smallint(5) unsigned NOT NULL DEFAULT 1,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`features`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plans_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plans`
--

LOCK TABLES `plans` WRITE;
/*!40000 ALTER TABLE `plans` DISABLE KEYS */;
INSERT INTO `plans` VALUES (1,'Starter','starter','Core tools for a growing retail shop.',9900,'GHS','monthly',1,'[\"products\",\"inventory\",\"pos\",\"online_store\"]',1,'2026-09-20 20:09:02','2026-09-20 20:09:02'),(2,'BUSINESS PACKAGE','business-package',NULL,100000,'GHS','yearly',12,'[\"pos\",\"online_store\",\"restaurant_foodstore\",\"inventory\",\"products\"]',1,'2026-09-27 16:38:25','2026-09-27 16:42:29');
/*!40000 ALTER TABLE `plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `platform_settings`
--

DROP TABLE IF EXISTS `platform_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `platform_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `platform_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `platform_settings`
--

LOCK TABLES `platform_settings` WRITE;
/*!40000 ALTER TABLE `platform_settings` DISABLE KEYS */;
INSERT INTO `platform_settings` VALUES (1,'login_wallpaper','platform/fsOICBcm0f65l9xNgJ64X0CZxN7TEeBZn2QQhU5G.jpg','2026-09-20 20:30:14','2026-09-28 11:29:56'),(2,'dashboard_wallpaper','platform/gcMQYE5FNsNuRA6JGsGM3xfOxknN0M9fk5xlPxp2.jpg','2026-09-21 14:32:29','2026-09-28 11:29:40'),(3,'pos_hero_image','platform/mgZZWGjemmsvRflHh8IXPbyraqtqu16o323yKlE3.jpg','2026-09-25 16:07:20','2026-09-28 11:29:33'),(4,'foodstore_hero_image','platform/KB0WhqPGCQpEcALCiUKZNHIGb1F6nrpAyXO4oXyw.jpg','2026-09-27 17:42:29','2026-09-28 11:53:30');
/*!40000 ALTER TABLE `platform_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(255) NOT NULL,
  `plan_id` bigint(20) unsigned NOT NULL,
  `amount_minor` bigint(20) unsigned DEFAULT NULL,
  `provider` varchar(30) NOT NULL DEFAULT 'paystack',
  `provider_reference` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'trialing',
  `starts_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `renews_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `grace_ends_at` timestamp NULL DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscriptions_provider_reference_unique` (`provider_reference`),
  KEY `subscriptions_plan_id_foreign` (`plan_id`),
  KEY `subscriptions_tenant_id_status_index` (`tenant_id`,`status`),
  CONSTRAINT `subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`),
  CONSTRAINT `subscriptions_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
INSERT INTO `subscriptions` VALUES (1,'demo',1,NULL,'internal',NULL,'active','2026-09-20 20:09:02','2026-10-20 20:09:02',NULL,NULL,NULL,'2026-09-20 20:09:02','2026-09-20 20:09:02'),(2,'royal',1,9900,'internal',NULL,'active','2026-09-28 12:30:45','2026-11-21 15:49:09',NULL,NULL,'{\"features\":[\"pos\",\"online_store\",\"restaurant_foodstore\",\"foodstore_online\"]}','2026-09-21 15:49:09','2026-09-28 11:30:45'),(3,'the-meat-box',2,100000,'internal',NULL,'active','2026-09-28 18:23:11','2027-09-27 17:12:17',NULL,NULL,'{\"features\":[\"pos\",\"online_store\",\"restaurant_foodstore\",\"foodstore_online\",\"sales_expenses\",\"audit_log\",\"whatsapp_orders\"]}','2026-09-27 17:12:17','2026-09-28 17:23:11');
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `super_admins`
--

DROP TABLE IF EXISTS `super_admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `super_admins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `super_admins_email_unique` (`email`),
  UNIQUE KEY `super_admins_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `super_admins`
--

LOCK TABLES `super_admins` WRITE;
/*!40000 ALTER TABLE `super_admins` DISABLE KEYS */;
INSERT INTO `super_admins` VALUES (1,'Enablstore Admin','superadmin','admin@enablstore.test',NULL,'$2y$12$PTBZ81cpyyuW6izliVoKsuf9gKaPeKESNA.c0QOMXjF/WzLJ93sEu',NULL,'2026-09-20 20:09:02','2026-09-20 20:09:02'),(2,'Dale Quist',NULL,'crepindale@gmail.com',NULL,'$2y$12$9feVzCCgzvliMDymFjP3Vulsg1ZbBBI4hXMNE8gdMWUtvwXxErXvG',NULL,'2026-09-21 19:02:33','2026-09-26 21:14:49');
/*!40000 ALTER TABLE `super_admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_payment_intents`
--

DROP TABLE IF EXISTS `tenant_payment_intents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenant_payment_intents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(255) NOT NULL,
  `reference` varchar(255) NOT NULL,
  `context` varchar(30) NOT NULL,
  `amount_minor` bigint(20) unsigned NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_payment_intents_reference_unique` (`reference`),
  KEY `tenant_payment_intents_tenant_id_context_status_index` (`tenant_id`,`context`,`status`),
  KEY `tenant_payment_intents_tenant_id_index` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_payment_intents`
--

LOCK TABLES `tenant_payment_intents` WRITE;
/*!40000 ALTER TABLE `tenant_payment_intents` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_payment_intents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_paystack_settings`
--

DROP TABLE IF EXISTS `tenant_paystack_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenant_paystack_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` varchar(255) NOT NULL,
  `public_key` varchar(255) DEFAULT NULL,
  `secret_key` text DEFAULT NULL,
  `test_public_key` varchar(255) DEFAULT NULL,
  `test_secret_key` text DEFAULT NULL,
  `mode` varchar(10) NOT NULL DEFAULT 'live',
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_paystack_settings_tenant_id_unique` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_paystack_settings`
--

LOCK TABLES `tenant_paystack_settings` WRITE;
/*!40000 ALTER TABLE `tenant_paystack_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_paystack_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenants`
--

DROP TABLE IF EXISTS `tenants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenants` (
  `id` varchar(255) NOT NULL,
  `subscriber_code` varchar(20) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `whatsapp_phone` varchar(40) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'trial',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenants_slug_unique` (`slug`),
  UNIQUE KEY `tenants_subscriber_code_unique` (`subscriber_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenants`
--

LOCK TABLES `tenants` WRITE;
/*!40000 ALTER TABLE `tenants` DISABLE KEYS */;
INSERT INTO `tenants` VALUES ('demo','ES001',NULL,NULL,NULL,NULL,NULL,'trial','2026-09-20 20:09:02','2026-09-21 16:48:05','{\"subscriber_code\":\"ES001\",\"name\":\"Demo Market\",\"slug\":\"demo\",\"email\":\"demo@enablstore.test\",\"phone\":null,\"status\":\"active\",\"created_at\":\"2026-09-20 21:09:02\",\"updated_at\":\"2026-09-20 21:09:02\"}'),('royal','ES002',NULL,NULL,NULL,NULL,NULL,'trial','2026-09-21 15:49:06','2026-09-27 14:50:25','{\"subscriber_code\":\"ES002\",\"name\":\"Royal Supermarket\",\"slug\":\"royal\",\"email\":\"royal@enablestore.com\",\"phone\":\"0241786330\",\"status\":\"active\",\"created_at\":\"2026-09-21 16:49:06\",\"updated_at\":\"2026-09-21 16:49:06\",\"tenancy_db_name\":\"tenantroyal\"}'),('the-meat-box',NULL,NULL,NULL,NULL,NULL,NULL,'trial','2026-09-27 17:12:16','2026-09-28 15:59:59','{\"subscriber_code\":\"ES003\",\"name\":\"THE MEAT BOX\",\"slug\":\"the-meat-box\",\"email\":null,\"phone\":null,\"whatsapp_phone\":\"+233241786330\",\"status\":\"active\",\"created_at\":\"2026-09-27 17:12:16\",\"updated_at\":\"2026-09-27 17:12:16\",\"tenancy_db_name\":\"tenantthe-meat-box\"}');
/*!40000 ALTER TABLE `tenants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `tenant_id` varchar(255) DEFAULT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'cashier',
  `pos_pin_hash` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_tenant_id_index` (`tenant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (5,'Demo Store Owner','ES001-admin','admin@enablstore.test','demo','admin',NULL,NULL,'$2y$12$xWGDBVUjcfFtfdw5koPqb.ieQjKpHlVAq5zFVsjRpL.gIKHikcO1i',NULL,'2026-09-20 20:09:03','2026-09-21 16:48:05'),(6,'demo-dale','ES001-dale','dale@enablstore.test','demo','cashier','$2y$12$fyNpGjZt4acJj4GCdpIIJuUwczk4WIqF93A8o5RXXZGxOfnaj1imC',NULL,'$2y$12$nAcv12AuDsg1ZtpV9WUJ9OfBYIQwfKwlqL1KAnFIwXH64MC30KDOq',NULL,'2026-09-20 20:09:03','2026-09-21 16:48:05'),(7,'Auntie Christy','es002-royal','royal@enablestore.com','royal','admin',NULL,NULL,'$2y$12$6DJMNflrz6M4.q1I0Tb.beYMa/IRIMUXE5hWR8DuUX7TPvN2e8BCq',NULL,'2026-09-21 15:49:09','2026-09-21 18:09:12'),(8,'Regina','ES002-regina',NULL,'royal','cashier','$2y$12$YS5j29Y06nCzRMk.SowHLuPiQG4SMLgR/RcVFckrRiEFPu7iexPLS',NULL,'$2y$12$pAdl4MBnTngYGG.tAN244OXtcFAFGBjxxB/P7JXAIVmjcfYEbBdCK',NULL,'2026-09-21 16:13:46','2026-09-21 16:48:05'),(10,'Samuel Kyei-Berko','ES003-tmb',NULL,'the-meat-box','admin',NULL,NULL,'$2y$12$qT2aps9NjNPmynIMB7BqEuJ0e/09xB1StfSoJtL0aUQoefxLNJZXa',NULL,'2026-09-27 17:12:18','2026-09-27 17:12:18'),(11,'Joana','ES003-joana',NULL,'the-meat-box','cashier','$2y$12$fhW/DN5NtcZVdMKAxFLOMuNNPEEbL0XXPAeFzcHOghF/j8BAlxbPq',NULL,'$2y$12$E..ErsFD6IbByS0/9Ciocu7q9UakCJ1dJk0BdGkchIwuWRrlMF0TK',NULL,'2026-09-27 17:19:11','2026-09-27 17:19:11');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'enablstore'
--

--
-- Dumping routines for database 'enablstore'
--

--
-- Current Database: `tenantthe-meat-box`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `tenantthe-meat-box` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `tenantthe-meat-box`;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `auditable_type` varchar(255) DEFAULT NULL,
  `auditable_id` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Groceries & Pantry','groceries-pantry','2026-09-28 11:33:14','2026-09-28 11:33:14'),(2,'Beverages','beverages','2026-09-28 11:33:14','2026-09-28 11:33:14'),(3,'Fresh Produce','fresh-produce','2026-09-28 11:33:14','2026-09-28 11:33:14'),(4,'Meat, Fish & Seafood','meat-fish-seafood','2026-09-28 11:33:14','2026-09-28 11:33:14'),(5,'Local & Traditional Foods','local-traditional-foods','2026-09-28 11:33:14','2026-09-28 11:33:14'),(6,'Household Cleaning','household-cleaning','2026-09-28 11:33:14','2026-09-28 11:33:14'),(7,'Personal Care & Beauty','personal-care-beauty','2026-09-28 11:33:14','2026-09-28 11:33:14'),(8,'Baby & Kids','baby-kids','2026-09-28 11:33:14','2026-09-28 11:33:14'),(9,'Health & Wellness','health-wellness','2026-09-28 11:33:14','2026-09-28 11:33:14'),(10,'Home & Kitchen','home-kitchen','2026-09-28 11:33:14','2026-09-28 11:33:14'),(11,'Electronics & Accessories','electronics-accessories','2026-09-28 11:33:14','2026-09-28 11:33:14'),(12,'Fashion & Footwear','fashion-footwear','2026-09-28 11:33:14','2026-09-28 11:33:14'),(13,'Stationery & Office','stationery-office','2026-09-28 11:33:14','2026-09-28 11:33:14'),(14,'Automotive','automotive','2026-09-28 11:33:14','2026-09-28 11:33:14');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(80) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `amount_minor` bigint(20) unsigned NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'GHS',
  `payment_method` varchar(30) NOT NULL,
  `spent_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_spent_at_index` (`spent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_stocks`
--

DROP TABLE IF EXISTS `inventory_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `low_stock_threshold` int(10) unsigned NOT NULL DEFAULT 5,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_stocks_product_id_unique` (`product_id`),
  CONSTRAINT `inventory_stocks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_stocks`
--

LOCK TABLES `inventory_stocks` WRITE;
/*!40000 ALTER TABLE `inventory_stocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_09_17_230000_create_tenant_users_table',1),(2,'2026_09_17_230010_create_categories_table',1),(3,'2026_09_17_230020_create_products_table',1),(4,'2026_09_17_230030_create_inventory_stocks_table',1),(5,'2026_09_17_230040_create_tenant_settings_table',1),(6,'2026_09_17_230050_create_sales_tables',1),(7,'2026_09_20_000003_add_catalogue_channels_to_products',1),(8,'2026_09_20_000004_seed_ghanaian_market_categories',1),(9,'2026_09_20_000005_add_pos_operations_tables',1),(10,'2026_09_20_000006_add_purchase_date_to_stock_movements',1),(11,'2026_09_20_000006_add_purchase_packaging_to_products',1),(12,'2026_09_21_000001_add_storefront_fields_to_products',1),(13,'2026_09_21_000002_add_customer_details_to_sales_table',1),(14,'2026_09_26_000001_add_cashier_name_to_sales_table',1),(15,'2026_09_26_000002_create_sale_payments_table',1),(16,'2026_09_26_000003_add_storefront_order_fields_to_sales',1),(17,'2026_09_26_000004_add_cash_receipt_and_customer_email_fields',1),(18,'2026_09_26_000005_add_external_payment_confirmation',1),(19,'2026_09_26_000006_create_restaurant_foodstore_tables',1),(20,'2026_09_27_000001_add_foodstore_menu_items_to_sale_items',1),(21,'2026_09_27_000002_add_package_and_image_fields_to_restaurant_menu_items',1),(22,'2026_09_27_000003_add_customer_phone_to_restaurant_orders',1),(23,'2026_09_28_000001_add_option_groups_to_restaurant_foodstore',2),(24,'2026_09_28_000002_create_expenses_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `price_minor` bigint(20) unsigned NOT NULL,
  `compare_at_price_minor` bigint(20) unsigned DEFAULT NULL,
  `cost_minor` bigint(20) unsigned DEFAULT NULL,
  `purchase_unit` varchar(40) NOT NULL DEFAULT 'unit',
  `units_per_purchase` int(10) unsigned NOT NULL DEFAULT 1,
  `currency` varchar(3) NOT NULL DEFAULT 'GHS',
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `image_gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`image_gallery`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `available_in_pos` tinyint(1) NOT NULL DEFAULT 1,
  `available_online` tinyint(1) NOT NULL DEFAULT 1,
  `is_online_deal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  UNIQUE KEY `products_barcode_unique` (`barcode`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_menu_items`
--

DROP TABLE IF EXISTS `restaurant_menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_menu_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `category` varchar(60) NOT NULL DEFAULT 'Mains',
  `description` text DEFAULT NULL,
  `price_minor` bigint(20) unsigned NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `unit_label` varchar(32) NOT NULL DEFAULT 'plate',
  `image_path` varchar(255) DEFAULT NULL,
  `option_groups` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`option_groups`)),
  PRIMARY KEY (`id`),
  KEY `restaurant_menu_items_is_available_category_index` (`is_available`,`category`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_menu_items`
--

LOCK TABLES `restaurant_menu_items` WRITE;
/*!40000 ALTER TABLE `restaurant_menu_items` DISABLE KEYS */;
INSERT INTO `restaurant_menu_items` VALUES (1,'Grilled Pork','Meat, Fish & Seafood',NULL,6000,1,'2026-09-28 15:53:47','2026-09-28 15:53:47','plate','foodstore/the-meat-box/menu/KKiaZirLtIpUd8qqZe7ZUI3NTJpvFkkMlMPL6MoT.jpg','[]'),(2,'Jollof','Local & Traditional Foods',NULL,3000,1,'2026-09-28 16:14:36','2026-09-28 16:14:36','plate','foodstore/the-meat-box/menu/c6MgP6uD3ZfJuIRnOTl2YXMs16F5Z5gh80alqodk.jpg','[{\"id\":\"949407c1-efd2-420b-84d5-e1cb0ea04dbd\",\"name\":\"Protein\",\"required\":false,\"multiple\":true,\"options\":[{\"id\":\"da66e676-525b-4075-8ba2-67c90b3f82b8\",\"name\":\"Chicken\",\"price_minor\":3000},{\"id\":\"cfa815c9-d91d-4132-92d9-d14d480fa7a0\",\"name\":\"Goat\",\"price_minor\":4000},{\"id\":\"bd1fe12d-7c6e-4604-9a94-33ca33cdb5ae\",\"name\":\"Beef\",\"price_minor\":2000}]}]');
/*!40000 ALTER TABLE `restaurant_menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_order_items`
--

DROP TABLE IF EXISTS `restaurant_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_order_id` bigint(20) unsigned NOT NULL,
  `restaurant_menu_item_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(120) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price_minor` bigint(20) unsigned NOT NULL,
  `line_total_minor` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `selected_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`selected_options`)),
  PRIMARY KEY (`id`),
  KEY `restaurant_order_items_restaurant_order_id_foreign` (`restaurant_order_id`),
  KEY `restaurant_order_items_restaurant_menu_item_id_foreign` (`restaurant_menu_item_id`),
  CONSTRAINT `restaurant_order_items_restaurant_menu_item_id_foreign` FOREIGN KEY (`restaurant_menu_item_id`) REFERENCES `restaurant_menu_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `restaurant_order_items_restaurant_order_id_foreign` FOREIGN KEY (`restaurant_order_id`) REFERENCES `restaurant_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_order_items`
--

LOCK TABLES `restaurant_order_items` WRITE;
/*!40000 ALTER TABLE `restaurant_order_items` DISABLE KEYS */;
INSERT INTO `restaurant_order_items` VALUES (1,1,1,'Grilled Pork',1,6000,6000,'2026-09-28 15:55:31','2026-09-28 15:55:31','[]'),(2,2,2,'Jollof',1,8000,8000,'2026-09-28 16:19:51','2026-09-28 16:19:51','[{\"group\":\"Protein\",\"name\":\"Chicken\",\"price_minor\":3000},{\"group\":\"Protein\",\"name\":\"Beef\",\"price_minor\":2000}]'),(3,3,2,'Jollof',1,12000,12000,'2026-09-28 16:34:46','2026-09-28 16:34:46','[{\"group\":\"Protein\",\"name\":\"Chicken\",\"price_minor\":3000,\"quantity\":1},{\"group\":\"Protein\",\"name\":\"Goat\",\"price_minor\":4000,\"quantity\":1},{\"group\":\"Protein\",\"name\":\"Beef\",\"price_minor\":2000,\"quantity\":1}]');
/*!40000 ALTER TABLE `restaurant_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_orders`
--

DROP TABLE IF EXISTS `restaurant_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `table_label` varchar(40) DEFAULT NULL,
  `customer_name` varchar(120) DEFAULT NULL,
  `customer_phone` varchar(40) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'queued',
  `total_minor` bigint(20) unsigned NOT NULL,
  `created_by_name` varchar(120) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `restaurant_orders_status_created_at_index` (`status`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_orders`
--

LOCK TABLES `restaurant_orders` WRITE;
/*!40000 ALTER TABLE `restaurant_orders` DISABLE KEYS */;
INSERT INTO `restaurant_orders` VALUES (1,NULL,'DALE','0241786330',NULL,'served',6000,'Online customer','2026-09-28 15:55:31','2026-09-28 16:37:05'),(2,NULL,'Mawuse','0554310034',NULL,'queued',8000,'Online customer via WhatsApp','2026-09-28 16:19:51','2026-09-28 16:19:51'),(3,NULL,'Hilda','0554310034','ALLERGY ALERT: Peanuts, Soy','queued',12000,'Online customer via WhatsApp','2026-09-28 16:34:46','2026-09-28 16:34:46');
/*!40000 ALTER TABLE `restaurant_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price_minor` bigint(20) unsigned NOT NULL,
  `line_total_minor` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `restaurant_menu_item_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(120) DEFAULT NULL,
  `selected_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`selected_options`)),
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_product_id_foreign` (`product_id`),
  KEY `sale_items_restaurant_menu_item_id_foreign` (`restaurant_menu_item_id`),
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `sale_items_restaurant_menu_item_id_foreign` FOREIGN KEY (`restaurant_menu_item_id`) REFERENCES `restaurant_menu_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_items`
--

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `sale_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_payments`
--

DROP TABLE IF EXISTS `sale_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `method` varchar(30) NOT NULL,
  `amount_minor` bigint(20) unsigned NOT NULL,
  `cash_received_minor` bigint(20) unsigned DEFAULT NULL,
  `externally_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `provider_reference` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sale_payments_provider_reference_unique` (`provider_reference`),
  KEY `sale_payments_sale_id_foreign` (`sale_id`),
  CONSTRAINT `sale_payments_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_payments`
--

LOCK TABLES `sale_payments` WRITE;
/*!40000 ALTER TABLE `sale_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `sale_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_uuid` char(36) NOT NULL,
  `cashier_name` varchar(120) DEFAULT NULL,
  `customer_name` varchar(120) DEFAULT NULL,
  `customer_phone` varchar(40) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `subtotal_minor` bigint(20) unsigned NOT NULL,
  `discount_minor` bigint(20) unsigned NOT NULL DEFAULT 0,
  `discount_type` varchar(20) DEFAULT NULL,
  `discount_reason` varchar(255) DEFAULT NULL,
  `total_minor` bigint(20) unsigned NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'GHS',
  `payment_method` varchar(30) NOT NULL,
  `source` varchar(30) NOT NULL DEFAULT 'pos',
  `delivery_location` varchar(120) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'completed',
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_transaction_uuid_unique` (`transaction_uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(30) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost_minor` bigint(20) unsigned DEFAULT NULL,
  `purchased_at` date DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_product_id_foreign` (`product_id`),
  KEY `stock_movements_supplier_id_foreign` (`supplier_id`),
  CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_movements_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_settings`
--

DROP TABLE IF EXISTS `tenant_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenant_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_settings`
--

LOCK TABLES `tenant_settings` WRITE;
/*!40000 ALTER TABLE `tenant_settings` DISABLE KEYS */;
INSERT INTO `tenant_settings` VALUES (1,'storefront_hero','{\"title\":\"Everyday essentials, delivered simply.\",\"subtitle\":\"Shop trusted products for your home, pantry, and daily routine. Add what you need and we will take care of the rest.\",\"badge\":\"In stock and ready to ship\",\"image_path\":\"storage\\/storefront\\/the-meat-box\\/CSgmK1XH1MtD9it4jdWE4ul8JbwV0G0dcQajbo6I.jpg\"}','2026-09-28 11:41:46','2026-09-28 11:41:46'),(2,'catalogue_mode','\"shared\"','2026-09-28 15:59:59','2026-09-28 15:59:59'),(3,'storefront_config','{\"store_name\":null,\"delivery_message\":\"Fast local delivery on every order\",\"hero_delivery_message\":\"Free local delivery on every order\",\"customer_service\":{\"phone\":\"+233 30 000 0000\",\"email\":\"support@enablstore.test\"}}','2026-09-28 15:59:59','2026-09-28 15:59:59');
/*!40000 ALTER TABLE `tenant_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'cashier',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'tenantthe-meat-box'
--

--
-- Dumping routines for database 'tenantthe-meat-box'
--

--
-- Current Database: `tenantdemo`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `tenantdemo` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `tenantdemo`;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `auditable_type` varchar(255) DEFAULT NULL,
  `auditable_id` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,NULL,'supplier.created','App\\Models\\Supplier','1','{\"name\":\"Enable Supplies\"}','127.0.0.1','2026-09-20 20:33:35','2026-09-20 20:33:35'),(2,NULL,'sale.completed','App\\Models\\Sale','1','{\"total_minor\":4200,\"discount_minor\":0}','127.0.0.1','2026-09-21 14:58:50','2026-09-21 14:58:50');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Grocery','grocery','2026-09-17 22:12:31','2026-09-17 22:12:31'),(2,'Groceries & Pantry','groceries-pantry','2026-09-20 16:18:25','2026-09-20 16:18:25'),(3,'Beverages','beverages','2026-09-20 16:18:25','2026-09-20 16:18:25'),(4,'Fresh Produce','fresh-produce','2026-09-20 16:18:25','2026-09-20 16:18:25'),(5,'Meat, Fish & Seafood','meat-fish-seafood','2026-09-20 16:18:25','2026-09-20 16:18:25'),(6,'Local & Traditional Foods','local-traditional-foods','2026-09-20 16:18:25','2026-09-20 16:18:25'),(7,'Household Cleaning','household-cleaning','2026-09-20 16:18:25','2026-09-20 16:18:25'),(8,'Personal Care & Beauty','personal-care-beauty','2026-09-20 16:18:25','2026-09-20 16:18:25'),(9,'Baby & Kids','baby-kids','2026-09-20 16:18:25','2026-09-20 16:18:25'),(10,'Health & Wellness','health-wellness','2026-09-20 16:18:25','2026-09-20 16:18:25'),(11,'Home & Kitchen','home-kitchen','2026-09-20 16:18:25','2026-09-20 16:18:25'),(12,'Electronics & Accessories','electronics-accessories','2026-09-20 16:18:25','2026-09-20 16:18:25'),(13,'Fashion & Footwear','fashion-footwear','2026-09-20 16:18:25','2026-09-20 16:18:25'),(14,'Stationery & Office','stationery-office','2026-09-20 16:18:25','2026-09-20 16:18:25'),(15,'Automotive','automotive','2026-09-20 16:18:25','2026-09-20 16:18:25');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_stocks`
--

DROP TABLE IF EXISTS `inventory_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `low_stock_threshold` int(10) unsigned NOT NULL DEFAULT 5,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_stocks_product_id_unique` (`product_id`),
  CONSTRAINT `inventory_stocks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_stocks`
--

LOCK TABLES `inventory_stocks` WRITE;
/*!40000 ALTER TABLE `inventory_stocks` DISABLE KEYS */;
INSERT INTO `inventory_stocks` VALUES (1,1,28,5,'2026-09-17 22:12:31','2026-09-17 22:12:31'),(2,2,16,5,'2026-09-17 22:12:31','2026-09-17 22:12:31'),(3,3,60,5,'2026-09-17 22:12:31','2026-09-17 22:12:31'),(4,5,19,5,'2026-09-20 15:56:23','2026-09-21 14:58:50'),(5,6,30,8,'2026-09-21 10:05:15','2026-09-21 10:05:15'),(6,7,45,8,'2026-09-21 10:05:15','2026-09-21 10:05:15'),(7,8,60,8,'2026-09-21 10:05:15','2026-09-21 10:05:15'),(8,9,40,8,'2026-09-21 10:05:15','2026-09-21 10:05:15'),(9,10,100,8,'2026-09-21 10:05:15','2026-09-21 10:05:15'),(10,11,25,8,'2026-09-21 10:05:15','2026-09-21 10:05:15'),(11,12,50,8,'2026-09-21 10:05:15','2026-09-21 10:05:15'),(12,13,70,8,'2026-09-21 10:05:15','2026-09-21 10:05:15'),(13,14,55,8,'2026-09-21 10:05:15','2026-09-21 10:05:15'),(14,15,79,8,'2026-09-21 10:05:16','2026-09-21 14:58:50'),(15,16,35,8,'2026-09-21 10:05:16','2026-09-21 10:05:16'),(16,17,30,8,'2026-09-21 10:05:16','2026-09-21 10:05:16'),(17,18,30,8,'2026-09-21 10:05:16','2026-09-21 10:05:16'),(18,19,20,8,'2026-09-21 10:05:16','2026-09-21 10:05:16'),(19,20,64,8,'2026-09-21 10:05:16','2026-09-21 14:58:50'),(20,21,45,8,'2026-09-21 10:05:16','2026-09-21 10:05:16'),(21,22,35,8,'2026-09-21 10:05:16','2026-09-21 10:05:16'),(22,23,40,8,'2026-09-21 10:05:16','2026-09-21 10:05:16'),(23,24,50,8,'2026-09-21 10:05:16','2026-09-21 10:05:16'),(24,25,35,8,'2026-09-21 10:05:16','2026-09-21 10:05:16');
/*!40000 ALTER TABLE `inventory_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_09_17_230000_create_tenant_users_table',1),(2,'2026_09_17_230010_create_categories_table',1),(3,'2026_09_17_230020_create_products_table',1),(4,'2026_09_17_230030_create_inventory_stocks_table',1),(5,'2026_09_17_230040_create_tenant_settings_table',1),(6,'2026_09_17_230050_create_sales_tables',1),(7,'2026_09_20_000003_add_catalogue_channels_to_products',2),(8,'2026_09_20_000004_seed_ghanaian_market_categories',3),(9,'2026_09_20_000005_add_pos_operations_tables',4),(10,'2026_09_20_000006_add_purchase_date_to_stock_movements',5),(11,'2026_09_20_000006_add_purchase_packaging_to_products',6),(12,'2026_09_21_000001_add_storefront_fields_to_products',7),(13,'2026_09_21_000002_add_customer_details_to_sales_table',8),(14,'2026_09_26_000001_add_cashier_name_to_sales_table',9),(15,'2026_09_26_000002_create_sale_payments_table',9),(16,'2026_09_26_000003_add_storefront_order_fields_to_sales',9),(17,'2026_09_26_000004_add_cash_receipt_and_customer_email_fields',9),(18,'2026_09_26_000005_add_external_payment_confirmation',9),(19,'2026_09_26_000006_create_restaurant_foodstore_tables',9),(20,'2026_09_27_000001_add_foodstore_menu_items_to_sale_items',9),(21,'2026_09_27_000002_add_package_and_image_fields_to_restaurant_menu_items',9),(22,'2026_09_27_000003_add_customer_phone_to_restaurant_orders',9),(23,'2026_09_28_000001_add_option_groups_to_restaurant_foodstore',9);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `price_minor` bigint(20) unsigned NOT NULL,
  `compare_at_price_minor` bigint(20) unsigned DEFAULT NULL,
  `cost_minor` bigint(20) unsigned DEFAULT NULL,
  `purchase_unit` varchar(40) NOT NULL DEFAULT 'unit',
  `units_per_purchase` int(10) unsigned NOT NULL DEFAULT 1,
  `currency` varchar(3) NOT NULL DEFAULT 'GHS',
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `image_gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`image_gallery`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `available_in_pos` tinyint(1) NOT NULL DEFAULT 1,
  `available_online` tinyint(1) NOT NULL DEFAULT 1,
  `is_online_deal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  UNIQUE KEY `products_barcode_unique` (`barcode`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Frytol Cooking Oil','frytol-cooking-oil','OIL-001',NULL,1850,NULL,0,'unit',1,'GHS',NULL,'storage/products/demo/QMOfbK6ilJ0wWbk42ZkGuoYzuJ92tZCBhVAGjWCL.jpg',NULL,0,1,1,0,'2026-09-17 22:12:31','2026-09-20 20:14:06'),(2,1,'Millet Porridge 500g','millet-porridge-500g','MIL-002',NULL,2400,NULL,NULL,'unit',1,'GHS',NULL,NULL,NULL,1,1,1,0,'2026-09-17 22:12:31','2026-09-17 22:12:31'),(3,1,'Voltic Water Sachet','voltic-water-sachet','WAT-003',NULL,500,NULL,NULL,'unit',1,'GHS',NULL,NULL,NULL,1,1,1,0,'2026-09-17 22:12:31','2026-09-17 22:12:31'),(5,NULL,'Frytol Cooking Oil','frytol-cooking-oil-2','FRYTOL-COOKING-OIL-EW4QF0',NULL,2000,NULL,1000,'unit',1,'GHS',NULL,'storage/products/demo/zsmImWDS3THDLLFKJ4jrLZdslSoMGYCAF4BclpmW.jpg',NULL,1,1,1,0,'2026-09-20 15:56:23','2026-09-20 20:13:32'),(6,2,'Premium Long Grain Rice 5kg','premium-long-grain-rice-5kg','RICE-5KG','2000000000011',14500,14800,10150,'unit',1,'GHS','Everyday long-grain rice for family meals.','storage/products/demo/seed/groceries.jpg','[\"storage\\/products\\/demo\\/seed\\/groceries.jpg\"]',1,1,1,1,'2026-09-21 10:05:15','2026-09-21 10:24:12'),(7,6,'Gari Ijebu 1kg','gari-ijebu-1kg','GARI-1KG','2000000000012',2800,NULL,1960,'unit',1,'GHS','Crisp gari, ideal for soaking or eba.','storage/products/demo/seed/groceries.jpg','[\"storage\\/products\\/demo\\/seed\\/groceries.jpg\"]',1,1,1,0,'2026-09-21 10:05:15','2026-09-21 10:24:12'),(8,2,'Tinned Tomato Paste 400g','tinned-tomato-paste-400g','TOMATO-400G','2000000000013',1600,NULL,1120,'unit',1,'GHS','Rich tomato paste for stews, jollof and sauces.','storage/products/demo/seed/groceries.jpg','[\"storage\\/products\\/demo\\/seed\\/groceries.jpg\"]',1,1,1,0,'2026-09-21 10:05:15','2026-09-21 10:24:12'),(9,2,'Vegetable Cooking Oil 1L','vegetable-cooking-oil-1l','OIL-1L','2000000000014',3800,4100,2660,'unit',1,'GHS','Versatile vegetable cooking oil for everyday meals.','storage/products/demo/Gj2VdkKkMdgUagZrkDM8kCKdEMfWmGBWXUrTca0g.jpg','[\"storage\\/products\\/demo\\/Gj2VdkKkMdgUagZrkDM8kCKdEMfWmGBWXUrTca0g.jpg\"]',1,1,1,1,'2026-09-21 10:05:15','2026-09-21 10:16:56'),(10,2,'Instant Noodles Chicken 70g','instant-noodles-chicken-70g','NOODLES-70G','2000000000015',700,NULL,490,'unit',1,'GHS','Quick chicken-flavoured instant noodles.','storage/products/demo/seed/groceries.jpg','[\"storage\\/products\\/demo\\/seed\\/groceries.jpg\"]',1,1,1,0,'2026-09-21 10:05:15','2026-09-21 10:24:12'),(11,2,'Malted Cocoa Drink 500g','malted-cocoa-drink-500g','MALT-500G','2000000000016',6200,NULL,4340,'unit',1,'GHS','Malted cocoa beverage powder for breakfast.','storage/products/demo/seed/groceries.jpg','[\"storage\\/products\\/demo\\/seed\\/groceries.jpg\"]',1,1,1,0,'2026-09-21 10:05:15','2026-09-21 10:24:12'),(12,3,'Sachet Water 500ml (Bag of 30)','sachet-water-500ml-bag-of-30','WATER-30PK','2000000000017',1800,2100,1260,'unit',1,'GHS','Chilled drinking water sachets, bag of 30.','storage/products/demo/seed/groceries.jpg','[\"storage\\/products\\/demo\\/seed\\/groceries.jpg\"]',1,1,1,1,'2026-09-21 10:05:15','2026-09-21 10:24:12'),(13,3,'Mineral Water 1.5L','mineral-water-15l','WATER-15L','2000000000018',800,NULL,560,'unit',1,'GHS','Refreshing bottled mineral water.','storage/products/demo/seed/groceries.jpg','[\"storage\\/products\\/demo\\/seed\\/groceries.jpg\"]',1,1,1,0,'2026-09-21 10:05:15','2026-09-21 10:24:12'),(14,3,'Malt Drink Can 330ml','malt-drink-can-330ml','MALT-330ML','2000000000019',1200,NULL,840,'unit',1,'GHS','Non-alcoholic malt beverage, 330ml can.','storage/products/demo/seed/groceries.jpg','[\"storage\\/products\\/demo\\/seed\\/groceries.jpg\"]',1,1,1,0,'2026-09-21 10:05:15','2026-09-21 10:24:12'),(15,3,'Canned Soft Drink 330ml','canned-soft-drink-330ml','SODA-330ML','2000000000020',1000,1300,700,'unit',1,'GHS','Cold, fizzy soft drink in a 330ml can.','storage/products/demo/seed/groceries.jpg','[\"storage\\/products\\/demo\\/seed\\/groceries.jpg\"]',1,1,1,1,'2026-09-21 10:05:16','2026-09-21 10:24:12'),(16,4,'Plantain (Ripe) 1kg','plantain-ripe-1kg','PLANTAIN-1KG','2000000000021',3000,NULL,2100,'unit',1,'GHS','Fresh ripe plantain, sold by weight.','storage/products/demo/seed/produce.jpg','[\"storage\\/products\\/demo\\/seed\\/produce.jpg\"]',1,1,1,0,'2026-09-21 10:05:16','2026-09-21 10:24:13'),(17,4,'Fresh Tomatoes 1kg','fresh-tomatoes-1kg','TOMATO-FRESH-1KG','2000000000022',3500,NULL,2450,'unit',1,'GHS','Fresh market tomatoes for everyday cooking.','storage/products/demo/seed/produce.jpg','[\"storage\\/products\\/demo\\/seed\\/produce.jpg\"]',1,1,1,0,'2026-09-21 10:05:16','2026-09-21 10:30:46'),(18,4,'Red Onions 1kg','red-onions-1kg','ONION-1KG','2000000000023',2800,NULL,1960,'unit',1,'GHS','Fresh red onions, sold by weight.','storage/products/demo/seed/produce.jpg','[\"storage\\/products\\/demo\\/seed\\/produce.jpg\"]',1,1,1,0,'2026-09-21 10:05:16','2026-09-21 10:24:13'),(19,5,'Frozen Chicken Drumsticks 1kg','frozen-chicken-drumsticks-1kg','CHICKEN-1KG','2000000000024',7200,7500,5040,'unit',1,'GHS','Convenient frozen chicken drumsticks, 1kg pack.','storage/products/demo/seed/groceries.jpg','[\"storage\\/products\\/demo\\/seed\\/groceries.jpg\"]',1,1,1,1,'2026-09-21 10:05:16','2026-09-21 10:24:13'),(20,8,'Bathing Soap Bar 175g','bathing-soap-bar-175g','SOAP-175G','2000000000025',1200,NULL,840,'unit',1,'GHS','Gentle everyday bathing soap bar.','storage/products/demo/seed/cleaning.jpg','[\"storage\\/products\\/demo\\/seed\\/cleaning.jpg\"]',1,1,1,0,'2026-09-21 10:05:16','2026-09-21 10:24:13'),(21,8,'Toothpaste Fresh Mint 140g','toothpaste-fresh-mint-140g','TOOTHPASTE-140G','2000000000026',2400,NULL,1680,'unit',1,'GHS','Fresh mint toothpaste for daily oral care.','storage/products/demo/seed/cleaning.jpg','[\"storage\\/products\\/demo\\/seed\\/cleaning.jpg\"]',1,1,1,0,'2026-09-21 10:05:16','2026-09-21 10:24:13'),(22,7,'Laundry Detergent Powder 1kg','laundry-detergent-powder-1kg','DETERGENT-1KG','2000000000027',4200,4500,2940,'unit',1,'GHS','Powerful laundry detergent for bright, clean clothes.','storage/products/demo/seed/cleaning.jpg','[\"storage\\/products\\/demo\\/seed\\/cleaning.jpg\"]',1,1,1,1,'2026-09-21 10:05:16','2026-09-21 10:24:13'),(23,7,'Dishwashing Liquid 500ml','dishwashing-liquid-500ml','DISHWASH-500ML','2000000000028',2200,NULL,1540,'unit',1,'GHS','Concentrated liquid for sparkling dishes.','storage/products/demo/seed/cleaning.jpg','[\"storage\\/products\\/demo\\/seed\\/cleaning.jpg\"]',1,1,1,0,'2026-09-21 10:05:16','2026-09-21 10:24:13'),(24,7,'Tissue Roll Pack of 4','tissue-roll-pack-of-4','TISSUE-4PK','2000000000029',2500,NULL,1750,'unit',1,'GHS','Soft household tissue, four-roll pack.','storage/products/demo/seed/cleaning.jpg','[\"storage\\/products\\/demo\\/seed\\/cleaning.jpg\"]',1,1,1,0,'2026-09-21 10:05:16','2026-09-21 10:24:13'),(25,8,'Sanitary Pads Regular 10 Pack','sanitary-pads-regular-10-pack','PADS-10PK','2000000000030',3000,NULL,2100,'unit',1,'GHS','Comfortable regular sanitary pads, pack of 10.','storage/products/demo/seed/cleaning.jpg','[\"storage\\/products\\/demo\\/seed\\/cleaning.jpg\"]',1,1,1,0,'2026-09-21 10:05:16','2026-09-21 10:24:13');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_menu_items`
--

DROP TABLE IF EXISTS `restaurant_menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_menu_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `category` varchar(60) NOT NULL DEFAULT 'Mains',
  `description` text DEFAULT NULL,
  `price_minor` bigint(20) unsigned NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `unit_label` varchar(32) NOT NULL DEFAULT 'plate',
  `image_path` varchar(255) DEFAULT NULL,
  `option_groups` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`option_groups`)),
  PRIMARY KEY (`id`),
  KEY `restaurant_menu_items_is_available_category_index` (`is_available`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_menu_items`
--

LOCK TABLES `restaurant_menu_items` WRITE;
/*!40000 ALTER TABLE `restaurant_menu_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `restaurant_menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_order_items`
--

DROP TABLE IF EXISTS `restaurant_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_order_id` bigint(20) unsigned NOT NULL,
  `restaurant_menu_item_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(120) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price_minor` bigint(20) unsigned NOT NULL,
  `line_total_minor` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `selected_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`selected_options`)),
  PRIMARY KEY (`id`),
  KEY `restaurant_order_items_restaurant_order_id_foreign` (`restaurant_order_id`),
  KEY `restaurant_order_items_restaurant_menu_item_id_foreign` (`restaurant_menu_item_id`),
  CONSTRAINT `restaurant_order_items_restaurant_menu_item_id_foreign` FOREIGN KEY (`restaurant_menu_item_id`) REFERENCES `restaurant_menu_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `restaurant_order_items_restaurant_order_id_foreign` FOREIGN KEY (`restaurant_order_id`) REFERENCES `restaurant_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_order_items`
--

LOCK TABLES `restaurant_order_items` WRITE;
/*!40000 ALTER TABLE `restaurant_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `restaurant_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_orders`
--

DROP TABLE IF EXISTS `restaurant_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `table_label` varchar(40) DEFAULT NULL,
  `customer_name` varchar(120) DEFAULT NULL,
  `customer_phone` varchar(40) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'queued',
  `total_minor` bigint(20) unsigned NOT NULL,
  `created_by_name` varchar(120) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `restaurant_orders_status_created_at_index` (`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_orders`
--

LOCK TABLES `restaurant_orders` WRITE;
/*!40000 ALTER TABLE `restaurant_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `restaurant_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price_minor` bigint(20) unsigned NOT NULL,
  `line_total_minor` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `restaurant_menu_item_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(120) DEFAULT NULL,
  `selected_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`selected_options`)),
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_product_id_foreign` (`product_id`),
  KEY `sale_items_restaurant_menu_item_id_foreign` (`restaurant_menu_item_id`),
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `sale_items_restaurant_menu_item_id_foreign` FOREIGN KEY (`restaurant_menu_item_id`) REFERENCES `restaurant_menu_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_items`
--

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
INSERT INTO `sale_items` VALUES (1,1,20,1,1200,1200,'2026-09-21 14:58:50','2026-09-21 14:58:50',NULL,NULL,NULL),(2,1,15,1,1000,1000,'2026-09-21 14:58:50','2026-09-21 14:58:50',NULL,NULL,NULL),(3,1,5,1,2000,2000,'2026-09-21 14:58:50','2026-09-21 14:58:50',NULL,NULL,NULL);
/*!40000 ALTER TABLE `sale_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_payments`
--

DROP TABLE IF EXISTS `sale_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `method` varchar(30) NOT NULL,
  `amount_minor` bigint(20) unsigned NOT NULL,
  `cash_received_minor` bigint(20) unsigned DEFAULT NULL,
  `externally_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `provider_reference` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sale_payments_provider_reference_unique` (`provider_reference`),
  KEY `sale_payments_sale_id_foreign` (`sale_id`),
  CONSTRAINT `sale_payments_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_payments`
--

LOCK TABLES `sale_payments` WRITE;
/*!40000 ALTER TABLE `sale_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `sale_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_uuid` char(36) NOT NULL,
  `cashier_name` varchar(120) DEFAULT NULL,
  `customer_name` varchar(120) DEFAULT NULL,
  `customer_phone` varchar(40) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `subtotal_minor` bigint(20) unsigned NOT NULL,
  `discount_minor` bigint(20) unsigned NOT NULL DEFAULT 0,
  `discount_type` varchar(20) DEFAULT NULL,
  `discount_reason` varchar(255) DEFAULT NULL,
  `total_minor` bigint(20) unsigned NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'GHS',
  `payment_method` varchar(30) NOT NULL,
  `source` varchar(30) NOT NULL DEFAULT 'pos',
  `delivery_location` varchar(120) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'completed',
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_transaction_uuid_unique` (`transaction_uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
INSERT INTO `sales` VALUES (1,'e43bb319-b308-40ab-82b5-8e174c9c6364',NULL,'dale',NULL,NULL,4200,0,NULL,NULL,4200,'GHS','cash','pos',NULL,'completed','2026-09-21 14:58:50','2026-09-21 14:58:50','2026-09-21 14:58:50');
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(30) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost_minor` bigint(20) unsigned DEFAULT NULL,
  `purchased_at` date DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_product_id_foreign` (`product_id`),
  KEY `stock_movements_supplier_id_foreign` (`supplier_id`),
  CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_movements_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (1,20,NULL,'sale',-1,NULL,NULL,'POS sale e43bb319-b308-40ab-82b5-8e174c9c6364','2026-09-21 14:58:50','2026-09-21 14:58:50'),(2,15,NULL,'sale',-1,NULL,NULL,'POS sale e43bb319-b308-40ab-82b5-8e174c9c6364','2026-09-21 14:58:50','2026-09-21 14:58:50'),(3,5,NULL,'sale',-1,NULL,NULL,'POS sale e43bb319-b308-40ab-82b5-8e174c9c6364','2026-09-21 14:58:50','2026-09-21 14:58:50');
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'Enable Supplies','Dale Quist','0241786330','enablemediahub2024@gmail.com','34B Nai Street, Gbawe Accra',1,'2026-09-20 20:33:35','2026-09-20 20:33:35');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_settings`
--

DROP TABLE IF EXISTS `tenant_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenant_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_settings`
--

LOCK TABLES `tenant_settings` WRITE;
/*!40000 ALTER TABLE `tenant_settings` DISABLE KEYS */;
INSERT INTO `tenant_settings` VALUES (1,'catalogue_mode','\"shared\"','2026-09-20 16:14:11','2026-09-21 10:30:45'),(2,'storefront_hero','{\"title\":\"Everyday essentials, delivered simply.\",\"subtitle\":\"Shop trusted products for your home, pantry, and daily routine. Add what you need and we will take care of the rest.\",\"badge\":\"In stock and ready to ship\",\"image_path\":\"storage\\/storefront\\/demo\\/zD8iBMz0uKPGgEomGMOf03Y8VtLnilbW6tKZrqMZ.jpg\"}','2026-09-21 08:49:41','2026-09-21 08:49:41'),(3,'storefront_config','{\"store_name\":\"DEMOSTORE\",\"delivery_locations\":[\"Accra\",\"Kumasi\",\"Tema\",\"Takoradi\",\"Cape Coast\"],\"default_delivery_location\":\"Accra\",\"delivery_message\":\"Fast local delivery on every order\",\"hero_delivery_message\":\"Free local delivery on every order\",\"new_arrivals_days\":30,\"customer_service\":{\"title\":\"Customer Service\",\"phone\":\"+233 30 000 0000\",\"email\":\"support@enablstore.test\",\"hours\":\"Mon\\u2013Sat, 8:00am \\u2013 6:00pm\",\"message\":\"Need help with an order, delivery, or product question? Our team is ready to assist you.\"}}','2026-09-21 09:08:34','2026-09-21 09:57:40');
/*!40000 ALTER TABLE `tenant_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'cashier',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'tenantdemo'
--

--
-- Dumping routines for database 'tenantdemo'
--

--
-- Current Database: `tenantroyal`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `tenantroyal` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `tenantroyal`;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `auditable_type` varchar(255) DEFAULT NULL,
  `auditable_id` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Groceries & Pantry','groceries-pantry','2026-09-21 15:49:08','2026-09-21 15:49:08'),(2,'Beverages','beverages','2026-09-21 15:49:08','2026-09-21 15:49:08'),(3,'Fresh Produce','fresh-produce','2026-09-21 15:49:08','2026-09-21 15:49:08'),(4,'Meat, Fish & Seafood','meat-fish-seafood','2026-09-21 15:49:08','2026-09-21 15:49:08'),(5,'Local & Traditional Foods','local-traditional-foods','2026-09-21 15:49:08','2026-09-21 15:49:08'),(6,'Household Cleaning','household-cleaning','2026-09-21 15:49:08','2026-09-21 15:49:08'),(7,'Personal Care & Beauty','personal-care-beauty','2026-09-21 15:49:08','2026-09-21 15:49:08'),(8,'Baby & Kids','baby-kids','2026-09-21 15:49:08','2026-09-21 15:49:08'),(9,'Health & Wellness','health-wellness','2026-09-21 15:49:08','2026-09-21 15:49:08'),(10,'Home & Kitchen','home-kitchen','2026-09-21 15:49:08','2026-09-21 15:49:08'),(11,'Electronics & Accessories','electronics-accessories','2026-09-21 15:49:08','2026-09-21 15:49:08'),(12,'Fashion & Footwear','fashion-footwear','2026-09-21 15:49:08','2026-09-21 15:49:08'),(13,'Stationery & Office','stationery-office','2026-09-21 15:49:08','2026-09-21 15:49:08'),(14,'Automotive','automotive','2026-09-21 15:49:08','2026-09-21 15:49:08');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_stocks`
--

DROP TABLE IF EXISTS `inventory_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `low_stock_threshold` int(10) unsigned NOT NULL DEFAULT 5,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_stocks_product_id_unique` (`product_id`),
  CONSTRAINT `inventory_stocks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_stocks`
--

LOCK TABLES `inventory_stocks` WRITE;
/*!40000 ALTER TABLE `inventory_stocks` DISABLE KEYS */;
INSERT INTO `inventory_stocks` VALUES (1,1,20,5,'2026-09-21 19:21:11','2026-09-21 19:21:11');
/*!40000 ALTER TABLE `inventory_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_09_17_230000_create_tenant_users_table',1),(2,'2026_09_17_230010_create_categories_table',1),(3,'2026_09_17_230020_create_products_table',1),(4,'2026_09_17_230030_create_inventory_stocks_table',1),(5,'2026_09_17_230040_create_tenant_settings_table',1),(6,'2026_09_17_230050_create_sales_tables',1),(7,'2026_09_20_000003_add_catalogue_channels_to_products',1),(8,'2026_09_20_000004_seed_ghanaian_market_categories',1),(9,'2026_09_20_000005_add_pos_operations_tables',1),(10,'2026_09_20_000006_add_purchase_date_to_stock_movements',1),(11,'2026_09_20_000006_add_purchase_packaging_to_products',1),(12,'2026_09_21_000001_add_storefront_fields_to_products',1),(13,'2026_09_21_000002_add_customer_details_to_sales_table',1),(14,'2026_09_26_000001_add_cashier_name_to_sales_table',2),(15,'2026_09_26_000002_create_sale_payments_table',2),(16,'2026_09_26_000003_add_storefront_order_fields_to_sales',2),(17,'2026_09_26_000004_add_cash_receipt_and_customer_email_fields',2),(18,'2026_09_26_000005_add_external_payment_confirmation',2),(19,'2026_09_26_000006_create_restaurant_foodstore_tables',2),(20,'2026_09_27_000001_add_foodstore_menu_items_to_sale_items',2),(21,'2026_09_27_000002_add_package_and_image_fields_to_restaurant_menu_items',2),(22,'2026_09_27_000003_add_customer_phone_to_restaurant_orders',2),(23,'2026_09_28_000001_add_option_groups_to_restaurant_foodstore',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `price_minor` bigint(20) unsigned NOT NULL,
  `compare_at_price_minor` bigint(20) unsigned DEFAULT NULL,
  `cost_minor` bigint(20) unsigned DEFAULT NULL,
  `purchase_unit` varchar(40) NOT NULL DEFAULT 'unit',
  `units_per_purchase` int(10) unsigned NOT NULL DEFAULT 1,
  `currency` varchar(3) NOT NULL DEFAULT 'GHS',
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `image_gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`image_gallery`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `available_in_pos` tinyint(1) NOT NULL DEFAULT 1,
  `available_online` tinyint(1) NOT NULL DEFAULT 1,
  `is_online_deal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  UNIQUE KEY `products_barcode_unique` (`barcode`),
  KEY `products_category_id_foreign` (`category_id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,10,'Huawei Beer Pints Cup','huawei-beer-pints-cup','9798999555557','9798999555557',50000,NULL,20000,'unit',1,'GHS',NULL,'storage/products/royal/KD5XuNZGAIAeOBqi0OHx6VIcOKKRi5LZN3oqpZSu.jpg','[]',1,1,1,1,'2026-09-21 19:21:11','2026-09-21 19:21:11');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_menu_items`
--

DROP TABLE IF EXISTS `restaurant_menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_menu_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `category` varchar(60) NOT NULL DEFAULT 'Mains',
  `description` text DEFAULT NULL,
  `price_minor` bigint(20) unsigned NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `unit_label` varchar(32) NOT NULL DEFAULT 'plate',
  `image_path` varchar(255) DEFAULT NULL,
  `option_groups` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`option_groups`)),
  PRIMARY KEY (`id`),
  KEY `restaurant_menu_items_is_available_category_index` (`is_available`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_menu_items`
--

LOCK TABLES `restaurant_menu_items` WRITE;
/*!40000 ALTER TABLE `restaurant_menu_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `restaurant_menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_order_items`
--

DROP TABLE IF EXISTS `restaurant_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_order_id` bigint(20) unsigned NOT NULL,
  `restaurant_menu_item_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(120) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price_minor` bigint(20) unsigned NOT NULL,
  `line_total_minor` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `selected_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`selected_options`)),
  PRIMARY KEY (`id`),
  KEY `restaurant_order_items_restaurant_order_id_foreign` (`restaurant_order_id`),
  KEY `restaurant_order_items_restaurant_menu_item_id_foreign` (`restaurant_menu_item_id`),
  CONSTRAINT `restaurant_order_items_restaurant_menu_item_id_foreign` FOREIGN KEY (`restaurant_menu_item_id`) REFERENCES `restaurant_menu_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `restaurant_order_items_restaurant_order_id_foreign` FOREIGN KEY (`restaurant_order_id`) REFERENCES `restaurant_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_order_items`
--

LOCK TABLES `restaurant_order_items` WRITE;
/*!40000 ALTER TABLE `restaurant_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `restaurant_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_orders`
--

DROP TABLE IF EXISTS `restaurant_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `table_label` varchar(40) DEFAULT NULL,
  `customer_name` varchar(120) DEFAULT NULL,
  `customer_phone` varchar(40) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'queued',
  `total_minor` bigint(20) unsigned NOT NULL,
  `created_by_name` varchar(120) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `restaurant_orders_status_created_at_index` (`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_orders`
--

LOCK TABLES `restaurant_orders` WRITE;
/*!40000 ALTER TABLE `restaurant_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `restaurant_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price_minor` bigint(20) unsigned NOT NULL,
  `line_total_minor` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `restaurant_menu_item_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(120) DEFAULT NULL,
  `selected_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`selected_options`)),
  PRIMARY KEY (`id`),
  KEY `sale_items_sale_id_foreign` (`sale_id`),
  KEY `sale_items_product_id_foreign` (`product_id`),
  KEY `sale_items_restaurant_menu_item_id_foreign` (`restaurant_menu_item_id`),
  CONSTRAINT `sale_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `sale_items_restaurant_menu_item_id_foreign` FOREIGN KEY (`restaurant_menu_item_id`) REFERENCES `restaurant_menu_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_items`
--

LOCK TABLES `sale_items` WRITE;
/*!40000 ALTER TABLE `sale_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `sale_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sale_payments`
--

DROP TABLE IF EXISTS `sale_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `method` varchar(30) NOT NULL,
  `amount_minor` bigint(20) unsigned NOT NULL,
  `cash_received_minor` bigint(20) unsigned DEFAULT NULL,
  `externally_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `provider_reference` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sale_payments_provider_reference_unique` (`provider_reference`),
  KEY `sale_payments_sale_id_foreign` (`sale_id`),
  CONSTRAINT `sale_payments_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_payments`
--

LOCK TABLES `sale_payments` WRITE;
/*!40000 ALTER TABLE `sale_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `sale_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_uuid` char(36) NOT NULL,
  `cashier_name` varchar(120) DEFAULT NULL,
  `customer_name` varchar(120) DEFAULT NULL,
  `customer_phone` varchar(40) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `subtotal_minor` bigint(20) unsigned NOT NULL,
  `discount_minor` bigint(20) unsigned NOT NULL DEFAULT 0,
  `discount_type` varchar(20) DEFAULT NULL,
  `discount_reason` varchar(255) DEFAULT NULL,
  `total_minor` bigint(20) unsigned NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'GHS',
  `payment_method` varchar(30) NOT NULL,
  `source` varchar(30) NOT NULL DEFAULT 'pos',
  `delivery_location` varchar(120) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'completed',
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_transaction_uuid_unique` (`transaction_uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(30) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost_minor` bigint(20) unsigned DEFAULT NULL,
  `purchased_at` date DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_product_id_foreign` (`product_id`),
  KEY `stock_movements_supplier_id_foreign` (`supplier_id`),
  CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `stock_movements_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_settings`
--

DROP TABLE IF EXISTS `tenant_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenant_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_settings`
--

LOCK TABLES `tenant_settings` WRITE;
/*!40000 ALTER TABLE `tenant_settings` DISABLE KEYS */;
INSERT INTO `tenant_settings` VALUES (1,'catalogue_mode','\"shared\"','2026-09-21 17:16:45','2026-09-21 17:16:45'),(2,'storefront_config','{\"store_name\":null,\"delivery_message\":\"Fast local delivery on every order\",\"hero_delivery_message\":\"Free local delivery on every order\",\"customer_service\":{\"phone\":\"+233 30 123 4567\",\"email\":\"support@enablstore.test\"}}','2026-09-21 17:16:45','2026-09-21 17:16:45');
/*!40000 ALTER TABLE `tenant_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'cashier',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'tenantroyal'
--

--
-- Dumping routines for database 'tenantroyal'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-28 19:33:56
