<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendDebtReminder extends Command
{
    protected $signature = 'reminder:utang';
    protected $description = 'Kirim reminder utang ke pelanggan';

    public function handle()
    {
        $customers = DB::table('transactions')
            ->select(
                'customer_name',
                'customer_phone',
                DB::raw('SUM(total - uang_diterima) as sisa'),
                DB::raw('MAX(due_date) as due_date')
            )
            ->whereNotNull('customer_name')
            ->groupBy('customer_name', 'customer_phone')
            ->havingRaw('SUM(total - uang_diterima) > 0')
            ->get();

        foreach ($customers as $c) {

            $pesan = "Halo {$c->customer_name}, kami dari Immanuel Store.%0A"
                . "Mengingatkan sisa utang Anda:%0A"
                . "Rp " . number_format($c->sisa, 0, ',', '.') . "%0A"
                . "Jatuh tempo: " . date('d-m-Y', strtotime($c->due_date)) . "%0A"
                . "Mohon segera dilunasi 🙏";

            // 🔥 KIRIM KE API WA
            $this->sendWA($c->customer_phone, $pesan);
        }
    }

    private function sendWA($phone, $message)
    {
        $phone = preg_replace('/^0/', '62', $phone);

        // contoh pakai Fonnte / Wablas / dll
        $token = "YOUR_API_TOKEN";

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.fonnte.com/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'target' => $phone,
                'message' => urldecode($message),
            ],
            CURLOPT_HTTPHEADER => [
                "Authorization: $token"
            ],
        ]);

        curl_exec($curl);
        curl_close($curl);
    }
}