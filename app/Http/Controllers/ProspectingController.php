<?php

namespace App\Http\Controllers;

use App\Models\Prospecting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class ProspectingController extends Controller
{
    public function index(Request $request)
    {
        try {
            $status = $request->input('status');
            $prospectingModel = new Prospecting();
            $prospecting = $prospectingModel->getProsespecting(Auth::id(), $status);
            foreach ($prospecting as $rc) {
                $rc->customer_id = Hashids::encode($rc->customer_id);
            }
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $prospecting
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {

            $validate = Validator::make($request->all(), [
                'customer_id' => 'required|exists:ms_customer,id',
                'status' => 'required|in:prospect,follow-up,negosiasi,dealing,cancel',
                'note' => 'nullable|string|max:255',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validate->errors()->first()
                ], 400);
            }

            $prospecting = new Prospecting();
            $prospecting->user_id = Auth::id();
            $prospecting->customer_id = $request->input('customer_id');
            $prospecting->status = 'prospect';
            $prospecting->note = $request->input('note');
            $prospecting->created_by = Auth::id();
            $prospecting->save();
            return response()->json([
                'status' => true,
                'message' => 'Success',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed'
            ], 500);
        }
    }
}
