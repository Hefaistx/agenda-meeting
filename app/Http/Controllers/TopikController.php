<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TopikController extends Controller
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
        $topics  = Topic::when($search, fn($q) => $q->where('nama', 'ILIKE', "%{$search}%"))
            ->orderBy('nama')
            ->get();
        $canEdit = $this->canModify();
        return view('master.topik.index', compact('topics', 'canEdit', 'search'));
    }

    public function create(): View|RedirectResponse
    {
        if (! $this->canModify()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        return view('master.topik.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->canModify()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:200|unique:topics,nama',
        ]);

        Topic::create($validated);

        return redirect()->route('topik.index')
            ->with('success', 'Topik berhasil ditambahkan.');
    }

    public function edit(Topic $topik): View|RedirectResponse
    {
        if (! $this->canModify()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        return view('master.topik.edit', compact('topik'));
    }

    public function update(Request $request, Topic $topik): RedirectResponse
    {
        if (! $this->canModify()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:200|unique:topics,nama,' . $topik->id,
        ]);

        $topik->update($validated);

        return redirect()->route('topik.index')
            ->with('success', 'Topik berhasil diperbarui.');
    }

    public function destroy(Topic $topik): RedirectResponse
    {
        if (! $this->canModify()) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $topik->delete();

        return redirect()->route('topik.index')
            ->with('success', 'Topik berhasil dihapus.');
    }
}
