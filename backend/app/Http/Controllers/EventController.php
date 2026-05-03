<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        return Event::where('user_id', $request->user_id)
            ->orderBy('date')
            ->orderBy('time')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'date' => 'required|date',
            'time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string',
            'color' => 'nullable|string',
            'done' => 'boolean',
            'user_id' => 'required|integer',
        ]);

        return Event::create($data);
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'date' => 'required|date',
            'time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string',
            'color' => 'nullable|string',
            'done' => 'boolean',
        ]);

        $event->update($data);

        return $event;
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return response()->json(['message' => 'Deleted']);
    }
}