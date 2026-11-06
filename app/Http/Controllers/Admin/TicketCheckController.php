<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\TicketPassService;
use Illuminate\Http\Request;

class TicketCheckController extends Controller
{
    public function index()
    {
        return view('admin.check-in', [
            'recent' => Order::with('concert', 'user')
                ->whereNotNull('checked_in_at')
                ->orderByDesc('checked_in_at')
                ->limit(10)
                ->get(),
        ]);
    }

    public function store(Request $request, TicketPassService $passes)
    {
        $validated = $request->validate(['code' => 'required|string|max:64']);
        $result = $passes->admit($validated['code']);

        return redirect()->route('admin.check-in')->with('scan', [
            'status' => $result['status'],
            'order_id' => optional($result['order'])->id,
        ]);
    }
}
