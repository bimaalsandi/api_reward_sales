<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterController extends Controller
{
    public function getProvinsi(Request $request)
    {
        try {
            $name = $request->input('name');
            $data = DB::table('ms_provinsi')
                ->where('id_negara', 1)
                ->where('name', 'like', '%' . $name . '%')
                ->get();
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed'
            ], 500);
        }
    }

    public function getKota(Request $request, $id = null)
    {
        try {
            if (!$id) {
                return response()->json([
                    'status' => false,
                    'message' => 'ID is required'
                ], 500);
            }
            $name = $request->input('name');
            $data = DB::table('ms_kabupaten')
                ->where('provinsi_id', $id)
                ->where('name', 'like', '%' . $name . '%')
                ->get();
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed'
            ], 500);
        }
    }

    public function getKecamatan(Request $request, $id = null)
    {
        try {
            if (!$id) {
                return response()->json([
                    'status' => false,
                    'message' => 'ID is required'
                ], 500);
            }

            $name = $request->input('name');
            $data = DB::table('ms_kecamatan')
                ->where('kabupaten_id', $id)
                ->where('name', 'like', '%' . $name . '%')
                ->get();
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed'
            ], 500);
        }
    }
}
