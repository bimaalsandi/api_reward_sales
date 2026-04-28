<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Vinkla\Hashids\Facades\Hashids;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $customerModel = new Customer();
            $customer = $customerModel->getCustomer(Auth::id(), $search);
            foreach ($customer as $rc) {
                $rc->encode_id = Hashids::encode($rc->id);
            }
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $customer
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
                'name' => 'required|string|max:255|unique:ms_customer,name',
                'alamat' => 'required|string',
                'city_id' => 'required|integer',
                'email' => 'required|email|max:255|unique:ms_customer,email',
                'phone_number' => 'required|string|max:20',
                'note' => 'nullable|string',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validate->errors()->first()
                ], 400);
            }


            $auth = Auth::user();
            $customer = new Customer();
            $customer->user_id = Auth::id();
            $customer->company_id = $auth->company_id;
            $customer->name = $request->input('name');
            $customer->alamat = $request->input('alamat');
            $customer->city_id = $request->input('city_id');
            $customer->email = $request->input('email');
            $customer->phone_number = $request->input('phone_number');
            $customer->status = 1;
            $customer->note = $request->input('note');
            $customer->created_by = Auth::id();
            $customer->save();

            return response()->json([
                'status' => true,
                'message' => 'Success',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create customer' . $e->getMessage()
            ], 500);
        }
    }


    public function show($encodeId = null)
    {
        try {
            if (!$encodeId) {
                return response()->json([
                    'status' => false,
                    'message' => 'ID is required'
                ], 500);
            }
            $id = Hashids::decode($encodeId)[0];
            $customerModel = new Customer();
            $customer = $customerModel->getCustomerById($id, Auth::id());

            if (!$customer) {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            $customer->encode_id = Hashids::encode($customer->id);

            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $customer
            ], 200);
        } catch (\Exception $e) {
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
                    'message' => 'ID is required'
                ], 500);
            }
            $id = Hashids::decode($encodeId)[0];
            $customer = Customer::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$customer) {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            $validate = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255|unique:ms_customer,name,' . $customer->id,
                'alamat' => 'nullable|string',
                'city_id' => 'nullable|integer',
                'email' => 'nullable|email|max:255|unique:ms_customer,email,' . $customer->id,
                'phone_number' => 'nullable|string|max:20',
                'note' => 'nullable|string',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validate->errors()->first()
                ], 400);
            }

            $auth = Auth::user();
            $customer->company_id = $auth->company_id;
            $customer->name = $request->input('name', $customer->name);
            $customer->alamat = $request->input('alamat', $customer->alamat);
            $customer->city_id = $request->input('city_id', $customer->city_id);
            $customer->email = $request->input('email', $customer->email);
            $customer->phone_number = $request->input('phone_number', $customer->phone_number);
            $customer->status = 1;
            $customer->note = $request->input('note', $customer->note);
            $customer->updated_by = Auth::id();
            $customer->save();

            return response()->json([
                'status' => true,
                'message' => 'Success',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update customer'
            ], 500);
        }
    }
}
