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

-- Dumping structure for table public.accounts
CREATE TABLE IF NOT EXISTS "accounts" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''accounts_id_seq''::regclass)',
	"name" VARCHAR(255) NOT NULL,
	"type" VARCHAR(255) NOT NULL,
	"initial_balance" NUMERIC(18,2) NOT NULL DEFAULT '0',
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	CONSTRAINT "accounts_type_check" CHECK ((((type)::text = ANY ((ARRAY['cash'::character varying, 'bank'::character varying, 'ewallet'::character varying])::text[]))))
);

-- Dumping data for table public.accounts: -1 rows
/*!40000 ALTER TABLE "accounts" DISABLE KEYS */;
INSERT INTO "accounts" ("id", "name", "type", "initial_balance", "created_at", "updated_at") VALUES
	(1, 'Tunai', 'cash', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06'),
	(2, 'BCA', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06'),
	(3, 'BNI', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06'),
	(4, 'MANDIRI', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06'),
	(5, 'BRI', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06'),
	(6, 'Bank Jatim', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06'),
	(7, 'BTN', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06'),
	(8, 'BSI', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06'),
	(9, 'BCA Syariah', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06'),
	(10, 'SeaBank Indonesia', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06'),
	(11, 'BSI Syariah', 'bank', 0.00, '2026-03-06 12:38:06', '2026-03-06 12:38:06'),
	(12, 'Allo Bank', 'bank', 0.00, '2026-03-07 07:50:21', '2026-03-07 07:50:21');
/*!40000 ALTER TABLE "accounts" ENABLE KEYS */;

-- Dumping structure for table public.account_openings
CREATE TABLE IF NOT EXISTS "account_openings" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''account_openings_id_seq''::regclass)',
	"account_id" BIGINT NOT NULL,
	"month" INTEGER NOT NULL,
	"year" INTEGER NOT NULL,
	"opening_balance" NUMERIC(18,2) NOT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	UNIQUE INDEX "account_openings_account_id_month_year_unique" ("account_id", "month", "year"),
	CONSTRAINT "account_openings_account_id_foreign" FOREIGN KEY ("account_id") REFERENCES "accounts" ("id") ON UPDATE NO ACTION ON DELETE CASCADE
);

-- Dumping data for table public.account_openings: -1 rows
/*!40000 ALTER TABLE "account_openings" DISABLE KEYS */;
/*!40000 ALTER TABLE "account_openings" ENABLE KEYS */;

-- Dumping structure for table public.account_transfers
CREATE TABLE IF NOT EXISTS "account_transfers" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''account_transfers_id_seq''::regclass)',
	"from_account_id" BIGINT NOT NULL,
	"to_account_id" BIGINT NOT NULL,
	"amount" NUMERIC(18,2) NOT NULL,
	"transfer_date" DATE NOT NULL,
	"description" TEXT NULL DEFAULT NULL,
	"created_by" BIGINT NULL DEFAULT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	CONSTRAINT "account_transfers_created_by_foreign" FOREIGN KEY ("created_by") REFERENCES "users" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT "account_transfers_from_account_id_foreign" FOREIGN KEY ("from_account_id") REFERENCES "accounts" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "account_transfers_to_account_id_foreign" FOREIGN KEY ("to_account_id") REFERENCES "accounts" ("id") ON UPDATE NO ACTION ON DELETE CASCADE
);

-- Dumping data for table public.account_transfers: -1 rows
/*!40000 ALTER TABLE "account_transfers" DISABLE KEYS */;
/*!40000 ALTER TABLE "account_transfers" ENABLE KEYS */;

-- Dumping structure for table public.cache
CREATE TABLE IF NOT EXISTS "cache" (
	"key" VARCHAR(255) NOT NULL,
	"value" TEXT NOT NULL,
	"expiration" INTEGER NOT NULL,
	PRIMARY KEY ("key")
);

-- Dumping data for table public.cache: -1 rows
/*!40000 ALTER TABLE "cache" DISABLE KEYS */;
/*!40000 ALTER TABLE "cache" ENABLE KEYS */;

-- Dumping structure for table public.cache_locks
CREATE TABLE IF NOT EXISTS "cache_locks" (
	"key" VARCHAR(255) NOT NULL,
	"owner" VARCHAR(255) NOT NULL,
	"expiration" INTEGER NOT NULL,
	PRIMARY KEY ("key")
);

-- Dumping data for table public.cache_locks: -1 rows
/*!40000 ALTER TABLE "cache_locks" DISABLE KEYS */;
/*!40000 ALTER TABLE "cache_locks" ENABLE KEYS */;

-- Dumping structure for table public.cashflow_categories
CREATE TABLE IF NOT EXISTS "cashflow_categories" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''cashflow_categories_id_seq''::regclass)',
	"name" VARCHAR(255) NOT NULL,
	"type" VARCHAR(255) NOT NULL,
	"is_active" BOOLEAN NOT NULL DEFAULT 'true',
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	CONSTRAINT "cashflow_categories_type_check" CHECK ((((type)::text = ANY ((ARRAY['income'::character varying, 'expense'::character varying])::text[]))))
);

-- Dumping data for table public.cashflow_categories: -1 rows
/*!40000 ALTER TABLE "cashflow_categories" DISABLE KEYS */;
INSERT INTO "cashflow_categories" ("id", "name", "type", "is_active", "created_at", "updated_at") VALUES
	(1, 'Penjualan', 'income', 'true', '2026-03-06 12:38:29', '2026-03-06 12:38:29'),
	(2, 'Pembelian Lunas', 'expense', 'true', '2026-03-07 22:33:37', '2026-03-07 22:33:37'),
	(3, 'Pembelian Sebagian Bayar', 'expense', 'true', '2026-03-07 22:33:37', '2026-03-07 22:33:37'),
	(4, 'Pembelian Hutang', 'expense', 'true', '2026-03-07 22:33:37', '2026-03-07 22:33:37');
/*!40000 ALTER TABLE "cashflow_categories" ENABLE KEYS */;

-- Dumping structure for table public.cashflow_closings
CREATE TABLE IF NOT EXISTS "cashflow_closings" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''cashflow_closings_id_seq''::regclass)',
	"month" INTEGER NOT NULL,
	"year" INTEGER NOT NULL,
	"closed_at" TIMESTAMP NOT NULL,
	"closed_by" BIGINT NOT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	UNIQUE INDEX "cashflow_closings_month_year_unique" ("month", "year"),
	CONSTRAINT "cashflow_closings_closed_by_foreign" FOREIGN KEY ("closed_by") REFERENCES "users" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION
);

-- Dumping data for table public.cashflow_closings: -1 rows
/*!40000 ALTER TABLE "cashflow_closings" DISABLE KEYS */;
/*!40000 ALTER TABLE "cashflow_closings" ENABLE KEYS */;

-- Dumping structure for table public.cash_flows
CREATE TABLE IF NOT EXISTS "cash_flows" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''cash_flows_id_seq''::regclass)',
	"warehouse_id" BIGINT NULL DEFAULT NULL,
	"type" VARCHAR(255) NOT NULL,
	"reference" VARCHAR(255) NULL DEFAULT NULL,
	"description" TEXT NULL DEFAULT NULL,
	"amount" NUMERIC(18,2) NOT NULL,
	"transaction_date" DATE NOT NULL,
	"created_by" BIGINT NOT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	"account_id" BIGINT NULL DEFAULT NULL,
	"category_id" BIGINT NULL DEFAULT NULL,
	"deleted_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_by" BIGINT NULL DEFAULT NULL,
	"deleted_by" BIGINT NULL DEFAULT NULL,
	"reference_type" VARCHAR(255) NULL DEFAULT NULL,
	"reference_id" BIGINT NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	INDEX "cash_flows_reference_type_reference_id_index" ("reference_type", "reference_id"),
	CONSTRAINT "cash_flows_account_id_foreign" FOREIGN KEY ("account_id") REFERENCES "accounts" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "cash_flows_category_id_foreign" FOREIGN KEY ("category_id") REFERENCES "cashflow_categories" ("id") ON UPDATE NO ACTION ON DELETE RESTRICT,
	CONSTRAINT "cash_flows_created_by_foreign" FOREIGN KEY ("created_by") REFERENCES "users" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "cash_flows_deleted_by_foreign" FOREIGN KEY ("deleted_by") REFERENCES "users" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT "cash_flows_updated_by_foreign" FOREIGN KEY ("updated_by") REFERENCES "users" ("id") ON UPDATE NO ACTION ON DELETE NO ACTION,
	CONSTRAINT "cash_flows_warehouse_id_foreign" FOREIGN KEY ("warehouse_id") REFERENCES "warehouses" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "cash_flows_type_check" CHECK ((((type)::text = ANY ((ARRAY['income'::character varying, 'expense'::character varying])::text[]))))
);

