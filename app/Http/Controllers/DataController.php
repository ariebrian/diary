<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Data;
use App\Models\User;

class DataController extends Controller
{
    //
    public function index(Request $request)
    {
        $query = Data::where('user_id', $request->user_id)->where('year', $request->year)->where('quarter', $request->quarter)->get();
        $result = [];

        if (count($query) > 0) {
            return response()->json([
                'count' => count($query),
                'result' => $query,
            ]);
        } else {
            return response()->json([
                'message' => 'No data'
            ], 204);
        }

    }

    public function read_entry($entry_id)
    {
        $query = Data::where('id', $entry_id)->first();

        if ($query) {
            return response()->json([
                'result' => $query,
            ]);
        } else {
            return response()->json([
                'message' => 'No data'
            ], 204);
        }
    }

    public function insert(Request $request)
    {

        $today = date('Y-m-d H:i:s');
        $token = $request->token;
        $user = User::where('api_token', $token)->first();

        $check = check_entry($user->id, $today);
        // var_dump($check->entry_data);
        // die();

        if ($check != False) {
            $data = $check;
            $data->entry_created_at = $today;
            $data->entry_data = $data->entry_data . $request->entry;

            $data->save();
        } else {
            $data = new Data;

            $year = date('Y');
            $month = date('m');
            if (in_array($month, ['01', '02', '03'])){
                $quarter = 1;
            } elseif (in_array($month, ['04', '05', '06'])) {
                $quarter = 2;
            } elseif (in_array($month, ['07', '08', '09'])) {
                $quarter = 3;
            } elseif (in_array($month, ['10', '11', '12'])) {
                $quarter = 4;
            }


            $data->entry_data = $request->entry;
            $data->year = $year;
            $data->quarter = $quarter;
            $data->user_id = $user->id;
            $data->entry_created_at = $today;
        }


        $data->save();

        return response()->json([
            'entry_data' => $data->entry_data
        ], 201);

    }

}


function check_entry($user_id, $datetime)
    {
        $today_data = Data::where('user_id', $user_id)->orderBy('entry_created_at', 'DESC')->first();

        if (date('Y-m-d', strtotime($today_data->entry_created_at)) == date('Y-m-d', strtotime($datetime))) {
            return $today_data;
        } else {
            return False;
        }

    }
