@if(isset($orders) && count($orders) > 0)
    @foreach($orders as $order)
        <tr class="border-b border-white/5 hover:bg-white/5 transition group">
            <td class="p-4">
                <input type="checkbox" name="order_ids[]" value="{{ $order->id }}"
                    class="order-checkbox rounded bg-white/10 border-white/20 text-brand-red focus:ring-0 cursor-pointer"
                    onchange="updateBatchBar()">
            </td>
            <td class="p-4 font-mono text-gray-400">{{ $order->order_id }}</td>
            <td class="p-4">
                <div class="font-bold text-white">{{ $order->customer_name }}</div>
                <div class="text-xs text-gray-500">{{ $order->customer_email }}</div>
            </td>
            <td class="p-4">
                <span class="px-2 py-1 rounded text-xs font-bold uppercase bg-white/5 text-white">{{ $order->plan }}</span>
                <div class="mt-1 text-gray-400 font-mono">RM {{ number_format($order->total_amount) }}</div>
            </td>
            <td class="p-4">
                @if($order->status == 'Paid')
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-500/10 text-green-500 border border-green-500/20">
                        <i class="fas fa-check-circle text-[10px]"></i> Completed
                    </span>
                @elseif($order->status == 'Rejected')
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500/10 text-red-500 border border-red-500/20">
                        <i class="fas fa-times-circle text-[10px]"></i> Cancelled
                    </span>
                @elseif($order->status == 'Processing')
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-500/10 text-orange-500 border border-orange-500/20">
                        <i class="fas fa-spinner fa-spin text-[10px]"></i> Processing
                    </span>
                @else
                    <span
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-500/10 text-yellow-500 border border-yellow-500/20">
                        <i class="fas fa-clock text-[10px]"></i> Pending
                    </span>
                @endif
            </td>
            <td class="p-4 text-gray-500">{{ $order->created_at->format('M d, H:i') }}</td>
            <td class="p-4 text-right">
                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition">
                    <!-- View Invoice (PDF) -->
                    <a href="{{ url('admin/invoice/' . $order->id . '/download') }}"
                        class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white flex items-center justify-center transition"
                        title="Download Invoice">
                        <i class="fas fa-file-pdf"></i>
                    </a>

                    <!-- Edit -->
                    <a href="{{ url('admin/edit/' . $order->id) }}"
                        class="w-8 h-8 rounded-lg bg-brand-gray/50 hover:bg-brand-gray text-gray-300 hover:text-white flex items-center justify-center transition"
                        title="Edit Order">
                        <i class="fas fa-edit"></i>
                    </a>

                    @if($order->status == 'Pending')
                        <!-- Mark Pending -> Processing -->
                        <a href="{{ url('admin/paid/' . $order->id) }}"
                            class="w-8 h-8 rounded-lg bg-green-500/10 hover:bg-green-500 text-green-500 hover:text-white flex items-center justify-center transition border border-green-500/20"
                            title="Accept (Move to Processing)">
                            <i class="fas fa-check-circle"></i>
                        </a>

                        <!-- Reject -->
                        <a href="{{ url('admin/reject/' . $order->id) }}"
                            onclick="return confirm('Are you sure you want to reject this order?')"
                            class="w-8 h-8 rounded-lg bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white flex items-center justify-center transition border border-red-500/20"
                            title="Reject Order">
                            <i class="fas fa-times-circle"></i>
                        </a>
                    @elseif($order->status == 'Processing')
                        <!-- Mark Processing -> Completed -->
                        <a href="{{ url('admin/completed/' . $order->id) }}"
                            class="w-8 h-8 rounded-lg bg-blue-500/10 hover:bg-blue-500 text-blue-500 hover:text-white flex items-center justify-center transition border border-blue-500/20"
                            title="Mark Completed">
                            <i class="fas fa-rocket"></i>
                        </a>
                    @endif
                </div>
            </td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="7" class="p-8 text-center text-gray-500">
            <i class="fas fa-inbox text-2xl mb-2 opacity-50"></i>
            <p>No orders found.</p>
        </td>
    </tr>
@endif