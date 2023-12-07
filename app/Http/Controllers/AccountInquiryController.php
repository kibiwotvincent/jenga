<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use NjoguAmos\Jenga\Api\AccountInquiry;

class AccountInquiryController extends Controller
{

    public function search(Request $request)
    {
        /*$validated = $request->validate([
            'countryCode' => ['required'],
            'accountNumber' => ['required'],
        ]);*/
        
        $validated['countryCode'] = 'KE';
        $validated['accountNumber'] = '1450160649886';

        $response = (new AccountInquiry())
            ->search(
                countryCode: $validated['countryCode'],
                accountNumber: $validated['accountNumber']
            );
        
        print $response;
    }
    
}
