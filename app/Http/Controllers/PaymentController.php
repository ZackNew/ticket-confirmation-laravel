<?php

namespace App\Http\Controllers;

use App\Mail\PaymentApprovedMail;
use App\Mail\PaymentRejectedMail;
use App\Mail\PaymentSubmittedMail;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mail;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $validator = Validator::make(request()->all(), [
            'payment_status' => 'nullable|in:pending,confirmed,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid payment status.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $status = request()->query('payment_status');
        $query = Payment::orderBy('updated_at', 'desc');

        if ($status) {
            $query->where('payment_status', $status);
        }

        $payments = $query->get();

        return response()->json([
            'status' => 'success',
            'data' => $payments,
        ], 200);
    }

    /**
     * Display a listing of the resource by id.
     */
    public function getByIds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid IDs provided.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payments = Payment::whereIn('id', $request->input('ids'))->get();

        if ($payments->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid id provided.',
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'data' => $payments,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'full_name' => 'required|string|min:2|max:100',
            'email' => 'required|email|min:5|max:100',
            'phone_number' => [
                'required',
                'string',
                'regex:/^(09|07)\d{8}$|^(\+?251(9|7)\d{8})$/'
            ],
            'image_link' => 'required|url',
            'address' => 'nullable|string|max:255',
            'number_of_tickets' => 'required|integer|min:1',
        ]);

        $existing = Payment::where('email', $fields['email'])
            ->orWhere('email', $fields['email'])
            ->first();
        if ($existing) {
            $fields['dup_flag'] = true;
            $fields['dup_id'] = $existing->id;
        }
        $data = Payment::create($fields);
        Mail::to($data->email)->queue(new PaymentSubmittedMail('Stored'));
        return response()->json([
            'status' => 'success',
            'message' => 'Payment created successfully',
        ], 201);
    }

    /**
     * Approve the payment
     */
    public function approveRequest(string $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid id provided.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payment = Payment::find($id);
        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found',
                'errors' => $validator->errors(),
            ], 400);
        }
        $ticketNumbers = generate_ticket_number($payment->number_of_tickets);
        $payment->payment_status = 'confirmed';
        $payment->tickets = $ticketNumbers;
        $payment->save();
        Mail::to($payment->email)->send(mailable: new PaymentApprovedMail($payment->full_name, $payment->tickets));

        return response()->json([
            'status' => 'success',
            'message' => 'Payment approved successfully',
            'data' => $payment,
        ], 200);
    }

    // }
    /**
     * Reject the payment
     */
    public function rejectRequest(string $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid UUID provided.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payment = Payment::find($id);
        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found',
                'errors' => $validator->errors(),
            ], 400);
        }
        $payment->payment_status = 'rejected';
        $payment->save();
        Mail::to($payment->email)->send(new PaymentRejectedMail($payment->full_name));

        return response()->json([
            'status' => 'success',
            'message' => 'Payment rejected successfully',
            'data' => $payment,
        ], 200);
    }

    public function resolveDuplicates(string $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid id provided.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payment = Payment::find($id);
        if (!$payment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found',
                'errors' => $validator->errors(),
            ], 400);
        }
        $payment->dup_flag = false;
        $payment->dup_id = null;
        $payment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment approved successfully',
            'data' => $payment,
        ], 200);
    }

    public function downloadPayments(Request $request)
    {
        $status = request()->query('payment_status');
        if($status === 'confirmed') {
            $payments = Payment::select('full_name', 'phone_number', 'tickets', 'address' )->where('payment_status', 'confirmed')->get(); 
            $pdf = Pdf::loadView('pdfs.confirmedPayments', compact('payments'));
    
            return $pdf->download('confirmed-payments-list.pdf');
        } elseif($status === 'pending') {
            $payments = Payment::select('full_name', 'phone_number', 'number_of_tickets', 'address' )->where('payment_status', 'pending')->get(); 
            $pdf = Pdf::loadView('pdfs.pendingPayments', compact('payments'));
    
            return $pdf->download('pendig-payments-list.pdf');
        } elseif($status === 'rejected') {
            $payments = Payment::select('full_name', 'phone_number', 'number_of_tickets', 'address' )->where('payment_status', 'rejected')->get(); 
            $pdf = Pdf::loadView('pdfs.rejectedPayments', compact('payments'));
    
            return $pdf->download('rejected-payments-list.pdf');
        }

    }
}
