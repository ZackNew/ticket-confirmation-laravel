<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitPaymentRequest;
use App\Mail\PaymentApprovedMail;
use App\Mail\PaymentRejectedMail;
use App\Mail\PaymentSubmittedMail;
use App\Services\PaymentService;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mail;

class PaymentController extends Controller
{
    protected $paymentService;
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $validator = Validator::make(request()->all(), [
          'payment_status' => 'nullable|in:pending,confirmed,rejected',
        ]);

        if($validator->fails()) {
          return response()->json([
              'status' => 'error',
              'message' => 'Invalid payment status.',
              'errors' => $validator->errors(),
          ], 422);
        }
        
        $payments = $this->paymentService->getPaymentsByStatus();

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

        $payments = $this->paymentService->getPaymentByIds($request->input('ids'));

        return response()->json([
            'status' => 'success',
            'data' => $payments,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubmitPaymentRequest $request)
    {
        $message = $this->paymentService->addPayment($request);
        return response()->json([
            'status' => 'success',
            'message' => $message,
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

        $paymentIn = Payment::find($id);
        if (!$paymentIn) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found',
                'errors' => $validator->errors(),
            ], 400);
        }
        if ($paymentIn->payment_status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment is not in pending status.',
            ], 400);
        }
        $payment = $this->paymentService->approvePayment($id, $paymentIn);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment approved successfully',
            'data' => $payment,
        ], 200);
    }
    
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

        $paymentIn = Payment::find($id);
        if (!$paymentIn) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found',
                'errors' => $validator->errors(),
            ], 400);
        }
        if ($paymentIn->payment_status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment is not in pending status.',
            ], 400);
        }
        $paymentIn->payment_status = 'rejected';
        $paymentIn->save();
        Mail::to($paymentIn->email)->send(new PaymentRejectedMail($paymentIn->full_name));

        return response()->json([
            'status' => 'success',
            'message' => 'Payment rejected successfully',
            'data' => $paymentIn,
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
