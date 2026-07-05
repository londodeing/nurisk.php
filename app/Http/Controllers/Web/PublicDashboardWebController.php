<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BencanaMasterJenis;
use Illuminate\Http\Request;

class PublicDashboardWebController extends Controller
{
    public function index(Request $request)
    {
        return view('public.dashboard');
    }

    public function map()
    {
        return view('public.map');
    }

    public function lapor()
    {
        $jenisBencana = BencanaMasterJenis::orderBy('nama_bencana')->get();
        $kabupatenList = \App\Models\WilayahKabupaten::orderBy('nama_kab')->get();
        return view('public.lapor', compact('jenisBencana', 'kabupatenList'));
    }

    public function resource()
    {
        return view('public.resource');
    }
}
