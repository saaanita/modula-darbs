<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        return response()->json(Event::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'day' => 'required|integer|min:1|max:31',
            'month' => 'required|integer|min:0|max:11',
            'year' => 'required|integer',
            'time' => 'nullable|date_format:H:i',
            'is_all_day' => 'required|boolean',
            'user_id' => 'nullable|integer',
            'group_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
        ]);

    if ($validated['is_all_day']) {
        $validated['time'] = null;
    }
        
    $event = Event::create($validated);

        return response()->json($event, 201);
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'day' => 'required|integer|min:1|max:31',
            'month' => 'required|integer|min:0|max:11',
            'year' => 'required|integer',
            'time' => 'nullable|date_format:H:i',
            'is_all_day' => 'required|boolean',
            'user_id' => 'nullable|integer',
            'group_id' => 'nullable|integer',
            'category_id' => 'nullable|integer',
        ]);
        
        if ($validated['is_all_day']) {
            $validated['time'] = null;
        }
        
        $event->update($validated);

        return response()->json($event);
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return response()->json([
            'message' => 'Notikums izdzēsts'
        ]);
    }
}