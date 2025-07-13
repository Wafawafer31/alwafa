<?php
define('IS_ADMIN_PAGE', true);
require_once '../includes/config.php';
checkAdmin();

// Di sini logika untuk menyimpan template (layout_json) ke database akan ditambahkan

$clients = $mysqli->query("SELECT id, name FROM clients ORDER BY name ASC");
$page_title = "Editor Kolase";
include 'includes/admin_header.php';
?>

<style>
    #collage-canvas-wrapper {
        position: relative;
        width: 400px; /* Lebar photostrip */
        height: 800px; /* Tinggi photostrip */
        border: 2px dashed #ccc;
        background-color: #f0f0f0;
        background-size: cover;
        background-position: center;
    }
    .placeholder-image, #frame-overlay {
        position: absolute;
        width: 380px; /* Lebar area foto */
        height: 380px; /* Tinggi area foto */
        border: 1px solid #999;
        background-color: #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: move; /* Menandakan bisa di-drag */
    }
    #placeholder1 { top: 10px; left: 10px; z-index: 1; }
    #placeholder2 { top: 410px; left: 10px; z-index: 1; }
    #frame-overlay {
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
        background-color: transparent;
        background-size: contain;
        background-repeat: no-repeat;
        pointer-events: none; /* Agar tidak menghalangi drag */
        z-index: 10;
    }
    #text-overlay {
        position: absolute;
        bottom: 20px;
        left: 10px;
        z-index: 5;
        font-size: 1.2rem;
        font-weight: bold;
        color: #fff;
        text-shadow: 1px 1px 2px black;
        cursor: move;
    }
</style>

<h1>Editor Template Kolase</h1>
<p>Desain template kolase yang akan digunakan klien. Fitur drag-and-drop memerlukan integrasi JavaScript lebih lanjut.</p>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5>Kontrol</h5>
                <form id="template-form">
                     <div class="mb-3">
                        <label for="client-select" class="form-label">Pilih Klien</label>
                        <select id="client-select" name="client_id" class="form-select" required>
                            <option value="">-- Pilih Klien --</option>
                            <?php while($client = $clients->fetch_assoc()): ?>
                            <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="template-name" class="form-label">Nama Template</label>
                        <input type="text" id="template-name" name="template_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="bg-upload" class="form-label">Upload Background</label>
                        <input type="file" id="bg-upload" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="frame-upload" class="form-label">Upload Frame (.png)</label>
                        <input type="file" id="frame-upload" class="form-control">
                    </div>
                     <div class="mb-3">
                        <label for="text-input" class="form-label">Teks Tambahan</label>
                        <input type="text" id="text-input" class="form-control" placeholder="Contoh: The Wedding Of...">
                    </div>
                    <button type="button" class="btn btn-primary" onclick="saveTemplate()">Simpan Template</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <h5>Preview Kanvas</h5>
        <div id="collage-canvas-wrapper">
            <div id="placeholder1" class="placeholder-image">Foto 1</div>
            <div id="placeholder2" class="placeholder-image">Foto 2</div>
            <div id="text-overlay"></div>
            <div id="frame-overlay"></div>
        </div>
    </div>
</div>

<script>
    // JavaScript untuk preview dan menyimpan data
    document.getElementById('bg-upload').addEventListener('change', function(e) {
        const url = URL.createObjectURL(e.target.files[0]);
        document.getElementById('collage-canvas-wrapper').style.backgroundImage = `url(${url})`;
    });

    document.getElementById('frame-upload').addEventListener('change', function(e) {
        const url = URL.createObjectURL(e.target.files[0]);
        document.getElementById('frame-overlay').style.backgroundImage = `url(${url})`;
    });

    document.getElementById('text-input').addEventListener('input', function(e) {
        document.getElementById('text-overlay').innerText = e.target.value;
    });

    function saveTemplate() {
        // Fungsi ini akan mengumpulkan data layout (posisi, ukuran, gambar, teks)
        // dan mengirimkannya ke server via AJAX untuk disimpan ke tabel collage_templates
        const layout = {
            placeholder1: { x: '10px', y: '10px', w: '380px', h: '380px', z: 1 },
            placeholder2: { x: '10px', y: '410px', w: '380px', h: '380px', z: 1 },
            text: {
                content: document.getElementById('text-input').value,
                x: '10px',
                y: '750px', // contoh
                z: 5
            }
        };

        const layoutJson = JSON.stringify(layout);
        const clientId = document.getElementById('client-select').value;
        const templateName = document.getElementById('template-name').value;
        
        if (!clientId || !templateName) {
            alert('Silakan pilih klien dan beri nama template.');
            return;
        }

        alert(`Template akan disimpan untuk Klien ID ${clientId} dengan nama "${templateName}"\nData JSON:\n${layoutJson}\n\n(Fungsionalitas AJAX untuk menyimpan belum diimplementasikan)`);
        
        // Contoh implementasi AJAX (memerlukan file PHP handler):
        /*
        const formData = new FormData();
        formData.append('client_id', clientId);
        formData.append('name', templateName);
        formData.append('layout_json', layoutJson);
        // Tambahkan file upload jika perlu

        fetch('/alwafahub/admin/ajax_save_template.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Template berhasil disimpan!');
            } else {
                alert('Gagal menyimpan template: ' + data.error);
            }
        });
        */
    }
</script>

<?php include 'includes/admin_footer.php'; ?>
