<?php

namespace App\Http\Controllers\staffperpus;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\transaksi_peminjaman;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Buku;
use Illuminate\Support\Str;

use Carbon\Carbon;

class TransaksiPeminjamanController extends Controller
{

    public function index(Request $request)
    {   
        // $transaksi = transaksi_peminjaman::with('buku')->orderBy('tgl_awal_peminjaman', 'desc')->get(); // Memuat relasi buku
        // return view('staff_perpus.transaksi.daftartransaksi', compact('transaksi'));

        $query = transaksi_peminjaman::with('buku')->orderBy('tgl_awal_peminjaman', 'desc');

    if ($request->has('search') && !empty($request->search)) {
        $query->where('kode_peminjam', 'LIKE', '%' . $request->search . '%');
    }

    $transaksi = $query->get();

    return view('staff_perpus.transaksi.daftartransaksi', compact('transaksi'));
    }
    // Menampilkan form transaksi peminjaman
    public function create()
{
    $siswa = Siswa::all();
    $guru = Guru::all();
    // $buku = Buku::all();
    $buku = Buku::orderBy('tgl_ditambahkan', 'desc')->get();
    return view('staff_perpus.transaksi.create', compact('siswa', 'guru', 'buku'));
}

// Menyimpan transaksi peminjaman
public function store(Request $request)
{
    $request->validate([
        'jenis_peminjam' => 'required|in:siswa,guru',
        'id_buku' => 'required|uuid',
        'nisn_nip' => 'required',
        'jumlah' => 'required|integer|min:1',
    ], [
        'jenis_peminjam.required' => 'Jenis peminjam harus dipilih.',
        'jenis_peminjam.in' => 'Jenis peminjam harus salah satu dari: siswa atau guru.',
        
        'id_buku.required' => 'Buku harus dipilih.',
        'id_buku.uuid' => 'ID buku harus berupa UUID yang valid.',
        
        'nisn_nip.required' => 'NISN atau NIP harus diisi.',
        
        'jumlah.required' => 'Jumlah buku yang dipinjam harus diisi.',
        'jumlah.integer' => 'Jumlah buku yang dipinjam harus berupa angka.',
        'jumlah.min' => 'Jumlah buku yang dipinjam harus minimal 1.',
    ]);

    // Mendapatkan data buku yang akan dipinjam
    $buku = Buku::findOrFail($request->id_buku);

    // Mengecek apakah stok buku mencukupi
    if ($buku->stok_buku < $request->jumlah) {
        return redirect()->back()->withErrors(['jumlah' => 'Stok buku tidak mencukupi.']);
    }

    // Memeriksa apakah peminjam adalah guru atau siswa
    if ($request->jenis_peminjam == 'siswa') {
        $siswa = Siswa::where('nisn', $request->nisn_nip)->first();
        if (!$siswa) {
            return redirect()->back()->withErrors(['nisn_nip' => 'NISN siswa tidak ditemukan.']);
        }

        // Pengecekan status denda, jika siswa memiliki 3 transaksi dengan status_denda = 1
        $jumlahDenda = transaksi_peminjaman::where('kode_peminjam', $siswa->nisn)
            ->where('status_denda', 1)
            ->count();

        if ($jumlahDenda >= 3) {
            return redirect()->back()->withErrors(['message' => 'Siswa memiliki 3 denda yang belum diselesaikan dan tidak dapat meminjam buku.']);
        }

        // Siswa hanya dapat meminjam buku jenis 'non-paket'
        if ($buku->id_jenis_buku != 1) {
            return redirect()->back()->withErrors(['id_buku' => 'Siswa hanya boleh meminjam buku jenis non-paket.']);
        }

        // Pengecekan apakah siswa telah meminjam 3 buku non-paket yang belum dikembalikan
        $jumlahPinjaman = transaksi_peminjaman::where('kode_peminjam', $siswa->nisn)
            ->where('status_pengembalian', 0)
            ->whereHas('buku', function ($query) {
                $query->where('id_jenis_buku', 1); // Pastikan ID jenis buku non-paket sesuai
            })
            ->count();

        if ($jumlahPinjaman >= 3) {
            return redirect()->back()->withErrors(['message' => 'Siswa telah mencapai batas peminjaman 3 buku non-paket yang belum dikembalikan.']);
        }

        // Siswa hanya bisa meminjam 1 stok buku jenis non-paket per buku
        if ($request->jumlah > 1) {
            return redirect()->back()->withErrors(['jumlah' => 'Siswa hanya boleh meminjam 1 stok per buku jenis non-paket.']);
        }

        // Durasi pinjam default 2 minggu untuk siswa
        $tgl_pengembalian = now()->addWeeks(2);
        $kode_peminjam = $siswa->nisn; // Menyimpan NISN sebagai kode_peminjam

    } else {
        $guru = Guru::where('nip', $request->nisn_nip)->first();
        if (!$guru) {
            return redirect()->back()->withErrors(['nisn_nip' => 'NIP guru tidak ditemukan.']);
        }

        // Pengecekan status denda, jika guru memiliki 3 transaksi dengan status_denda = 1
        $jumlahDenda = transaksi_peminjaman::where('kode_peminjam', $guru->nip)
            ->where('status_denda', 1)
            ->count();

        if ($jumlahDenda >= 3) {
            return redirect()->back()->withErrors(['message' => 'Guru memiliki 3 denda yang belum diselesaikan dan tidak dapat meminjam buku.']);
        }

        // Pengecekan apakah guru telah meminjam 3 buku non-paket yang belum dikembalikan
        $jumlahPinjaman = transaksi_peminjaman::where('kode_peminjam', $guru->nip)
            ->where('status_pengembalian', 0)
            ->whereHas('buku', function ($query) {
                $query->where('id_jenis_buku', 1); // Pastikan ID jenis buku non-paket sesuai
            })
            ->count();

        if ($jumlahPinjaman >= 3 && $buku->id_jenis_buku == 1) {
            return redirect()->back()->withErrors(['message' => 'Guru telah mencapai batas peminjaman 3 buku non-paket yang belum dikembalikan.']);
        }

        // Guru bebas menentukan jumlah stok untuk buku jenis 'buku paket'
        if ($buku->id_jenis_buku == 2) {
            $jumlahPinjamanBukuPaket = $request->jumlah; // Guru bebas menentukan jumlah stok buku paket
        } else {
            // Batas 1 stok untuk buku non-paket
            if ($request->jumlah > 1) {
                return redirect()->back()->withErrors(['jumlah' => 'Guru hanya boleh meminjam 1 stok per buku jenis non-paket.']);
            }
        }

        // Durasi pinjam default 1 tahun untuk guru
        $tgl_pengembalian = now()->addYear();
        $kode_peminjam = $guru->nip; // Menyimpan NIP sebagai kode_peminjam
    }

    // Buat transaksi peminjaman
    transaksi_peminjaman::create([
        'id_transaksi_peminjaman' => (string) Str::uuid(),
        'id_buku' => $buku->id_buku,
        'kode_peminjam' => $kode_peminjam, // Menggunakan NISN atau NIP sebagai kode_peminjam
        'tgl_awal_peminjaman' => now(),
        'tgl_pengembalian' => $tgl_pengembalian,
        'denda' => 0,  // Anggap 0 saat peminjaman awal
        'status_pengembalian' => 0,  // 0 untuk belum dikembalikan
        'jenis_peminjam' => $request->jenis_peminjam == 'siswa' ? 1 : 2,
        'status_denda' => 0,
        'stok' => $request->jumlah,  // Jumlah stok yang dipinjam
    ]);

    // Mengurangi stok buku setelah transaksi berhasil
    $buku->decrement('stok_buku', $request->jumlah);

    return redirect()->route('staff_perpus.transaksi.daftartransaksi')->with('success', 'Transaksi peminjaman berhasil ditambahkan');
}


        // Metode untuk menampilkan form edit transaksi
        public function edit($id)
        {
            $transaksi = transaksi_peminjaman::findOrFail($id);
            $transaksi->tgl_awal_peminjaman = Carbon::parse($transaksi->tgl_awal_peminjaman);
            $transaksi->tgl_pengembalian = Carbon::parse($transaksi->tgl_pengembalian);
        
            return view('staff_perpus.transaksi.edit', compact('transaksi'));
        }

        public function update(Request $request, $id)
        {
            $request->validate([
                'tgl_pengembalian' => 'required|date',
            ]);

            $transaksi = transaksi_peminjaman::findOrFail($id);
            $transaksi->update([
                'tgl_pengembalian' => $request->tgl_pengembalian,
            ]);

            return redirect()->route('staff_perpus.transaksi.daftartransaksi')->with('success', 'Tenggat pengembalian berhasil diperbarui');
        }


        public function destroy($id)
        {
            $transaksi = transaksi_peminjaman::findOrFail($id);
            $transaksi->delete();

            return redirect()->route('staff_perpus.transaksi.daftartransaksi')->with('success', 'Transaksi berhasil dihapus.');
        }

    
}