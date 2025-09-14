<?php
// Memulai sesi untuk menyimpan data antar halaman
session_start();

// Inisialisasi array untuk menyimpan daftar pendaftaran jika belum ada
if (!isset($_SESSION['pendaftaran_list'])) {
    $_SESSION['pendaftaran_list'] = [];
}

// Menentukan halaman yang sedang aktif berdasarkan parameter URL
$page = $_GET['page'] ?? 'pilihan';
$edit_id = $_GET['edit_id'] ?? null;

// Logika untuk menghapus data
if ($page === 'hasil' && isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    if (isset($_SESSION['pendaftaran_list'][$delete_id])) {
        unset($_SESSION['pendaftaran_list'][$delete_id]);
        // Re-index array untuk menjaga urutan
        $_SESSION['pendaftaran_list'] = array_values($_SESSION['pendaftaran_list']);
    }
    header('Location: ?page=hasil');
    exit;
}

// Logika untuk memproses data formulir saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $berkas_name = $_POST['berkas_lama'] ?? 'Tidak ada file diupload'; // Ambil nama berkas lama jika ada
    if (isset($_FILES['berkas']) && $_FILES['berkas']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $berkas_name = basename($_FILES['berkas']['name']);
        $target_file = $upload_dir . $berkas_name;
        move_uploaded_file($_FILES['berkas']['tmp_name'], $target_file);
    }

    $data_baru = [
        'Nama' => htmlspecialchars($_POST['nama']),
        'Email' => htmlspecialchars($_POST['email']),
        'Nomor HP' => htmlspecialchars($_POST['hp']),
        'Semester' => htmlspecialchars($_POST['semester']),
        'IPK Terakhir' => htmlspecialchars($_POST['ipk']), // Ambil IPK dari input manual
        'Pilihan Beasiswa' => htmlspecialchars($_POST['beasiswa']),
        'Berkas Syarat' => $berkas_name,
        'Status Ajuan' => 'Belum diverifikasi'
    ];

    if (isset($_POST['edit_id']) && $_POST['edit_id'] !== '') {
        // Mode Update: Perbarui data yang ada
        $id = $_POST['edit_id'];
        $_SESSION['pendaftaran_list'][$id] = $data_baru;
    } else {
        // Mode Daftar: Tambahkan data baru ke array
        $_SESSION['pendaftaran_list'][] = $data_baru;
    }
    
    header('Location: ?page=hasil');
    exit;
}

