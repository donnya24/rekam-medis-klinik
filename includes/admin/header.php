<?php
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "Pengguna";
?>

<!-- Top Navbar -->
<nav class="navbar navbar-default" role="navigation" style="background: rgba(255, 255, 255, 0.2);">
    <div class="container-fluid">
        <div class="navbar-header">
        </div>
        <ul class="nav navbar-nav navbar-right" style="display: flex; align-items: center; width: 100%; padding-right: 15px;">
            <li style="margin-right: auto; margin-left: 235px;">
                <span style="color: white; font-weight: bold;">
                    Selamat Datang di Klinik Pratama Adi Hayati, <?php echo htmlspecialchars($username); ?>
                </span>
            </li>
            <li>
                <a href="../admin/profiladmin.php" style="color: white; font-weight: bold; display: flex; align-items: center;">
                    <i class="fas fa-user-circle" style="font-size: 24px;"></i>
                </a>
            </li>
            <li>
                <a href="/klinikku/auth/login.php" style="color: white; font-weight: bold; display: flex; align-items: center; margin-left: 10px;">
                    <i class="fas fa-sign-out-alt" style="font-size: 18px; margin-right: 5px;"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>
