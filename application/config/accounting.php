<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Accounting System Configuration
|--------------------------------------------------------------------------
|
| SIMPLE MODE: 
| - Pengeluaran tanpa potongan PPH
| - 2-way journal entry
| - Vendor receive full amount
|
| ADVANCED MODE:
| - Pengeluaran dengan potongan PPH
| - 3-way journal entry (includes OCAS/Tax accounts)
| - Track PPH & PPN hutang ke negara
| - Support Setor PPH & PPN
|
*/

// ============================================
// ACCOUNTING MODE
// ============================================
// Set to 'simple' or 'advanced'
$config['accounting_mode'] = 'advanced';  // ✅ UPGRADED TO ADVANCED

// ============================================
// TAX SYSTEM CONFIGURATION
// ============================================

// Enable/Disable OCAS tracking (Tax liability accounts)
$config['enable_ocas'] = true;  // ✅ ENABLED

// Minimum tax amount to record (ignore if below this)
$config['min_pph_amount'] = 1000;  // Rp 1.000 (ignore cents for PPH)
$config['min_ppn_amount'] = 1000;  // Rp 1.000 (ignore cents for PPN)

// ============================================
// TAX ACCOUNTS MAPPING
// ============================================

/**
 * Tax Accounts Configuration
 * Maps tax type to account code in tb_akunbiaya
 */
$config['tax_accounts'] = [
    // PPH (Pajak Penghasilan - withholding tax from vendors)
    'pph23' => '51',   // PPH Pasal 23: Tax on services, royalties, etc.
    'pph42' => '52',   // PPH Pasal 4(2): Final tax on rent, construction
    
    // PPN (Pajak Pertambahan Nilai - VAT from customers)
    'ppn_keluaran' => '53',  // PPN Keluaran: Output VAT collected from customers
];

/**
 * Legacy Support (deprecated, use tax_accounts instead)
 * Kept for backward compatibility with older code
 */
$config['pph_accounts'] = [
    'pph23' => '51',  // PPH 23 Memotong 2%
    'pph42' => '52',  // PPH 4(2) Memotong 0.5%
];

// ============================================
// TAX RATE CONFIGURATION
// ============================================

/**
 * Tax Rate Thresholds
 * Used for auto-detection of tax type based on rate
 */
$config['pph_rates'] = [
    'pph23' => 2.0,    // 2% - Jasa, royalti, dll
    'pph42' => 0.5,    // 0.5% - Sewa, konstruksi
];

$config['ppn_rate'] = 11.0;  // 11% - Standard PPN rate (updated 2022)

// ============================================
// TAX TYPES METADATA
// ============================================

/**
 * Tax Types Configuration
 * Metadata for display, validation, and UI rendering
 */
$config['tax_types'] = [
    'pph23' => [
        'code' => '51',
        'name' => 'PPH Pasal 23',
        'short_name' => 'PPH 23',
        'description' => 'Pajak penghasilan atas jasa, royalti, dan penghasilan lainnya',
        'rate_range' => '2%',
        'type' => 'withholding',  // withholding = dipotong dari vendor
        'source' => 'vendor',
        'color' => 'danger',
        'icon' => 'fa-receipt',
        'enabled' => true
    ],
    'pph42' => [
        'code' => '52',
        'name' => 'PPH Pasal 4 Ayat 2',
        'short_name' => 'PPH 4(2)',
        'description' => 'Pajak final atas sewa tanah/bangunan dan konstruksi',
        'rate_range' => '0.5%',
        'type' => 'withholding',
        'source' => 'vendor',
        'color' => 'warning',
        'icon' => 'fa-building',
        'enabled' => true
    ],
    'ppn_keluaran' => [
        'code' => '53',
        'name' => 'PPN Keluaran',
        'short_name' => 'PPN Keluaran',
        'description' => 'Pajak pertambahan nilai dari penjualan ke customer (Output VAT)',
        'rate_range' => '11%',
        'type' => 'output_vat',  // output_vat = dipungut dari customer
        'source' => 'customer',
        'color' => 'success',
        'icon' => 'fa-file-invoice-dollar',
        'enabled' => true
    ]
];

// ============================================
// TAX PAYMENT DEADLINE CONFIGURATION
// ============================================

/**
 * Tax Payment Deadlines
 * Day of month when tax must be paid to government
 */
$config['tax_deadline'] = [
    'pph' => 10,      // PPH: Tanggal 10 bulan berikutnya
    'ppn' => 15,      // PPN: Tanggal 15 bulan berikutnya (akhir masa pajak)
    'pph_annual' => 31, // PPH Tahunan: 31 Maret tahun berikutnya
];

/**
 * Tax Deadline Messages
 * Display templates for deadline warnings
 */
$config['tax_deadline_messages'] = [
    'pph' => 'Batas pembayaran: Tanggal %d bulan berikutnya',
    'ppn' => 'Batas pembayaran: Tanggal %d bulan berikutnya',
];

// ============================================
// TAX PAYMENT CONFIGURATION
// ============================================

/**
 * Bank Accounts for Tax Payment
 * List of bank account codes that can be used for paying taxes
 * Empty array = all banks allowed
 */
$config['tax_payment_banks'] = []; // Empty = allow all banks

/**
 * Tax Payment Reff No Prefix
 * Prefix for tax payment reference numbers
 */
$config['tax_payment_prefix'] = 'TAX-'; // TAX-00001, TAX-00002, etc.

/**
 * Legacy prefix (deprecated)
 */
$config['pph_payment_prefix'] = 'PPH-'; // Old prefix, kept for reference

// ============================================
// TAX REMINDER CONFIGURATION
// ============================================

/**
 * Enable Tax Payment Reminder
 * Show reminder X days before deadline
 */
