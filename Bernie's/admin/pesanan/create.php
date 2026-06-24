<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

$page_title = 'Tambah Pesanan';
$current_page = 'pesanan';

$new_id = generateId($conn, 'pesanan', 'id_pesanan', 'PSN');

$pelanggan_query = $conn->query("SELECT id_pelanggan, nama FROM pelanggan ORDER BY nama ASC");
$produk_query = $conn->query("SELECT id_produk, nama_produk, harga, stok FROM produk WHERE stok > 0 ORDER BY nama_produk ASC");

// Prepare product data for JavaScript
$products_data = [];
while($p = $produk_query->fetch_assoc()) {
    $products_data[$p['id_produk']] = [
        'nama' => $p['nama_produk'],
        'harga' => $p['harga'],
        'stok' => $p['stok']
    ];
}
$produk_query->data_seek(0); // Reset pointer for HTML select

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pelanggan = sanitize($_POST['id_pelanggan']);
    $status = sanitize($_POST['status']);
    $items = $_POST['items'] ?? [];
    $qty = $_POST['qty'] ?? [];
    
    if(empty($items) || count($items) === 0) {
        $error = "Pesanan harus memiliki minimal 1 item produk.";
    } else {
        $conn->begin_transaction();
        try {
            // Calculate total first
            $total = 0;
            $pesanan_items = [];
            
            foreach($items as $index => $id_produk) {
                if(empty($id_produk) || empty($qty[$index])) continue;
                
                $id_produk = sanitize($id_produk);
                $jumlah = (int)$qty[$index];
                
                if($jumlah <= 0) continue;
                
                // Get current price and stock
                $check = $conn->prepare("SELECT harga, stok FROM produk WHERE id_produk = ?");
                $check->bind_param("s", $id_produk);
                $check->execute();
                $prod = $check->get_result()->fetch_assoc();
                
                if($prod['stok'] < $jumlah) {
                    throw new Exception("Stok tidak mencukupi untuk beberapa produk.");
                }
                
                $subtotal = $prod['harga'] * $jumlah;
                $total += $subtotal;
                
                $pesanan_items[] = [
                    'id_produk' => $id_produk,
                    'jumlah' => $jumlah,
                    'subtotal' => $subtotal
                ];
            }
            
            if(empty($pesanan_items)) {
                throw new Exception("Item pesanan tidak valid.");
            }
            
            // 1. Insert Pesanan
            $stmt = $conn->prepare("INSERT INTO pesanan (id_pesanan, id_pelanggan, total, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssds", $new_id, $id_pelanggan, $total, $status);
            $stmt->execute();
            
            // 2. Insert Detail & Update Stok
            foreach($pesanan_items as $item) {
                $detail_id = generateId($conn, 'detail_pesanan', 'id_detail', 'DTL');
                
                $dtl = $conn->prepare("INSERT INTO detail_pesanan (id_detail, id_pesanan, id_produk, jumlah, subtotal) VALUES (?, ?, ?, ?, ?)");
                $dtl->bind_param("sssid", $detail_id, $new_id, $item['id_produk'], $item['jumlah'], $item['subtotal']);
                $dtl->execute();
                
                // Update Stok
                $upd = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE id_produk = ?");
                $upd->bind_param("is", $item['jumlah'], $item['id_produk']);
                $upd->execute();
            }
            
            $conn->commit();
            header("Location: index.php?msg=success");
            exit;
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}

include '../../includes/admin_header.php';
?>

<div class="form-card fade-in" style="max-width: 1000px;">
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form action="" method="POST" id="orderForm">
        <div class="form-row" style="margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #f0e6e0;">
            <div class="form-group">
                <label class="form-label">ID Pesanan</label>
                <input type="text" class="form-control" name="id_pesanan" value="<?= $new_id ?>" readonly>
            </div>
            
            <div class="form-group">
                <label class="form-label">Pelanggan *</label>
                <select name="id_pelanggan" class="form-control" required>
                    <option value="">Pilih Pelanggan...</option>
                    <?php while($pl = $pelanggan_query->fetch_assoc()): ?>
                        <option value="<?= $pl['id_pelanggan'] ?>"><?= $pl['id_pelanggan'] ?> - <?= htmlspecialchars($pl['nama']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Status Awal</label>
                <select name="status" class="form-control" required>
                    <option value="pending">Pending</option>
                    <option value="proses">Proses</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>
        </div>

        <div class="items-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h4 style="color: var(--primary);">Detail Item Produk</h4>
                <button type="button" class="btn btn-sm btn-secondary" onclick="addItemRow()">
                    <i class="fas fa-plus"></i> Tambah Item
                </button>
            </div>
            
            <table class="data-table" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width: 40%;">Produk</th>
                        <th style="width: 20%;">Harga</th>
                        <th style="width: 15%;">Jumlah</th>
                        <th style="width: 20%;">Subtotal</th>
                        <th style="width: 5%;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <!-- Dynamic rows will go here -->
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right; font-weight: bold;">TOTAL</td>
                        <td colspan="2" style="font-weight: bold; font-size: 1.2rem; color: var(--primary);">
                            Rp <span id="grandTotalDisplay">0</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="form-actions" style="margin-top: 40px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Proses Pesanan</button>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </form>
</div>

<!-- Product options template for JS -->
<template id="productOptions">
    <option value="">Pilih Produk...</option>
    <?php while($pr = $produk_query->fetch_assoc()): ?>
        <option value="<?= $pr['id_produk'] ?>"><?= htmlspecialchars($pr['nama_produk']) ?> (Stok: <?= $pr['stok'] ?>)</option>
    <?php endwhile; ?>
</template>

<script>
const productsData = <?= json_encode($products_data) ?>;

function formatMoney(amount) {
    return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function addItemRow() {
    const tbody = document.getElementById('itemsBody');
    const rowId = 'row_' + Date.now();
    const optionsHtml = document.getElementById('productOptions').innerHTML;
    
    const tr = document.createElement('tr');
    tr.id = rowId;
    tr.innerHTML = `
        <td>
            <select name="items[]" class="form-control product-select" required onchange="updateRow('${rowId}')">
                ${optionsHtml}
            </select>
        </td>
        <td>
            <div id="price_${rowId}">-</div>
        </td>
        <td>
            <input type="number" name="qty[]" class="form-control" value="1" min="1" required onchange="updateRow('${rowId}')" onkeyup="updateRow('${rowId}')">
        </td>
        <td>
            <div id="subtotal_${rowId}" style="font-weight: 600;">Rp 0</div>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeRow('${rowId}')"><i class="fas fa-times"></i></button>
        </td>
    `;
    
    tbody.appendChild(tr);
}

function removeRow(rowId) {
    document.getElementById(rowId).remove();
    calculateGrandTotal();
}

function updateRow(rowId) {
    const row = document.getElementById(rowId);
    const select = row.querySelector('.product-select');
    const qtyInput = row.querySelector('input[name="qty[]"]');
    const priceDisplay = document.getElementById('price_' + rowId);
    const subtotalDisplay = document.getElementById('subtotal_' + rowId);
    
    const productId = select.value;
    
    if(productId && productsData[productId]) {
        const price = parseFloat(productsData[productId].harga);
        let qty = parseInt(qtyInput.value) || 0;
        
        // Prevent exceeding stock
        const maxStock = parseInt(productsData[productId].stok);
        if(qty > maxStock) {
            alert('Stok tidak mencukupi! Maksimal: ' + maxStock);
            qtyInput.value = maxStock;
            qty = maxStock;
        }
        
        const subtotal = price * qty;
        
        priceDisplay.innerHTML = 'Rp ' + formatMoney(price);
        subtotalDisplay.innerHTML = 'Rp ' + formatMoney(subtotal);
        row.dataset.subtotal = subtotal;
    } else {
        priceDisplay.innerHTML = '-';
        subtotalDisplay.innerHTML = 'Rp 0';
        row.dataset.subtotal = 0;
    }
    
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let total = 0;
    const rows = document.getElementById('itemsBody').getElementsByTagName('tr');
    
    for(let i=0; i<rows.length; i++) {
        if(rows[i].dataset.subtotal) {
            total += parseFloat(rows[i].dataset.subtotal);
        }
    }
    
    document.getElementById('grandTotalDisplay').innerText = formatMoney(total);
}

// Add one row by default when page loads
document.addEventListener('DOMContentLoaded', function() {
    addItemRow();
});

// Validate form before submit
document.getElementById('orderForm').addEventListener('submit', function(e) {
    const rows = document.getElementById('itemsBody').getElementsByTagName('tr');
    if(rows.length === 0) {
        e.preventDefault();
        alert('Tambahkan minimal 1 item produk!');
        return;
    }
    
    let hasValidItem = false;
    for(let i=0; i<rows.length; i++) {
        const select = rows[i].querySelector('.product-select');
        if(select.value !== '') {
            hasValidItem = true;
            break;
        }
    }
    
    if(!hasValidItem) {
        e.preventDefault();
        alert('Pilih produk untuk dipesan!');
    }
});
</script>

<?php include '../../includes/admin_footer.php'; ?>
