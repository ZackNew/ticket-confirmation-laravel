<?php

namespace App\Services;
use App\Mail\PaymentApprovedMail;
use App\Mail\PaymentSubmittedMail;
use App\Models\Payment;
use Mail;

class PaymentService
{
    public function getPaymentsByStatus()
    {
        $status = request()->query('payment_status');
        $query = Payment::orderBy('updated_at', 'desc');

        if ($status) {
            $query->where('payment_status', $status);
        }

        return $query->get();
    }

    public function getPaymentByIds($ids)
    {
        $payments = Payment::whereIn('id', $ids)->get();

        if ($payments->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid id provided.',
            ], 400);
        }
        return $payments;
    }

    public function addPayment($data)
    {
        $existing = Payment::where('email', $data['email'])
            ->orWhere('email', $data['email'])
            ->first();

        if ($existing) {
            $data['dup_flag'] = true;
            $data['dup_id'] = $existing->id;
        }

        $data = Payment::create($data->validated());
        Mail::to($data->email)->queue(new PaymentSubmittedMail('Stored'));

        return 'Payment created successfully.';
    }

    public function approvePayment(string $id, Payment $payment)
    {
        $ticketNumbers = generate_ticket_number($payment->number_of_tickets);
        $payment->payment_status = 'confirmed';
        $payment->tickets = $ticketNumbers;
        $payment->save();
        Mail::to($payment->email)->send(mailable: new PaymentApprovedMail($payment->full_name, $payment->tickets));

        return $payment;
    }
}