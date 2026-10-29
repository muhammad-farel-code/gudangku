<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Transaksi - GudangKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 10pt; color: #212529; background: #fff; }
        .table-print th, .table-print td { padding: 5px 6px; border: 1px solid #dee2e6; }
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
            <h5 class="fw-bold mb-0">Pratinjau Cetak Laporan Mutasi Transaksi</h5>
            <span class="text-muted small">Ukuran kertas A4 Landscape disarankan.</span>
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
            <div><strong>LAPORAN MUTASI TRANSAKSI BARANG</strong></div>
            <div>Dicetak: {{ now()->format('d F Y, H:i') }} WIB</div>
            <div>Oleh: {{ Auth::user()->name }} ({{ strtoupper(Auth::user()->role) }})</div>
        </div>
    </div>

    {{-- RINGKASAN --}}
    <div class="row g-2 mb-3 small">
        <div class="col-4">
            <strong>Total Mutasi:</strong> {{ number_format($transactions->count()) }} transaksi
        </div>
        <div class="col-4">
            <strong>Total Masuk:</strong> +{{ number_format($totalIn) }} unit
        </div>
        <div class="col-4 text-end">
            <strong>Total Keluar:</strong> -{{ number_format($totalOut) }} unit
        </div>
    </div>

    {{-- TABEL LAPORAN --}}
    <table class="table table-print table-sm w-100 mb-4" style="font-size: 9pt;">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th>No. Referensi</th>
                <th>Tanggal</th>
                <th>Tipe</th>
                <th>Kode SKU</th>
                <th>Nama Produk</th>
                <th style="width: 50px;">Jumlah</th>
                <th>Sebelum &rarr; Sesudah</th>
                <th>Pemasok / Penerima</th>
                <th>Petugas</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $idx => $t)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-monospace fw-semibold">{{ $t->reference_no }}</td>
                    <td class="text-center">{{ $t->transaction_date->format('d/m/Y') }}</td>
                    <td class="text-center fw-bold">{{ strtoupper($t->type) }}</td>
                    <td class="font-monospace">{{ $t->product->sku ?? '-' }}</td>
                    <td>{{ $t->product->name ?? '-' }}</td>
                    <td class="text-end fw-bold">
                        {{ $t->type === 'in' ? '+' : ($t->type === 'out' ? '-' : '') }}{{ number_format($t->quantity) }}
                    </td>
                    <td class="text-center">{{ $t->stock_before }} &rarr; {{ $t->stock_after }}</td>
                    <td>
                        {{ $t->type === 'in' ? ($t->supplier->name ?? 'Tanpa Pemasok') : ($t->type === 'out' ? ($t->recipient ?: '-') : 'Stock Opname') }}
                    </td>
                    <td>{{ $t->user->name ?? '-' }}</td>
                    <td>{{ $t->notes ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <div class="row pt-4 mt-4" style="page-break-inside: avoid;">
        <div class="col-8"></div>
        <div class="col-4 text-center small">
            <div>Jakarta, {{ now()->format('d F Y') }}</div>
            <div style="height: 60px;"></div>
            <div class="fw-bold text-decoration-underline">{{ Auth::user()->name }}</div>
            <div class="text-secondary">{{ strtoupper(Auth::user()->role) }} GUDANG</div>
        </div>
    </div>
</body>
</html>
