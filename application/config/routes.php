<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/

// CORE ROUTES
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// AUTH ROUTES
$route['login'] = 'login/index';
$route['login/proses'] = 'login/proses';
$route['logout'] = 'login/logout';
$route['home'] = 'home';

// PENGGUNA / KARYAWAN
$route['pengguna'] = 'pengguna';
$route['pengguna/tambah'] = 'pengguna/tambah';
$route['pengguna/ubah/(:num)'] = 'pengguna/ubah/$1';
$route['pengguna/hapus/(:num)'] = 'pengguna/hapus/$1';

// CUSTOMER
$route['customer'] = 'customer';
$route['customer/tambah'] = 'customer/tambah';
$route['customer/ubah/(:num)'] = 'customer/ubah/$1';
$route['customer/hapus/(:num)'] = 'customer/hapus/$1';

// VENDOR
$route['vendorr'] = 'vendorr';
$route['vendorr/tambah'] = 'vendorr/tambah';
$route['vendorr/ubah/(:num)'] = 'vendorr/ubah/$1';
$route['vendorr/hapus/(:num)'] = 'vendorr/hapus/$1';

// DRIVER
$route['driver'] = 'driver';
$route['driver/tambah'] = 'driver/tambah';
$route['driver/ubah/(:num)'] = 'driver/ubah/$1';
$route['driver/hapus/(:num)'] = 'driver/hapus/$1';

// AKUN BIAYA
$route['akunbiaya'] = 'akunbiaya';
$route['akunbiaya/tambah'] = 'akunbiaya/tambah';
$route['akunbiaya/ubah/(:num)'] = 'akunbiaya/ubah/$1';
$route['akunbiaya/hapus/(:num)'] = 'akunbiaya/hapus/$1';

// UNIT
$route['unit'] = 'unit';
$route['unit/tambah'] = 'unit/tambah';
$route['unit/ubah/(:num)'] = 'unit/ubah/$1';
$route['unit/hapus/(:num)'] = 'unit/hapus/$1';

// MASTER RUTE
$route['rute'] = 'rute/index';
$route['rute/tambah'] = 'rute/tambah';
$route['rute/proses_tambah'] = 'rute/proses_tambah';
$route['rute/ubah/(:num)'] = 'rute/ubah/$1';
$route['rute/proses_ubah/(:num)'] = 'rute/proses_ubah/$1';
$route['rute/hapus/(:num)'] = 'rute/hapus/$1';
$route['rute/filter'] = 'rute/filter';
$route['rute/import'] = 'rute/import';
$route['rute/proses_import'] = 'rute/proses_import';

// JURNAL / PEMASUKAN
$route['pemasukan'] = 'pemasukan/index';
$route['pemasukan/tambah'] = 'pemasukan/tambah';
$route['pemasukan/proses_tambah'] = 'pemasukan/proses_tambah';
$route['pemasukan/generate_kode'] = 'pemasukan/generate_kode';

// Unit Document
$route['unit_document/proses_tambah'] = 'UnitDocument/proses_tambah';
$route['unit_document/proses_ubah/(:num)'] = 'UnitDocument/proses_ubah/$1';
$route['unit_document/hapus/(:num)'] = 'UnitDocument/hapus/$1';

// Unit Fuel
$route['unit_fuel/proses_tambah'] = 'UnitFuel/proses_tambah';
$route['unit_fuel/proses_ubah/(:num)'] = 'UnitFuel/proses_ubah/$1';
$route['unit_fuel/hapus/(:num)'] = 'UnitFuel/hapus/$1';

// Unit Maintenance
$route['unit_maintenance/proses_tambah'] = 'UnitMaintenance/proses_tambah';
$route['unit_maintenance/proses_ubah/(:num)'] = 'UnitMaintenance/proses_ubah/$1';
$route['unit_maintenance/hapus/(:num)'] = 'UnitMaintenance/hapus/$1';

$route['driver_keluhan']          = 'driver_keluhan/index';
$route['driver_keluhan/submit']   = 'driver_keluhan/submit';
$route['driver_keluhan/admin']    = 'driver_keluhan/admin';
$route['driver_keluhan/detail/(:num)'] = 'driver_keluhan/detail/$1';
$route['driver_keluhan/update_status/(:num)'] = 'driver_keluhan/update_status/$1';
$route['driver_keluhan/export'] = 'driver_keluhan/export';
$route['driver_keluhan/hapus/(:num)'] = 'driver_keluhan/hapus/$1';

