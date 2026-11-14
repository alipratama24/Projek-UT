<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\StudioSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminScheduleController extends Controller
{
    /**
     * Tampilkan kalender dan jadwal studio
     */
    public function index(Request $request)
    {
        $selectedMonth = $request->query('bulan', Carbon::now()->format('Y-m'));
        try {
            $monthDate = Carbon::createFromFormat('Y-m-d', $selectedMonth.'-01');
        } catch (\Exception $e) {
            $monthDate = Carbon::now()->startOfMonth();
            $selectedMonth = $monthDate->format('Y-m');
        }

        $slots = StudioSlot::with('order')
            ->whereYear('date', $monthDate->year)
            ->whereMonth('date', $monthDate->month)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Hitung ringkasan
        $totalSlots = $slots->count();
        $availableSlots = $slots->where('type', 'available')->count();
        $bookedSlots = $slots->where('type', 'booked')->count();
        $blockedSlots = $slots->where('type', 'blocked')->count();

        // Ambil juga pesanan yang memiliki session_date pada bulan ini
        $monthOrders = Order::whereYear('session_date', $monthDate->year)
            ->whereMonth('session_date', $monthDate->month)
            ->orderBy('session_date')
            ->get();

        return view('admin.jadwal', compact(
            'slots',
            'monthDate',
            'selectedMonth',
            'totalSlots',
            'availableSlots',
            'bookedSlots',
            'blockedSlots',
            'monthOrders'
        ));
    }

    /**
     * Simpan slot baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'type' => 'required|in:available,blocked,booked',
            'note' => 'nullable|string|max:255',
        ]);

        StudioSlot::create([
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'type' => $validated['type'],
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()->route('admin.jadwal', ['bulan' => Carbon::parse($validated['date'])->format('Y-m')])
            ->with('success', 'Slot studio pada '.$validated['date'].' ('.$validated['start_time'].'–'.$validated['end_time'].') berhasil ditambahkan.');
    }

    /**
     * Update slot yang ada
     */
    public function update(Request $request, $id)
    {
        $slot = StudioSlot::findOrFail($id);

        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'type' => 'required|in:available,blocked,booked',
            'note' => 'nullable|string|max:255',
        ]);

        $slot->update($validated);

        return redirect()->route('admin.jadwal', ['bulan' => Carbon::parse($slot->date)->format('Y-m')])
            ->with('success', 'Perubahan slot studio berhasil disimpan.');
    }

    /**
     * Hapus slot
     */
    public function destroy($id)
    {
        $slot = StudioSlot::findOrFail($id);
        $dateStr = $slot->date;
        $slot->delete();

        return redirect()->route('admin.jadwal', ['bulan' => Carbon::parse($dateStr)->format('Y-m')])
            ->with('success', 'Slot studio berhasil dihapus.');
    }
}