-- Dumping data for table public.cash_flows: -1 rows
/*!40000 ALTER TABLE "cash_flows" DISABLE KEYS */;
INSERT INTO "cash_flows" ("id", "warehouse_id", "type", "reference", "description", "amount", "transaction_date", "created_by", "created_at", "updated_at", "account_id", "category_id", "deleted_at", "updated_by", "deleted_by", "reference_type", "reference_id") VALUES
	(1, NULL, 'income', NULL, 'Penjualan POS #INV1772775509', 75000.00, '2026-03-06', 2, '2026-03-06 12:38:29', '2026-03-06 12:38:29', 1, 1, NULL, NULL, NULL, 'pos', 1),
	(2, NULL, 'income', NULL, 'Penjualan POS #INV1772776683', 10000.00, '2026-03-06', 2, '2026-03-06 12:58:03', '2026-03-06 12:58:03', 1, 1, NULL, NULL, NULL, 'pos', 2),
	(3, NULL, 'income', NULL, 'Penjualan POS #INV1772776720', 15000.00, '2026-03-06', 2, '2026-03-06 12:58:40', '2026-03-06 12:58:40', 1, 1, NULL, NULL, NULL, 'pos', 3),
	(4, NULL, 'income', NULL, 'Penjualan POS #INV1772776758', 7000.00, '2026-03-06', 2, '2026-03-06 12:59:18', '2026-03-06 12:59:18', 1, 1, NULL, NULL, NULL, 'pos', 4),
	(5, NULL, 'income', NULL, 'Penjualan POS #INV1772776901', 15000.00, '2026-03-06', 2, '2026-03-06 13:01:41', '2026-03-06 13:01:41', 1, 1, NULL, NULL, NULL, 'pos', 5),
	(6, NULL, 'income', NULL, 'Penjualan POS #INV1772776913', 15000.00, '2026-03-06', 2, '2026-03-06 13:01:53', '2026-03-06 13:01:53', 1, 1, NULL, NULL, NULL, 'pos', 6),
	(7, NULL, 'income', NULL, 'Penjualan POS #INV1772776968', 13000.00, '2026-03-06', 2, '2026-03-06 13:02:48', '2026-03-06 13:02:48', 1, 1, NULL, NULL, NULL, 'pos', 7),
	(8, NULL, 'income', NULL, 'Penjualan POS #INV1772776984', 15000.00, '2026-03-06', 2, '2026-03-06 13:03:05', '2026-03-06 13:03:05', 1, 1, NULL, NULL, NULL, 'pos', 8),
	(9, NULL, 'income', NULL, 'Penjualan POS #INV1772776997', 15000.00, '2026-03-06', 2, '2026-03-06 13:03:17', '2026-03-06 13:03:17', 1, 1, NULL, NULL, NULL, 'pos', 9),
	(10, NULL, 'income', NULL, 'Penjualan POS #INV1772777216', 15000.00, '2026-03-06', 2, '2026-03-06 13:06:56', '2026-03-06 13:06:56', 1, 1, NULL, NULL, NULL, 'pos', 10),
	(11, NULL, 'income', NULL, 'Penjualan POS #INV1772778660', 30000.00, '2026-03-06', 2, '2026-03-06 13:31:01', '2026-03-06 13:31:01', 1, 1, NULL, NULL, NULL, 'pos', 11),
	(12, NULL, 'income', NULL, 'Penjualan POS #INV1772844788', 30000.00, '2026-03-07', 2, '2026-03-07 07:53:08', '2026-03-07 07:53:08', 12, 1, NULL, NULL, NULL, 'pos', 12),
	(13, NULL, 'income', NULL, 'Penjualan POS #INV1772845764', 30000.00, '2026-03-07', 2, '2026-03-07 08:09:24', '2026-03-07 08:09:24', 1, 1, NULL, NULL, NULL, 'pos', 13),
	(14, NULL, 'income', NULL, 'Penjualan POS #INV1772845808', 15000.00, '2026-03-07', 2, '2026-03-07 08:10:08', '2026-03-07 08:10:08', 1, 1, NULL, NULL, NULL, 'pos', 14),
	(15, NULL, 'income', NULL, 'pembayaran hutang lama', 48000.00, '2026-03-07', 2, '2026-03-07 08:14:13', '2026-03-07 08:14:13', 2, 1, NULL, NULL, NULL, NULL, NULL),
	(16, NULL, 'expense', NULL, 'bayar dagangan', 30000.00, '2026-03-07', 2, '2026-03-07 08:15:07', '2026-03-07 08:15:07', 2, 1, NULL, NULL, NULL, NULL, NULL);
/*!40000 ALTER TABLE "cash_flows" ENABLE KEYS */;

-- Dumping structure for table public.categories
CREATE TABLE IF NOT EXISTS "categories" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''categories_id_seq''::regclass)',
	"idpenginput" INTEGER NOT NULL,
	"name" VARCHAR(255) NOT NULL,
	"slug" VARCHAR(255) NOT NULL,
	"code" VARCHAR(255) NULL DEFAULT NULL,
	"parent_id" BIGINT NULL DEFAULT NULL,
	"description" TEXT NULL DEFAULT NULL,
	"icon" VARCHAR(255) NULL DEFAULT NULL,
	"banner" VARCHAR(255) NULL DEFAULT NULL,
	"is_active" BOOLEAN NOT NULL DEFAULT 'true',
	"sort_order" INTEGER NOT NULL DEFAULT '0',
	"meta_title" VARCHAR(255) NULL DEFAULT NULL,
	"meta_keywords" VARCHAR(255) NULL DEFAULT NULL,
	"meta_description" TEXT NULL DEFAULT NULL,
	"created_by" BIGINT NULL DEFAULT NULL,
	"updated_by" BIGINT NULL DEFAULT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	"type" VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	UNIQUE INDEX "categories_slug_unique" ("slug"),
	UNIQUE INDEX "categories_code_unique" ("code"),
	CONSTRAINT "categories_created_by_foreign" FOREIGN KEY ("created_by") REFERENCES "users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL,
	CONSTRAINT "categories_parent_id_foreign" FOREIGN KEY ("parent_id") REFERENCES "categories" ("id") ON UPDATE NO ACTION ON DELETE SET NULL,
	CONSTRAINT "categories_updated_by_foreign" FOREIGN KEY ("updated_by") REFERENCES "users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL
);

-- Dumping data for table public.categories: -1 rows
/*!40000 ALTER TABLE "categories" DISABLE KEYS */;
INSERT INTO "categories" ("id", "idpenginput", "name", "slug", "code", "parent_id", "description", "icon", "banner", "is_active", "sort_order", "meta_title", "meta_keywords", "meta_description", "created_by", "updated_by", "created_at", "updated_at", "type") VALUES
	(1, 2, 'Minuman', 'minuman', '0001', NULL, 'minuman kemasan botol, kotak, dll', NULL, NULL, 'true', 1, 'minuman', 'minuman', 'minuman kemasan', NULL, NULL, '2026-03-06 11:34:40', '2026-03-06 11:34:40', NULL),
	(2, 2, 'makanan', 'makanan', '0002', NULL, 'makanan ringan, berat, penutup', NULL, NULL, 'true', 2, 'makanan', 'makanan', 'makanan ringan, berat, penutup', NULL, NULL, '2026-03-06 11:35:22', '2026-03-06 11:35:22', NULL);
