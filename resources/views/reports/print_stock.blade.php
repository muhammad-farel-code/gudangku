<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Stok - GudangKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11pt; color: #212529; background: #fff; }
        .table-print th, .table-print td { padding: 6px 8px; border: 1px solid #dee2e6; }
        .table-print thead th { background-color: #f8f9fa; font-weight: 600; text-align: center; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body class="p-4">
    <div class="no-print mb-4 d-flex justify-content-between align-items-center border-bottom pb-3">
        <div>
            <h5 class="fw-bold mb-0">Pratinjau Cetak Laporan Stok</h5>
            <span class="text-muted small">Gunakan ukuran kertas A4 / Letter.</span>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.close()" class="btn btn-secondary btn-sm px-3">Tutup</button>
            <button onclick="window.print()" class="btn btn-dark btn-sm px-3">Cetak Sekarang</button>
        </div>
    </div>

    {{-- KOP DOKUMEN --}}
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <div>
            <h3 class="fw-bold mb-0">GudangKu</h3>
            <div class="text-secondary small">Sistem Manajemen Inventaris Gudang & Logistik</div>
        </div>
        <div class="text-end small text-secondary">
            <div><strong>LAPORAN POSISI STOK BARANG</strong></div>
            <div>Dicetak: {{ now()->format('d F Y, H:i') }} WIB</div>
            <div>Oleh: {{ Auth::user()->name }} ({{ strtoupper(Auth::user()->role) }})</div>
        </div>
    </div>

    {{-- RINGKASAN --}}
    <div class="row g-2 mb-3 small">
        <div class="col-4">
            <strong>Total Varian:</strong> {{ number_format($products->count()) }} produk
        </div>
        <div class="col-4">
            <strong>Total Kuantitas:</strong> {{ number_format($totalUnits) }} unit
        </div>
        <div class="col-4 text-end">
            <strong>Estimasi Total Nilai Aset:</strong> Rp {{ number_format($totalValue, 0, ',', '.') }}
        </div>
    </div>

    {{-- TABEL LAPORAN --}}
    <table class="table table-print table-sm w-100 mb-4" style="font-size: 9.5pt;">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Kode SKU</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Pemasok</th>
                <th style="width: 70px;">Stok</th>
                <th style="width: 50px;">Satuan</th>
                <th style="width: 50px;">Min</th>
                <th style="width: 90px;">Harga Beli</th>
                <th style="width: 100px;">Total Nilai</th>
                <th style="width: 60px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $idx => $p)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-monospace">{{ $p->sku }}</td>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->category->name ?? '-' }}</td>
                    <td>{{ $p->supplier->name ?? '-' }}</td>
                    <td class="text-end fw-bold">{{ number_format($p->stock) }}</td>
                    <td class="text-center">{{ $p->unit }}</td>
                    <td class="text-center text-muted">{{ $p->min_stock }}</td>
                    <td class="text-end">Rp {{ number_format($p->purchase_price, 0, ',', '.') }}</td>
                    <td class="text-end fw-semibold">Rp {{ number_format($p->stock * $p->purchase_price, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $p->stock_status }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold" style="background: #f8f9fa;">
                <td colspan="5" class="text-center">TOTAL KESELURUHAN</td>
                <td class="text-end">{{ number_format($totalUnits) }}</td>
                <td colspan="3"></td>
                <td class="text-end">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- TANDA TANGAN --}}
    <div class="row pt-4 mt-4" style="page-break-inside: avoid;">
        <div class="col-8"></div>
        <div class="col-4 text-center small">
            <div>Dicetak di Jakarta, {{ now()->format('d F Y') }}</div>
            <div style="height: 60px;"></div>
            <div class="fw-bold text-decoration-underline">{{ Auth::user()->name }}</div>
            <div class="text-secondary">{{ strtoupper(Auth::user()->role) }} GUDANG</div>
        </div>
    </div>
</body>
</html>
