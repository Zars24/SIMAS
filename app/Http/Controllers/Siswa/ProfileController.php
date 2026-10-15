<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $student = $request->user()->student;

        if ($student) {
            $student->load(['user', 'schoolClass.homeroomTeacher.user']);
        }

        return view('siswa.profile', [
            'student' => $student,
            'user' => $request->user(),
        ]);
    }
}