/*!40000 ALTER TABLE "categories" ENABLE KEYS */;

-- Dumping structure for table public.failed_jobs
CREATE TABLE IF NOT EXISTS "failed_jobs" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''failed_jobs_id_seq''::regclass)',
	"uuid" VARCHAR(255) NOT NULL,
	"connection" TEXT NOT NULL,
	"queue" TEXT NOT NULL,
	"payload" TEXT NOT NULL,
	"exception" TEXT NOT NULL,
	"failed_at" TIMESTAMP NOT NULL DEFAULT 'CURRENT_TIMESTAMP',
	PRIMARY KEY ("id"),
	UNIQUE INDEX "failed_jobs_uuid_unique" ("uuid")
);

-- Dumping data for table public.failed_jobs: -1 rows
/*!40000 ALTER TABLE "failed_jobs" DISABLE KEYS */;
/*!40000 ALTER TABLE "failed_jobs" ENABLE KEYS */;

-- Dumping structure for table public.jobs
CREATE TABLE IF NOT EXISTS "jobs" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''jobs_id_seq''::regclass)',
	"queue" VARCHAR(255) NOT NULL,
	"payload" TEXT NOT NULL,
	"attempts" SMALLINT NOT NULL,
	"reserved_at" INTEGER NULL DEFAULT NULL,
	"available_at" INTEGER NOT NULL,
	"created_at" INTEGER NOT NULL,
	PRIMARY KEY ("id"),
	INDEX "jobs_queue_index" ("queue")
);

-- Dumping data for table public.jobs: -1 rows
/*!40000 ALTER TABLE "jobs" DISABLE KEYS */;
/*!40000 ALTER TABLE "jobs" ENABLE KEYS */;

-- Dumping structure for table public.job_batches
CREATE TABLE IF NOT EXISTS "job_batches" (
	"id" VARCHAR(255) NOT NULL,
	"name" VARCHAR(255) NOT NULL,
	"total_jobs" INTEGER NOT NULL,
	"pending_jobs" INTEGER NOT NULL,
	"failed_jobs" INTEGER NOT NULL,
	"failed_job_ids" TEXT NOT NULL,
	"options" TEXT NULL DEFAULT NULL,
	"cancelled_at" INTEGER NULL DEFAULT NULL,
	"created_at" INTEGER NOT NULL,
	"finished_at" INTEGER NULL DEFAULT NULL,
	PRIMARY KEY ("id")
);

-- Dumping data for table public.job_batches: -1 rows
/*!40000 ALTER TABLE "job_batches" DISABLE KEYS */;
/*!40000 ALTER TABLE "job_batches" ENABLE KEYS */;

-- Dumping structure for table public.migrations
CREATE TABLE IF NOT EXISTS "migrations" (
	"id" INTEGER NOT NULL DEFAULT 'nextval(''migrations_id_seq''::regclass)',
	"migration" VARCHAR(255) NOT NULL,
	"batch" INTEGER NOT NULL,
	PRIMARY KEY ("id")
);

-- Dumping data for table public.migrations: -1 rows
/*!40000 ALTER TABLE "migrations" DISABLE KEYS */;
INSERT INTO "migrations" ("id", "migration", "batch") VALUES
	(1, '0001_01_01_000001_create_cache_table', 1),
	(2, '0001_01_01_000002_create_jobs_table', 1),
	(3, '2025_08_31_110930_create_users_table', 1),
	(4, '2025_08_31_110931_create_categories_table', 1),
	(5, '2025_08_31_110932_create_products_table', 1),
	(6, '2025_08_31_110933_create_variations_tables', 1),
	(7, '2025_08_31_110937_transactions', 1),
	(8, '2025_08_31_110938_transaction_items', 1),
	(9, '2025_08_31_110939_product_sales_summary', 1),
	(10, '2025_09_27_072651_stock_transactions', 1),
	(11, '2025_10_14_144554_create_warehouses_table', 1),
	(12, '2025_10_14_144603_create_warehouse_products_table', 1),
	(13, '2025_10_14_151505_create_warehouse_transfer_table', 1),
	(14, '2025_10_14_152516_update_product_tables_for_warehouse_stock', 1),
	(15, '2025_10_18_230058_add_uang_diterima_and_kembalian_to_transactions_table', 1),
	(16, '2025_10_20_184532_add_customer_name_and_due_date_to_transactions_table', 1),
	(17, '2025_10_30_191143_add_supplier_name_to_warehouse_products_table', 1),
	(18, '2025_10_31_214727_add_action_type_to_warehouse_products_table', 1),
	(19, '2025_10_31_224134_create_warehouse_stock_logs_table', 1),
	(20, '2026_02_25_203256_cash_flows', 1),
	(21, '2026_02_25_213521_accounts', 1),
	(22, '2026_02_25_213659_add_account_id_to_cash_flows', 1),
	(23, '2026_02_25_215217_create_cashflow_categories_table', 1),
	(24, '2026_02_25_215332_add_category_id_to_cash_flows', 1),
	(25, '2026_02_25_220148_add_soft_deletes_to_cash_flows', 1),
	(26, '2026_02_25_220450_add_audit_fields_to_cash_flows', 1),
	(27, '2026_02_25_222122_create_account_transfers_table', 1),
	(28, '2026_02_25_223848_create_cashflow_closings_table', 1),
	(29, '2026_02_25_225840_create_account_openings_table', 1),
	(30, '2026_02_26_000556_add_reference_to_cash_flows_table', 1),
	(31, '2026_02_26_003907_add_account_id_to_transactions_table', 1),
	(32, '2026_02_26_004826_add_type_to_categories_table', 1),
	(33, '2026_02_26_223648_drop_category_column_from_cash_flows', 1),
	(34, '2026_03_03_220845_create_warehouse_stock_transactions_table', 1),
	(35, '2026_03_03_221813_add_fields_to_warehouse_stock_transactions_table', 1);
/*!40000 ALTER TABLE "migrations" ENABLE KEYS */;

-- Dumping structure for table public.password_reset_tokens
CREATE TABLE IF NOT EXISTS "password_reset_tokens" (
	"email" VARCHAR(255) NOT NULL,
	"token" VARCHAR(255) NOT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("email")
);

-- Dumping data for table public.password_reset_tokens: -1 rows
/*!40000 ALTER TABLE "password_reset_tokens" DISABLE KEYS */;
/*!40000 ALTER TABLE "password_reset_tokens" ENABLE KEYS */;

-- Dumping structure for table public.products
CREATE TABLE IF NOT EXISTS "products" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''products_id_seq''::regclass)',
	"name" VARCHAR(255) NOT NULL,
	"slug" VARCHAR(255) NOT NULL,
	"sku" VARCHAR(255) NULL DEFAULT NULL,
	"barcode" VARCHAR(255) NULL DEFAULT NULL,
	"description" TEXT NULL DEFAULT NULL,
	"idpenginput" INTEGER NOT NULL,
	"user_id" BIGINT NOT NULL,
	"category_id" BIGINT NULL DEFAULT NULL,
	"price" NUMERIC(12,2) NOT NULL,
	"discount_price" NUMERIC(12,2) NULL DEFAULT NULL,
	"cost_price" NUMERIC(12,2) NULL DEFAULT NULL,
	"unit" VARCHAR(50) NOT NULL DEFAULT 'pcs',
	"product_type" VARCHAR(255) NOT NULL DEFAULT 'goods',
	"expiry_date" DATE NULL DEFAULT NULL,
	"batch_number" VARCHAR(255) NULL DEFAULT NULL,
	"thumbnail" VARCHAR(255) NULL DEFAULT NULL,
	"images" JSON NULL DEFAULT NULL,
	"attributes" JSON NULL DEFAULT NULL,
	"is_active" BOOLEAN NOT NULL DEFAULT 'true',
	"is_featured" BOOLEAN NOT NULL DEFAULT 'false',
	"is_promo" BOOLEAN NOT NULL DEFAULT 'false',
	"promo_price" NUMERIC(12,2) NULL DEFAULT NULL,
	"promo_start" DATE NULL DEFAULT NULL,
	"promo_end" DATE NULL DEFAULT NULL,
	"meta_title" VARCHAR(255) NULL DEFAULT NULL,
	"meta_keywords" VARCHAR(255) NULL DEFAULT NULL,
	"meta_description" TEXT NULL DEFAULT NULL,
	"ai_insights" JSON NULL DEFAULT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	UNIQUE INDEX "products_slug_unique" ("slug"),
	UNIQUE INDEX "products_sku_unique" ("sku"),
	CONSTRAINT "products_category_id_foreign" FOREIGN KEY ("category_id") REFERENCES "categories" ("id") ON UPDATE NO ACTION ON DELETE SET NULL,
	CONSTRAINT "products_user_id_foreign" FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "products_product_type_check" CHECK ((((product_type)::text = ANY ((ARRAY['goods'::character varying, 'service'::character varying])::text[]))))
);

