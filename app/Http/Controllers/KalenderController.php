<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Room;
use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KalenderController extends Controller
{
    public function index(Request $request): View
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        // Default divisi dari sim_user session
        $defaultDivisi = 'IT';
        $simUser = session('sim_user');
        if ($simUser) {
            foreach (Meeting::$externalDivisions as $code => $div) {
                if (isset($div['members'][$simUser['name']])) {
                    $defaultDivisi = $code;
                    break;
                }
            }
        }
        $divisi = $request->get('divisi', $defaultDivisi);

        $year  = max(2015, min(2035, $year));
        $month = max(1, min(12, $month));

        $validDivisions = array_merge(['IT'], array_keys(Meeting::$externalDivisions));
        if (!in_array($divisi, $validDivisions)) $divisi = 'IT';

        $query = Meeting::whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->with(['ruangan', 'topic'])
            ->orderBy('tanggal')
            ->orderBy('jam_mulai');

        if ($divisi !== 'IT') {
            $members = array_keys(Meeting::$externalDivisions[$divisi]['members'] ?? []);
            $query->where(function ($q) use ($members) {
                foreach ($members as $m) {
                    $q->orWhereRaw("pic_external ILIKE ?", ["%{$m}%"]);
                }
            });
        }

        // Semua meeting bulan ini (untuk grid kalender — tidak difilter form)
        $allMeetings = $query->get();
        $meetings    = $allMeetings->groupBy(fn ($m) => $m->tanggal->format('Y-m-d'));

        // Filter tambahan untuk tabel di bawah kalender
        $tableData = $allMeetings;

        if ($tanggal = $request->get('tanggal')) {
            $tableData = $tableData->filter(fn ($m) => $m->tanggal->format('Y-m-d') === $tanggal);
        }
        if ($kategori = $request->get('kategori')) {
            $tableData = $tableData->filter(fn ($m) => strtolower($m->kategori) === strtolower($kategori));
        }
        if ($status = $request->get('status')) {
            $tableData = $tableData->filter(fn ($m) => strtolower($m->status ?? 'to do') === strtolower($status));
        }
        if ($picInt = $request->get('pic_internal')) {
            $pics = array_filter(array_map('trim', explode(',', $picInt)));
            if ($pics) {
                $tableData = $tableData->filter(function ($m) use ($pics) {
                    $stored = strtolower($m->pic_internal ?? '');
                    foreach ($pics as $p) {
                        if (str_contains($stored, strtolower($p))) return true;
                    }
                    return false;
                });
            }
        }
        $picExtNames = [];
        if ($picExt = $request->get('pic_external')) {
            $picExtNames = array_filter(array_map('trim', explode(',', $picExt)));
        }
        if (empty($picExtNames) && ($divisiExt = $request->get('divisi_ext'))) {
            $divCodes = array_filter(array_map('trim', explode(',', $divisiExt)));
            foreach ($divCodes as $code) {
                $members = array_keys(Meeting::$externalDivisions[$code]['members'] ?? []);
                $picExtNames = array_merge($picExtNames, $members);
            }
        }
        if ($picExtNames) {
            $tableData = $tableData->filter(function ($m) use ($picExtNames) {
                $stored = strtolower($m->pic_external ?? '');
                foreach ($picExtNames as $p) {
                    if (str_contains($stored, strtolower($p))) return true;
                }
                return false;
            });
        }
        if ($ruanganId = $request->get('ruangan_id')) {
            $tableData = $tableData->filter(fn ($m) => $m->ruangan_id == $ruanganId);
        }
        if ($topikId = $request->get('topik_id')) {
            $tableData = $tableData->filter(fn ($m) => $m->topik_id == $topikId);
        }
        $tableData = $tableData->values();

        $currentDate = Carbon::createFromDate($year, $month, 1);
        $prevMonth   = $currentDate->copy()->subMonth();
        $nextMonth   = $currentDate->copy()->addMonth();

        $rooms  = Room::orderBy('nama')->get();
        $topics = Topic::orderBy('nama')->get();

        return view('kalender.index', compact(
            'currentDate', 'meetings', 'allMeetings', 'tableData', 'rooms', 'topics',
            'year', 'month', 'prevMonth', 'nextMonth',
            'divisi', 'validDivisions'
        ));
    }
}