// Siapkan data untuk mode edit
$data_edit = null;
if ($page === 'daftar' && $edit_id !== null && isset($_SESSION['pendaftaran_list'][$edit_id])) {
    $data_edit = $_SESSION['pendaftaran_list'][$edit_id];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Beasiswa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --warna-utama: #4F46E5; /* Indigo-600 */
            --warna-utama-hover: #4338CA; /* Indigo-700 */
            --warna-latar: #F9FAFB; /* Gray-50 */
            --warna-teks: #111827; /* Gray-900 */
            --warna-border: #E5E7EB; /* Gray-200 */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--warna-latar);
            color: var(--warna-teks);
        }
        .nav-link {
            @apply px-5 py-2 text-center text-gray-600 transition duration-300 font-medium rounded-md;
        }
        .nav-link:hover {
            @apply text-gray-900 bg-gray-100;
        }
        .nav-link.active {
            background-color: var(--warna-utama);
            color: white;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        .card {
             @apply bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-[var(--warna-border)] transition-transform duration-300;
        }
        .form-input-wrapper {
            @apply relative;
        }
        .form-input-icon {
            @apply absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400;
        }
        .form-input {
            @apply shadow-sm border rounded-lg w-full py-2.5 px-3.5 pl-10 text-gray-800 leading-tight focus:outline-none focus:ring-2;
            border-color: var(--warna-border);
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-input:focus {
            border-color: var(--warna-utama);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }
        .btn {
            @apply font-bold py-2.5 px-6 rounded-lg transition duration-300 flex items-center justify-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed;
        }
        .btn-primary {
            background-color: var(--warna-utama);
            color: white;
        }
        .btn-primary:hover:not(:disabled) {
            background-color: var(--warna-utama-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn-secondary {
            @apply bg-white border border-[var(--warna-border)] text-gray-700;
        }
        .btn-secondary:hover {
            @apply bg-gray-50 border-gray-300;
        }
        .status-badge {
            @apply px-2.5 py-0.5 text-xs font-medium rounded-full;
        }
    </style>
</head>
<body class="bg-gray-100">

    <div class="container mx-auto max-w-5xl p-4">
        
        <header class="w-full bg-white/70 backdrop-blur-lg p-3 mb-8 rounded-xl shadow-md sticky top-4 z-10">
            <nav class="flex justify-center items-center space-x-2 sm:space-x-4">
                <a href="?page=pilihan" class="nav-link <?= ($page === 'pilihan') ? 'active' : '' ?>">
                    <i class="fas fa-graduation-cap mr-2"></i>Pilihan Beasiswa
                </a>
                <a href="?page=daftar" class="nav-link <?= ($page === 'daftar') ? 'active' : '' ?>">
                    <i class="fas fa-edit mr-2"></i>Daftar
                </a>
                <a href="?page=hasil" class="nav-link <?= ($page === 'hasil') ? 'active' : '' ?>">
                    <i class="fas fa-poll mr-2"></i>Hasil
                </a>
            </nav>
        </header>

        <main>
            <?php if ($page === 'pilihan'): ?>
                <div class="card">
                    <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">PILIHAN BEASISWA</h1>
                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="border-l-4 border-indigo-500 p-6 rounded-r-lg bg-indigo-50 shadow-sm">
                            <h2 class="text-2xl font-semibold mb-2 text-indigo-800">Beasiswa Akademik</h2>
                            <p class="text-gray-600">Beasiswa ini ditujukan bagi mahasiswa yang memiliki prestasi akademik gemilang dengan IPK di atas 3.5. Bantuan yang diberikan berupa potongan UKT sebesar 50% selama satu semester.</p>
                        </div>
                        <div class="border-l-4 border-teal-500 p-6 rounded-r-lg bg-teal-50 shadow-sm">
                            <h2 class="text-2xl font-semibold mb-2 text-teal-800">Beasiswa Non-Akademik</h2>
                            <p class="text-gray-600">Diberikan kepada mahasiswa yang aktif dan berprestasi di bidang non-akademik seperti olahraga, seni, atau organisasi. Beasiswa ini mencakup biaya hidup bulanan.</p>
                        </div>
                    </div>
                </div>

            <?php elseif ($page === 'daftar'): ?>
                <div class="card max-w-3xl mx-auto">
                    <h1 class="text-3xl font-bold text-center mb-2"><?= $data_edit ? 'Edit Data Pendaftaran' : 'Formulir Pendaftaran Beasiswa' ?></h1>
                    <p class="text-center text-gray-500 mb-8">Silakan lengkapi data di bawah ini dengan benar.</p>
                    
                    <form action="?page=daftar" method="POST" enctype="multipart/form-data" class="space-y-5">
                        <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
                        <?php if($data_edit) { echo '<input type="hidden" name="berkas_lama" value="'.$data_edit['Berkas Syarat'].'">'; } ?>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="nama" class="font-medium text-gray-700 mb-1 block">Nama Lengkap</label>
                                <div class="form-input-wrapper">
                                    <div class="form-input-icon"><i class="fas fa-user"></i></div>
                                    <input class="form-input" id="nama" name="nama" type="text" placeholder="Masukkan nama Anda" value="<?= $data_edit['Nama'] ?? '' ?>" required>
                                </div>
                            </div>
                            <div>
                                <label for="email" class="font-medium text-gray-700 mb-1 block">Alamat Email</label>
                                <div class="form-input-wrapper">
                                    <div class="form-input-icon"><i class="fas fa-envelope"></i></div>
                                    <input class="form-input" id="email" name="email" type="email" placeholder="contoh@email.com" value="<?= $data_edit['Email'] ?? '' ?>" required>
                                </div>
                            </div>
                            <div>
                                <label for="hp" class="font-medium text-gray-700 mb-1 block">Nomor HP</label>
                                <div class="form-input-wrapper">
                                    <div class="form-input-icon"><i class="fas fa-phone"></i></div>
                                    <input class="form-input" id="hp" name="hp" type="tel" pattern="[0-9]+" title="Hanya boleh angka" placeholder="08xxxxxxxxxx" value="<?= $data_edit['Nomor HP'] ?? '' ?>" required>
                                </div>
                            </div>
                            <div>
                                <label for="semester" class="font-medium text-gray-700 mb-1 block">Semester</label>
                                <select class="form-input !pl-3" id="semester" name="semester" required>
                                    <option value="" disabled selected>Pilih semester saat ini</option>
                                    <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?= $i ?>" <?= (($data_edit['Semester'] ?? '') == $i) ? 'selected' : '' ?>>Semester <?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                             <div>
                                <label for="ipk" class="font-medium text-gray-700 mb-1 block">IPK Terakhir</label>
                                <div class="form-input-wrapper">
                                    <div class="form-input-icon"><i class="fas fa-star"></i></div>
                                    <input class="form-input" id="ipk" name="ipk" type="number" step="0.01" min="0" max="4.00" placeholder="Contoh: 3.50" value="<?= $data_edit['IPK Terakhir'] ?? '' ?>" required oninput="checkEligibility()">
                                </div>
                            </div>
                            <div>
                                <label for="beasiswa" class="font-medium text-gray-700 mb-1 block">Pilihan Beasiswa</label>
                                <select class="form-input !pl-3 disabled:bg-gray-100" id="beasiswa" name="beasiswa" required disabled>
                                    <option value="" disabled selected>Pilih jenis beasiswa</option>
                                    <option value="Akademik" <?= (($data_edit['Pilihan Beasiswa'] ?? '') == 'Akademik') ? 'selected' : '' ?>>Beasiswa Akademik</option>
                                    <option value="Non-Akademik" <?= (($data_edit['Pilihan Beasiswa'] ?? '') == 'Non-Akademik') ? 'selected' : '' ?>>Beasiswa Non-Akademik</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="berkas" class="font-medium text-gray-700 mb-1 block">Upload Berkas Syarat (PDF, JPG, ZIP)</label>
                            <input class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 disabled:opacity-50" id="berkas" name="berkas" type="file" <?= $data_edit ? '' : 'required' ?> disabled>
                            <?php if($data_edit): ?><small class="text-gray-500 mt-1 block">Kosongkan jika tidak ingin mengubah berkas yang sudah ada.</small><?php endif; ?>
                        </div>
                        <div class="flex items-center space-x-4 pt-4 border-t mt-6">
                            <button id="submitBtn" class="btn btn-primary" type="submit" disabled><?= $data_edit ? 'Simpan Perubahan' : 'Daftar Sekarang' ?></button>
                            <a href="?page=hasil" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>

            <?php elseif ($page === 'hasil'): ?>
                <div class="card">
                    <h1 class="text-3xl font-bold text-center mb-8">Hasil Pendaftaran Beasiswa</h1>
                    <div class="space-y-6">
                        <?php if (!empty($_SESSION['pendaftaran_list'])): 
                            foreach($_SESSION['pendaftaran_list'] as $id => $data):
                        ?>
                            <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 shadow-sm relative">
                                <div class="absolute top-4 right-4 flex space-x-3">
                                    <a href="?page=daftar&edit_id=<?= $id ?>" class="text-gray-400 hover:text-indigo-600 transition-colors"><i class="fas fa-edit fa-lg"></i></a>
                                    <a href="?page=hasil&delete_id=<?= $id ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" class="text-gray-400 hover:text-red-600 transition-colors"><i class="fas fa-trash fa-lg"></i></a>
                                </div>
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl font-bold mr-4">
                                        <?= substr($data['Nama'], 0, 1) ?>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-semibold text-gray-800"><?= $data['Nama'] ?></h2>
                                        <p class="text-sm text-gray-500"><?= $data['Email'] ?></p>
                                    </div>
                                </div>
                                <div class="border-t my-4"></div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                                    <div class="flex items-center text-gray-600"><i class="fas fa-phone w-5 text-center mr-2 text-gray-400"></i> <?= $data['Nomor HP'] ?></div>
                                    <div class="flex items-center text-gray-600"><i class="fas fa-layer-group w-5 text-center mr-2 text-gray-400"></i> Semester <?= $data['Semester'] ?></div>
                                    <div class="flex items-center text-gray-600"><i class="fas fa-star w-5 text-center mr-2 text-gray-400"></i> IPK: <?= number_format((float)$data['IPK Terakhir'], 2) ?></div>
                                    <div class="flex items-center text-gray-600 sm:col-span-2"><i class="fas fa-graduation-cap w-5 text-center mr-2 text-gray-400"></i> <?= $data['Pilihan Beasiswa'] ?></div>
                                    <div class="flex items-center text-gray-600"><i class="fas fa-file-alt w-5 text-center mr-2 text-gray-400"></i> <?= $data['Berkas Syarat'] ?></div>
                                    <div class="flex items-center">
                                        <i class="fas fa-info-circle w-5 text-center mr-2 text-gray-400"></i>
                                        <span class="status-badge bg-yellow-100 text-yellow-800"><?= $data['Status Ajuan'] ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endforeach;
                        else: 
                        ?>
                            <div class="text-center py-12 border-2 border-dashed rounded-lg">
                                <i class="fas fa-folder-open fa-3x text-gray-300 mb-4"></i>
                                <p class="text-gray-500 font-medium">Belum ada data pendaftaran.</p>
                                <p class="text-sm text-gray-400">Silakan isi formulir di menu "Daftar".</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>

        <footer class="text-center text-gray-500 mt-12 text-sm">
            Copyright &copy; <?= date("Y") ?> - Kampusaja.ac.id
        </footer>
    </div>
    
    <?php if ($page === 'daftar'): ?>
    <script>
        function checkEligibility() {
            const ipkInput = document.getElementById('ipk');
            const beasiswaSelect = document.getElementById('beasiswa');
            const berkasInput = document.getElementById('berkas');
            const submitBtn = document.getElementById('submitBtn');
            const ipkValue = parseFloat(ipkInput.value);

            if (ipkValue >= 3.0) {
                beasiswaSelect.disabled = false;
                berkasInput.disabled = false;
                submitBtn.disabled = false;
            } else {
                beasiswaSelect.disabled = true;
                berkasInput.disabled = true;
                submitBtn.disabled = true;
            }
        }
        document.addEventListener('DOMContentLoaded', checkEligibility);
    </script>
    <?php endif; ?>
</body>
</html>

