<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use NjoguAmos\Jenga\Api\AccountInquiry;
use Illuminate\Support\Facades\Http;
use Spatie\Crypto\Rsa\PrivateKey;
use Log;

class AccountInquiryController extends Controller
{
    public function create()
    {
        return view('account-inquiry', [
            'countryCode' => config('jenga.country'),
            'accountNumber' => config('jenga.account'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'countryCode' => ['required'],
            'accountNumber' => ['required'],
        ]);

        $response = (new AccountInquiry())
            ->search(
                countryCode: $validated['countryCode'],
                accountNumber: $validated['accountNumber']
            );

        return back()
            ->withInput(input: $request->all())
            ->with(key: 'response', value: $response);
    }
    
    public function search2(Request $request)
    {
        /*$validated = $request->validate([
            'countryCode' => ['required'],
            'accountNumber' => ['required'],
        ]);*/
        
        $accounts = ['1090161410997','1090180048160'];
        
        $validated['countryCode'] = 'KE';
        $validated['accountNumber'] = $accounts[0];

        $response = (new AccountInquiry())
            ->search(
                countryCode: $validated['countryCode'],
                accountNumber: $validated['accountNumber']
            );
        
        print $response;
    }
    
    public function test1(Request $request)
    {
        $validated = [
            'countryCode' => 'KE',
            'accountNumber' => '1450160649886', // '1090161410997'//'1450160649886',
        ];

        $response = (new AccountInquiry())
            ->search(
                countryCode: $validated['countryCode'],
                accountNumber: $validated['accountNumber']
            );
        print_r($response);
        die();
        return back()
            ->withInput(input: $request->all())
            ->with(key: 'response', value: $response);
    }
    
    public function search(Request $request)
    {
        $token = $this->getToken();
        $baseUrl = config(key: 'jenga.host');
        $accountNumber = 1090161410997;
        $params = ['countryCode'   => 'KE', 'accountNumber' => $accountNumber];
        $signature = $this->getSignature($params);
       
        return Http::withToken(token: $this->getToken())
            ->withUrlParameters(parameters: $params)
            ->withHeaders(headers: ['Signature' => $signature])
            ->get(url: $baseUrl.'/v3-apis/account-api/v3.0/accounts/search/KE/'.$accountNumber)
            ->body();
    }
    
    public function send()
    {
        $url = config(key: 'jenga.host') . "/authentication/api/v3/authenticate/merchant";
        $url = "https://uat.finserve.africa/v3-apis/transaction-api/v3.0/remittance/sendmobile";
        $apiKey = config(key: 'jenga.key');
        $merchantCode = config(key: 'jenga.merchant');
        $consumerSecret = config(key: 'jenga.secret');
        $token = $this->getToken();
        $baseUrl = config(key: 'jenga.host');
        
        $amount = 5;
        $currencyCode = "KES";
        $reference = "TMN".time();
        $accountNumber = 1090161410997;
        
        $params = ['amount' => $amount, 'currencyCode' => $currencyCode, 'reference' => $reference, 'accountNumber' => $accountNumber];
        $signature = $this->getSignature($params);
        
        $response = Http::acceptJson()
            ->withToken(token: $token)
            ->withHeaders(headers: ['Signature' => $signature])
            ->post(url: $url, data: [
                "source" => [
                    "countryCode" => "KE",
                    "name" => "Vincent Kibiwott Chumba",
                    "accountNumber" => $accountNumber
                ],
                "destination" => [
                    "type" => "mobile",
                    "countryCode" => "KE",
                    "name" => "Vincent Kibiwot",
                    "mobileNumber" => "254706038461",
                    "walletName" => "Mpesa"
                ],
                "transfer" => [
                    "type" => "MobileWallet",
                    "amount" => $amount,
                    "currencyCode" => $currencyCode,
                    "reference" => $reference,
                    "date" => "2023-12-09",
                    "description" => "Payment for invoice NT4345",
                    "callbackUrl" => "https://ifam.co.ke/api/equity/callback"
                ]
            ]);

        return $response;
    }
    
    public function callback(Request $request) {
        Log::info($request->all());
    }
    
    public function getToken()
    {
        $url = config(key: 'jenga.host') . "/authentication/api/v3/authenticate/merchant";
        $apiKey = config(key: 'jenga.key');
        $merchantCode = config(key: 'jenga.merchant');
        $consumerSecret = config(key: 'jenga.secret');

        $response = Http::acceptJson()
            ->withHeaders(headers: ['Api-Key' => $apiKey])
            ->retry(times: 3, sleepMilliseconds: 100)
            ->post(url: $url, data: [
                'merchantCode'   => $merchantCode,
                'consumerSecret' => $consumerSecret
            ]);

        if (! $response->successful()) {
            // @TODO: Refactor error
            $this->error(string: trans(key: 'jenga::jenga.token.error'));

            return self::FAILURE;
        }
        $data = $response->json();
        
        return $data['accessToken'];
    }

    public function getSignature($data): string
    {
        $dataString = implode('', $data);;
        $privateKey = config(key: 'jenga.keys_path').'/jenga.key';

        return PrivateKey::fromFile($privateKey)->sign($dataString);
    }
}
