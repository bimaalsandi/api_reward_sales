<?php

namespace App\Http\Controllers;

use App\Models\Prospecting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function totalProspect()
    {
        try {
            $prospectingModel = new Prospecting();
            $totalProspect = $prospectingModel->getTotalCount(Auth::id(), 'prospect');
            $totalFollowUp = $prospectingModel->getTotalCount(Auth::id(), 'follow-up');
            $totalNegosiasi = $prospectingModel->getTotalCount(Auth::id(), 'negosiasi');
            $totalDealing = $prospectingModel->getTotalCount(Auth::id(), 'dealing');
            $totalCancel = $prospectingModel->getTotalCount(Auth::id(), 'cancel');
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => [
                    'pipeline' => [
                        'total_prospect' => $totalProspect,
                        'total_follow_up' => $totalFollowUp,
                        'total_negosiasi' => $totalNegosiasi,
                        'total_dealing' => $totalDealing,
                        'total_cancel' => $totalCancel
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed'
            ], 500);
        }
    }
}
