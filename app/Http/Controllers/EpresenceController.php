<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Epresence;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class EpresenceController extends Controller
{
    public function epresence(Request $request)
    {
        $user = Auth::user();
        $user = $user->makeHidden(['email_verified_at', 'password', 'remember_token']);

        $validate = Validator::make($request->all(), [
            'type' => ['string', 'required'],
            'waktu' => ['string', 'required'],
        ]);

        if ($validate->fails()) {
            $response['status'] = false;
            $response['message'] = 'Gagal Absen';
            $response['error'] = $validate->errors();

            return response()->json($response, 422);
        }

        $waktu = substr($request->waktu, 0, 10);

        $cek = DB::table('epresences')
            ->where('id_users', $user->id)
            ->where('type', $request->type)
            ->where('waktu', 'LIKE', "%{$waktu}%")
            ->first();

        if ($cek) {
            return response()->json('Anda sudah absen', 422);
        }

        $user = Epresence::create([
            'id_users' => $user->id,
            'type' => $request->type,
            'waktu' => $request->waktu,
        ]);

        $response['status'] = true;
        $response['message'] = 'Berhasil Absen';
        $response['data'] = $user;

        return response()->json($response, 200);
    }

    public function history()
    {
        $user = Auth::user();

        $db = DB::table('epresences as ep')
            ->where('ep.id_users', $user->id)
            ->where('ep.type', 'IN')
            ->join('users as us', 'us.id', '=', 'ep.id_users')
            ->join('epresences as ep2', function ($join) {
                $join->on('ep2.id_users', '=', 'ep.id_users')
                    ->where('ep2.type', 'OUT')
                    ->whereRaw('DATE_FORMAT(ep2.waktu, "%d-%b-%Y") = DATE_FORMAT(ep.waktu, "%d-%b-%Y")');
            })
            ->select(DB::raw('ep.id_users,us.name,substr(ep.waktu, 1, 10) as tanggal,substr(ep.waktu, -8) as waktu_datang,substr(ep2.waktu, -8) as waktu_pulang,ep.is_approve as status_datang,ep2.is_approve as status_pulang'))
            ->orderBy('ep.id', 'desc')
            ->limit(2)
            ->get();

        for ($i = 0; $i < count($db); $i++) {
            $db[$i]->status_datang = $db[$i]->status_datang ? 'APPROVE' : 'REJECT';
            $db[$i]->status_pulang = $db[$i]->status_pulang ? 'APPROVE' : 'REJECT';
        }

        $response['status'] = true;
        $response['message'] = 'Success get history';
        $response['data'] = $db;

        return response()->json($response, 200);
    }

    public function approve($id)
    {
        $user = Auth::user();

        $db = DB::table('epresences as ep')
            ->where('ep.id', $id)
            ->join('users as us', 'us.id', '=', 'ep.id_users')
            ->first();

        if ($user->npp == $db->npp_supervisor) {
            DB::table('epresences')
                ->where('id', $id)
                ->update(['is_approve' => true]);
            $response['status'] = true;
            $response['message'] = 'Berhasil';
        } else {
            $response['status'] = false;
            $response['message'] = 'Gagal Approve, Anda Bukan Supervisor Pegawai Terkait';
        }

        return response()->json($response, 200);
    }
}
