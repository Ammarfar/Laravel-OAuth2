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

        $proses = [];
        $result = [];

        for ($i = 0; $i < count($db); $i++) {
            $db[$i]->jam = substr($db[$i]->waktu, -8);
            $db[$i]->is_approve == 0 ? $db[$i]->is_approve = 'REJECT' : $db[$i]->is_approve = 'APPROVE';
        }

        for ($i = 0; $i < count($db); $i++) {
            $db[$i]->waktu = substr($db[$i]->waktu, 0, 10);
        }

        for ($i = 0; $i < count($db); $i++) {
            $j = $i + 1;
            $j == 4 ? $j = 0 : '';
            $db[$i]->waktu == $db[$j]->waktu && $db[$i]->id_users == $db[$j]->id_users ? array_push($proses, [$db[$i], $db[$j]])  : '';
        }

        for ($i = 0; $i < count($proses); $i++) {
            array_push($result, [
                'id_user' => $proses[$i][0]->id_users,
                'nama_user' => $proses[$i][0]->name,
                'tanggal' => $proses[$i][0]->waktu,
                'waktu_masuk' => $proses[$i][1]->jam,
                'waktu_pulang' => $proses[$i][0]->jam,
                'status_masuk' => $proses[$i][1]->is_approve,
                'status_pulang' => $proses[$i][0]->is_approve,
            ]);
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