-- Dumping data for table public.products: -1 rows
/*!40000 ALTER TABLE "products" DISABLE KEYS */;
INSERT INTO "products" ("id", "name", "slug", "sku", "barcode", "description", "idpenginput", "user_id", "category_id", "price", "discount_price", "cost_price", "unit", "product_type", "expiry_date", "batch_number", "thumbnail", "images", "attributes", "is_active", "is_featured", "is_promo", "promo_price", "promo_start", "promo_end", "meta_title", "meta_keywords", "meta_description", "ai_insights", "created_at", "updated_at") VALUES
	(2, 'roti tawar', 'roti-tawar-pWNOo', NULL, '910270213', NULL, 2, 2, 2, 15000.00, NULL, 13000.00, 'bungkus', 'goods', '2026-04-30', NULL, 'assets/images/product/1772772439_Pranata Komputer.jpg', NULL, '{}', 'true', 'false', 'false', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-06 11:37:17', '2026-03-06 11:47:19'),
	(1, 'soda gembira', 'soda-gembira-9hvLS', NULL, '0123124234', 'minuman soda gembira sekali', 2, 2, 1, 15000.00, 0.00, 12000.00, 'gelas', 'goods', '2026-07-31', NULL, 'assets/images/product/1772772454_TOPOLOGI JARINGAN RSSG 2025.png', NULL, '{}', 'true', 'false', 'false', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-06 11:36:32', '2026-03-06 11:47:34');
/*!40000 ALTER TABLE "products" ENABLE KEYS */;

-- Dumping structure for table public.product_sales_summary
CREATE TABLE IF NOT EXISTS "product_sales_summary" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''product_sales_summary_id_seq''::regclass)',
	"idpenginput" INTEGER NOT NULL,
	"product_id" BIGINT NOT NULL,
	"variation_id" BIGINT NULL DEFAULT NULL,
	"date" DATE NOT NULL,
	"total_qty" INTEGER NOT NULL DEFAULT '0',
	"total_sales" NUMERIC(15,2) NOT NULL DEFAULT '0',
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	UNIQUE INDEX "sales_summary_unique" ("product_id", "variation_id", "date"),
	CONSTRAINT "product_sales_summary_product_id_foreign" FOREIGN KEY ("product_id") REFERENCES "products" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "product_sales_summary_variation_id_foreign" FOREIGN KEY ("variation_id") REFERENCES "product_variations" ("id") ON UPDATE NO ACTION ON DELETE SET NULL
);

-- Dumping data for table public.product_sales_summary: -1 rows
/*!40000 ALTER TABLE "product_sales_summary" DISABLE KEYS */;
/*!40000 ALTER TABLE "product_sales_summary" ENABLE KEYS */;

-- Dumping structure for table public.product_variations
CREATE TABLE IF NOT EXISTS "product_variations" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''product_variations_id_seq''::regclass)',
	"idpenginput" INTEGER NOT NULL,
	"product_id" BIGINT NOT NULL,
	"name" VARCHAR(255) NOT NULL,
	"sku" VARCHAR(255) NULL DEFAULT NULL,
	"price" NUMERIC(15,2) NULL DEFAULT NULL,
	"weight" NUMERIC(8,2) NULL DEFAULT NULL,
	"image" VARCHAR(255) NULL DEFAULT NULL,
	"is_active" BOOLEAN NOT NULL DEFAULT 'true',
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	UNIQUE INDEX "product_variations_sku_unique" ("sku"),
	CONSTRAINT "product_variations_product_id_foreign" FOREIGN KEY ("product_id") REFERENCES "products" ("id") ON UPDATE NO ACTION ON DELETE CASCADE
);

-- Dumping data for table public.product_variations: -1 rows
/*!40000 ALTER TABLE "product_variations" DISABLE KEYS */;
INSERT INTO "product_variations" ("id", "idpenginput", "product_id", "name", "sku", "price", "weight", "image", "is_active", "created_at", "updated_at") VALUES
	(1, 2, 2, 'Variasi 1', 'SKU-69AA5C9FEF5D6', 16000.00, 300.00, NULL, 'true', '2026-03-06 11:48:31', '2026-03-06 11:48:31'),
	(2, 2, 1, 'Variasi 1', 'SKU-69AA5CBD9C1A6', 15000.00, 250.00, NULL, 'true', '2026-03-06 11:49:01', '2026-03-06 11:49:01');
/*!40000 ALTER TABLE "product_variations" ENABLE KEYS */;

-- Dumping structure for table public.product_variation_options
CREATE TABLE IF NOT EXISTS "product_variation_options" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''product_variation_options_id_seq''::regclass)',
	"idpenginput" INTEGER NOT NULL,
	"variation_id" BIGINT NOT NULL,
	"option_id" BIGINT NOT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	CONSTRAINT "product_variation_options_option_id_foreign" FOREIGN KEY ("option_id") REFERENCES "variation_options" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "product_variation_options_variation_id_foreign" FOREIGN KEY ("variation_id") REFERENCES "product_variations" ("id") ON UPDATE NO ACTION ON DELETE CASCADE
);

-- Dumping data for table public.product_variation_options: -1 rows
/*!40000 ALTER TABLE "product_variation_options" DISABLE KEYS */;
INSERT INTO "product_variation_options" ("id", "idpenginput", "variation_id", "option_id", "created_at", "updated_at") VALUES
	(1, 2, 1, 1, '2026-03-06 11:48:31', '2026-03-06 11:48:31'),
	(2, 2, 2, 1, '2026-03-06 11:49:01', '2026-03-06 11:49:01');
/*!40000 ALTER TABLE "product_variation_options" ENABLE KEYS */;

-- Dumping structure for table public.sessions
CREATE TABLE IF NOT EXISTS "sessions" (
	"id" VARCHAR(255) NOT NULL,
	"user_id" BIGINT NULL DEFAULT NULL,
	"ip_address" VARCHAR(45) NULL DEFAULT NULL,
	"user_agent" TEXT NULL DEFAULT NULL,
	"payload" TEXT NOT NULL,
	"last_activity" INTEGER NOT NULL,
	PRIMARY KEY ("id"),
	INDEX "sessions_user_id_index" ("user_id"),
	INDEX "sessions_last_activity_index" ("last_activity")
);

-- Dumping data for table public.sessions: 1 rows
/*!40000 ALTER TABLE "sessions" DISABLE KEYS */;
INSERT INTO "sessions" ("id", "user_id", "ip_address", "user_agent", "payload", "last_activity") VALUES
	('Jt0oj59WfWMCw8QUm2cSHiar7Iqtf4VCYPm2q2wF', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicGpndGxTUVVzeTUxd0VXNU5YYTV5YWxvclVmTGlTVFlxRTJxM041dCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC91bWttL2xhcG9yYW4tc3RvayI7czo1OiJyb3V0ZSI7czoxNjoidW1rbS5yZXBvcnQuc3RvayI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7fQ==', 1772928356);
/*!40000 ALTER TABLE "sessions" ENABLE KEYS */;

-- Dumping structure for table public.stock_transactions
CREATE TABLE IF NOT EXISTS "stock_transactions" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''stock_transactions_id_seq''::regclass)',
	"item_type" VARCHAR(255) NOT NULL,
	"item_id" BIGINT NOT NULL,
	"transaction_type" VARCHAR(255) NOT NULL,
	"quantity" INTEGER NOT NULL,
	"supplier" VARCHAR(255) NULL DEFAULT NULL,
	"note" TEXT NULL DEFAULT NULL,
	"user_id" BIGINT NULL DEFAULT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	CONSTRAINT "stock_transactions_transaction_type_check" CHECK ((((transaction_type)::text = ANY ((ARRAY['in'::character varying, 'out'::character varying, 'adjust'::character varying])::text[]))))
);

