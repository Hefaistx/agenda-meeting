<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'meeting_code', 'ruangan_id', 'topik_id', 'tanggal', 'jam_mulai', 'jam_selesai', 'kategori', 'kegiatan',
        'status', 'pic_internal', 'pic_external',
        'link_nm', 'nm_file', 'hasil', 'reschedule_history',
    ];

    protected $casts = [
        'tanggal'            => 'date',
        'reschedule_history' => 'array',
    ];

    public static array $statusOptions = [
        'To Do',
        'Done',
        'Cancelled',
        'Rescheduled',
    ];

    public static array $kategoriOptions = [
        'Internal',
        'External',
    ];

    public static array $kategoriColors = [
        'Internal' => '#29b4d0',
        'External' => '#4361ee',
    ];

    public static array $picOptions = [
        'Aldy Raditya',
        'Dhimas Rafi\' Hardoyo',
        'Reyvaldo Wildan Anggara',
        'Asraf Rafli Daifuloh',
        'Puput Wulandari',
        'Nabil Ilyasa',
        'Andre Maulana Mustofa',
        'Krisna Harsya Saputra',
        'Alma Rizky',
        'Naufal Anshor A',
        'Delima Danarini',
        'Heru Widyastono',
        'Jimmy Raymond Wijaya',
        'Fazal Said Wicaksono',
        'Chandra Karim',
        'Roby Purnomo',
        'Gampang Rozaki',
        'Damai Yanti Anggraeni',
        'Ahmad Zakky Makarim',
        'Salsabila Lattifa Fikri',
        'Firman Aji Prasetyo',
        'Muttafakur',
        'Muhammad Riyan Andriyanto',
        'Risanti Filanika Sari',
        'Muhammad Nur Afif Na\'im',
    ];

    // Divisi eksternal: code => { label, members: { name => role } }
    public static array $externalDivisions = [
        'FO' => [
            'label'   => 'Front Office',
            'members' => [
                'Agus Dermawan'    => 'Manager FO',
                'Wahyu Hidayat'    => 'Head FO',
            ],
        ],
        'HRD' => [
            'label'   => 'Human Resources',
            'members' => [
                'Retno Wulandari'  => 'Manager HRD',
                'Fajar Nugroho'    => 'Head HRD',
            ],
        ],
        'BDSM' => [
            'label'   => 'Business Dev & Sales Marketing',
            'members' => [
                'Bagas Saputra'    => 'Manager BDSM',
                'Cindy Margaretha' => 'Head BDSM',
            ],
        ],
    ];

    // name => role — untuk WA header "Agenda [role] (Nama)"
    public static array $picRoles = [
        'Aldy Raditya'              => 'Manager IT',
        'Dhimas Rafi\' Hardoyo'     => 'Head IT',
        'Reyvaldo Wildan Anggara'   => 'Head IT',
        'Asraf Rafli Daifuloh'      => 'Staff IT Sistem Analis',
        'Puput Wulandari'           => 'Staff IT Sistem Analis',
        'Nabil Ilyasa'              => 'Staff IT Sistem Analis',
        'Andre Maulana Mustofa'     => 'Staff IT Web Dev',
        'Krisna Harsya Saputra'     => 'Staff IT QA',
        'Alma Rizky'                => 'Staff IT QA',
        'Naufal Anshor A'           => 'Staff IT Web Dev',
        'Delima Danarini'           => 'Staff IT QA',
        'Heru Widyastono'           => 'Staff IT Mobile Dev',
        'Jimmy Raymond Wijaya'      => 'Staff IT Web Dev',
        'Fazal Said Wicaksono'      => 'Staff IT Web Dev',
        'Chandra Karim'             => 'Staff IT QA',
        'Roby Purnomo'              => 'Staff IT Mobile Dev',
        'Gampang Rozaki'            => 'Staff IT Web Dev',
        'Damai Yanti Anggraeni'     => 'Staff IT Sistem Analis',
        'Ahmad Zakky Makarim'       => 'Staff IT Sistem Analis',
        'Salsabila Lattifa Fikri'   => 'Staff IT Web Dev',
        'Firman Aji Prasetyo'       => 'Staff IT QA',
        'Muttafakur'                => 'Staff IT QA',
        'Muhammad Riyan Andriyanto' => 'Staff IT Web Dev',
        'Risanti Filanika Sari'     => 'Staff IT Web Dev',
        'Muhammad Nur Afif Na\'im'  => 'Staff IT Web Dev',
    ];

    // Labels for PIC dropdowns: name => "Name – Jabatan"
    // Includes backward-compat entries for old short names
    public static array $picLabels = [
        // ── old short names (backward compat) ──
        'Dhimas'   => "Dhimas Rafi' Hardoyo – Head IT",
        'Reyvaldo' => 'Reyvaldo Wildan Anggara – Head IT',
        'Asraf'    => 'Asraf Rafli Daifuloh – Staff IT Sistem Analis',
        'Puput'    => 'Puput Wulandari – Staff IT Sistem Analis',
        'Nabil'    => 'Nabil Ilyasa – Staff IT Sistem Analis',
        'Andre'    => 'Andre Maulana Mustofa – Staff IT Web Dev',
        'Krisna'   => 'Krisna Harsya Saputra – Staff IT QA',
        'Krishna'  => 'Krisna Harsya Saputra – Staff IT QA',
        'Alma'     => 'Alma Rizky – Staff IT QA',
        'Naufal'   => 'Naufal Anshor A – Staff IT Web Dev',
        'Delima'   => 'Delima Danarini – Staff IT QA',
        'Heru'     => 'Heru Widyastono – Staff IT Mobile Dev',
        'Jimmy'    => 'Jimmy Raymond Wijaya – Staff IT Web Dev',
        'Fazal'    => 'Fazal Said Wicaksono – Staff IT Web Dev',
        'Chandra'  => 'Chandra Karim – Staff IT QA',
        'Roby'     => 'Roby Purnomo – Staff IT Mobile Dev',
        'Gampang'  => 'Gampang Rozaki – Staff IT Web Dev',
        'Damai'    => 'Damai Yanti Anggraeni – Staff IT Sistem Analis',
        'Zakky'    => 'Ahmad Zakky Makarim – Staff IT Sistem Analis',
        'Zaky'     => 'Ahmad Zakky Makarim – Staff IT Sistem Analis',
        'Salsabila'=> 'Salsabila Lattifa Fikri – Staff IT Web Dev',
        'Bila'     => 'Salsabila Lattifa Fikri – Staff IT Web Dev',
        'Firman'   => 'Firman Aji Prasetyo – Staff IT QA',
        'Riyan'    => 'Muhammad Riyan Andriyanto – Staff IT Web Dev',
        'Risanti'  => 'Risanti Filanika Sari – Staff IT Web Dev',
        'Afif'     => "Muhammad Nur Afif Na'im – Staff IT Web Dev",
        'Santi'    => 'Staff IT',
        // ── full names ──
        'Aldy Raditya'              => 'Aldy Raditya – Manager IT',
        'Dhimas Rafi\' Hardoyo'     => "Dhimas Rafi' Hardoyo – Head IT",
        'Reyvaldo Wildan Anggara'   => 'Reyvaldo Wildan Anggara – Head IT',
        'Asraf Rafli Daifuloh'      => 'Asraf Rafli Daifuloh – Staff IT Sistem Analis',
        'Puput Wulandari'           => 'Puput Wulandari – Staff IT Sistem Analis',
        'Nabil Ilyasa'              => 'Nabil Ilyasa – Staff IT Sistem Analis',
        'Andre Maulana Mustofa'     => 'Andre Maulana Mustofa – Staff IT Web Dev',
        'Krisna Harsya Saputra'     => 'Krisna Harsya Saputra – Staff IT QA',
        'Alma Rizky'                => 'Alma Rizky – Staff IT QA',
        'Naufal Anshor A'           => 'Naufal Anshor A – Staff IT Web Dev',
        'Delima Danarini'           => 'Delima Danarini – Staff IT QA',
        'Heru Widyastono'           => 'Heru Widyastono – Staff IT Mobile Dev',
        'Jimmy Raymond Wijaya'      => 'Jimmy Raymond Wijaya – Staff IT Web Dev',
        'Fazal Said Wicaksono'      => 'Fazal Said Wicaksono – Staff IT Web Dev',
        'Chandra Karim'             => 'Chandra Karim – Staff IT QA',
        'Roby Purnomo'              => 'Roby Purnomo – Staff IT Mobile Dev',
        'Gampang Rozaki'            => 'Gampang Rozaki – Staff IT Web Dev',
        'Damai Yanti Anggraeni'     => 'Damai Yanti Anggraeni – Staff IT Sistem Analis',
        'Ahmad Zakky Makarim'       => 'Ahmad Zakky Makarim – Staff IT Sistem Analis',
        'Salsabila Lattifa Fikri'   => 'Salsabila Lattifa Fikri – Staff IT Web Dev',
        'Firman Aji Prasetyo'       => 'Firman Aji Prasetyo – Staff IT QA',
        'Muttafakur'                => 'Muttafakur – Staff IT QA',
        'Muhammad Riyan Andriyanto' => 'Muhammad Riyan Andriyanto – Staff IT Web Dev',
        'Risanti Filanika Sari'     => 'Risanti Filanika Sari – Staff IT Web Dev',
        'Muhammad Nur Afif Na\'im'  => "Muhammad Nur Afif Na'im – Staff IT Web Dev",
    ];

    public static array $accounts = [
        ['name' => 'Aldy Raditya',           'role' => 'Manager IT',         'abbrev' => 'AR', 'color' => '#29b4d0'],
        ['name' => 'Dhimas Rafi\' Hardoyo',  'role' => 'Head IT',            'abbrev' => 'DH', 'color' => '#4361ee'],
        ['name' => 'Reyvaldo Wildan Anggara','role' => 'Head IT',            'abbrev' => 'RW', 'color' => '#7209b7'],
        ['name' => 'Nabil Ilyasa',           'role' => 'Staff IT Sistem Analis', 'abbrev' => 'NI', 'color' => '#2ec4b6'],
        ['name' => 'Alma Rizky',             'role' => 'Staff IT QA',        'abbrev' => 'AL', 'color' => '#e63946'],
    ];

    public static array $managerRoles = [
        'Manager IT', 'Head IT',
    ];

    // Lookup label "Nama – Jabatan" untuk PIC eksternal
    public static function externalPicLabel(string $name): string
    {
        $name = trim($name);
        foreach (self::$externalDivisions as $code => $div) {
            if (isset($div['members'][$name])) {
                return $name . ' – ' . $div['members'][$name];
            }
        }
        return $name;
    }

    // Ambil daftar kode divisi unik dari string pic_external
    public static function externalPicDivisions(string $picStr): array
    {
        $names     = array_filter(array_map('trim', explode(',', $picStr)));
        $divisions = [];
        foreach ($names as $name) {
            foreach (self::$externalDivisions as $code => $div) {
                if (isset($div['members'][$name]) && !in_array($code, $divisions)) {
                    $divisions[] = $code;
                }
            }
        }
        return $divisions;
    }

    public static function picLabel(string $name): string
    {
        $name = trim($name);
        return self::$picLabels[$name] ?? $name;
    }

    public function ruangan()
    {
        return $this->belongsTo(\App\Models\Room::class, 'ruangan_id');
    }

    public function topic()
    {
        return $this->belongsTo(\App\Models\Topic::class, 'topik_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'Done'        => 'success',
            'To Do'       => 'primary',
            'Cancelled'   => 'danger',
            'Rescheduled' => 'warning',
            default       => 'secondary',
        };
    }

    public function getKategoriColorAttribute(): string
    {
        return self::$kategoriColors[$this->kategori] ?? '#6c757d';
    }
}
