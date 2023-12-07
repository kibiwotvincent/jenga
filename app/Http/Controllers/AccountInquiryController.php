<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use NjoguAmos\Jenga\Api\AccountInquiry;
use Illuminate\Support\Facades\Http;
use Spatie\Crypto\Rsa\PrivateKey;

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
    
    public function test(Request $request)
    {
        $token = $this->getToken();
        $signature = $this->getSignature();
        $baseUrl = config(key: 'jenga.host');
       // echo $baseUrl;die;
        return Http::withToken(token: $this->getToken())
            ->withUrlParameters(parameters: [
                'endpoint'      => $baseUrl,
                'countryCode'   => 'KE',
                'accountNumber' => 1090180048160,
            ])->withHeaders(headers: ['Signature' => $signature])
            ->get(url: $baseUrl.'/v3-apis/account-api/v3.0/accounts/balances/KE/1090180048160')
            ->body();
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

    public function getSignature(): string
    {
        $dataString = 'KE1090180048160';
        $privateKey = config(key: 'jenga.keys_path').'/jenga.key';

        return PrivateKey::fromFile($privateKey)->sign($dataString);
    }
}