-- Dumping data for table public.stock_transactions: -1 rows
/*!40000 ALTER TABLE "stock_transactions" DISABLE KEYS */;
/*!40000 ALTER TABLE "stock_transactions" ENABLE KEYS */;

-- Dumping structure for table public.transactions
CREATE TABLE IF NOT EXISTS "transactions" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''transactions_id_seq''::regclass)',
	"invoice_number" VARCHAR(255) NOT NULL,
	"transaction_type" VARCHAR(255) NOT NULL DEFAULT 'sale',
	"idpenginput" INTEGER NOT NULL,
	"user_id" BIGINT NOT NULL,
	"customer_id" BIGINT NULL DEFAULT NULL,
	"supplier_id" BIGINT NULL DEFAULT NULL,
	"transaction_date" TIMESTAMP NOT NULL DEFAULT 'CURRENT_TIMESTAMP',
	"subtotal" NUMERIC(15,2) NOT NULL DEFAULT '0',
	"discount" NUMERIC(15,2) NOT NULL DEFAULT '0',
	"tax" NUMERIC(15,2) NOT NULL DEFAULT '0',
	"shipping_cost" NUMERIC(15,2) NOT NULL DEFAULT '0',
	"total" NUMERIC(15,2) NOT NULL DEFAULT '0',
	"payment_status" VARCHAR(255) NOT NULL DEFAULT 'unpaid',
	"payment_method" VARCHAR(255) NULL DEFAULT NULL,
	"status" VARCHAR(255) NOT NULL DEFAULT 'pending',
	"notes" TEXT NULL DEFAULT NULL,
	"customer_name" VARCHAR(255) NULL DEFAULT NULL,
	"due_date" DATE NULL DEFAULT NULL,
	"uang_diterima" NUMERIC(15,2) NULL DEFAULT NULL,
	"kembalian" NUMERIC(15,2) NULL DEFAULT NULL,
	"created_by" BIGINT NULL DEFAULT NULL,
	"updated_by" BIGINT NULL DEFAULT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	"account_id" BIGINT NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	UNIQUE INDEX "transactions_invoice_number_unique" ("invoice_number"),
	CONSTRAINT "transactions_account_id_foreign" FOREIGN KEY ("account_id") REFERENCES "accounts" ("id") ON UPDATE NO ACTION ON DELETE SET NULL,
	CONSTRAINT "transactions_created_by_foreign" FOREIGN KEY ("created_by") REFERENCES "users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL,
	CONSTRAINT "transactions_customer_id_foreign" FOREIGN KEY ("customer_id") REFERENCES "users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL,
	CONSTRAINT "transactions_supplier_id_foreign" FOREIGN KEY ("supplier_id") REFERENCES "users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL,
	CONSTRAINT "transactions_updated_by_foreign" FOREIGN KEY ("updated_by") REFERENCES "users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL,
	CONSTRAINT "transactions_user_id_foreign" FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "transactions_transaction_type_check" CHECK ((((transaction_type)::text = ANY ((ARRAY['sale'::character varying, 'purchase'::character varying, 'return_sale'::character varying, 'return_purchase'::character varying])::text[])))),
	CONSTRAINT "transactions_payment_status_check" CHECK ((((payment_status)::text = ANY ((ARRAY['unpaid'::character varying, 'partial'::character varying, 'paid'::character varying, 'refunded'::character varying])::text[])))),
	CONSTRAINT "transactions_payment_method_check" CHECK ((((payment_method)::text = ANY ((ARRAY['cash'::character varying, 'bank_transfer'::character varying, 'ewallet'::character varying, 'credit_card'::character varying])::text[])))),
	CONSTRAINT "transactions_status_check" CHECK ((((status)::text = ANY ((ARRAY['pending'::character varying, 'completed'::character varying, 'cancelled'::character varying, 'refunded'::character varying])::text[]))))
);

-- Dumping data for table public.transactions: 14 rows
/*!40000 ALTER TABLE "transactions" DISABLE KEYS */;
INSERT INTO "transactions" ("id", "invoice_number", "transaction_type", "idpenginput", "user_id", "customer_id", "supplier_id", "transaction_date", "subtotal", "discount", "tax", "shipping_cost", "total", "payment_status", "payment_method", "status", "notes", "customer_name", "due_date", "uang_diterima", "kembalian", "created_by", "updated_by", "created_at", "updated_at", "account_id") VALUES
	(1, 'INV1772775509', 'sale', 2, 2, NULL, NULL, '2026-03-06 12:38:30', 75000.00, 0.00, 0.00, 0.00, 75000.00, 'paid', 'cash', 'completed', NULL, NULL, NULL, 75000.00, 0.00, NULL, NULL, '2026-03-06 12:38:29', '2026-03-06 12:38:29', 1),
	(2, 'INV1772776683', 'sale', 2, 2, NULL, NULL, '2026-03-06 12:58:03', 15000.00, 0.00, 0.00, 0.00, 15000.00, 'unpaid', 'cash', 'completed', NULL, 'rega', '2026-03-12', 10000.00, 0.00, NULL, NULL, '2026-03-06 12:58:03', '2026-03-06 12:58:03', 1),
	(3, 'INV1772776720', 'sale', 2, 2, NULL, NULL, '2026-03-06 12:58:40', 15000.00, 0.00, 0.00, 0.00, 15000.00, 'paid', 'cash', 'completed', NULL, NULL, NULL, 20000.00, 5000.00, NULL, NULL, '2026-03-06 12:58:40', '2026-03-06 12:58:40', 1),
	(4, 'INV1772776758', 'sale', 2, 2, NULL, NULL, '2026-03-06 12:59:19', 15000.00, 0.00, 0.00, 0.00, 15000.00, 'unpaid', 'cash', 'completed', NULL, 'reni', '2026-03-09', 7000.00, 0.00, NULL, NULL, '2026-03-06 12:59:18', '2026-03-06 12:59:18', 1),
	(5, 'INV1772776901', 'sale', 2, 2, NULL, NULL, '2026-03-06 13:01:41', 15000.00, 0.00, 0.00, 0.00, 15000.00, 'paid', 'cash', 'completed', NULL, NULL, NULL, 20000.00, 5000.00, NULL, NULL, '2026-03-06 13:01:41', '2026-03-06 13:01:41', 1),
	(6, 'INV1772776913', 'sale', 2, 2, NULL, NULL, '2026-03-06 13:01:53', 15000.00, 0.00, 0.00, 0.00, 15000.00, 'paid', 'cash', 'completed', NULL, NULL, NULL, 20000.00, 5000.00, NULL, NULL, '2026-03-06 13:01:53', '2026-03-06 13:01:53', 1),
	(7, 'INV1772776968', 'sale', 2, 2, NULL, NULL, '2026-03-06 13:02:48', 15000.00, 0.00, 0.00, 0.00, 15000.00, 'unpaid', 'cash', 'completed', NULL, 'regi', '2026-03-10', 13000.00, 0.00, NULL, NULL, '2026-03-06 13:02:48', '2026-03-06 13:02:48', 1),
	(8, 'INV1772776984', 'sale', 2, 2, NULL, NULL, '2026-03-06 13:03:05', 15000.00, 0.00, 0.00, 0.00, 15000.00, 'paid', 'cash', 'completed', NULL, NULL, NULL, 50000.00, 35000.00, NULL, NULL, '2026-03-06 13:03:04', '2026-03-06 13:03:04', 1),
	(9, 'INV1772776997', 'sale', 2, 2, NULL, NULL, '2026-03-06 13:03:18', 15000.00, 0.00, 0.00, 0.00, 15000.00, 'paid', 'cash', 'completed', NULL, NULL, NULL, 100000.00, 85000.00, NULL, NULL, '2026-03-06 13:03:17', '2026-03-06 13:03:17', 1),
	(10, 'INV1772777216', 'sale', 2, 2, NULL, NULL, '2026-03-06 13:06:56', 15000.00, 0.00, 0.00, 0.00, 15000.00, 'paid', 'cash', 'completed', NULL, NULL, NULL, 15000.00, 0.00, NULL, NULL, '2026-03-06 13:06:56', '2026-03-06 13:06:56', 1),
	(11, 'INV1772778660', 'sale', 2, 2, NULL, NULL, '2026-03-06 13:31:01', 30000.00, 0.00, 0.00, 0.00, 30000.00, 'paid', 'cash', 'completed', NULL, NULL, NULL, 30000.00, 0.00, NULL, NULL, '2026-03-06 13:31:01', '2026-03-06 13:31:01', 1),
	(12, 'INV1772844788', 'sale', 2, 2, NULL, NULL, '2026-03-07 07:53:09', 30000.00, 0.00, 0.00, 0.00, 30000.00, 'paid', 'bank_transfer', 'completed', NULL, NULL, NULL, 30000.00, 0.00, NULL, NULL, '2026-03-07 07:53:08', '2026-03-07 07:53:08', 12),
	(13, 'INV1772845764', 'sale', 2, 2, NULL, NULL, '2026-03-07 08:09:25', 30000.00, 0.00, 0.00, 0.00, 30000.00, 'paid', 'cash', 'completed', NULL, NULL, NULL, 100000.00, 70000.00, NULL, NULL, '2026-03-07 08:09:24', '2026-03-07 08:09:24', 1),
	(14, 'INV1772845808', 'sale', 2, 2, NULL, NULL, '2026-03-07 08:10:09', 15000.00, 0.00, 0.00, 0.00, 15000.00, 'paid', 'cash', 'completed', NULL, NULL, NULL, 15000.00, 0.00, NULL, NULL, '2026-03-07 08:10:08', '2026-03-07 08:10:08', 1);
