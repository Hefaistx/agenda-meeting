<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Room;
use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeetingController extends Controller
{
    // ── Helpers ──────────────────────────────────────────────────────────────

    private function simUser(): array
    {
        return session('sim_user', Meeting::$accounts[0]);
    }

    private function checkPicConflict(Request $request, ?int $excludeId = null): ?string
    {
        $pics = array_filter(array_map('trim', explode(',', $request->pic_internal ?? '')));
        foreach ($pics as $pic) {
            $conflict = Meeting::whereDate('tanggal', $request->tanggal)
                ->whereRaw('jam_mulai < ?', [$request->jam_selesai])
                ->whereRaw('jam_selesai > ?', [$request->jam_mulai])
                ->whereNotIn('status', ['Cancelled'])
                ->whereRaw("pic_internal ILIKE ?", ['%' . $pic . '%'])
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->first();
            if ($conflict) {
                return "{$pic} sudah ada jadwal meeting ({$conflict->meeting_code}) pada waktu yang sama.";
            }
        }
        return null;
    }

    // ── Conflict Check API ────────────────────────────────────────────────────

    public function checkConflict(Request $request): JsonResponse
    {
        $tanggal    = $request->tanggal;
        $jamMulai   = $request->jam_mulai;
        $jamSelesai = $request->jam_selesai;
        $ruanganId  = $request->ruangan_id;
        $picStr     = $request->pic_internal ?? '';
        $excludeId  = $request->exclude_id;

        if (! $tanggal || ! $jamMulai || ! $jamSelesai) {
            return response()->json(['room_conflict' => null, 'pic_conflicts' => []]);
        }

        $base = Meeting::whereDate('tanggal', $tanggal)
            ->whereRaw('jam_mulai < ?', [$jamSelesai])
            ->whereRaw('jam_selesai > ?', [$jamMulai])
            ->whereNotIn('status', ['Cancelled'])
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId));

        // Room conflict
        $roomConflict = null;
        if ($ruanganId) {
            $rc = (clone $base)->where('ruangan_id', $ruanganId)->first();
            if ($rc) {
                $roomConflict = [
                    'meeting_code' => $rc->meeting_code,
                    'kegiatan'     => $rc->kegiatan,
                    'jam'          => Carbon::parse($rc->jam_mulai)->format('H:i') . '–' . Carbon::parse($rc->jam_selesai)->format('H:i'),
                ];
            }
        }

        // PIC conflicts
        $pics         = array_filter(array_map('trim', explode(',', $picStr)));
        $picConflicts = [];
        foreach ($pics as $pic) {
            $m = (clone $base)
                ->whereRaw("pic_internal ILIKE ?", ['%' . $pic . '%'])
                ->first();
            if ($m) {
                $picConflicts[] = [
                    'name'         => $pic,
                    'meeting_code' => $m->meeting_code,
                    'kegiatan'     => $m->kegiatan,
                    'jam'          => Carbon::parse($m->jam_mulai)->format('H:i') . '–' . Carbon::parse($m->jam_selesai)->format('H:i'),
                ];
            }
        }

        return response()->json([
            'room_conflict' => $roomConflict,
            'pic_conflicts' => $picConflicts,
        ]);
    }

    // ── CRUD ─────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = Meeting::query()->orderBy('tanggal')->orderBy('jam_mulai');

        if ($request->filled('tanggal'))      $query->whereDate('tanggal', $request->tanggal);
        if ($request->filled('kategori'))     $query->where('kategori', $request->kategori);
        if ($request->filled('jam_dari'))     $query->whereTime('jam_mulai', '>=', $request->jam_dari);
        if ($request->filled('jam_sampai'))   $query->whereTime('jam_mulai', '<=', $request->jam_sampai);
        if ($request->filled('pic_internal')) {
            $pics = array_filter(array_map('trim', explode(',', $request->pic_internal)));
            $query->where(function ($q) use ($pics) {
                foreach ($pics as $p) $q->orWhereRaw("pic_internal ILIKE ?", ['%' . $p . '%']);
            });
        }
        if ($request->filled('pic_external')) {
            $pics = array_filter(array_map('trim', explode(',', $request->pic_external)));
            $query->where(function ($q) use ($pics) {
                foreach ($pics as $p) $q->orWhereRaw("pic_external ILIKE ?", ['%' . $p . '%']);
            });
        }
        if ($request->filled('ruangan_id'))   $query->where('ruangan_id', $request->ruangan_id);
        if ($request->filled('topik_id'))     $query->where('topik_id', $request->topik_id);

        if ($request->filled('tab') && $request->tab !== 'semua') {
            $query->where('status', $request->tab);
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $meetings = $query->with(['ruangan', 'topic'])->paginate(20)->withQueryString();
        $simUser  = $this->simUser();
        $rooms    = Room::orderBy('nama')->get();

        $counts = Meeting::query()
            ->when($request->filled('tanggal'), fn($q) => $q->whereDate('tanggal', $request->tanggal))
            ->when($request->filled('kategori'), fn($q) => $q->where('kategori', $request->kategori))
            ->selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        $counts['semua'] = array_sum($counts);

        $topics = Topic::orderBy('nama')->get();
        return view('agenda.index', compact('meetings', 'simUser', 'counts', 'rooms', 'topics'));
    }

    public function create(): View
    {
        $rooms  = Room::orderBy('nama')->get();
        $topics = Topic::orderBy('nama')->get();
        return view('agenda.create', compact('rooms', 'topics'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal'      => 'required|date',
            'jam_mulai'    => 'required',
            'jam_selesai'  => 'required',
            'ruangan_id'   => 'required|exists:rooms,id',
            'topik_id'     => 'nullable|exists:topics,id',
            'kategori'     => 'required|string|max:100',
            'kegiatan'     => 'required|string',
            'pic_internal' => 'nullable|string',
            'pic_external' => 'nullable|string',
            'link_nm'      => 'nullable|string|max:500',
            'hasil'        => 'nullable|string',
        ]);

        if ($validated['jam_selesai'] <= $validated['jam_mulai']) {
            return back()->withInput()->withErrors(['jam_selesai' => 'Jam selesai harus setelah jam mulai.']);
        }

        if ($err = $this->checkPicConflict($request)) {
            return back()->withInput()->withErrors(['pic_internal' => $err]);
        }

        $initial   = $validated['kategori'] === 'Internal' ? 'INT' : 'EXT';
        $yearMonth = now('Asia/Jakarta')->format('Ym');
        do {
            $random = strtolower(substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 3));
            $code   = $initial . '-' . $yearMonth . '-' . $random;
        } while (Meeting::where('meeting_code', $code)->exists());

        $validated['meeting_code'] = $code;
        $validated['status']       = 'To Do';

        Meeting::create($validated);

        return redirect()->route('agenda.index')
            ->with('success', 'Agenda meeting berhasil ditambahkan.');
    }

    public function show(Meeting $agenda): View
    {
        $agenda->load('ruangan');
        return view('agenda.show', compact('agenda'));
    }

    public function edit(Meeting $agenda): View
    {
        $rooms  = Room::orderBy('nama')->get();
        $topics = Topic::orderBy('nama')->get();
        return view('agenda.edit', compact('agenda', 'rooms', 'topics'));
    }

    public function update(Request $request, Meeting $agenda): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal'      => 'required|date',
            'jam_mulai'    => 'required',
            'jam_selesai'  => 'required',
            'ruangan_id'   => 'required|exists:rooms,id',
            'topik_id'     => 'nullable|exists:topics,id',
            'kategori'     => 'required|string|max:100',
            'kegiatan'     => 'required|string',
            'status'       => 'nullable|string|max:50',
            'pic_internal' => 'nullable|string',
            'pic_external' => 'nullable|string',
            'link_nm'      => 'nullable|string|max:500',
            'hasil'        => 'nullable|string',
        ]);

        if ($validated['jam_selesai'] <= $validated['jam_mulai']) {
            return back()->withInput()->withErrors(['jam_selesai' => 'Jam selesai harus setelah jam mulai.']);
        }

        if ($err = $this->checkPicConflict($request, $agenda->id)) {
            return back()->withInput()->withErrors(['pic_internal' => $err]);
        }

        $agenda->update($validated);

        return redirect()->route('agenda.index')
            ->with('success', 'Agenda meeting berhasil diperbarui.');
    }

    public function destroy(Meeting $agenda): RedirectResponse
    {
        $agenda->delete();
        return redirect()->route('agenda.index')
            ->with('success', 'Agenda meeting berhasil dihapus.');
    }

    // ── Cancel ────────────────────────────────────────────────────────────────

    public function cancel(Meeting $agenda): RedirectResponse
    {
        $agenda->update(['status' => 'Cancelled']);
        return redirect()->route('agenda.index')
            ->with('success', 'Agenda meeting berhasil dibatalkan.');
    }

    // ── Reschedule ────────────────────────────────────────────────────────────

    public function reschedule(Request $request, Meeting $agenda): RedirectResponse
    {
        $request->validate([
            'tanggal_baru'     => 'required|date',
            'jam_mulai_baru'   => 'required',
            'jam_selesai_baru' => 'required',
            'alasan'           => 'nullable|string|max:255',
            'pic_internal'     => 'nullable|string',
            'pic_external'     => 'nullable|string',
            'ruangan_id'       => 'nullable|exists:rooms,id',
        ]);

        if ($request->jam_selesai_baru <= $request->jam_mulai_baru) {
            return back()->with('error', 'Jam selesai harus setelah jam mulai.');
        }

        $history   = $agenda->reschedule_history ?? [];
        $history[] = [
            'dari_tanggal'     => $agenda->tanggal->format('Y-m-d'),
            'dari_jam_mulai'   => Carbon::parse($agenda->jam_mulai)->format('H:i'),
            'dari_jam_selesai' => Carbon::parse($agenda->jam_selesai)->format('H:i'),
            'ke_tanggal'       => $request->tanggal_baru,
            'ke_jam_mulai'     => $request->jam_mulai_baru,
            'ke_jam_selesai'   => $request->jam_selesai_baru,
            'alasan'           => $request->alasan,
            'rescheduled_by'   => $this->simUser()['name'],
            'rescheduled_at'   => now('Asia/Jakarta')->format('Y-m-d H:i'),
        ];

        $agenda->update([
            'tanggal'            => $request->tanggal_baru,
            'jam_mulai'          => $request->jam_mulai_baru,
            'jam_selesai'        => $request->jam_selesai_baru,
            'status'             => 'Rescheduled',
            'reschedule_history' => $history,
            'pic_internal'       => $request->pic_internal,
            'pic_external'       => $request->pic_external,
            'ruangan_id'         => $request->ruangan_id ?: null,
        ]);

        return redirect()->route('agenda.index')
            ->with('success', 'Meeting berhasil dijadwalkan ulang.');
    }

    // ── Upload NM ─────────────────────────────────────────────────────────────

    public function uploadNm(Request $request, Meeting $agenda): RedirectResponse
    {
        $request->validate([
            'link_nm' => 'required|url|max:500',
        ]);

        $agenda->update([
            'link_nm' => $request->link_nm,
            'status'  => 'Done',
        ]);

        return redirect()->route('agenda.index')
            ->with('success', 'Link notula berhasil disimpan — status diubah ke Done.');
    }
}
