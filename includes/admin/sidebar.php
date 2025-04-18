<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Styling Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh; /* Tinggi sesuai layar */
            position: fixed;
            top: 0;
            left: 0;
            background-color: #1e2a38; /* Warna sidebar */
            color: white;
            overflow-y: auto; /* Aktifkan scrollbar jika konten terlalu panjang */
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            font-size: 2rem; /* Perbesar ukuran font judul */
            text-align: center;
            margin: 0;
            padding: 20px 10px;
            background-color: #14222e;
            color: #ffffff;
            letter-spacing: 2px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1; /* Membuat daftar menu memenuhi sidebar */
        }

        .sidebar ul li {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #cfd8dc; /* Warna teks */
            font-size: 1.2rem; /* Perbesar ukuran font menu */
            padding: 18px 22px; /* Tambahkan padding agar lebih luas */
            transition: all 0.3s ease-in-out;
        }

        .sidebar ul li a i {
            margin-right: 12px;
            font-size: 1.5rem; /* Perbesar ikon */
        }

        .sidebar ul li a:hover {
            background-color: #34495e; /* Warna hover */
            color: #ffffff;
        }

        /* Responsif untuk layar kecil */
        @media (max-width: 768px) {
            .sidebar {
                width: 220px;
            }

            .sidebar h2 {
                font-size: 1.8rem; /* Sesuaikan ukuran font di layar kecil */
            }

            .sidebar ul li a {
                font-size: 1rem;
                padding: 15px 18px;
            }

            .sidebar ul li a i {
                font-size: 1.2rem;
            }
        }

        /* Responsif untuk layar kecil */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .sidebar h2 {
                font-size: 1.5rem;
            }

            .sidebar ul li a {
                font-size: 0.9rem;
                padding: 12px 16px;
            }

            .sidebar ul li a i {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>KLINIK PRATAMA ADI HAYATI</h2>
        <ul>
            <li><a href="../../index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="../../../klinikku/app/admin/pendaftaran.php"><i class="fas fa-user-plus"></i> Pendaftaran Pasien</a></li>
            <li><a href="../../../klinikku/app/admin/pasien.php"><i class="fas fa-users"></i> Data Pasien</a></li>
            <li><a href="../../../klinikku/app/admin/reservasi.php"><i class="fas fa-bed"></i> Reservasi Pasien</a></li>
            <li><a href="../../../klinikku/app/admin/dokter.php"><i class="fas fa-user-md"></i> Data Dokter</a></li>
            <li><a href="../../../klinikku/app/admin/perawat.php"><i class="fas fa-user-nurse"></i> Data Perawat</a></li>
            <li><a href="../../../klinikku/app/admin/petugas_administrasi.php"><i class="fas fa-user-shield"></i> Data Petugas Adminstrasi</a></li>
            <li><a href="../../../klinikku/app/admin/apoteker.php"><i class="fas fa-capsules"></i> Data Apoteker</a></li>
            <li><a href="../../../klinikku/app/admin/ruangan.php"><i class="fas fa-procedures"></i> Manajemen Ruangan</a></li>
            <li><a href="../../../klinikku/app/admin/perawatan.php"><i class="fas fa-stethoscope"></i>Manajemen Poliklinik</a></li>
            <li><a href="../../../klinikku/app/admin/obat.php"><i class="fas fa-pills"></i> Manajemen Obat</a></li>
            <li><a href="../../../klinikku/app/admin/hasil_pemeriksaan.php"><i class="fas fa-file-medical"></i> Hasil Pemeriksaan</a></li>
            <li><a href="../../../klinikku/app/admin/catatan_medis.php"><i class="fas fa-notes-medical"></i> Catatan Medis</a></li>
            <li><a href="../../../klinikku/app/admin/pembayaran.php"><i class="fas fa-credit-card"></i> Pembayaran</a></li>
            <li><a href="../../../klinikku/app/admin/laporan_obat.php"><i class="fas fa-file-invoice"></i> Laporan Transaksi Obat</a></li>
            <li><a href="../../../klinikku/app/admin/laporan_perawatan.php"><i class="fas fa-file-invoice"></i> Laporan Transaksi Perawatan</a></li>
            <li><a href="../../../klinikku/app/admin/kelola_akun.php"><i class="fas fa-user-cog"></i> Kelola Akun Petugas</a></li>
        </ul>
    </div>
</body>
