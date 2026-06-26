<?php

namespace App\Http\Controllers;

use App\Models\KonfigurasiWaktu;
use App\Models\Meeting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KonfigurasiWaktuController extends Controller
{
    private function simUser(): array
    {
        return session('sim_user', Meeting::$accounts[0]);
    }

    private function canModify(): bool
    {
        return in_array($this->simUser()['role'], Meeting::$managerRoles);
    }

    public function index(Request $request): View
    {
        $search = $request->get('search', '');
        $data   = KonfigurasiWaktu::when($search, fn($q) => $q->where('kategori', 'ILIKE', "%{$search}%"))
            ->orderBy('kategori')
            ->get();
        $canEdit = $this->canModify();
        return view('master.konfigurasi-waktu.index', compact('data', 'canEdit', 'search'));
    }

    public function create(): View|RedirectResponse
    {
        if (! $this->canModify()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $used      = KonfigurasiWaktu::pluck('kategori')->toArray();
        $available = array_values(array_diff(Meeting::$kategoriOptions, $used));

        return view('master.konfigurasi-waktu.create', compact('available'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->canModify()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'kategori'         => 'required|string|max:100|unique:konfigurasi_waktu,kategori',
            'waktu_mulai_min'  => 'nullable|date_format:H:i',
            'waktu_selesai_max' => 'nullable|date_format:H:i|after_or_equal:waktu_mulai_min',
        ], [
            'kategori.unique'                  => 'Kategori ini sudah memiliki konfigurasi waktu.',
            'waktu_selesai_max.after_or_equal' => 'Waktu selesai maksimal harus setelah waktu mulai minimal.',
        ]);

        $validated['waktu_mulai_min']   = $validated['waktu_mulai_min']   ?: null;
        $validated['waktu_selesai_max'] = $validated['waktu_selesai_max'] ?: null;

        KonfigurasiWaktu::create($validated);

        return redirect()->route('konfigurasi-waktu.index')
            ->with('success', "Konfigurasi waktu untuk kategori {$validated['kategori']} berhasil ditambahkan.");
    }

    public function edit(KonfigurasiWaktu $konfigurasiWaktu): View|RedirectResponse
    {
        if (! $this->canModify()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        return view('master.konfigurasi-waktu.edit', ['konfigurasi' => $konfigurasiWaktu]);
    }

    public function update(Request $request, KonfigurasiWaktu $konfigurasiWaktu): RedirectResponse
    {
        if (! $this->canModify()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'kategori'         => 'required|string|max:100|unique:konfigurasi_waktu,kategori,' . $konfigurasiWaktu->id,
            'waktu_mulai_min'  => 'nullable|date_format:H:i',
            'waktu_selesai_max' => 'nullable|date_format:H:i|after_or_equal:waktu_mulai_min',
        ], [
            'kategori.unique'                  => 'Kategori ini sudah memiliki konfigurasi waktu.',
            'waktu_selesai_max.after_or_equal' => 'Waktu selesai maksimal harus setelah waktu mulai minimal.',
        ]);

        $validated['waktu_mulai_min']   = $validated['waktu_mulai_min']   ?: null;
        $validated['waktu_selesai_max'] = $validated['waktu_selesai_max'] ?: null;

        $konfigurasiWaktu->update($validated);

        return redirect()->route('konfigurasi-waktu.index')
            ->with('success', "Konfigurasi waktu {$konfigurasiWaktu->kategori} berhasil diperbarui.");
    }

    public function destroy(KonfigurasiWaktu $konfigurasiWaktu): RedirectResponse
    {
        if (! $this->canModify()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $konfigurasiWaktu->delete();

        return redirect()->route('konfigurasi-waktu.index')
            ->with('success', "Konfigurasi waktu {$konfigurasiWaktu->kategori} berhasil dihapus.");
    }
}
