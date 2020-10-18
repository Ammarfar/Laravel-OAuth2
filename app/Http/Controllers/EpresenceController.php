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
            ->join('users as us', 'us.id', '=', 'ep.id_users')
            ->select('ep.id_users', 'us.name', 'ep.waktu', 'ep.type', 'ep.is_approve')
            ->orderBy('ep.id', 'desc')
            ->limit(4)
            ->get();

        $result = [];

        if ($db[0]->type == 'OUT') {
            for ($i = 0; $i < 3; $i++) {
                if ($db[$i]->type == 'OUT') {
                    array_push($result, [
                        'id_user' => $db[$i]->id_users,
                        'nama' => $db[$i]->name,
                        'tanggal' => substr($db[$i]->waktu, 0, 10),
                        'waktu_masuk' => substr($db[$i + 1]->waktu, -8),
                        'waktu_pulang' => substr($db[$i]->waktu, -8),
                        'status_masuk' => $db[$i + 1]->is_approve == 0 ? 'REJECT' : 'APPROVE',
                        'status_pulang' => $db[$i]->is_approve == 0 ? 'REJECT' : 'APPROVE',
                    ]);
                }
            }
        } else {
            return response()->json('Silahkan absen pulang', 422);
        }

        $response['status'] = true;
        $response['message'] = 'Success get history';
        $response['data'] = $result;

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
