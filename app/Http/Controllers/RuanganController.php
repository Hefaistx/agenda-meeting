<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RuanganController extends Controller
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
        $search  = $request->get('search', '');
        $rooms   = Room::withCount('meetings')
            ->when($search, fn($q) => $q->where('nama', 'ILIKE', "%{$search}%"))
            ->orderBy('nama')
            ->get();
        $canEdit = $this->canModify();
        return view('master.ruangan.index', compact('rooms', 'canEdit', 'search'));
    }

    public function create(): View|RedirectResponse
    {
        if (! $this->canModify()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        return view('master.ruangan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->canModify()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'nama'       => 'required|string|max:100',
            'kapasitas'  => 'nullable|integer|min:1',
            'lokasi'     => 'nullable|string|max:200',
            'keterangan' => 'nullable|string',
        ]);

        Room::create($validated);

        return redirect()->route('ruangan.index')
            ->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function edit(Room $ruangan): View|RedirectResponse
    {
        if (! $this->canModify()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        return view('master.ruangan.edit', compact('ruangan'));
    }

    public function update(Request $request, Room $ruangan): RedirectResponse
    {
        if (! $this->canModify()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'nama'       => 'required|string|max:100',
            'kapasitas'  => 'nullable|integer|min:1',
            'lokasi'     => 'nullable|string|max:200',
            'keterangan' => 'nullable|string',
        ]);

        $ruangan->update($validated);

        return redirect()->route('ruangan.index')
            ->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Room $ruangan): RedirectResponse
    {
        if (! $this->canModify()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $ruangan->delete();

        return redirect()->route('ruangan.index')
            ->with('success', 'Ruangan berhasil dihapus.');
    }
}
