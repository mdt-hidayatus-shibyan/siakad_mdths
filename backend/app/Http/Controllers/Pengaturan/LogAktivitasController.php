<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class LogAktivitasController extends Controller
{
    /**
     * TAMPILKAN DAFTAR LOG AKTIVITAS & MONITORING SESI
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filterPlatform = $request->input('platform');
        $filterEvent = $request->input('event');
        $filterStatus = $request->input('status');
        $filterDateRange = $request->input('date_range', 'all');
        $filterRole = $request->input('role');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // 1. Hitung Ringkasan Metrik Cepat
        $todayStart = Carbon::today()->startOfDay();
        $totalLogs = ActivityLog::count();
        $todayLogs = ActivityLog::where('created_at', '>=', $todayStart)->count();
        $todayUstadzLogs = ActivityLog::where('platform', 'app_ustadz')
            ->where('event', 'login')
            ->where('status', 'success')
            ->where('created_at', '>=', $todayStart)
            ->count();
        $todayWebLogs = ActivityLog::where('platform', 'web')
            ->where('event', 'login')
            ->where('status', 'success')
            ->where('created_at', '>=', $todayStart)
            ->count();
        $failedLogs = ActivityLog::where('status', 'failed')
            ->where('created_at', '>=', $todayStart)
            ->count();

        // 2. Query Data Log dengan Filter
        $query = ActivityLog::with(['user.roles', 'user.ustadz', 'user.administrator'])
            ->when($search, function ($q, $search) {
                return $q->search($search);
            })
            ->when($filterPlatform && $filterPlatform !== 'all', function ($q) use ($filterPlatform) {
                return $q->where('platform', $filterPlatform);
            })
            ->when($filterEvent && $filterEvent !== 'all', function ($q) use ($filterEvent) {
                return $q->where('event', $filterEvent);
            })
            ->when($filterStatus && $filterStatus !== 'all', function ($q) use ($filterStatus) {
                return $q->where('status', $filterStatus);
            })
            ->when($filterRole, function ($q, $filterRole) {
                return $q->whereHas('user.roles', function ($rq) use ($filterRole) {
                    $rq->where('name', $filterRole);
                });
            });

        // Filter Rentang Tanggal
        if ($filterDateRange === 'today') {
            $query->where('created_at', '>=', Carbon::today()->startOfDay());
        } elseif ($filterDateRange === 'yesterday') {
            $query->whereBetween('created_at', [
                Carbon::yesterday()->startOfDay(),
                Carbon::yesterday()->endOfDay(),
            ]);
        } elseif ($filterDateRange === '7days') {
            $query->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay());
        } elseif ($filterDateRange === '30days') {
            $query->where('created_at', '>=', Carbon::now()->subDays(30)->startOfDay());
        } elseif ($filterDateRange === 'custom' && $startDate && $endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);
        }

        // 3. Sorting & Pagination
        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // 4. Master Data untuk Dropdown Filter
        $roles = Role::orderBy('name', 'asc')->get();

        // 5. Response AJAX Grid Refresh
        if ($request->ajax() && !$request->has('modal')) {
            return response()->json([
                'html' => view('log-aktivitas.list', compact('logs'))->render(),
                'metrics' => [
                    'total'        => $totalLogs,
                    'today'        => $todayLogs,
                    'today_ustadz' => $todayUstadzLogs,
                    'today_web'    => $todayWebLogs,
                    'failed'       => $failedLogs,
                ]
            ]);
        }

        return view('log-aktivitas.index', compact(
            'logs',
            'roles',
            'totalLogs',
            'todayLogs',
            'todayUstadzLogs',
            'todayWebLogs',
            'failedLogs',
            'search',
            'filterPlatform',
            'filterEvent',
            'filterStatus',
            'filterDateRange',
            'filterRole',
            'startDate',
            'endDate'
        ));
    }

    /**
     * MODAL DETAIL LOG AKTIVITAS (AJAX)
     */
    public function show($id)
    {
        $log = ActivityLog::with(['user.roles', 'user.ustadz', 'user.administrator'])->findOrFail($id);

        return view('log-aktivitas.detail-modal', compact('log'));
    }

    /**
     * HAPUS 1 REKAMAN LOG
     */
    public function destroy(Request $request, $id)
    {
        $log = ActivityLog::findOrFail($id);
        $log->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Rekaman log aktivitas berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Rekaman log aktivitas berhasil dihapus.');
    }

    /**
     * BERSIHKAN LOG KADALUARSA (PRUNE / RETENTION)
     */
    public function clearOld(Request $request)
    {
        $request->validate([
            'period' => 'required|in:30,60,90,all',
        ]);

        $period = $request->input('period');
        $query = ActivityLog::query();

        if ($period !== 'all') {
            $days = (int) $period;
            $cutoff = Carbon::now()->subDays($days);
            $deletedCount = ActivityLog::where('created_at', '<', $cutoff)->delete();
            $msg = "Berhasil menghapus {$deletedCount} rekaman log yang lebih lama dari {$days} hari.";
        } else {
            $deletedCount = ActivityLog::count();
            ActivityLog::truncate();
            $msg = "Seluruh rekaman log aktivitas ({$deletedCount} data) berhasil dibersihkan.";
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }
}
