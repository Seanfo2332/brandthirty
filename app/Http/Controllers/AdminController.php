<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Carbon\Carbon;
use App\Models\Setting;
use App\Exports\OrdersExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    private $validUsername = 'admin';
    private $validPassword = '123';

    // Helper to log actions
    private function logAction($orderId, $action, $details = null)
    {
        \App\Models\OrderLog::create([
            'order_id' => $orderId,
            'user' => 'Admin', // In a real app, use Auth::user()->name
            'action' => $action,
            'details' => $details
        ]);
    }

    public function index(Request $request)
    {
        // 1. Check Login
        if (!Session::get('logged_in')) {
            return view('admin.login');
        }

        // 2. Metrics & BI Data
        $today = Carbon::today();

        $todaySales = Order::whereDate('created_at', $today)->whereIn('status', ['Paid', 'Processing'])->sum('total_amount');
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $totalRevenue = Order::whereIn('status', ['Paid', 'Processing'])->sum('total_amount');
        $pendingCount = Order::whereIn('status', ['Pending', 'Processing'])->count();

        // Charts Data
        $salesData = Order::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->whereIn('status', ['Paid', 'Processing'])
            ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
        $chartLabels = $salesData->pluck('date');
        $chartValues = $salesData->pluck('total');

        $planData = Order::select('plan', DB::raw('count(*) as total'))->groupBy('plan')->get();
        $planLabels = $planData->pluck('plan');
        $planValues = $planData->pluck('total');

        // Recent Activity
        $recentOrders = Order::orderBy('created_at', 'desc')->take(5)->get();
        // Fetch Logs
        $recentLogs = \App\Models\OrderLog::orderBy('created_at', 'desc')->take(10)->get();

        // 3. Search & Advanced Filter
        $query = Order::query();

        // 3a. Tab Logic (Status)
        // Default to 'Pending' if no status is set, or ALL if user wants to see everything (optional, but requested tabs: Pending, Processing, Completed, Cancelled)
        // Mapping: Completed -> Paid, Cancelled -> Rejected
        $statusTab = $request->input('status', 'Pending');

        if ($statusTab != 'All') {
            if ($statusTab == 'Completed')
                $query->where('status', 'Paid');
            elseif ($statusTab == 'Cancelled')
                $query->where('status', 'Rejected');
            else
                $query->where('status', $statusTab);
        }

        // 3b. Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%$search%")
                    ->orWhere('company_name', 'like', "%$search%")
                    ->orWhere('order_id', 'like', "%$search%");
            });
        }

        // 3c. Date Range
        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        // 3d. Amount Range
        if ($request->filled('min_amount')) {
            $query->where('total_amount', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('total_amount', '<=', $request->max_amount);
        }

        // Get Results
        $orders = $query->orderBy('created_at', 'desc')->get();
        $totalOrders = Order::count();

        if ($request->ajax()) {
            return view('admin.partials.dashboard_rows', compact('orders'))->render();
        }

        return view('admin.dashboard', compact(
            'orders',
            'totalRevenue',
            'pendingCount',
            'totalOrders',
            'todaySales',
            'todayOrders',
            'chartLabels',
            'chartValues',
            'planLabels',
            'planValues',
            'recentOrders',
            'recentLogs',
            'statusTab'
        ));
    }

    public function getLatestUpdates()
    {
        // 1. Fetch Latest 10 Orders (Pending or Processing)
        $latestOrders = Order::whereIn('status', ['Pending', 'Processing'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // 2. Fetch Stats
        $today = Carbon::today();
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $pendingCount = Order::whereIn('status', ['Pending', 'Processing'])->count();

        // 3. Render Partial Rows
        $html = view('admin.partials.dashboard_rows', ['orders' => $latestOrders])->render();

        return response()->json([
            'latest_order_id' => $latestOrders->first() ? $latestOrders->first()->id : 0,
            'today_orders' => $todayOrders,
            'pending_count' => $pendingCount,
            'html' => $html
        ]);
    }

    public function orders(Request $request)
    {
        // 1. Check Login
        if (!Session::get('logged_in')) {
            return redirect('/admin/login');
        }

        // 2. Query Builder
        $query = Order::query();

        // 3a. Tab Logic (Status)
        $statusTab = $request->input('status', 'Pending');

        if ($statusTab != 'All') {
            if ($statusTab == 'Completed')
                $query->where('status', 'Paid');
            elseif ($statusTab == 'Cancelled')
                $query->where('status', 'Rejected');
            else
                $query->where('status', $statusTab);
        }

        // 3b. Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%$search%")
                    ->orWhere('company_name', 'like', "%$search%")
                    ->orWhere('order_id', 'like', "%$search%");
            });
        }

        // 3c. Date Range
        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        // 3d. Amount Range
        if ($request->filled('min_amount')) {
            $query->where('total_amount', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('total_amount', '<=', $request->max_amount);
        }

        // Get Results
        $orders = $query->orderBy('created_at', 'desc')->paginate(20); // Pagination for better performance

        return view('admin.orders', compact('orders', 'statusTab'));
    }

    public function login(Request $request)
    {
        $inputUser = $request->input('username');
        $inputPass = $request->input('password');

        if ($inputUser === $this->validUsername && $inputPass === $this->validPassword) {
            Session::put('logged_in', true);
            return redirect('/admin');
        } else {
            return view('admin.login')->with('error', "Invalid credentials.");
        }
    }

    public function logout()
    {
        Session::forget('logged_in');
        return redirect('/admin');
    }

    // --- CRM / Customer Logic ---

    // Helper: Syncs ALL customers from orders (Simple approach for this scale)
    // In a larger app, this would be event-driven or a job.
    private function syncCustomers()
    {
        // Get all unique emails from orders
        $emails = Order::select('customer_email')->distinct()->pluck('customer_email');

        foreach ($emails as $email) {
            // Find or create customer
            $customerOrder = Order::where('customer_email', $email)->latest()->first();

            if (!$customerOrder)
                continue;

            $customer = \App\Models\Customer::firstOrCreate(
                ['email' => $email],
                ['name' => $customerOrder->customer_name, 'phone' => $customerOrder->phone]
            );

            // Calculate stats
            $stats = Order::where('customer_email', $email)
                ->where('status', 'Paid')
                ->selectRaw('sum(total_amount) as total_spent, count(*) as order_count, max(created_at) as last_order')
                ->first();

            $totalSpent = $stats->total_spent ?? 0;
            $orderCount = $stats->order_count ?? 0;
            $lastOrder = $stats->last_order ?? null;
            $isVip = $totalSpent >= 10000;

            $customer->update([
                'name' => $customerOrder->customer_name, // Update name in case it changed
                'phone' => $customerOrder->phone,
                'total_spent' => $totalSpent,
                'order_count' => $orderCount,
                'is_vip' => $isVip,
                'last_order_at' => $lastOrder
            ]);
        }
    }

    public function customers(Request $request)
    {
        if (!Session::get('logged_in'))
            return redirect('/admin/login');

        // Sync first to ensure fresh data
        $this->syncCustomers();

        $query = \App\Models\Customer::query();

        if ($request->has('search') && $request->search != '') {
            $s = $request->search;
            $query->where('name', 'like', "%$s%")
                ->orWhere('email', 'like', "%$s%")
                ->orWhere('phone', 'like', "%$s%");
        }

        $customers = $query->orderBy('is_vip', 'desc')
            ->orderBy('total_spent', 'desc')
            ->get();

        return view('admin.customers', compact('customers'));
    }

    // --- Actions ---

    public function batchUpdate(Request $request)
    {
        $ids = $request->input('order_ids', []);
        $action = $request->input('action'); // 'processing', 'completed', 'cancelled', 'delete'

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No orders selected.');
        }

        foreach ($ids as $id) {
            $order = Order::find($id);
            if (!$order)
                continue;

            switch ($action) {
                case 'processing':
                    $order->update(['status' => 'Processing']);
                    $this->logAction($id, 'Status Change', 'Marked as Processing');
                    break;
                case 'completed':
                    $order->update(['status' => 'Paid']);
                    $this->logAction($id, 'Status Change', 'Marked as Paid (Completed)');
                    break;
                case 'cancelled':
                    $order->update(['status' => 'Rejected']);
                    $this->logAction($id, 'Status Change', 'Marked as Rejected (Cancelled)');
                    break;
                case 'delete':
                    $this->logAction($id, 'Delete', 'Order Deleted');
                    $order->delete();
                    break;
            }
        }

        // Sync customers after batch update to reflect potentially new 'Paid' statuses
        $this->syncCustomers();

        return redirect()->back()->with('success', 'Batch action completed successfully.');
    }

    public function markPaid($id)
    {
        Order::where('id', $id)->update(['status' => 'Processing']);
        $this->logAction($id, 'Status Change', 'Marked as Processing (Accepted)');
        return redirect()->back()->with('success', 'Order accepted and moved to Processing.');
    }

    public function markCompleted($id)
    {
        Order::where('id', $id)->update(['status' => 'Paid']);
        $this->logAction($id, 'Status Change', 'Marked as Paid (Completed)');
        $this->syncCustomers(); // Update stats
        return redirect()->back()->with('success', 'Order marked as Completed.');
    }

    public function markRejected($id)
    {
        Order::where('id', $id)->update(['status' => 'Rejected']);
        $this->logAction($id, 'Status Change', 'Marked as Rejected');
        return redirect()->back()->with('success', 'Order marked as Rejected.');
    }

    public function deleteOrder($id)
    {
        $this->logAction($id, 'Delete', 'Order Deleted');
        Order::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Order Deleted.');
    }

    public function invoice($id)
    {
        $order = Order::findOrFail($id);
        return view('admin.invoice', compact('order'));
    }

    public function downloadInvoice($id)
    {
        $order = Order::findOrFail($id);
        $pdf = Pdf::loadView('admin.invoice', compact('order'));
        return $pdf->download('Invoice_' . $order->order_id . '.pdf');
    }

    public function exportOrders()
    {
        return Excel::download(new OrdersExport, 'orders.xlsx');
    }

    public function edit($id)
    {
        $order = Order::findOrFail($id);
        return view('admin.edit_order', compact('order'));
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $order->customer_name = $request->input('customer_name');
        $order->customer_email = $request->input('customer_email');
        $order->phone = $request->input('phone');
        $order->company_name = $request->input('company_name');
        $order->website_url = $request->input('website_url');
        $order->plan = $request->input('plan');
        $order->strategy = $request->input('strategy');
        $order->total_amount = $request->input('total_amount');

        $order->save();
        $this->logAction($id, 'Update', 'Order details updated');

        return redirect('admin')->with('success', 'Order Updated!');
    }

    // --- Settings ---
    public function settings()
    {
        if (!Session::get('logged_in'))
            return redirect('/admin/login');

        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
