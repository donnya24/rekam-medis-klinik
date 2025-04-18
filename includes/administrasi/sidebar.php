<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #1e2a38;
            color: white;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            font-size: 2rem;
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
            flex-grow: 1;
        }

        .sidebar ul li {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #cfd8dc;
            font-size: 1.2rem;
            padding: 18px 22px;
            transition: all 0.3s ease-in-out;
        }

        .sidebar ul li a i {
            margin-right: 12px;
            font-size: 1.5rem;
        }

        .sidebar ul li a:hover {
            background-color: #34495e;
            color: #ffffff;
        }

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
            <li><a href="../../../klinikku/app/administrasi/pendaftaran.php"><i class="fas fa-user-plus"></i> Pendaftaran Pasien</a></li>
            <li><a href="../../../klinikku/app/administrasi/pasien.php"><i class="fas fa-users"></i> Data Pasien</a></li>
            <li><a href="../../../klinikku/app/administrasi/reservasi.php"><i class="fas fa-bed"></i> Reservasi Pasien</a></li>
            <li><a href="../../../klinikku/app/administrasi/pembayaran.php"><i class="fas fa-credit-card"></i> Pembayaran</a></li>
            <li><a href="../../../klinikku/app/administrasi/laporan_obat.php"><i class="fas fa-file-invoice"></i> Laporan Transaksi Obat</a></li>
            <li><a href="../../../klinikku/app/administrasi/laporan_perawatan.php"><i class="fas fa-file-invoice"></i> Laporan Transaksi Perawatan</a></li>
        </ul>
    </div>
</body>
