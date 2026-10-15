<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identitas sekolah (data non-sensitif untuk demo)
    |--------------------------------------------------------------------------
    */
    'school_name' => env('SIMAS_SCHOOL_NAME', 'SMA Nusantara Cendekia'),

    /*
    |--------------------------------------------------------------------------
    | Admin utama
    |--------------------------------------------------------------------------
    |
    | Akun ini dilindungi: tidak dapat dihapus atau dinonaktifkan lewat UI.
    |
    */
    'primary_admin_email' => env('SIMAS_PRIMARY_ADMIN_EMAIL', 'admin@simas.test'),

    /*
    |--------------------------------------------------------------------------
    | Kebijakan absensi
    |--------------------------------------------------------------------------
    |
    | edit_window_days: guru hanya boleh mengoreksi sesi absensi yang tanggalnya
    | berada dalam N hari terakhir (termasuk hari ini).
    |
    */
    'attendance' => [
        'edit_window_days' => (int) env('SIMAS_ATTENDANCE_EDIT_WINDOW_DAYS', 7),
    ],

];
