<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Xendit\Xendit;

class HomeController extends Controller
{
    public function __construct()
    {
        Xendit::setApiKey(env('XENDIT_API_KEY'));
    }

    public function invoice()
    {
        $params = [
            'external_id'       => 'testing_13345',
            'amount'            => 40000,
            'description'       => 'Pembayaran E-book',
            'invoice_duration'  => 1
        ];

        $createInvoce = \Xendit\Invoice::create($params);
        dd($createInvoce);

        // $inv = Transaction::create([
        //     'external_id' => $createInvoce['external_id'],
        // ]);
    }

    public function xenditCallback()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $data = file_get_contents("php://input");
            Log::info('===CALLBACK XENDIT INVOICE===');
            Log::info($data);
            $data = json_decode($data);

            // check status transaksi
            // jika expired
            // update status jadi expired
            $xenditCallback = Transaction::where('id', $data->external_id)->first();
            $xenditCallback->status = 'EXPIRED';
            $xenditCallback->save();


            // jika paid
            // cari external_id
            $xenditCallback = Transaction::where('id', $data->external_id)->first();
            $xenditCallback->status = 'PAID';
            $xenditCallback->save();

            return response()->json("callback success", 200);
        } else {
            abort(400);
        }
    }
}