$config['enable_tax_reminder'] = true;
$config['tax_reminder_days_before'] = 3; // Reminder 3 hari sebelum deadline

/**
 * Tax Reminder Colors
 * Badge colors for different urgency levels
 */
$config['tax_reminder_colors'] = [
    'urgent' => 'danger',    // <= 1 day before deadline
    'warning' => 'warning',  // 2-3 days before deadline
    'info' => 'info',        // > 3 days before deadline
];

// ============================================
// JOURNAL ENTRY CONFIGURATION
// ============================================

/**
 * Journal Entry Templates
 * Standard journal entry patterns for different transactions
 */
$config['journal_templates'] = [
    // Pengeluaran dengan PPH (3-way entry)
    'pengeluaran_with_pph' => [
        'description' => 'Expense with PPH withholding',
        'entries' => [
            ['type' => 'expense', 'side' => 'debit'],   // DR Expense (nominal + PPN)
            ['type' => 'bank', 'side' => 'credit'],     // CR Bank (total_bayar)
            ['type' => 'pph', 'side' => 'credit'],      // CR PPH/OCAS (pph amount)
        ]
    ],
    
    // Invoice dengan PPN (3-way entry)
    'invoice_with_ppn' => [
        'description' => 'Sales invoice with PPN output VAT',
        'entries' => [
            ['type' => 'receivable', 'side' => 'debit'], // DR Piutang/Kas
            ['type' => 'revenue', 'side' => 'credit'],   // CR Pendapatan
            ['type' => 'ppn', 'side' => 'credit'],       // CR PPN Keluaran
        ]
    ],
    
    // Pembayaran Pajak (2-way entry)
    'tax_payment' => [
        'description' => 'Tax payment to government',
        'entries' => [
            ['type' => 'tax', 'side' => 'debit'],   // DR Tax Account (reduce liability)
            ['type' => 'bank', 'side' => 'credit'], // CR Bank
        ]
    ]
];

// ============================================
// VALIDATION RULES
// ============================================

/**
 * Tax Validation Rules
 * Rules for validating tax transactions
 */
$config['tax_validation'] = [
    'require_masa_pajak' => true,      // Require tax period (masa pajak) field
    'require_bukti_potong' => false,   // Optional tax certificate number
    'validate_balance' => true,        // Validate payment doesn't exceed balance
    'allow_partial_payment' => true,   // Allow paying less than full balance
    'check_deadline' => true,          // Warn if past deadline
];

// ============================================
// REPORTING CONFIGURATION
// ============================================

/**
 * Tax Report Settings
 */
$config['tax_reports'] = [
    'default_period' => 'monthly',     // monthly, quarterly, annually
    'export_formats' => ['xlsx', 'pdf'], // Supported export formats
    'include_details' => true,         // Include transaction details in reports
    'group_by_type' => true,          // Group by tax type (PPH23, PPH42, PPN)
];

// ============================================
// FEATURE FLAGS
// ============================================

/**
 * Feature Flags
 * Enable/disable specific features
 */
$config['tax_features'] = [
    'enable_pph23' => true,           // Enable PPH 23 tracking
    'enable_pph42' => true,           // Enable PPH 4(2) tracking
    'enable_ppn_keluaran' => true,    // Enable PPN Keluaran tracking
    'enable_period_filter' => true,   // Enable period-based filtering
    'enable_auto_reminder' => true,   // Enable automatic deadline reminders
    'enable_bulk_payment' => false,   // Enable bulk tax payment (future feature)
];

// ============================================
// INTEGRATION SETTINGS
// ============================================

/**
 * External System Integration
 * Settings for integration with external systems (e.g., e-Filing, e-Bupot)
 */
$config['tax_integration'] = [
    'enable_efiling' => false,        // Connect to DJP e-Filing (future)
    'enable_ebupot' => false,         // Connect to e-Bupot (future)
    'api_endpoint' => '',             // API endpoint for tax system
    'api_key' => '',                  // API key (store securely)
];

// ============================================
// DEBUG & LOGGING
// ============================================

/**
 * Debug & Logging Settings
 */
$config['tax_debug'] = [
    'log_tax_calculations' => true,   // Log all tax calculations
    'log_journal_entries' => true,    // Log journal entry creation
    'log_payments' => true,           // Log tax payments
    'log_level' => 'info',            // debug, info, warning, error
];

// ============================================
// NOTES & DOCUMENTATION
// ============================================

/*
|--------------------------------------------------------------------------
| ACCOUNTING FLOW DOCUMENTATION
|--------------------------------------------------------------------------
|
| PPH FLOW (Withholding Tax from Vendors):
| ========================================
| 1. Bayar Vendor → PPH dipotong
|    DR Biaya (nominal + PPN)
|    CR Bank (total_bayar = nominal + PPN - PPH)
|    CR OCAS/PPH (PPH amount) ← Hutang ke negara
|
| 2. Bayar PPH ke Negara
|    DR OCAS/PPH (pay the liability)
|    CR Bank
|
| PPN FLOW (Output VAT from Customers):
| ======================================
| 1. Invoice ke Customer → PPN dipungut
|    DR Piutang/Kas (amount + PPN)
|    CR Pendapatan (amount)
|    CR PPN Keluaran (PPN amount) ← Hutang ke negara
|
| 2. Bayar PPN ke Negara
|    DR PPN Keluaran (pay the liability)
|    CR Bank
|
| OCAS BALANCE:
| =============
| OCAS Balance = Total Kredit - Total Debit
|              = (Tax collected/withheld) - (Tax paid)
|              = Hutang ke negara
|
| When balance = 0 → All taxes paid!
|
*/

// ============================================
// END OF CONFIGURATION
// ============================================