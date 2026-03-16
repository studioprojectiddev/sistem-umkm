-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               PostgreSQL 14.5, compiled by Visual C++ build 1914, 64-bit
-- Server OS:                    
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES  */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table public.accounting
CREATE TABLE IF NOT EXISTS "accounting" (
	"id" INTEGER NOT NULL,
	"journal_no" VARCHAR NULL DEFAULT NULL,
	"transaction_date" DATE NULL DEFAULT NULL,
	"reference" VARCHAR NULL DEFAULT NULL,
	"description" TEXT NULL DEFAULT NULL,
	"source_module" VARCHAR NULL DEFAULT NULL,
	"source_id" BIGINT NULL DEFAULT NULL,
	"status" VARCHAR NULL DEFAULT NULL,
	"created_by" BIGINT NULL DEFAULT NULL,
	"posted_by" BIGINT NULL DEFAULT NULL,
	"posted_at" TIMESTAMP NULL DEFAULT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id")
);

-- Dumping data for table public.accounting: -1 rows
/*!40000 ALTER TABLE "accounting" DISABLE KEYS */;
/*!40000 ALTER TABLE "accounting" ENABLE KEYS */;

-- Dumping structure for table public.accounting_details
CREATE TABLE IF NOT EXISTS "accounting_details" (
	"id" BIGINT NOT NULL,
	"accounting_id" INTEGER NULL DEFAULT NULL,
	"account_id" INTEGER NULL DEFAULT NULL,
	"description" TEXT NULL DEFAULT NULL,
	"debit" DOUBLE PRECISION NULL DEFAULT NULL,
	"credit" DOUBLE PRECISION NULL DEFAULT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id")
);

-- Dumping data for table public.accounting_details: -1 rows
/*!40000 ALTER TABLE "accounting_details" DISABLE KEYS */;
/*!40000 ALTER TABLE "accounting_details" ENABLE KEYS */;

-- Dumping structure for table public.accounts
CREATE TABLE IF NOT EXISTS "accounts" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''accounts_id_seq''::regclass)',
	"name" VARCHAR(255) NOT NULL,
	"type" VARCHAR(255) NOT NULL,
	"initial_balance" NUMERIC(18,2) NOT NULL DEFAULT '0',
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	"parent_id" BIGINT NULL DEFAULT NULL,
	"code" VARCHAR NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	CONSTRAINT "accounts_type_check" CHECK ((((type)::text = ANY (ARRAY[('cash'::character varying)::text, ('bank'::character varying)::text, ('ewallet'::character varying)::text]))))
);

-- Dumping data for table public.accounts: -1 rows
/*!40000 ALTER TABLE "accounts" DISABLE KEYS */;
INSERT INTO "accounts" ("id", "name", "type", "initial_balance", "created_at", "updated_at", "parent_id", "code") VALUES
	(1, 'Tunai', 'cash', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06', NULL, NULL),
	(2, 'BCA', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06', NULL, NULL),
	(3, 'BNI', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06', NULL, NULL),
	(4, 'MANDIRI', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06', NULL, NULL),
	(5, 'BRI', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06', NULL, NULL),
	(6, 'Bank Jatim', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06', NULL, NULL),
	(7, 'BTN', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06', NULL, NULL),
	(8, 'BSI', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06', NULL, NULL),
	(9, 'BCA Syariah', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06', NULL, NULL),
	(10, 'SeaBank Indonesia', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06', NULL, NULL),
	(11, 'BSI Syariah', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06', NULL, NULL),
	(12, 'Allo Bank', 'bank', 0.00, '2026-03-07 07:50:21', '2026-03-07 07:50:21', NULL, NULL);
/*!40000 ALTER TABLE "accounts" ENABLE KEYS */;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
