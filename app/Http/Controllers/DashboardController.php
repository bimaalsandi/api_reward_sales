<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Prospecting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class DashboardController extends Controller
{
    public function totalProspect()
    {
        try {
            $prospectingModel = new Prospecting();
            $totalProspect = $prospectingModel->getTotalCount(Auth::id(), 1);
            $totalQuotation = $prospectingModel->getTotalCount(Auth::id(), 2);
            $totalNegosiasi = $prospectingModel->getTotalCount(Auth::id(), 3);
            $totalDealing = $prospectingModel->getTotalCount(Auth::id(), 4);
            $totalCancel = $prospectingModel->getTotalCount(Auth::id(), 5);


            $activityModel = new Activity();
            $activity = Activity::where('activity.user_id', Auth::id())
                ->select(
                    'activity.id',
                    'activity.type',
                    'activity.note',
                    'activity.activity_date',
                    'ms_activity_status.name as activity_status',
                    'users.name',
                    'ms_customer.name as customer_name',
                )
                ->leftJoin('users', 'users.id', '=', 'activity.user_id')
                ->leftJoin('prospecting', 'prospecting.id', '=', 'activity.prospect_id')
                ->leftJoin('ms_customer', 'ms_customer.id', '=', 'prospecting.customer_id')
                ->leftJoin('ms_activity_status', 'ms_activity_status.id', '=', 'activity.activity_status')
                ->limit(5)->get();

            foreach ($activity as $ra) {
                $ra->date = $ra->activity_date ?  date('d-m-Y', strtotime($ra->activity_date)) : null;
                $ra->time = $ra->activity_date ? date('H:i', strtotime($ra->activity_date)) : null;
                $ra->encode_id = Hashids::encode($ra->id);
                unset($ra->activity_date);
            }

            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => [
                    'pipeline' => [
                        'total_prospect' => $totalProspect,
                        'total_quotation' => $totalQuotation,
                        'total_negosiasi' => $totalNegosiasi,
                        'total_dealing' => $totalDealing,
                        'total_cancel' => $totalCancel
                    ],
                    'activity' => $activity,
                    'sales_target' => [
                        'date' => 'Jan 2026',
                        'revenue' => '1.000.000',
                        'target' => '10.000.000',
                        'percentage' => '10%',
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed' . $e->getMessage()
            ], 500);
        }
    }
}
