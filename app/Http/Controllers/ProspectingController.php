<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Prospecting;
use App\Models\ProspectingPipeline;
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
                $rc->encode_id = Hashids::encode($rc->id);
                $rc->encode_customer_id = Hashids::encode($rc->customer_id);
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
            DB::beginTransaction();

            $validate = Validator::make($request->all(), [
                'customer_id' => 'required|exists:ms_customer,id',
                'status' => 'required|exists:ms_pipeline,id',
                'note' => 'nullable|string|max:255',
                'kode' => 'required|string|max:15',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validate->errors()->first()
                ], 400);
            }

            $companyProses = Prospecting::where('customer_id', $request->input('customer_id'))
                ->where('status', '!=', 5)
                ->get();
            if (count($companyProses) > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer is still in process'
                ], 400);
            }

            $prospecting = new Prospecting();
            $prospecting->user_id = Auth::id();
            $prospecting->customer_id = $request->input('customer_id');
            $prospecting->status = $request->input('status');
            $prospecting->note = $request->input('note');
            $prospecting->kode = $request->input('kode');
            $prospecting->created_by = Auth::id();
            $prospecting->updated_by = Auth::id();
            $prospecting->save();

            // !!PROSPECTING PIPELINE
            $prospectPipeline = new ProspectingPipeline();
            $prospectPipeline->prospecting_id = $prospecting->id;
            $prospectPipeline->status = $request->input('status');
            $prospectPipeline->note = $request->input('note');
            $prospectPipeline->created_by = Auth::id();
            $prospectPipeline->updated_by = Auth::id();
            $prospectPipeline->save();

            $activity = Activity::where('prospect_id', $prospecting->id)->get();
            if ($prospecting->status != 1 && count($activity) == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to create becouse there is no activity'
                ], 400);
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Success',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed'
            ], 500);
        }
    }

    public function show($encodeId = null)
    {
        try {
            if (!$encodeId) {
                return response()->json([
                    'status' => true,
                    'message' => 'ID is required',
                ], 500);
            }
            $id = Hashids::decode($encodeId)[0];

            $prospectingModel = new Prospecting();
            $prospecting = $prospectingModel->getProspectinById($id);
            $kota = DB::table('ms_kabupaten')->where('id', $prospecting->city_id)->first();
            $provinsi = DB::table('ms_provinsi')->where('id', $kota->provinsi_id)->first();
            $prospecting->alamat = $kota->name . ', ' . $provinsi->name;
            $prospecting->encode_id = Hashids::encode($prospecting->id);
            $prospecting->encode_customer_id = Hashids::encode($prospecting->customer_id);
            $pipeline = DB::table('prospecting_pipeline')
                ->select(
                    'prospecting_pipeline.id',
                    'prospecting_pipeline.prospecting_id',
                    'ms_pipeline.name as status',
                    'prospecting_pipeline.created_at',
                    'prospecting_pipeline.updated_at',
                )
                ->where('prospecting_pipeline.prospecting_id', $prospecting->id)
                ->leftJoin('ms_pipeline', 'ms_pipeline.id', '=', 'prospecting_pipeline.status')
                ->get();
            $prospecting->history = $pipeline;
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $prospecting
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $encodeId = null)
    {
        try {
            DB::beginTransaction();
            if (!$encodeId) {
                return response()->json([
                    'status' => false,
                    'message' => 'ID is required'
                ], 500);
            }
            $id = Hashids::decode($encodeId)[0];

            $validate = Validator::make($request->all(), [
                'status' => 'required|exists:ms_pipeline,id',
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validate->errors()->first()
                ], 400);
            }

            $activity = Activity::where('prospect_id', $id)->get();
            if (count($activity) == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update becouse there is no activity'
                ], 400);
            }

            $result = Prospecting::find($id);

            if ($result->status <= $request->input('status')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to update becouse status not valid'
                ], 400);
            }

            $result->status = $request->input('status');
            $result->updated_by = Auth::id();
            $result->save();

            $prospectPipeline = new ProspectingPipeline();
            $prospectPipeline->prospecting_id = $id;
            $prospectPipeline->status = $request->input('status');
            $prospectPipeline->note = $request->input('note');
            $prospectPipeline->created_by = Auth::id();
            $prospectPipeline->updated_by = Auth::id();
            $prospectPipeline->save();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Success'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed'
            ], 500);
        }
    }

    public function pipeline()
    {
        try {
            $companyID = Auth::user()->company_id;
            $data = DB::table('ms_pipeline')->select('id', 'name')->where('company_id', $companyID)->get();

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