/*!40000 ALTER TABLE "transactions" ENABLE KEYS */;

-- Dumping structure for table public.transaction_items
CREATE TABLE IF NOT EXISTS "transaction_items" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''transaction_items_id_seq''::regclass)',
	"transaction_id" BIGINT NOT NULL,
	"idpenginput" INTEGER NOT NULL,
	"product_id" BIGINT NOT NULL,
	"variation_id" BIGINT NULL DEFAULT NULL,
	"quantity" INTEGER NOT NULL DEFAULT '1',
	"price" NUMERIC(15,2) NOT NULL,
	"discount" NUMERIC(15,2) NOT NULL DEFAULT '0',
	"subtotal" NUMERIC(15,2) NOT NULL,
	"unit" VARCHAR(50) NULL DEFAULT NULL,
	"notes" TEXT NULL DEFAULT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	CONSTRAINT "transaction_items_product_id_foreign" FOREIGN KEY ("product_id") REFERENCES "products" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "transaction_items_transaction_id_foreign" FOREIGN KEY ("transaction_id") REFERENCES "transactions" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "transaction_items_variation_id_foreign" FOREIGN KEY ("variation_id") REFERENCES "product_variations" ("id") ON UPDATE NO ACTION ON DELETE SET NULL
);

-- Dumping data for table public.transaction_items: 15 rows
/*!40000 ALTER TABLE "transaction_items" DISABLE KEYS */;
INSERT INTO "transaction_items" ("id", "transaction_id", "idpenginput", "product_id", "variation_id", "quantity", "price", "discount", "subtotal", "unit", "notes", "created_at", "updated_at") VALUES
	(1, 1, 2, 2, NULL, 4, 15000.00, 0.00, 60000.00, 'bungkus', NULL, '2026-03-06 12:38:29', '2026-03-06 12:38:29'),
	(2, 1, 2, 1, NULL, 1, 15000.00, 0.00, 15000.00, 'gelas', NULL, '2026-03-06 12:38:29', '2026-03-06 12:38:29'),
	(3, 2, 2, 2, NULL, 1, 15000.00, 0.00, 15000.00, 'bungkus', NULL, '2026-03-06 12:58:03', '2026-03-06 12:58:03'),
	(4, 3, 2, 1, NULL, 1, 15000.00, 0.00, 15000.00, 'gelas', NULL, '2026-03-06 12:58:40', '2026-03-06 12:58:40'),
	(5, 4, 2, 2, NULL, 1, 15000.00, 0.00, 15000.00, 'bungkus', NULL, '2026-03-06 12:59:18', '2026-03-06 12:59:18'),
	(6, 5, 2, 1, NULL, 1, 15000.00, 0.00, 15000.00, 'gelas', NULL, '2026-03-06 13:01:41', '2026-03-06 13:01:41'),
	(7, 6, 2, 1, NULL, 1, 15000.00, 0.00, 15000.00, 'gelas', NULL, '2026-03-06 13:01:53', '2026-03-06 13:01:53'),
	(8, 7, 2, 1, NULL, 1, 15000.00, 0.00, 15000.00, 'gelas', NULL, '2026-03-06 13:02:48', '2026-03-06 13:02:48'),
	(9, 8, 2, 1, NULL, 1, 15000.00, 0.00, 15000.00, 'gelas', NULL, '2026-03-06 13:03:05', '2026-03-06 13:03:05'),
	(10, 9, 2, 1, NULL, 1, 15000.00, 0.00, 15000.00, 'gelas', NULL, '2026-03-06 13:03:17', '2026-03-06 13:03:17'),
	(11, 10, 2, 1, NULL, 1, 15000.00, 0.00, 15000.00, 'gelas', NULL, '2026-03-06 13:06:56', '2026-03-06 13:06:56'),
	(12, 11, 2, 2, NULL, 2, 15000.00, 0.00, 30000.00, 'bungkus', NULL, '2026-03-06 13:31:01', '2026-03-06 13:31:01'),
	(13, 12, 2, 2, NULL, 2, 15000.00, 0.00, 30000.00, 'bungkus', NULL, '2026-03-07 07:53:08', '2026-03-07 07:53:08'),
	(14, 13, 2, 2, NULL, 2, 15000.00, 0.00, 30000.00, 'bungkus', NULL, '2026-03-07 08:09:24', '2026-03-07 08:09:24'),
	(15, 14, 2, 2, NULL, 1, 15000.00, 0.00, 15000.00, 'bungkus', NULL, '2026-03-07 08:10:08', '2026-03-07 08:10:08');
/*!40000 ALTER TABLE "transaction_items" ENABLE KEYS */;

-- Dumping structure for table public.users
CREATE TABLE IF NOT EXISTS "users" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''users_id_seq''::regclass)',
	"name" VARCHAR(255) NOT NULL,
	"username" VARCHAR(255) NULL DEFAULT NULL,
	"email" VARCHAR(255) NULL DEFAULT NULL,
	"phone" VARCHAR(20) NULL DEFAULT NULL,
	"avatar" VARCHAR(255) NULL DEFAULT NULL,
	"email_verified_at" TIMESTAMP NULL DEFAULT NULL,
	"password" VARCHAR(255) NOT NULL,
	"remember_token" VARCHAR(100) NULL DEFAULT NULL,
	"role" VARCHAR(255) NOT NULL DEFAULT 'pelanggan',
	"is_active" BOOLEAN NOT NULL DEFAULT 'true',
	"created_by" BIGINT NULL DEFAULT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	UNIQUE INDEX "users_username_unique" ("username"),
	UNIQUE INDEX "users_email_unique" ("email"),
	UNIQUE INDEX "users_phone_unique" ("phone"),
	CONSTRAINT "users_created_by_foreign" FOREIGN KEY ("created_by") REFERENCES "users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL,
	CONSTRAINT "users_role_check" CHECK ((((role)::text = ANY ((ARRAY['superadmin'::character varying, 'admin'::character varying, 'kasir'::character varying, 'manajer'::character varying, 'pelanggan'::character varying])::text[]))))
);

