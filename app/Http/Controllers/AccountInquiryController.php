<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use NjoguAmos\Jenga\Api\AccountInquiry;

class AccountInquiryController extends Controller
{

    public function balance(Request $request)
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
        
        print $response;
    }
    
}
