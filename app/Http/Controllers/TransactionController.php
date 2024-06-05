<?php

namespace App\Http\Controllers;

use App\Models\RefGate;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transaction = RefGate::paginate(10);

        return view('transaction', compact('transaction'));
    }
}