-- Dumping data for table public.users: -1 rows
/*!40000 ALTER TABLE "users" DISABLE KEYS */;
INSERT INTO "users" ("id", "name", "username", "email", "phone", "avatar", "email_verified_at", "password", "remember_token", "role", "is_active", "created_by", "created_at", "updated_at") VALUES
	(1, 'Test User', NULL, 'test@example.com', NULL, NULL, '2026-03-05 15:27:51', '$2y$12$wrTkTaW6SRSUSG8kdnbX.eykNhqYKClO0cilNPO07FYS4.4DGL736', '5YmJ8fb14q', 'pelanggan', 'true', NULL, '2026-03-05 15:27:52', '2026-03-05 15:27:52'),
	(2, 'Admin', NULL, 'admin@umkm.test', NULL, NULL, '2026-03-05 15:27:51', '$2y$12$wrTkTaW6SRSUSG8kdnbX.eykNhqYKClO0cilNPO07FYS4.4DGL736', '5YmJ8fb14q', 'superadmin', 'true', NULL, '2026-03-05 15:27:52', '2026-03-05 15:27:52');
/*!40000 ALTER TABLE "users" ENABLE KEYS */;

-- Dumping structure for table public.variation_attributes
CREATE TABLE IF NOT EXISTS "variation_attributes" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''variation_attributes_id_seq''::regclass)',
	"idpenginput" INTEGER NOT NULL,
	"name" VARCHAR(255) NOT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id")
);

-- Dumping data for table public.variation_attributes: -1 rows
/*!40000 ALTER TABLE "variation_attributes" DISABLE KEYS */;
INSERT INTO "variation_attributes" ("id", "idpenginput", "name", "created_at", "updated_at") VALUES
	(1, 2, 'warna', '2026-03-06 11:48:09', '2026-03-06 11:48:09');
/*!40000 ALTER TABLE "variation_attributes" ENABLE KEYS */;

-- Dumping structure for table public.variation_options
CREATE TABLE IF NOT EXISTS "variation_options" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''variation_options_id_seq''::regclass)',
	"idpenginput" INTEGER NOT NULL,
	"attribute_id" BIGINT NOT NULL,
	"value" VARCHAR(255) NOT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	CONSTRAINT "variation_options_attribute_id_foreign" FOREIGN KEY ("attribute_id") REFERENCES "variation_attributes" ("id") ON UPDATE NO ACTION ON DELETE CASCADE
);

-- Dumping data for table public.variation_options: -1 rows
/*!40000 ALTER TABLE "variation_options" DISABLE KEYS */;
INSERT INTO "variation_options" ("id", "idpenginput", "attribute_id", "value", "created_at", "updated_at") VALUES
	(1, 2, 1, 'merah', '2026-03-06 11:48:09', '2026-03-06 11:48:09');
/*!40000 ALTER TABLE "variation_options" ENABLE KEYS */;

-- Dumping structure for table public.warehouses
CREATE TABLE IF NOT EXISTS "warehouses" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''warehouses_id_seq''::regclass)',
	"name" VARCHAR(255) NOT NULL,
	"code" VARCHAR(255) NULL DEFAULT NULL,
	"address" VARCHAR(255) NULL DEFAULT NULL,
	"city" VARCHAR(255) NULL DEFAULT NULL,
	"phone" VARCHAR(255) NULL DEFAULT NULL,
	"pic_name" VARCHAR(255) NULL DEFAULT NULL,
	"pic_contact" VARCHAR(255) NULL DEFAULT NULL,
	"type" VARCHAR(255) NOT NULL DEFAULT 'warehouse',
	"idpenginput" INTEGER NOT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	UNIQUE INDEX "warehouses_code_unique" ("code"),
	CONSTRAINT "warehouses_type_check" CHECK ((((type)::text = ANY ((ARRAY['store'::character varying, 'warehouse'::character varying])::text[]))))
);

-- Dumping data for table public.warehouses: 2 rows
/*!40000 ALTER TABLE "warehouses" DISABLE KEYS */;
INSERT INTO "warehouses" ("id", "name", "code", "address", "city", "phone", "pic_name", "pic_contact", "type", "idpenginput", "created_at", "updated_at") VALUES
	(3, 'gudang in aja', 'GUD0001', 'mojo', 'mojokerto', '0812377777', 'rudi', '0833333', 'warehouse', 2, '2026-03-06 11:40:26', '2026-03-06 11:40:26'),
	(1, 'toko abjad123', 'tokoabjad', 'mojokertooooo', 'mojokerto', '0812344', 'anda', '08123', 'store', 2, '2026-03-06 11:39:01', '2026-03-06 12:20:31');
/*!40000 ALTER TABLE "warehouses" ENABLE KEYS */;

-- Dumping structure for table public.warehouse_products
CREATE TABLE IF NOT EXISTS "warehouse_products" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''warehouse_products_id_seq''::regclass)',
	"warehouse_id" BIGINT NOT NULL,
	"product_id" BIGINT NOT NULL,
	"variation_id" BIGINT NULL DEFAULT NULL,
	"stock" INTEGER NOT NULL DEFAULT '0',
	"reserved" INTEGER NOT NULL DEFAULT '0',
	"min_stock" INTEGER NOT NULL DEFAULT '0',
	"rack_position" VARCHAR(255) NULL DEFAULT NULL,
	"is_active" BOOLEAN NOT NULL DEFAULT 'true',
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	"supplier_name" VARCHAR(255) NULL DEFAULT NULL,
	"action_type" VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	UNIQUE INDEX "unique_warehouse_product" ("warehouse_id", "product_id", "variation_id"),
	INDEX "warehouse_products_warehouse_id_index" ("warehouse_id"),
	INDEX "warehouse_products_product_id_index" ("product_id"),
	INDEX "warehouse_products_variation_id_index" ("variation_id"),
	CONSTRAINT "warehouse_products_product_id_foreign" FOREIGN KEY ("product_id") REFERENCES "products" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "warehouse_products_variation_id_foreign" FOREIGN KEY ("variation_id") REFERENCES "product_variations" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "warehouse_products_warehouse_id_foreign" FOREIGN KEY ("warehouse_id") REFERENCES "warehouses" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "warehouse_products_action_type_check" CHECK ((((action_type)::text = ANY ((ARRAY['add'::character varying, 'reduce'::character varying])::text[]))))
);

-- Dumping data for table public.warehouse_products: -1 rows
/*!40000 ALTER TABLE "warehouse_products" DISABLE KEYS */;
INSERT INTO "warehouse_products" ("id", "warehouse_id", "product_id", "variation_id", "stock", "reserved", "min_stock", "rack_position", "is_active", "created_at", "updated_at", "supplier_name", "action_type") VALUES
	(3, 1, 1, NULL, 12, 0, 0, NULL, 'true', NULL, NULL, NULL, NULL),
	(4, 1, 2, NULL, 7, 0, 0, NULL, 'true', NULL, NULL, NULL, NULL),
	(1, 3, 1, NULL, 100, 0, 8, 'RAK002', 'true', '2026-03-06 11:41:07', '2026-03-07 22:09:09', 'arya', NULL),
	(2, 3, 2, NULL, 32, 0, 1, 'RAK002', 'true', '2026-03-06 11:41:37', '2026-03-08 05:39:20', 'budi', NULL);
/*!40000 ALTER TABLE "warehouse_products" ENABLE KEYS */;

