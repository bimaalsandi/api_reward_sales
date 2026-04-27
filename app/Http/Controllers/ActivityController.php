<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class ActivityController extends Controller
{

    public function index()
    {
        try {
            $activityModel = new Activity();
            $activity = $activityModel->getActivity(Auth::id());
            foreach ($activity as $ra) {
                $ra->encode_id = Hashids::encode($ra->id);
                $ra->encode_prospect_id = Hashids::encode($ra->prospect_id);
            }
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $activity
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed' . $e->getMessage()
            ], 500);
        }
    }

    public function activityStatus()
    {
        try {
            $companyId = Auth::user()->company_id;
            $activityStatus = DB::table('ms_activity_status')->where('company_id', $companyId)->get();
            if (count($activityStatus) == 0) {
                $result = DB::table('ms_activity_status')->select('id', 'name')->get();
            } else {
                $result = DB::table('ms_activity_status')->where('company_id', $companyId)->select('id', 'name')->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $result
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
                'prospect_id' => 'required|string',
                'type' => 'required|string|max:255',
                'note' => 'nullable|string',
                'activity_date' => 'required|date',
                'activity_status' => 'required|exists:ms_activity_status,id',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validate->errors()->first()
                ], 400);
            }

            $activity = new Activity();
            $activity->user_id = Auth::id();
            $activity->prospect_id = $request->input('prospect_id');
            $activity->type = $request->input('type');
            $activity->note = $request->input('note');
            $activity->activity_date = $request->input('activity_date');
            $activity->activity_status = $request->input('activity_status');
            $activity->created_by = Auth::id();
            $activity->save();


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

    public function update(Request $request, $encodeId = null)
    {
        try {
            if (!$encodeId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Id is required'
                ], 400);
            }
            $id = Hashids::decode($encodeId)[0];
            $validate = Validator::make($request->all(), [
                'prospect_id' => 'nullable|integer',
                'type' => 'nullable|string|max:255',
                'note' => 'nullable|string',
                'activity_date' => 'nullable|date',
                'activity_status' => 'nullable|exists:ms_activity_status,id',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validate->errors()->first()
                ], 400);
            }

            $activity = Activity::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$activity) {
                return response()->json([
                    'status' => false,
                    'message' => 'Activity not found'
                ], 404);
            }

            $activity->prospect_id = $request->input('prospect_id', $activity->prospect_id);
            $activity->type = $request->input('type', $activity->type);
            $activity->note = $request->input('note', $activity->note);
            $activity->activity_date = $request->input('activity_date', $activity->activity_date);
            $activity->activity_status = $request->input('activity_status', $activity->activity_status);
            $activity->updated_by = Auth::id();
            $activity->save();

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
