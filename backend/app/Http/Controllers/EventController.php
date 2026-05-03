<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'search' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'status' => ['nullable', Rule::in(['done', 'open'])],
            'sort_by' => ['nullable', Rule::in(['title', 'date', 'time', 'priority', 'done', 'created_at'])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $query = Event::with('user:id,username,email,role');

        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        if (!empty($data['search'])) {
            $search = $data['search'];

            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('email', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($data['date_from'])) {
            $query->whereDate('date', '>=', $data['date_from']);
        }

        if (!empty($data['date_to'])) {
            $query->whereDate('date', '<=', $data['date_to']);
        }

        if (!empty($data['priority'])) {
            $query->where('priority', $data['priority']);
        }

        if (!empty($data['status'])) {
            $query->where('done', $data['status'] === 'done');
        }

        $sortBy = $data['sort_by'] ?? 'date';
        $sortDir = $data['sort_dir'] ?? 'asc';

        return $query
            ->orderBy($sortBy, $sortDir)
            ->when($sortBy !== 'time', fn ($query) => $query->orderBy('time', $sortDir))
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'date' => 'required|date',
            'time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'color' => 'nullable|string',
            'done' => 'boolean',
        ]);

        $data['user_id'] = $request->user()->id;

        return Event::create($data);
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $this->ensureCanManage($request, $event);

        $data = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'date' => 'required|date',
            'time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            'color' => 'nullable|string',
            'done' => 'boolean',
        ]);

        $event->update($data);

        return $event;
    }

    public function destroy(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $this->ensureCanManage($request, $event);

        $event->delete();

        return response()->json(['message' => 'Notikums izdzēsts']);
    }

    public function stats(Request $request)
    {
        $query = Event::query();

        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        $today = Carbon::today()->toDateString();

        $monthExpression = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', date)"
            : "DATE_FORMAT(date, '%Y-%m')";

        return response()->json([
            'total' => (clone $query)->count(),
            'today' => (clone $query)->whereDate('date', $today)->count(),
            'upcoming' => (clone $query)->whereDate('date', '>=', $today)->count(),
            'done' => (clone $query)->where('done', true)->count(),
            'open' => (clone $query)->where('done', false)->count(),
            'by_priority' => (clone $query)
                ->selectRaw('priority, count(*) as total')
                ->groupBy('priority')
                ->pluck('total', 'priority'),
            'by_month' => (clone $query)
                ->selectRaw("{$monthExpression} as month, count(*) as total")
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month'),
        ]);
    }

    private function ensureCanManage(Request $request, Event $event): void
    {
        if ($request->user()->role === 'admin') {
            return;
        }

        abort_unless($event->user_id === $request->user()->id, 403, 'Nav tiesību mainīt šo notikumu');
    }
}