-- Dumping structure for table public.warehouse_stock_logs
CREATE TABLE IF NOT EXISTS "warehouse_stock_logs" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''warehouse_stock_logs_id_seq''::regclass)',
	"warehouse_id" BIGINT NOT NULL,
	"product_id" BIGINT NOT NULL,
	"variation_id" BIGINT NULL DEFAULT NULL,
	"action_type" VARCHAR(255) NOT NULL,
	"quantity" INTEGER NOT NULL DEFAULT '0',
	"note" VARCHAR(255) NULL DEFAULT NULL,
	"user_id" BIGINT NULL DEFAULT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	CONSTRAINT "warehouse_stock_logs_product_id_foreign" FOREIGN KEY ("product_id") REFERENCES "products" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "warehouse_stock_logs_user_id_foreign" FOREIGN KEY ("user_id") REFERENCES "users" ("id") ON UPDATE NO ACTION ON DELETE SET NULL,
	CONSTRAINT "warehouse_stock_logs_variation_id_foreign" FOREIGN KEY ("variation_id") REFERENCES "product_variations" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "warehouse_stock_logs_warehouse_id_foreign" FOREIGN KEY ("warehouse_id") REFERENCES "warehouses" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "warehouse_stock_logs_action_type_check" CHECK ((((action_type)::text = ANY ((ARRAY['add'::character varying, 'reduce'::character varying, 'transfer_in'::character varying, 'transfer_out'::character varying])::text[]))))
);

-- Dumping data for table public.warehouse_stock_logs: 3 rows
/*!40000 ALTER TABLE "warehouse_stock_logs" DISABLE KEYS */;
INSERT INTO "warehouse_stock_logs" ("id", "warehouse_id", "product_id", "variation_id", "action_type", "quantity", "note", "user_id", "created_at", "updated_at") VALUES
	(1, 3, 1, NULL, 'add', 100, 'ardi', 2, '2026-03-06 11:41:07', '2026-03-06 11:41:07'),
	(2, 3, 2, NULL, 'add', 50, 'ardi', 2, '2026-03-06 11:41:37', '2026-03-06 11:41:37'),
	(3, 3, 1, NULL, 'add', 20, 'arya', 2, '2026-03-07 22:09:09', '2026-03-07 22:09:09'),
	(4, 3, 2, NULL, 'add', 1, 'budi', 2, '2026-03-08 05:38:20', '2026-03-08 05:38:20'),
	(5, 3, 2, NULL, 'add', 1, 'budi', 2, '2026-03-08 05:39:20', '2026-03-08 05:39:20');
/*!40000 ALTER TABLE "warehouse_stock_logs" ENABLE KEYS */;

-- Dumping structure for table public.warehouse_stock_transactions
CREATE TABLE IF NOT EXISTS "warehouse_stock_transactions" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''warehouse_stock_transactions_id_seq''::regclass)',
	"warehouse_id" BIGINT NOT NULL,
	"product_id" BIGINT NOT NULL,
	"variation_id" BIGINT NULL DEFAULT NULL,
	"action_type" VARCHAR(255) NOT NULL,
	"quantity" INTEGER NOT NULL,
	"price" NUMERIC(15,2) NULL DEFAULT NULL,
	"total" NUMERIC(15,2) NULL DEFAULT NULL,
	"paid" NUMERIC(15,2) NOT NULL DEFAULT '0',
	"remaining" NUMERIC(15,2) NOT NULL DEFAULT '0',
	"supplier_name" VARCHAR(255) NULL DEFAULT NULL,
	"due_date" DATE NULL DEFAULT NULL,
	"idpenginput" BIGINT NOT NULL,
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	"min_stock" INTEGER NULL DEFAULT NULL,
	"rack_position" VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	CONSTRAINT "warehouse_stock_transactions_product_id_foreign" FOREIGN KEY ("product_id") REFERENCES "products" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "warehouse_stock_transactions_variation_id_foreign" FOREIGN KEY ("variation_id") REFERENCES "product_variations" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "warehouse_stock_transactions_warehouse_id_foreign" FOREIGN KEY ("warehouse_id") REFERENCES "warehouses" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "warehouse_stock_transactions_action_type_check" CHECK ((((action_type)::text = ANY ((ARRAY['add'::character varying, 'reduce'::character varying])::text[]))))
);

-- Dumping data for table public.warehouse_stock_transactions: 4 rows
/*!40000 ALTER TABLE "warehouse_stock_transactions" DISABLE KEYS */;
INSERT INTO "warehouse_stock_transactions" ("id", "warehouse_id", "product_id", "variation_id", "action_type", "quantity", "price", "total", "paid", "remaining", "supplier_name", "due_date", "idpenginput", "created_at", "updated_at", "min_stock", "rack_position") VALUES
	(1, 3, 1, NULL, 'add', 100, 15000.00, 1500000.00, 1500000.00, 0.00, 'ardi', NULL, 2, '2026-03-06 11:41:07', '2026-03-06 11:41:07', 10, 'RAK001'),
	(2, 3, 2, NULL, 'add', 50, 15000.00, 750000.00, 750000.00, 0.00, 'ardi', NULL, 2, '2026-03-06 11:41:37', '2026-03-06 11:41:37', 10, 'RAK001'),
	(3, 3, 1, NULL, 'add', 20, 16200.00, 324000.00, 300000.00, 24000.00, 'arya', '2026-03-31', 2, '2026-03-07 22:09:09', '2026-03-07 22:09:09', 8, 'RAK002'),
	(4, 3, 2, NULL, 'add', 1, 18000.00, 18000.00, 0.00, 18000.00, 'budi', '2026-04-16', 2, '2026-03-08 05:38:20', '2026-03-08 05:38:20', 1, 'RAK002'),
	(5, 3, 2, NULL, 'add', 1, 18000.00, 18000.00, 8000.00, 10000.00, 'budi', '2026-04-13', 2, '2026-03-08 05:39:20', '2026-03-08 05:39:20', 1, 'RAK002');
/*!40000 ALTER TABLE "warehouse_stock_transactions" ENABLE KEYS */;

-- Dumping structure for table public.warehouse_transfers
CREATE TABLE IF NOT EXISTS "warehouse_transfers" (
	"id" BIGINT NOT NULL DEFAULT 'nextval(''warehouse_transfers_id_seq''::regclass)',
	"from_warehouse_id" BIGINT NOT NULL,
	"to_warehouse_id" BIGINT NOT NULL,
	"product_id" BIGINT NULL DEFAULT NULL,
	"variation_id" BIGINT NULL DEFAULT NULL,
	"quantity" INTEGER NOT NULL,
	"status" VARCHAR(255) NOT NULL DEFAULT 'pending',
	"created_at" TIMESTAMP NULL DEFAULT NULL,
	"updated_at" TIMESTAMP NULL DEFAULT NULL,
	PRIMARY KEY ("id"),
	CONSTRAINT "warehouse_transfers_from_warehouse_id_foreign" FOREIGN KEY ("from_warehouse_id") REFERENCES "warehouses" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "warehouse_transfers_product_id_foreign" FOREIGN KEY ("product_id") REFERENCES "products" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "warehouse_transfers_to_warehouse_id_foreign" FOREIGN KEY ("to_warehouse_id") REFERENCES "warehouses" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "warehouse_transfers_variation_id_foreign" FOREIGN KEY ("variation_id") REFERENCES "product_variations" ("id") ON UPDATE NO ACTION ON DELETE CASCADE,
	CONSTRAINT "warehouse_transfers_status_check" CHECK ((((status)::text = ANY ((ARRAY['pending'::character varying, 'in_transit'::character varying, 'received'::character varying])::text[]))))
);

-- Dumping data for table public.warehouse_transfers: 2 rows
/*!40000 ALTER TABLE "warehouse_transfers" DISABLE KEYS */;
INSERT INTO "warehouse_transfers" ("id", "from_warehouse_id", "to_warehouse_id", "product_id", "variation_id", "quantity", "status", "created_at", "updated_at") VALUES
	(1, 3, 1, 1, NULL, 20, 'received', '2026-03-06 11:41:57', '2026-03-06 11:41:57'),
	(2, 3, 1, 2, NULL, 20, 'received', '2026-03-06 11:42:09', '2026-03-06 11:42:09');
/*!40000 ALTER TABLE "warehouse_transfers" ENABLE KEYS */;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