$route['notifikasi/get']       = 'notifikasi/get';
$route['notifikasi/mark_read'] = 'notifikasi/mark_read';

// ── Support Ticket ──────────────────────────────────────────
$route['ticket']                      = 'ticket/index';
$route['ticket/buat']                 = 'ticket/buat';
$route['ticket/detail/(:num)']        = 'ticket/detail/$1';
$route['ticket/update_status/(:num)'] = 'ticket/update_status/$1';

$route['jadwal_kerja'] = 'JadwalKerja/index';
$route['jadwal_kerja/tambah_jadwal'] = 'JadwalKerja/tambah_jadwal';
$route['jadwal_kerja/ubah_jadwal/(:num)'] = 'JadwalKerja/ubah_jadwal/$1';
$route['jadwal_kerja/hapus_jadwal/(:num)'] = 'JadwalKerja/hapus_jadwal/$1';
$route['jadwal_kerja/simpan_mapping'] = 'JadwalKerja/simpan_mapping';
$route['jadwal_kerja/hapus_mapping/(:any)'] = 'JadwalKerja/hapus_mapping/$1';
$route['jadwal_kerja/tambah_hari_off'] = 'JadwalKerja/tambah_hari_off';
$route['jadwal_kerja/hapus_hari_off/(:num)'] = 'JadwalKerja/hapus_hari_off/$1';

// RFID CARDS
$route['rfid_cards'] = 'RfidCards/index';
$route['rfid_cards/tambah'] = 'RfidCards/tambah';
$route['rfid_cards/edit/(:num)'] = 'RfidCards/edit/$1';
$route['rfid_cards/hapus/(:num)'] = 'RfidCards/hapus/$1';
$route['rfid_cards/toggle/(:num)'] = 'RfidCards/toggle/$1';
$route['absensi/rfid_submit'] = 'absensi/rfid_submit';
$route['rfid_cards/pending']       = 'RfidCards/pending';
$route['rfid_cards/check_pending'] = 'RfidCards/check_pending';
$route['rfid_cards/assign']        = 'RfidCards/assign';
$route['rfid_cards/hapus_pending/(:any)'] = 'RfidCards/hapus_pending/$1';

// Register Karyawan — public
$route['register']           = 'registerkaryawan/index';
$route['register/proses']    = 'registerkaryawan/proses';
$route['register/sukses']    = 'registerkaryawan/sukses';
 
// Register Karyawan — admin (superadmin only)
$route['register/pending']   = 'registerkaryawan/pending';
$route['register/list_ajax'] = 'registerkaryawan/list_ajax';
$route['register/approve']   = 'registerkaryawan/approve';
$route['register/reject']    = 'registerkaryawan/reject';
$route['register/hapus']     = 'registerkaryawan/hapus';

//Broadcast
$route['broadcast']                  = 'broadcast/index';
$route['broadcast/get_json/(:num)']  = 'broadcast/get_json/$1';
$route['broadcast/store']            = 'broadcast/store';
$route['broadcast/update/(:num)']    = 'broadcast/update/$1';
$route['broadcast/delete/(:num)']    = 'broadcast/delete/$1';
$route['broadcast/toggle/(:num)']    = 'broadcast/toggle/$1';
$route['broadcast/get_banner']       = 'broadcast/get_banner';
$route['broadcast/dismiss/(:num)']   = 'broadcast/dismiss/$1';
$route['broadcast/dismiss_all']      = 'broadcast/dismiss_all';
$route['broadcast/count']            = 'broadcast/count';

//Laporan data
$route['absensi/laporan_data'] = 'absensi/laporan_data';

// Org Chart
$route['org_chart']                = 'OrgChart/index';
$route['org_chart/manage']         = 'OrgChart/manage';
$route['org_chart/tambah']         = 'OrgChart/tambah';
$route['org_chart/ubah/(:num)']    = 'OrgChart/ubah/$1';
$route['org_chart/hapus/(:num)']   = 'OrgChart/hapus/$1';
$route['org_chart/set_visibility'] = 'OrgChart/set_visibility';
$route['org_chart/get_tree']       = 'OrgChart/get_tree';

//analytics
$route['analytics']           = 'Analytics/index';
$route['analytics/import']    = 'Analytics/import';
$route['analytics/do_import'] = 'Analytics/do_import';
$route['analytics/export']    = 'Analytics/export';