<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title><?= $title ?? 'TSC' ?> - PT Tata Sanjaya Cakrawala</title>

<!-- Favicon -->
<link rel="icon" type="image/png" href="<?= base_url('assets/img/TSC_page-0001.png') ?>">

<!-- Tabler CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;300;400;600;700;800;900&display=swap"
    rel="stylesheet">

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<style>
    body {
        font-family: 'Nunito', sans-serif;
    }

    .page-body {
        padding-top: 1.5rem;
    }

    /* ─── NAVBAR ─── */
    .navbar-brand-image {
        max-height: 36px;
    }

    /* ─── FOOTER ─── */
    .app-footer {
        background: #fff;
        border-top: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 24px;
        font-size: 0.85rem;
        color: #6c757d;
    }

    /* ─── CARD ─── */
    .card {
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
        transition: box-shadow 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    /* ─── PAGE HEADING ─── */
    .page-title {
        font-size: 1.3rem;
        font-weight: 800;
        color: #2c3e50;
    }

    /* ─── TABLE ─── */
    .table th {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ─── FORM ─── */
    .form-control:focus,
    .form-select:focus {
        border-color: #066fd1;
        box-shadow: 0 0 0 0.2rem rgba(6, 111, 209, 0.2);
    }

    /* ─── ALERT ─── */
    .alert {
        border-radius: 10px;
        font-size: 0.875rem;
    }

    /* ─── BUTTONS ─── */
    .btn {
        font-weight: 600;
        font-size: 0.85rem;
    }

    /* ─── BADGE COUNTER ─── */
    .badge-counter {
        font-size: 0.65rem;
        padding: 2px 6px;
        border-radius: 10px;
    }
</style>